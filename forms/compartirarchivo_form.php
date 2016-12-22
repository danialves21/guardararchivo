<?php
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . "/formslib.php"); //lineas 2 a 4 siempre van!

class guardararchivo_compartirarchivo_form extends moodleform {
	public function definition() {
		global $DB, $CFG;
		$shareform = $this->_form; // Siempre va!!!
		
		//mail
		$shareform->addElement('text', 'email', "Email");
		$shareform->setType('email', PARAM_TEXT);
		$shareform->addRule('email', "Por favor ingrese un email válido", 'required', null, 'client');
		
		//asunto
		$shareform->addElement('text', 'asunto', "Asunto");
		$shareform->setType('asunto', PARAM_TEXT);
		$shareform->addRule('asunto', "Por favor ingrese un asunto", 'required', null, 'client');
		
		//mensaje
		$shareform->addElement('text', 'mensaje', "Mensaje");
		$shareform->setType('mensaje', PARAM_TEXT);
		$shareform->addRule('mensaje', "Por favor ingrese un mensaje", 'required', null, 'client');
		
		$shareform->addElement('hidden', 'action', "sharefile");
		
		$this->add_action_buttons(true, 'Enviar'); //Siempre con this!
	}
	
	public function validation($data, $files){
		$errors = array();
		
		$email = $data['email'];
		$asunto = $data['asunto'];
		$mensaje = $data['mensaje'];
		
		if(empty($email)) {
			$errors['email'] = "Por favor ingrese un email válido";
		}
		
		if(empty($asunto)) {
			$errors['asunto'] = "Por favor ingrese un asunto";
		}
		
		if(empty($mensaje)) {
			$errors['asunto'] = "Por favor ingrese un mensaje";
		}
		
		return $errors;
		
	}
}