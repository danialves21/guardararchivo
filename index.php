<?php
// Functions
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . "/local/guardararchivo/forms/guardararchivo_form.php");
require_once($CFG->dirroot . "/local/guardararchivo/forms/compartirarchivo_form.php");
require_once ($CFG->dirroot . "/repository/lib.php");
global $PAGE, $CFG, $OUTPUT, $DB, $USER;

$action = optional_param("action","viewfiles",PARAM_TEXT);
$id = optional_param("fileid", null, PARAM_INT);
$status = optional_param("status", 1, PARAM_INT);


require_login();
if (isguestuser()) {
	die();
}

// Building moodle page (always at the beggining)
$url = new moodle_url("/local/guardararchivo/index.php");
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin ( 'ui' );
$PAGE->requires->jquery_plugin ( 'ui-css' );
$PAGE->set_url($url);
$PAGE->set_pagelayout("standard");
$title = "Subir un archivo";
$PAGE->set_title($title);
$PAGE->set_heading($title);

if ($action == "addfiles") {
	$mform = new guardararchivo_subirarchivo_form();
	
	if ($mform->is_cancelled()) { //If cancel button is pressed
		$home = new moodle_url("/local/guardararchivo/index.php", array("action" => "viewfiles"));
		redirect($home);
	} else if ($mform->get_data()) { //data process
		$data = $mform->get_data();
	
		$path = $CFG->dataroot. "/temp/local/guardararchivo";
		if(!file_exists($path . "/unread/")) {
			mkdir($path . "/unread/", 0777, true);
		}
		//Get file extension
		$filename = $mform->get_new_filename("userfile");
		$expldeo = explode(".",$filename);
		$totalexp = count($expldeo);
		$extension = $expldeo[$totalexp-1];
		
		//Save file with new name
		$file = $mform->save_file("userfile", $path."/unread/".$data->filename.".".$extension,false);
		
		//Validate that file is up correctly
		$uploadfile = $path . "/unread/".$data->filename.".".$extension;
		
		//Get the file
		$fs = get_file_storage();
		
		//File info
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
		
		//If the file exists, is deleted
		if ($fs->file_exists($context->id,"local_guardararchivo", "draft", 0, "/", $data->filename.".".$extension)) {
			$previousfile = $fs->get_file($context->id, "local_guardararchivo", "draft", 0, "/", $data->filename.".".$extension);
			$previousfile->delete();
		}
		
		//Info of the new file
		$fileinfo = $fs->create_file_from_pathname($file_record, $uploadfile);	
		
		//Isert on mdl_guardararchivo file name and user id
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
		
		//change action value
		$action ="viewfiles";
	}
}


if($action == "deletefile") {
	if ($id == null) {
		echo $OUTPUT->error_text("No existe el archivo que a eliminar");
		$action = "viewfiles";
	} else {
		$delete = new stdClass();
		$delete->id = $id;
		$delete->status = 0;

		$DB->update_record('guardararchivo_archivo', $delete);
		$action = "viewfiles";

	}
}

if($action == "sharefile") {
	$shareform = new guardararchivo_compartirarchivo_form();

	if ($shareform->is_cancelled()) { //If the cancel button is pressed
		$home = new moodle_url("/local/guardararchivo/index.php", array("action" => "viewfiles"));
		redirect($home);
	} else if ($shareform->get_data()) {
		$data_share = $shareform->get_data();

		$email = $data_share->email;
		$asunto = $data_share->asunto;
		$mensaje = $data_share->mensaje;
	}
}

if ($action == "viewfiles") {
	//Get data from db
	$results = $DB->get_records_sql('SELECT * FROM {guardararchivo_archivo} WHERE iduser = ? AND status = ?', array($USER->id, $status));
	
	//URL
	$url_add = new moodle_url("/local/guardararchivo/index.php", array("action" => "addfiles"));
	//Create table
	$newtable = new html_table();
	//Create headers of table
	$newtable->head = array("Archivo","Fecha de subida", "Compartido", "Descargado");
	// Fill table
	foreach ($results as $rec) {
		$fileid = $rec->id;
		$url_share = new moodle_url("/local/guardararchivo/index.php", array("action" => "sharefile", "fileid" => $fileid));
		$url_delete = new moodle_url("/local/guardararchivo/index.php", array("action" => "deletefile", "fileid" => $fileid));
		$nombres = $rec->namearchive;
		$file_url = moodle_url::make_pluginfile_url($context->id, "local_guardararchivo", "draft", 0, "/", $nombres);
		
		$newtable->data[] = array(
								$rec->namearchive,
								date("F j, Y, g:i a", $rec->uploaddate), 
								$rec->shared, 
								$rec->downloaded,
								html_writer::nonempty_tag("div",$OUTPUT->action_icon($file_url, new pix_icon('i/down', "Descargar")), array("class" => "descargar", "fileid" => $fileid)),
								$OUTPUT->action_icon("", new pix_icon('i/edit', "Editar")),
								$OUTPUT->action_icon($url_share, new pix_icon('i/email', "Compartir")),
								html_writer::nonempty_tag("div",$OUTPUT->action_icon($url_delete, new pix_icon('i/delete', "Borrar"),  new confirm_action("¿Estás seguro que quieres eliminar este archivo?")), array("style" => "height:27px; width:27px"))
						   	); 
	}
}

echo $OUTPUT->header();

if ($action == "viewfiles") {
	//Shows table
	echo html_writer::table($newtable);
	//Button
	echo html_writer::nonempty_tag("div",$OUTPUT->single_button($url_add,"Subir nuevo archivo"),array("align" => "middle"));
}
//Shows the form
if ($action == "addfiles") {
	$mform->display();
}
if ($action == "sharefile") {
	$shareform->display();
}
 
//Always at the end
echo $OUTPUT->footer();

?>
<script>
	$(document).ready(function() {
		$(".descargar").on("click", function() {
			var w = $(this).attr("fileid");

			$.ajax({
				type: "POST",
				url:"updatefiles.php",
				dataType: "json",
				data: {'fileid' : w}
			});
		});
	});
</script>