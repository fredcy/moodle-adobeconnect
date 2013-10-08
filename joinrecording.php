<?php // $Id: joinrecording.php,v 1.1.2.1 2011/07/21 22:33:34 adelamarre Exp $

/**
 * The purpose of this file is to add a log entry when the user views a
 * recording
 *
 * @author  Your Name <adelamarre@remote-learner.net>
 * @version $Id: joinrecording.php,v 1.1.2.1 2011/07/21 22:33:34 adelamarre Exp $
 * @package mod/adobeconnect
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/connect_class.php');
require_once(dirname(__FILE__).'/connect_class_dom.php');

$id         = required_param('id', PARAM_INT);
$groupid    = required_param('groupid', PARAM_INT);

// Do the usual Moodle setup
if (! $cm = get_coursemodule_from_id('adobeconnect', $id)) {
    error('Course Module ID was incorrect');
}

if (! $course = get_record('course', 'id', $cm->course)) {
    error('Course is misconfigured');
}

if (! $adobeconnect = get_record('adobeconnect', 'id', $cm->instance)) {
    error('Course module is incorrect');
}

require_login($course, true, $cm);


// ---------- //


global $CFG, $USER;

// Get HTTPS setting
$https      = false;
$protocol   = 'http://';
if (isset($CFG->adobeconnect_https) and (!empty($CFG->adobeconnect_https))) {
    $https      = true;
    $protocol   = 'https://';
}


// Create a Connect Pro login session for this user
$usrobj = new stdClass();
$usrobj = clone($USER);
$login  = $usrobj->username;

$aconnect = new connect_class_dom($CFG->adobeconnect_host, $CFG->adobeconnect_port,
                                  '', '', '', $https);

$aconnect->request_http_header_login(1, $login);
$adobesession = $aconnect->get_cookie();

// Get exact meeting instance
$sql = "SELECT meetingscoid FROM {$CFG->prefix}adobeconnect_meeting_groups amg WHERE ".
       "amg.instanceid = {$cm->instance} AND amg.groupid = $groupid";

$meetscoid = get_record_sql($sql);


// Get the Meeting recording details
$aconnect   = aconnect_login();
$recording  = array();
$fldid      = aconnect_get_folder($aconnect, 'content');

$data = aconnect_get_recordings($aconnect, $fldid, $meetscoid->meetingscoid);

if (!empty($data)) {
    $recording = $data;
}

// If at first you don't succeed ...
$data2 = aconnect_get_recordings($aconnect, $meetscoid->meetingscoid, $meetscoid->meetingscoid);

if (!empty($data2)) {
     $recording = $data2;
}

aconnect_logout($aconnect);

if (empty($recording) and confirm_sesskey()) {
    notify(get_string('errormeeting', 'adobeconnect'));
    die();
}

add_to_log($course->id, 'adobeconnect', 'view',
           "view.php?id=$cm->id", "View recording {$adobeconnect->name} details", $cm->id);

// Include the port number only if it is a port other than 80
$port = '';

if (!empty($CFG->adobeconnect_port) and (80 != $CFG->adobeconnect_port)) {
    $port = ':' . $CFG->adobeconnect_port;
}


redirect($protocol . $CFG->adobeconnect_meethost . $port
                     . $recording->url . '?session=' . $adobesession);

?>