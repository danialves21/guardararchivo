<?php
defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . "/formslib.php");

class guardararchivo_subirarchivo_form extends moodleform {
	public function definition() {
		global $DB, $CFG;
		$mform = $this->_form; // Siempre va!!!
		
		$mform->addElement('text', 'filename', "Nombre del archivo");
		$mform->addElement('filepicker', 'userfile', "Subir el archivo");
		
		$this->add_action_buttons(true, 'Subir'); //Siempre con this!
	}
	
	public function validation(){
		
	}
}