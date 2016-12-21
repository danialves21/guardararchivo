<?php
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . "/formslib.php"); //lineas 2 a 4 siempre van!

class guardararchivo_subirarchivo_form extends moodleform {
	public function definition() {
		global $DB, $CFG;
		$mform = $this->_form; // Siempre va!!!
		
		//addElement('tipo de elemento', 'nombre elemento', 'etiqueta elemento')
		//text
		$mform->addElement('text', 'filename', "Nombre del archivo");
		$mform->setType('filename', PARAM_TEXT);
		//regla para cantidad minima de caracteres en el filename
		//$mform->addRule('filename', "Por favor ingrese un nombre de mínimo 4 caracteres", 'minlength', 4, 'client');
		//$mform->addRule('filename', "Por favor ingrese un nombre", 'required', null, 'client');
		
		//filepicker
		$mform->addElement('filepicker', 'userfile', "Subir el archivo");
		$mform->setType('userfile', PARAM_FILE);
		$mform->addRule('userfile', "Por favor suba un archivo antes de continuar", 'required', null, 'client');
		$mform->addElement('hidden', 'action', "addfiles");
			
		//buttons
		$this->add_action_buttons(true, 'Subir'); //Siempre con this!
	}
	
	public function validation($data, $files){
		$errors = array();
		
		$filename = $data['filename'];
		$userfile = $data['userfile'];
		
		if(empty($filename)) {
			$errors['filename'] = "Por favor ingrese un nombre";
		}
		
		if(empty($userfile)) {
			$errors['userfile'] = "Por favor suba un archivo antes de continuar";
		}
		return $errors;
	}
}