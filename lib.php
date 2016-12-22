<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * Library of interface functions and constants for module emarking
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the emarking specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package local_paperattendance
 * @copyright 2016 Hans Jeria (hansjeria@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function local_guardararchivo_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
	global $DB, $CFG, $USER;

	require_login();
	$filename = array_pop($args);
	$itemid = array_pop($args);
	//$contextcategory = context_coursecat::instance($course->category);
	//$contextcourse = context_course::instance($course->id);
	/*
	// Security! We always protect the exams filearea.
	if ($filearea === 'exams') {
	send_file_not_found();
	}
	if ($filearea === 'pages') {
	$parts = explode('-', $filename);
	if (count($parts) != 3) {
	send_file_not_found();
	}
	if (! ($parts [0] === intval($parts [0]) . "") || ! ($parts [1] === intval($parts [1]) . "")) {
	send_file_not_found();
	}
	$subparts = explode('.', $parts [2]);
	$isanonymous = substr($subparts [0], - strlen('_a')) === '_a';
	$imageuser = intval($parts [0]);
	$usercangrade = has_capability('mod/emarking:grade', $context);
	$bothenrolled = is_enrolled($contextcourse) && is_enrolled($contextcourse, $imageuser);
	if ($USER->id != $imageuser && // If user does not owns the image.
	! $usercangrade && // And can not grade.
	! $isanonymous && // And we are not in anonymous mode.
	! is_siteadmin($USER) && // And the user is not admin.
	! $bothenrolled) {
	send_file_not_found();
	}
	}
	if ($filearea === 'response') {
	$parts = explode('_', $filename);
	if (count($parts) != 3) {
	send_file_not_found();
	}
	if (! ($parts [0] === "response") || ! ($parts [1] === intval($parts [1]) . "")) {
	send_file_not_found();
	}
	$subparts = explode('.', $parts [2]);
	$studentid = intval($subparts [0]);
	$emarkingid = intval($parts [1]);
	if (! $emarking = $DB->get_record('emarking', array(
	'id' => $emarkingid))) {
	send_file_not_found();
	}
	if ($studentid != $USER->id && ! is_siteadmin($USER) && ! has_capability('mod/emarking:supervisegrading', $context)) {
	send_file_not_found();
	}
	}
	if ($filearea === 'examstoprint') {
	if (! has_capability('mod/emarking:downloadexam', $contextcategory)) {
	// Add to Moodle log so some auditing can be done.
	\mod_emarking\event\invalidaccessdownload_attempted::create_from_exam($exam, $contextcourse)->trigger();
	send_file_not_found();
	}
	$token = required_param('token', PARAM_INT);
	if ($token > 9999 && $_SESSION [$USER->sesskey . "smstoken"] === $token) {
	if (! $exam = $DB->get_record('emarking_exams', array(
	'emarking' => $itemid))) {
	send_file_not_found();
	}
	$now = new DateTime();
	$tokendate = new DateTime();
	$tokendate->setTimestamp($_SESSION [$USER->sesskey . "smsdate"]);
	$diff = $now->diff($tokendate);
	if ($diff->i > 5 && false) {
	// Add to Moodle log so some auditing can be done.
	\mod_emarking\event\invalidtokendownload_attempted::create_from_exam($exam, $contextcourse)->trigger();
	send_file_not_found();
	}
	// Everything is fine, now we update the exam status and deliver the file.
	$exam->status = EMARKING_EXAM_SENT_TO_PRINT;
	$DB->update_record('emarking_exams', $exam);
	} else {
	// Add to Moodle log so some auditing can be done.
	\mod_emarking\event\invalidtokendownload_attempted::create_from_exam($exam, $contextcourse)->trigger();
	send_file_not_found();
	}
	// Notify everyone that the exam was downloaded.
	emarking_send_examdownloaded_notification($exam, $course, $USER);
	// Add to Moodle log so some auditing can be done.
	\mod_emarking\event\exam_downloaded::create_from_exam($exam, $contextcourse)->trigger();
	}
	*/
	$fs = get_file_storage();
	if (! $file = $fs->get_file($context->id, 'local_guardararchivo', $filearea, $itemid, '/', $filename)) {
		echo $context->id . ".." . $filearea . ".." . $itemid . ".." . $filename;
		echo "File really not found";
		send_file_not_found();
	}
	send_file($file, $filename);
}