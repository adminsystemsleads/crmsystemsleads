<?php
/**
 * Mezcla traducciones (clave en español) en lang/en.json y lang/pt.json.
 * Uso: C:\xampp\php\php.exe scripts/merge_translations.php
 *
 * Convención: la clave es el texto en español tal como aparece en __('...').
 * - es.json NO necesita estas claves (el fallback devuelve la propia clave = español).
 * - en.json: clave_es -> inglés ; pt.json: clave_es -> portugués
 */

$LANG = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lang';

// clave_es => [ingles, portugues]
$TRANSLATIONS = [
    // --- Menú lateral ---
    'Panel Principal'             => ['Main Panel', 'Painel Principal'],
    'Mi Perfil'                   => ['My Profile', 'Meu Perfil'],
    'WhatsApp'                    => ['WhatsApp', 'WhatsApp'],
    'CRM'                         => ['CRM', 'CRM'],
    'Contactos'                   => ['Contacts', 'Contatos'],
    'Super Administrador'         => ['Super Administrator', 'Super Administrador'],
    'Generar Códigos de Licencia' => ['Generate License Codes', 'Gerar Códigos de Licença'],
    'Administración'              => ['Administration', 'Administração'],
    'Importar Reporte'            => ['Import Report', 'Importar Relatório'],
    'Lista Gastos'                => ['Expenses List', 'Lista de Despesas'],
    'Perfiles'                    => ['Profiles', 'Perfis'],
    'Categorías de Pago'          => ['Payment Categories', 'Categorias de Pagamento'],
    'WhatsApp Cuentas'            => ['WhatsApp Accounts', 'Contas do WhatsApp'],
    'Licencia'                    => ['License', 'Licença'],
    'Módulos'                     => ['Modules', 'Módulos'],
    'General'                     => ['General', 'Geral'],
    'Cuenta'                      => ['Account', 'Conta'],
    'Configuración'               => ['Settings', 'Configurações'],
    'Agregar usuario nuevo'       => ['Add new user', 'Adicionar novo usuário'],
    'Permisos de Acceso CRM'      => ['CRM Access Permissions', 'Permissões de Acesso CRM'],
    'Campos personalizados'       => ['Custom Fields', 'Campos personalizados'],
    'Tema'                        => ['Theme', 'Tema'],
    'Cerrar sesión'               => ['Log Out', 'Sair'],
    'Ocultar menú'                => ['Hide menu', 'Ocultar menu'],
    'Mostrar menú'                => ['Show menu', 'Mostrar menu'],
    'Soporte'                     => ['Support', 'Suporte'],

    // --- Campana de notificaciones ---
    'Notificaciones'              => ['Notifications', 'Notificações'],
    'Marcar todas como leídas'    => ['Mark all as read', 'Marcar todas como lidas'],
    'No tienes notificaciones'    => ['You have no notifications', 'Você não tem notificações'],
    'Nueva negociación asignada'  => ['New deal assigned', 'Nova negociação atribuída'],
    'Se te asignó una negociación'=> ['A deal was assigned to you', 'Uma negociação foi atribuída a você'],
    'Actividad por vencer'        => ['Upcoming activity', 'Atividade a vencer'],
    'Configurar notificaciones'   => ['Notification settings', 'Configurações de notificações'],
    'Silenciar'                   => ['Mute', 'Silenciar'],
    'Activar sonido'              => ['Enable sound', 'Ativar som'],
    'Volver'                      => ['Back', 'Voltar'],
    'Activar notificaciones'      => ['Enable notifications', 'Ativar notificações'],
    'Sonido'                      => ['Sound', 'Som'],
    'Notificar negociaciones asignadas' => ['Notify assigned deals', 'Notificar negociações atribuídas'],
    'Notificar actividades por vencer'  => ['Notify upcoming activities', 'Notificar atividades a vencer'],
    'Embudos a notificar'         => ['Pipelines to notify', 'Funis a notificar'],
    'Ninguno'                     => ['None', 'Nenhum'],
    'Todos'                       => ['All', 'Todos'],
    'No hay embudos disponibles.' => ['No pipelines available.', 'Não há funis disponíveis.'],
    'Guardar'                     => ['Save', 'Salvar'],
];

function merge_lang(string $path, array $translations, int $idx): void
{
    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) {
        fwrite(STDERR, "ERROR: no se pudo leer $path\n");
        exit(1);
    }
    $added = 0;
    foreach ($translations as $es => $vals) {
        $val = $vals[$idx];
        if (!array_key_exists($es, $data) || $data[$es] !== $val) {
            $data[$es] = $val;
            $added++;
        }
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    file_put_contents($path, $json . "\n");
    printf("%s: %d claves agregadas/actualizadas, total %d\n", basename($path), $added, count($data));
}

// Carga traducciones adicionales generadas por el workflow (es => [en, pt]).
$extraPath = __DIR__ . DIRECTORY_SEPARATOR . 'translations_extra.json';
if (is_file($extraPath)) {
    $extra = json_decode(file_get_contents($extraPath), true);
    if (is_array($extra)) {
        foreach ($extra as $es => $vals) {
            // Acepta tanto [en, pt] como {"en":..,"pt":..}
            if (isset($vals['en']) || isset($vals['pt'])) {
                $vals = [$vals['en'] ?? $es, $vals['pt'] ?? $es];
            }
            $TRANSLATIONS[$es] = [$vals[0] ?? $es, $vals[1] ?? $es];
        }
        printf("Cargadas %d traducciones extra desde translations_extra.json\n", count($extra));
    }
}

merge_lang($LANG . DIRECTORY_SEPARATOR . 'en.json', $TRANSLATIONS, 0);
merge_lang($LANG . DIRECTORY_SEPARATOR . 'pt.json', $TRANSLATIONS, 1);
echo "Listo.\n";
