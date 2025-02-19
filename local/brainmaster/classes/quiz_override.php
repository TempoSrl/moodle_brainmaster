<?php
namespace local_brainmaster;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class quiz_override {
    public static function quiz_create_attempt($quiz, $attemptnumber, $lastattempt, $timenow, $ispreview = false, $userid = null) {
        global $USER, $DB;

        if (!$userid) {
            $userid = $USER->id;
        }

        // Intercetta solo il quiz "BrainMaster"
        if ($quiz->name == 'BrainMaster') {
            $newquestions = my_external_service::get_questions($quiz->course, $userid);
            $layout = implode(',', $newquestions);
        } else {
            $layout = quiz_repaginate($quiz->layout, $quiz->questionsperpage, $quiz->shuffleanswers);
        }

        $attempt = new stdClass();
        $attempt->quiz = $quiz->id;
        $attempt->userid = $userid;
        $attempt->attempt = $attemptnumber;
        $attempt->layout = $layout;
        $attempt->timecreated = $timenow;
        $attempt->timemodified = $timenow;
        $attempt->preview = $ispreview ? 1 : 0;

        $attempt->id = $DB->insert_record('quiz_attempts', $attempt);

        return $attempt;
    }
}
