<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->dirroot . "/repository/lib.php");

global $CFG, $DB;

$fileid = $_POST['fileid'];
$userid = $_POST['userid'];

$query = $DB->get_records_sql('SELECT * FROM {guardararchivo_archivo} WHERE iduser = ? AND id= ?', array($userid, $fileid));

$download = $query->downloaded;
$download++;

$descarga = new stdClass ();
$descarga->id = $fileid;
$descarga->downloaded = $download;

$DB->update_record('guardararchivo_archivo', $descarga);


