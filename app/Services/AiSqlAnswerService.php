<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiSqlAnswerService
{
    /**
     * Genera respuesta sobre gastos del team del usuario.
     */
    public function answer(array $params): array
    {
        $user     = Auth::user();
        $team     = $user->currentTeam;
        $question = trim($params['question'] ?? '');

        if (!$team || $question === '') {
            return [
                'answer'   => 'Debes indicar una pregunta y tener un team activo.',
                'sql'      => null,
                'rows'     => [],
                'raw_json' => null,
            ];
        }

        // Esquema simple: tablas relevantes y columnas permitidas
        $schema = <<<SCHEMA
Tablas principales (campos relevantes):

users
- id (PK), name, email

teams
- id (PK), name

memberships
- id (PK)
- user_id (FK → users.id)
- team_id (FK → teams.id)
- role (string)  // admin|editor|...

team_member_profiles
- id (PK)
- team_id (FK → teams.id)
- user_id (FK → users.id)          // puede ser null si perfil genérico del equipo
- perfil (string)                   // p.ej. 'propietario' o 'residente'
- unidad (string)                   // ej: '502', 'A-302'
- correo, telefono, notas
- created_at, updated_at

categorias
- id (PK)
- team_id (FK → teams.id)
- nombre (string)
- tipo (enum: 'INGRESOS', 'GASTOS FIJOS', 'GASTOS VARIABLES', 'OTROS')

gasto_mensuales g
- id (PK)
- user_id (FK → users.id)                      // usuario que registró
- team_id (FK → teams.id)                      // equipo dueño del gasto
- team_member_profile_id (FK → team_member_profiles.id) // null = "General/sin unidad"
- categoria_id (FK → categorias.id)
- mes (varchar)                                // puede ser 'Octubre' o '10'
- año (int)
- codigopago (string)      // opcional
- dia_pago (date)          // opcional
- link_vaucher (string)    // opcional
- monto_pagar (decimal(10,2))
- pago_verificado (tinyint[0|1])
- descripcion (text)       // opcional
- created_at, updated_at

Relaciones típicas:
- g.team_id = teams.id
- g.categoria_id = categorias.id AND categorias.team_id = g.team_id
- g.team_member_profile_id = team_member_profiles.id (cuando no es null)
- Para filtrar por unidad: JOIN team_member_profiles p ON p.id = g.team_member_profile_id AND p.unidad = '...'
- Para “general/sin unidad”: WHERE g.team_member_profile_id IS NULL
- Siempre restringir por `g.team_id = :teamId`
SCHEMA;

        // Pedimos a la IA que primero produzca SQL (y una explicación)
        $system = "Eres un asistente SQL. Tu tarea: traducir preguntas del usuario sobre el esquema dado y devolver un JSON con:\n".
                  "- sql: SELECT ...\n".
                  "- explanation_es: breve explicación en español del resultado.\n".
                  "No inventes tablas ni columnas. Nunca dañes datos. Siempre filtra por team_id={$team->id}.";

        $userPrompt = "Pregunta del usuario: \"{$question}\"\n\n$schema\n\n".
                      "Devuélveme un JSON con las claves EXACTAS: {\"sql\": \"...\", \"explanation_es\": \"...\"}.";

        // Llamada a OpenAI vía HTTP client de Laravel (sin paquetes adicionales)
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return [
                'answer'   => 'Falta configurar OPENAI_API_KEY en .env',
                'sql'      => null,
                'rows'     => [],
                'raw_json' => null,
            ];
        }

        // Puedes usar el endpoint nuevo de "responses" o el clásico chat completions.
        // Aquí uso chat completions para máxima compatibilidad:
        $resp = Http::withToken($apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
                'temperature' => 0.2,
            ]);

        if (!$resp->ok()) {
            return [
                'answer'   => 'No pude consultar el modelo de IA.',
                'sql'      => null,
                'rows'     => [],
                'raw_json' => $resp->body(),
            ];
        }

        $content = data_get($resp->json(), 'choices.0.message.content');
        // Intentamos extraer un JSON (la IA puede envolverlo en texto)
        $json = $this->extractJson($content);

        $rawSql      = $json['sql'] ?? null;
        $explanation = $json['explanation_es'] ?? 'Resultado de la consulta.';

        if (!$rawSql) {
            return [
                'answer'   => "No pude generar SQL a partir de tu pregunta.\n\nRespuesta del modelo:\n{$content}",
                'sql'      => null,
                'rows'     => [],
                'raw_json' => $content,
            ];
        }

        // HARDENING: validación mínima del SQL
        if (!$this->isSelectSafe($rawSql)) {
            return [
                'answer'   => "Por seguridad, solo se aceptan consultas SELECT simples.",
                'sql'      => null,
                'rows'     => [],
                'raw_json' => $content,
            ];
        }

        // Enforce team_id en el WHERE si la IA lo olvidó
        $sql = $this->enforceTeamFilter($rawSql, $team->id);

        // Ejecutamos un SELECT con DB::select (sólo lectura)
        try {
            $rows = DB::select($sql);
        } catch (\Throwable $e) {
            return [
                'answer'   => "Tu consulta generó un error de SQL.\n{$e->getMessage()}",
                'sql'      => $sql,
                'rows'     => [],
                'raw_json' => $content,
            ];
        }

        // Armamos una respuesta amistosa
        $answer = $explanation;
        if (count($rows) === 0) {
            $answer .= "\n\nNo se encontraron resultados.";
        } else {
            $answer .= "\n\nSe encontraron ".count($rows)." filas.";
        }

        return [
            'answer'   => $answer,
            'sql'      => $sql,
            'rows'     => $rows,
            'raw_json' => $content,
        ];
    }

    private function extractJson(?string $text): array
    {
        if (!$text) return [];
        // Busca bloque JSON entre llaves
        if (Str::contains($text, '{')) {
            $start = strpos($text, '{');
            $end   = strrpos($text, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $maybe = substr($text, $start, $end - $start + 1);
                try {
                    $decoded = json_decode($maybe, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($decoded)) return $decoded;
                } catch (\Throwable $e) { /* ignore */ }
            }
        }
        // fallback
        $decoded = json_decode($text, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function isSelectSafe(string $sql): bool
    {
        $s = strtolower($sql);
        if (!Str::startsWith(trim($s), 'select')) return false;
        foreach ([';','--','/*','*/',' drop ',' delete ',' update ',' insert ',' alter '] as $bad) {
            if (Str::contains($s, $bad)) return false;
        }
        return true;
    }

    private function enforceTeamFilter(string $sql, int $teamId): string
    {
        $s = strtolower($sql);
        // Si ya tiene un filtro por g.team_id ó team_id, no tocar
        if (Str::contains($s, 'g.team_id') || Str::contains($s, ' team_id')) {
            return $sql;
        }

        // Si existe WHERE, agregamos AND
        if (Str::contains($s, ' where ')) {
            return preg_replace('/where/i', "WHERE g.team_id = {$teamId} AND", $sql, 1);
        }

        // Si no, insertamos WHERE
        // Si hay 'group by'/'order by'/'limit', insertamos antes
        $insertPos = stripos($sql, 'group by');
        $insertPos = $insertPos === false ? stripos($sql, 'order by') : $insertPos;
        $insertPos = $insertPos === false ? stripos($sql, 'limit')   : $insertPos;

        if ($insertPos !== false) {
            return substr($sql, 0, $insertPos)
                . " WHERE g.team_id = {$teamId} "
                . substr($sql, $insertPos);
        }

        return rtrim($sql) . " WHERE g.team_id = {$teamId}";
    }
}
