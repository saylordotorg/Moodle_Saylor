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
 * Question type class for the algebra question type.
 *
 * @package    qtype_algebra
 * @author  Roger Moore <rwmoore@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questiontypebase.php');
require_once($CFG->dirroot . '/question/type/algebra/question.php');
require_once($CFG->dirroot . '/question/type/algebra/parser.php');

/**
 * ALGEBRA QUESTION TYPE CLASS
 *
 * @package questionbank
 * @subpackage questiontypes
 */

class qtype_algebra extends question_type {

    /**
     * Defines the table which extends the question table. This allows the base questiontype
     * to automatically save, backup and restore the extra fields.
     *
     * @return an array with the table name (first) and then the column names (apart from id and questionid)
     */
    public function extra_question_fields() {
        return array('qtype_algebra_options',
                     'compareby',        // Name of comparison algorithm to use
                     'nchecks',          // Number of evaluate checks to make when comparing by evaluation
                     'tolerance',        // Max. fractional difference allowed for evaluation checks
                     'allowedfuncs',     // Comma separated list of functions allowed in responses
                     'disallow',         // Response which may be correct but which is not allowed
                     'answerprefix'      // String which is placed in front of the asnwer box.
                     );
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_algebra_options', array('questionid' => $questionid));
        $DB->delete_records('qtype_algebra_variables', array('questionid' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

    /**
     * Saves the questions variables to the database
     *
     * This is called by {@link save_question_options()} to save the variables of the question to
     * the database from the data in the submitted form. The method returns an array of the variables
     * IDs written to the database or, in the event of an error, throws an exception.
     *
     * @param object $question  This holds the information from the editing form,
     *                          it is not a standard question object.
     */
    public function save_question_variables($question) {
        global $DB;
        // Get all the old variables from the database as an array.
        $oldvars = $DB->get_records('qtype_algebra_variables',
                array('questionid' => $question->id), 'id ASC');

        // Loop over all the variables in the question form and write them to the database.
        foreach ($question->variable as $key => $varname) {
            // Check to see that there is a variable and skip any which are empty.
            if ($varname == '') {
                continue;
            }

            // Update an existing variable if possible.
            $variable = array_shift($oldvars);
            if (!$variable) {
                $variable = new stdClass();
                $variable->questionid = $question->id;
                $variable->name = '';
                $variable->min = '';
                $variable->max = '';
                $variable->id = $DB->insert_record('qtype_algebra_variables', $variable);
            }
            $variable->name = trim($varname);
            $variable->min  = trim($question->varmin[$key]);
            $variable->max  = trim($question->varmax[$key]);
            $DB->update_record('qtype_algebra_variables', $variable);
        }   // End loop over variables.

        // Delete any left over old variables records.
        foreach ($oldvars as $oldvar) {
            $DB->delete_records('qtype_algebra_variables', array('id' => $oldvar->id));
        }
    }

    /**
     * Returns true is answer with the $key is empty in the question data and should not be saved in DB.
     * @param object $questiondata This holds the information from the question editing form or import.
     * @param int $key A key of the answer in question.
     * @return bool True if answer shouldn't be saved in DB.
     */
    protected function is_answer_empty($questiondata, $key) {
        return trim($questiondata->answer[$key]) == '';
    }
    /**
     * Saves question-type specific options
     *
     * This is called by {@link save_question()} to save the question-type specific data from a
     * submitted form. This method takes the form data and formats into the correct format for
     * writing to the database. It then calls the parent method to actually write the data.
     *
     * @param object $question  This holds the information from the editing form,
     *                          it is not a standard question object.
     * @return object $result->error or $result->noticeyesno or $result->notice
     */
    public function save_question_options($question) {
        // Start a try block to catch any exceptions generated when we attempt to parse and
        // then add the answers and variables to the database.
        try {
            // First write out all the variables associated with the question.
            $this->save_question_variables($question);

            // Loop over all the answers in the question form and parse them to generate
            // a parser string. This ensures a constant formatting is stored in the database.
            foreach ($question->answer as &$answer) {
                $expr = $this->parse_expression($answer);
                // TODO detect invalid answer and issue a warning.
                $answer = $expr->sage();
            }

            // Now we need to write out all the answers to the question to the database.
            $this->save_question_answers($question);

        } catch (Exception $e) {
            // Error when adding answers or variables to the database so create a result class
            // and put the error string in the error member function and then return the class
            // This keeps us compatible with the existing save_question_options methods.
            $result = new stdClass;
            $result->error = $e->getMessage();
            return $result;
        }
        // Process the allowed functions field. This code just sets up the variable, it is saved
        // in the parent class' save_question_options method called at the end of this method
        // Look for the 'all' option. If we find it then set the string to an empty value.
        if (array_key_exists('all', $question->allowedfuncs)) {
            $question->allowedfuncs = '';
        } else {
            // Not all functions are allowed so set allowed functions to those which are.
            // Create a comma separated string of the function names which are stored in the
            // keys of the array.
            $question->allowedfuncs = implode(',', array_keys($question->allowedfuncs));
        }

        parent::save_question_options($question);
        $this->save_hints($question);
    }

    /**
     * Loads the question type specific options for the question.
     *
     * This function loads the compare algorithm type, disallowed strings and variables
     * into the class from the database table in which they are stored. It first uses the
     * parent class method to get the database information.
     *
     * @param object $question The question object for the question. This object
     *                         should be updated to include the question type
     *                         specific information (it is passed by reference).
     * @return bool            Indicates success or failure.
     */
    public function get_question_options($question) {
        // Get the information from the database table. If this fails then immediately bail.
        // Note unlike the save_question_options base class method this method DOES get the question's
        // answers along with any answer extensions.
        global $DB, $OUTPUT;
        if (!parent::get_question_options($question)) {
            return false;
        }
        // Check that we have answers and if not then bail since this question type requires answers.
        if (count($question->options->answers) == 0) {
            echo $OUTPUT->notification('Failed to load question answers from the table ' .
                    'qtype_algebra_answers for questionid ' . $question->id);

            return false;
        }
        // Now get the variables from the database as well.
        $question->options->variables = $DB->get_records('qtype_algebra_variables', array('questionid' => $question->id));
        // Check that we have variables and if not then bail since this question type requires variables.

        if (count($question->options->variables) == 0) {
            echo $OUTPUT->notification('Failed to load question variables from the table ' .
                    'qtype_algebra_variables for questionid ' . $question->id);
            return false;
        }

        // Check to see if there are any allowed functions.
        if ($question->options->allowedfuncs != '') {
            // Extract the allowed functions as an array.
            $question->options->allowedfuncs = explode(',', $question->options->allowedfuncs);
        } else {
            // Otherwise just create an empty array.
            $question->options->allowedfuncs = array();
        }

        // Everything worked so return true.
        return true;
    }

    /**
     * Imports the question from Moodle XML format.
     *
     * This method is called by the format class when importing an algebra question from the
     * Moodle XML format.
     *
     * @param $data structure containing the XML data
     * @param $question question object to fill: ignored by this function (assumed to be null)
     * @param $format format class importing the question
     * @param $extra extra information (not required for importing this question in this format)
     * @return text string containing the question data in XML format
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!array_key_exists('@', $data)) {
            return false;
        }
        if (!array_key_exists('type', $data['@'])) {
            return false;
        }
        if ($data['@']['type'] == 'algebra') {
            // Import the common question headers.
            $qo = $format->import_headers($data);
            // Set the question type.
            $qo->qtype = 'algebra';

            $qo->compareby = $format->getpath($data, array('#', 'compareby', 0, '#'), 'eval');
            $qo->tolerance = $format->getpath($data, array('#', 'tolerance', 0, '#'), '0');
            $qo->nchecks   = $format->getpath($data, array('#', 'nchecks', 0, '#'), '10');
            $qo->disallow  = $format->getpath($data, array('#', 'disallow', 0, '#', 'text', 0, '#'), '', true);
            $allowedfuncs  = $format->getpath($data, array('#', 'allowedfuncs', 0, '#'), '');
            if ($allowedfuncs == '') {
                $qo->allowedfuncs = array('all' => 1);
            } else {
                // Need to separate the allowed functions into an array of strings and then
                // flip the values of this array into the keys because this is what the
                // save options method requires.
                $qo->allowedfuncs = array_flip(explode(',', $allowedfuncs));
            }
            $qo->answerprefix = $format->getpath($data, array('#', 'answerprefix', 0, '#', 'text', 0, '#'), '', true);

            // Import all the answers.
            $answers = $data['#']['answer'];
            $acount = 0;
            // Loop over each answer block found in the XML.
            foreach ($answers as $answer) {
                // Use the common answer import function in the format class to load the data.
                $ans = $format->import_answer($answer);
                $qo->answer[$acount] = $ans->answer['text'];
                $qo->fraction[$acount] = $ans->fraction;
                $qo->feedback[$acount] = $ans->feedback;
                ++$acount;
            }

            // Import all the variables.
            $vars = $data['#']['variable'];
            $vcount = 0;
            // Loop over each answer block found in the XML.
            foreach ($vars as $var) {
                $qo->variable[$vcount] = $format->getpath($var, array('@', 'name'), 0);
                $qo->varmin[$vcount]   = $format->getpath($var,
                        array('#', 'min', 0, '#'), '0', false, get_string('novarmin', 'qtype_algebra'));
                $qo->varmax[$vcount]   = $format->getpath($var,
                        array('#', 'max', 0, '#'), '0', false, get_string('novarmax', 'qtype_algebra'));
                ++$vcount;
            }

            $format->import_hints($qo, $data);

            return $qo;
        }
        return false;
    }


    /**
     * Exports the question to Moodle XML format.
     *
     * This method is called by the format class when exporting an algebra question into then
     * Moodle XML format.
     *
     * @param $question question to be exported into XML format
     * @param $format format class exporting the question
     * @param $extra extra information (not required for exporting this question in this format)
     * @return text string containing the question data in XML format
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $expout = '';
        // Create a text string of the allowed functions from the array.
        $allowedfuncs = implode(',', $question->options->allowedfuncs);
        // Write out all the extra fields belonging to the algebra question type.
        $expout .= "    <compareby>{$question->options->compareby}</compareby>\n";
        $expout .= "    <tolerance>{$question->options->tolerance}</tolerance>\n";
        $expout .= "    <nchecks>{$question->options->nchecks}</nchecks>\n";
        $expout .= "    <disallow>".$format->writetext($question->options->disallow, 1, true)."</disallow>\n";
        $expout .= "    <allowedfuncs>$allowedfuncs</allowedfuncs>\n";
        $expout .= "    <answerprefix>".$format->writetext($question->options->answerprefix, 1, true).
            "</answerprefix>\n";
        // Write out all the answers.
        $expout .= $format->write_answers($question->options->answers);
        // Loop over all the variables for the question and write out all their details.
        foreach ($question->options->variables as $var) {
            $expout .= "<variable name=\"{$var->name}\">\n";
            $expout .= "    <min>{$var->min}</min>\n";
            $expout .= "    <max>{$var->max}</max>\n";
            $expout .= "</variable>\n";
        }
        return $expout;
    }

    // Gets all the question responses.
    public function get_all_responses(&$question, &$state) {
        $result = new stdClass;
        $answers = array();
        // Loop over all the answers.
        if (is_array($question->options->answers)) {
            foreach ($question->options->answers as $aid => $answer) {
                $r = new stdClass;
                $r->answer = $answer->answer;
                $r->credit = $answer->fraction;
                $answers[$aid] = $r;
            }
        }
        $result->id = $question->id;
        $result->responses = $answers;
        return $result;
    }

    /**
     * Parses the given expression with the parser if required.
     *
     * This method will check to see if the argument it is given is already a parsed
     * expression and if not will attempt to parse it.
     *
     * @param $expr expression which will be parsed
     * @param $question question containing the expression or null if none
     * @return top term of the parse tree or a string if an exception is thrown
     */
    public function parse_expression($expr) {
        // Check to see if this is already a parsed expression.
        if (is_a($expr, 'qtype_algebra_parser_term')) {
            // It is a parsed expression so simply return it.
            return $expr;
        }
        // Check whether we have a state object or a simple string. If a state
        // then replace it with the response string.
        if (isset($expr->responses[''])) {
            $expr = $expr->responses[''];
        }
        // Create an empty array of variable names for the parser (no variable checking here as it is done in the form validation
        // TODO see in case of import.
        $varnames = array();

        // We now assume that we have a string to parse. Create a parser instance to
        // to this and return the parser expression at the top of the parse tree.
        $p = new qtype_algebra_parser;
        // Perform the actual parsing inside a try-catch block so that any exceptions.
        // can be caught and converted into errors.
        try {
            return $p->parse($expr, $varnames);
        } catch (Exception $e) {
            // If the expression cannot be parsed then return a null term. This will
            // make Moodle treat the answer as wrong.
            // TODO: Would be nice to have support for 'invalid answer' in the quiz
            // engine since an unparseable response is usually caused by a silly typo.
            return new qtype_algebra_parser_nullterm;
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->variables = array();
        if (!empty($questiondata->options->variables)) {
            foreach ($questiondata->options->variables as $v) {
                $question->variables[$v->id] = new qtype_algebra_variable($v->id, $v->name, $v->min, $v->max);
            }
        }
        $question->compareby = $questiondata->options->compareby;
        $question->nchecks = $questiondata->options->nchecks;
        $question->tolerance = $questiondata->options->tolerance;
        $question->allowedfuncs = $questiondata->options->allowedfuncs;
        $question->disallow = $questiondata->options->disallow;
        $question->answerprefix = $questiondata->options->answerprefix;
        $this->initialise_question_answers($question, $questiondata);
    }

    public function get_random_guess_score($questiondata) {
        return 0;
    }

    public function get_possible_responses($questiondata) {
        $responses = array();

        foreach ($questiondata->options->answers as $aid => $answer) {
            $responses[$aid] = new question_possible_response($answer->answer,
                    $answer->fraction);
        }
        $responses[0] = new question_possible_response(
                    get_string('didnotmatchanyanswer', 'question'), 0);
        $responses[null] = question_possible_response::no_response();

        return array($questiondata->id => $responses);
    }
}