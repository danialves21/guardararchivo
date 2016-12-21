<?php
// Funciones
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . "/local/guardararchivo/forms/guardararchivo_form.php");
global $PAGE, $CFG, $OUTPUT, $DB, $USER;
require_login();
if (isguestuser()) {
	die();
}

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
$action = optional_param('action','viewfiles',PARAM_TEXT);

$mform = new guardararchivo_subirarchivo_form();

if ($action == 'addfiles') {
	if ($mform->is_cancelled()) { //si se presiona boton cancelar
		$home = new moodle_url('/my/');
		redirect($home);
	} else if ($mform->get_data()) { //se procesan datos validados
		require_capability('local/guardararchivo:upload', $context);
	
		$data = $mform->get_data();
	
		$path = $CFG->dataroot. "/temp/local/guardararchivo";
		if(!file_exists($path . "/unread/")) {
			mkdir($path . "/unread/", 0777, true);
		}
		//Guardar archivo
		$filename = $mform->get_new_filename('userfile');
		$file = $mform->save_file('userfile', $path."/unread/".$filename,false);
		$time = strtotime(date("d-m-Y H:s:i"));
	
		//validar que se subió bien el archivo
		$uploadfile = $path . "/unread/".$data->filename;
		
		//Datos del archivo
		$file_record = array(
						'contextid' => $context->id,
						'component' => 'local_guardararchivo',
						'filearea' => 'draft',
						'itemid' => 0,
						'filepath' => '/',
						'filename' => $data->filename,
						'timecreated' => time(),
						'timemodified' => time(),
						'userid' => $USER->id,
						'author' => $USER->firstname." ".$USER->lastname,
						'license' => 'allrightsreserved'
		);
				
		//Información del nuevo archivo
		$fileinfo = $fs->create_file_from_pathname($file_record, $uploadfile);
	
		//Se cambia valor al action
		$action = 'viewfiles';
	}
}

//Muestra el formulario
if ($action == 'addfiles') {
	$mform->display();
} else if ($action == 'viewfiles') {
	echo "hola moodle";//solo prueba
	$this->add_action_buttons(null,'Subir nuevo archivo');
}

//Siempre al final
echo $OUTPUT->footer();