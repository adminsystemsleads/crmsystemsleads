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
    'Marcar todos'                => ['Select all', 'Marcar todos'],
    'Desmarcar todos'             => ['Unselect all', 'Desmarcar todos'],
    'No hay embudos disponibles.' => ['No pipelines available.', 'Não há funis disponíveis.'],
    'Guardar'                     => ['Save', 'Salvar'],
    'Recordatorio'                => ['Reminder', 'Lembrete'],
    'Sin notificación'            => ['No notification', 'Sem notificação'],
    '5 minutos antes'             => ['5 minutes before', '5 minutos antes'],
    '15 minutos antes'            => ['15 minutes before', '15 minutos antes'],
    '30 minutos antes'            => ['30 minutes before', '30 minutos antes'],
    '1 hora antes'                => ['1 hour before', '1 hora antes'],
    '2 horas antes'               => ['2 hours before', '2 horas antes'],
    '3 horas antes'               => ['3 hours before', '3 horas antes'],
    'Editar'                      => ['Edit', 'Editar'],
    'Eliminar'                    => ['Delete', 'Excluir'],
    'Cancelar'                    => ['Cancel', 'Cancelar'],
    'Pendiente'                   => ['Pending', 'Pendente'],
    'Completada'                  => ['Completed', 'Concluída'],
    '¿Eliminar esta actividad?'   => ['Delete this activity?', 'Excluir esta atividade?'],
    'Recordatorios'               => ['Reminders', 'Lembretes'],
    'puedes elegir varios'        => ['you can choose several', 'você pode escolher vários'],
    'Perdida'                     => ['Missed', 'Perdida'],
    'Completar'                   => ['Complete', 'Concluir'],
    'Reporte de Actividades'      => ['Activities Report', 'Relatório de Atividades'],
    'Exportar CSV'                => ['Export CSV', 'Exportar CSV'],
    'Mes de creación'             => ['Creation month', 'Mês de criação'],
    'Fecha de creación'           => ['Creation date', 'Data de criação'],
    'actividades'                 => ['activities', 'atividades'],
    'Negociación'                 => ['Deal', 'Negociação'],
    'Creada'                      => ['Created', 'Criada'],
    'Vence'                       => ['Due', 'Vence'],
    'Tipo'                        => ['Type', 'Tipo'],
    'Aún no hay actividades.'     => ['No activities yet.', 'Ainda não há atividades.'],
    'Creada desde'                => ['Created from', 'Criada desde'],
    'Creada hasta'                => ['Created until', 'Criada até'],
    'Estado, Responsable y Mes permiten varios: Ctrl/Cmd + clic.' => ['Status, Owner and Month allow multiple: Ctrl/Cmd + click.', 'Status, Responsável e Mês permitem vários: Ctrl/Cmd + clique.'],
    'Modo de selección'           => ['Selection mode', 'Modo de seleção'],
    'Selección única'             => ['Single selection', 'Seleção única'],
    'Selección múltiple'          => ['Multiple selection', 'Seleção múltipla'],
    'Lista (múltiple)'            => ['List (multiple)', 'Lista (múltipla)'],
    'Sin opciones'                => ['No options', 'Sem opções'],
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
