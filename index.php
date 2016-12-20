<?php
// Funciones
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . "/local/guardararchivo/forms/guardararchivo_form.php");
global $PAGE, $CFG, $OUTPUT, $DB;
require_login();

// Construcción de la pagina en formato moodle (siempre al inicio)
$url = new moodle_url('/local/guardararchivo/index.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$title = "Subir un archivo";
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $OUTPUT->header();

$mform = new guardararchivo_subirarchivo_form();
$mform->display();

//Siempre al final
echo $OUTPUT->footer();