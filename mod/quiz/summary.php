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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page prints a summary of a quiz attempt before it is submitted.
 *
 * @package   mod_quiz
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_quiz\output\navigation_panel_attempt;
use mod_quiz\output\renderer;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$attemptid = required_param('attempt', PARAM_INT); // The attempt to summarise.
$cmid = optional_param('cmid', null, PARAM_INT);


/**
 * Sends result to the external service
 */
function send_answers($attempt_id) {
    global $CFG;
    if (empty($CFG->BrainMasterService)){
        return;
    }            
    $url = $CFG->BrainMasterService."moodle_get_answers"; // URL del web service.

    $data = json_encode([
        'id_attempt' => $attempt_id
    ]);

    // Usa cURL per inviare i dati al web service.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  // http_build_query($data)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 200) {
        // Decodifica la risposta JSON
        $decodedResponse = json_decode($response, true); // Usa true per un array associativo
        # file_put_contents('C:\wamp64\www\moodle\allactivities_log.txt', "got response {$response}". PHP_EOL, FILE_APPEND);  
      
    } else {
        debugging("Brainmaster: Failed to notify web service. Response: $response", DEBUG_DEVELOPER);
        return  null;
    }
}

$PAGE->set_url('/mod/quiz/summary.php', ['attempt' => $attemptid]);
// During quiz attempts, the browser back/forwards buttons should force a reload.
$PAGE->set_cacheable(false);
$PAGE->set_secondary_active_tab("modulepage");

$attemptobj = quiz_create_attempt_handling_errors($attemptid, $cmid);
if ($attemptobj->get_attempt()->action !== null){
    send_answers($attemptid);
}

// Check login.
require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

// Check that this attempt belongs to this user.
if ($attemptobj->get_userid() != $USER->id) {
    if ($attemptobj->has_capability('mod/quiz:viewreports')) {
        redirect($attemptobj->review_url(null));
    } else {
        throw new moodle_exception('notyourattempt', 'quiz', $attemptobj->view_url());
    }
}

// Check capabilites.
if (!$attemptobj->is_preview_user()) {
    $attemptobj->require_capability('mod/quiz:attempt');
}

if ($attemptobj->is_preview_user()) {
    navigation_node::override_active_url($attemptobj->start_attempt_url());
}

// Check access.
$accessmanager = $attemptobj->get_access_manager(time());
$accessmanager->setup_attempt_page($PAGE);
/** @var renderer $output */
$output = $PAGE->get_renderer('mod_quiz');
$messages = $accessmanager->prevent_access();
if (!$attemptobj->is_preview_user() && $messages) {
    throw new \moodle_exception('attempterror', 'quiz', $attemptobj->view_url(),
            $output->access_messages($messages));
}
if ($accessmanager->is_preflight_check_required($attemptobj->get_attemptid())) {
    redirect($attemptobj->start_attempt_url(null));
}

$displayoptions = $attemptobj->get_display_options(false);

// If the attempt is now overdue, or abandoned, deal with that.
$attemptobj->handle_if_time_expired(time(), true);

// If the attempt is already closed, redirect them to the review page.
if ($attemptobj->is_finished()) {
    redirect($attemptobj->review_url());
}

\core\session\manager::keepalive(); // Try to prevent sessions expiring during quiz attempts.

// Arrange for the navigation to be displayed.
if (empty($attemptobj->get_quiz()->showblocks)) {
    $PAGE->blocks->show_only_fake_blocks();
}

$navbc = $attemptobj->get_navigation_panel($output, navigation_panel_attempt::class, -1);
$regions = $PAGE->blocks->get_regions();
$PAGE->blocks->add_fake_block($navbc, reset($regions));

$PAGE->navbar->add(get_string('summaryofattempt', 'quiz'));
$PAGE->set_title($attemptobj->summary_page_title());
$PAGE->set_heading($attemptobj->get_course()->fullname);
$PAGE->activityheader->disable();
// Display the page.
echo $output->summary_page($attemptobj, $displayoptions);

// Log this page view.
$attemptobj->fire_attempt_summary_viewed_event();
