<?php
// Funciones
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . "/local/guardararchivo/forms/guardararchivo_form.php");
require_once($CFG->dirroot . "/local/guardararchivo/forms/compartirarchivo_form.php");
require_once ($CFG->dirroot . "/repository/lib.php");
global $PAGE, $CFG, $OUTPUT, $DB, $USER;

$action = optional_param("action","viewfiles",PARAM_TEXT);
$edit = optional_param("edit", null, PARAM_INT);


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
		
		//Obtener el archivo
		$fs = get_file_storage();
		
		//Datos del archivo
		$file_record = array(
						"contextid" => $context->id,
						"component" => "local_guardararchivo",
						"filearea" => "draft",
						"itemid" => 0,
						"filepath" => "/",
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
		$DB->insert_record('guardararchivo_archivo', $datos);
		
		//Cambiar valor de action
		$action ="viewfiles";
	}
}


if ($action == "viewfiles") {
	//Sacar datos base de datos
	$results = $DB->get_records_sql('SELECT * FROM {guardararchivo_archivo} WHERE iduser = ?', array($USER->id));
	$fileid = $rec->id;
	//URL's
	$url_add = new moodle_url("/local/guardararchivo/index.php", array("action" => "addfiles"));
	$url_share = new moodle_url("/local/guardararchivo/index.php", array("action" => "sharefile"));
	//Crea tabla
	$newtable = new html_table();
	//Crea titulos tabla
	$newtable->head = array("Archivo","Fecha de subida", "Fecha de edición", "Compartido", "Descargado");
	// llenar tabla
	foreach ($results as $rec) {
		$nombres = $rec->namearchive;
		$file_url = moodle_url::make_pluginfile_url($context->id, "local_guardararchivo", "draft", 0, "/", $nombres);
		$newtable->data[] = array(
								$rec->namearchive,
								date("F j, Y, g:i a", $rec->uploaddate), 
								date("F j, Y, g:i a", $rec->editiondate), 
								$rec->shared, 
								$rec->downloaded,
								$OUTPUT->action_icon($file_url, new pix_icon('i/down', "Descargar")),
								$OUTPUT->action_icon("", new pix_icon('i/edit', "Editar")),
								$OUTPUT->action_icon($url_share, new pix_icon('i/email', "Compartir")),
								html_writer::nonempty_tag("div",$OUTPUT->action_icon("", new pix_icon('i/delete', "Borrar")), array("style" => "height:27px; width:27px"))
						   	);
	}
}

if($action == "sharefile") {
	$shareform = new guardararchivo_compartirarchivo_form();
	
	if ($shareform->is_cancelled()) { //si se presiona boton cancelar
		redirect($home);
	} else if ($shareform->get_data()) {
		$data_share = $shareform->get_data();
		
		
	}
}

echo $OUTPUT->header();

if ($action == "viewfiles") {
	//Muestra tabla
	echo html_writer::table($newtable);
	//botón
	echo html_writer::nonempty_tag("div",$OUTPUT->single_button($url_add,"Subir nuevo archivo"),array("align" => "middle"));
}
//Muestra el formulario
if ($action == "addfiles") {
	$mform->display();
}
if ($action == "sharefile") {
	$shareform->display();
}
//Siempre al final
echo $OUTPUT->footer();