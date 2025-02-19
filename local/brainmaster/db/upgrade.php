// upgrade.php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_yourplugin_upgrade($oldversion) {
    global $DB;

    // Aggiungi il campo "action" se non esiste
    if ($oldversion < 2025021700) {
        // Ottieni la tabella mdl_question_attempt
        $table = new xmldb_table('question_attempt');
        
        // Verifica se il campo "action" non esiste
        if (!$DB->get_manager()->field_exists($table, 'action')) {
            $field = new xmldb_field('action', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timemodified');
            $DB->get_manager()->add_field($table, $field);
        }

        // Salva l'aggiornamento
        upgrade_plugin_savepoint(true, 2025021700, 'local', 'yourplugin');
    }

    return true;
}
