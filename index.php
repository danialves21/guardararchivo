<?php
// Funciones
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . "/local/guardararchivo/forms/guardararchivo_form.php");
require_once ($CFG->dirroot . "/repository/lib.php");
global $PAGE, $CFG, $OUTPUT, $DB, $USER;

$action = optional_param("action","viewfiles",PARAM_TEXT);


require_login();
if (isguestuser()) {
	die();
}

// Construcción de la pagina en formato moodle (siempre al inicio)
$url = new moodle_url("/local/guardararchivo/index.php");
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout("standard");
$title = "Subir un archivo";
$PAGE->set_title($title);
$PAGE->set_heading($title);

if ($action == "addfiles") {
	$mform = new guardararchivo_subirarchivo_form();
	
	if ($mform->is_cancelled()) { //si se presiona boton cancelar
		$home = new moodle_url("/local/guardararchivo/index.php", array("action" => "viewfiles"));
		redirect($home);
	} else if ($mform->get_data()) { //se procesan datos validados
		$data = $mform->get_data();
	
		$path = $CFG->dataroot. "/temp/local/guardararchivo";
		if(!file_exists($path . "/unread/")) {
			mkdir($path . "/unread/", 0777, true);
		}
		//Obtener información de extensión
		$filename = $mform->get_new_filename("userfile");
		$expldeo = explode(".",$filename);
		$totalexp = count($expldeo);
		$extension = $expldeo[$totalexp-1];
		
		//Guardar archivo con nuevo nombre
		$file = $mform->save_file("userfile", $path."/unread/".$data->filename.".".$extension,false);
		
		//validar que se subió bien el archivo
		$uploadfile = $path . "/unread/".$data->filename.".".$extension;
		
		$fs = get_file_storage();
		
		//Datos del archivo
		$file_record = array(
						"contextid" => $context->id,
						"component" => "local_guardararchivo",
						"filearea" => "draft",
						"itemid" => 0,
						"filepath" => $path."/unread/",
						"filename" => $data->filename.".".$extension,
						"timecreated" => time(),
						"timemodified" => time(),
						"userid" => $USER->id,
						"author" => $USER->firstname." ".$USER->lastname,
						"license" => "allrightsreserved"
		);
		
		//Si el archivo ya existe, se elimina
		if ($fs->file_exists($context->id,"local_guardararchivo", "draft", 0, "/", $data->filename.".".$extension)) {
			$previousfile = $fs->get_file($context->id, "local_guardararchivo", "draft", 0, "/", $data->filename.".".$extension);
			$previousfile->delete();
		}
		
		//Información del nuevo archivo
		$fileinfo = $fs->create_file_from_pathname($file_record, $uploadfile);	
		
		//Insertar en mdl_guardararchivo nombre archivo y user id
		$datos = array(
				"namearchive" => $file_record["filename"], 
				"editiondate" => $file_record["timemodified"], 
				"uploaddate" => $file_record["timecreated"], 
				"status" => 1, 
				"shared" => 0, 
				"downloaded" => 0, 
				"path" => $file_record["filepath"], 
				"iduser" => $file_record["userid"]
				
		);
		//echo date("F j, Y, g:i a",$datos["uploaddate"]);
		//$DB->insert_record('guardararchivo_archivo', $datos);
		//Cambiar valor de action
		$action ="viewfiles";
	}
}


if ($action == "viewfiles") {
	$url_add = new moodle_url("/local/guardararchivo/index.php", array("action" => "addfiles"));
	//Crea tabla
	$newtable = new html_table();
	//Crea titulos tabla
	$newtable->head = array("Archivo","Fecha de subida", "Fecha de edición", "Compartido", "Descargado");
	//Sacar datos base de datos
	$DB->get_records_sql('SELECT g.namearchive, g.editiondate, g.uploaddate, g.shared, g.downloaded FROM {guardararchivo_archivo} AS g WHERE g.iduser = ?', array($USER->id));

	// llenar tabla
	
}

echo $OUTPUT->header();

if ($action == "viewfiles") {
	//Muestra tabla
	echo html_writer::table($newtable);
	//botón
	echo $OUTPUT->single_button($url_add,"Subir nuevo archivo");
}
//Muestra el formulario
if ($action == "addfiles") {
	$mform->display();
}


//Siempre al final
echo $OUTPUT->footer();