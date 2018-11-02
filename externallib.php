<?php

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
 * External Web Service for SEB Auth Key
 *
 * @package    localseb_authkey
 * @copyright  2018 ETH Zurich - amr.hourani@id.ethz.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class local_seb_authkey_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function add_seb_key_parameters() {
        return new external_function_parameters(
                array('sebkey' => new external_value(PARAM_TEXT, 'The SEB Key (64 digits SHA256 one-key-per-request).', VALUE_DEFAULT, ''),
                      'quizcmid' => new external_value(PARAM_INT, 'The Quiz course-module-id SEB Key (Can be found in the url /mod/quiz/view.php?id=XXX).', VALUE_DEFAULT, 0)));
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function add_seb_key($sebkey, $quizcmid) {
        global $USER, $DB;

        //Parameter validation
        $params = self::validate_parameters(self::add_seb_key_parameters(), array('sebkey' => $sebkey, 'quizcmid' => $quizcmid));

        //Context validation
        $context = context_system::instance();
        self::validate_context($context);

        //Capability checking
        if (!has_capability('moodle/site:config', $context)) {
            throw new moodle_exception('accessnotallowed');
        }
        //Validate Key style.
        if (!preg_match('~^[a-f0-9]{64}$~', strtolower($sebkey)) || empty(trim($sebkey))) {
            throw new moodle_exception('wrongkeysyntax');
        }
        //Validate Quiz Course Module.
        if (empty($quizcmid) || $quizcmid == 0 || !$quizcmid) {
            throw new moodle_exception('missingquizcmid');
        }

        //Check Quiz Course Module in moodle.
        if (!$cm = get_coursemodule_from_id('quiz', $quizcmid)) {
            throw new moodle_exception('invalidcoursemodule');
        }
        if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
            throw new moodle_exception('coursemisconf');
        }

        //Update record in quizaccess_safeexambrowser
        $record = new stdClass;
        $record->quizid = $cm->instance;
        $record->allowedkeys = strtolower($sebkey);
        //Check if new or existant.
        $safeexambrowser = $DB->get_record('quizaccess_safeexambrowser', array('quizid' => $cm->instance) );
        if ($safeexambrowser) {
            $record->id = $safeexambrowser->id;
            $record->allowedkeys = $safeexambrowser->allowedkeys . PHP_EOL . $record->allowedkeys;
            $result = $DB->update_record('quizaccess_safeexambrowser', $record);
        } else {
            $result = $DB->insert_record('quizaccess_safeexambrowser', $record);
            if(!$result){
              throw new moodle_exception('quizaccess_safeexambrowsernotenabled');
            }
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function add_seb_key_returns() {
        return new external_value(PARAM_TEXT, 'TRUE (1) or FALSE (0/none) will be returned to confirm the addition of the SEB Key for that specific quiz.');
    }



}
