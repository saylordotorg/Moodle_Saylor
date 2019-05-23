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
 * Defines the hooks necessary to make the algebra question type combinable
 *
 * @package   qtype_algebra
 * @copyright  2019 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/algebra/parser.php');

define('SYMB_QUESTION_NUMANS_START', 2);
define('SYMB_QUESTION_NUMANS_ADD', 1);
define('SYMB_QUESTION_NUMVARS_ADD', 1);
define('SYMB_QUESTION_NUMVARS_START', 1);

class qtype_combined_combinable_type_algebra extends qtype_combined_combinable_type_base {

    protected $identifier = 'algebra';

    protected function extra_question_properties() {
        return array('answerprefix' => '', 'allowedfuncs' => array('all' => 1));
    }

    protected function extra_answer_properties() {
        return array('fraction' => '1', 'feedback' => array('text' => '', 'format' => FORMAT_PLAIN));
    }

    public function subq_form_fragment_question_option_fields() {
        return array('compareby' => null,
                     'nchecks' => null,
                     'disallow' => null,
                     'allowedfuncs' => null);
    }
}


class qtype_combined_combinable_algebra extends qtype_combined_combinable_text_entry {
    /**
     * Get the form fields needed to edit one variable.
     * @param MoodleQuickForm $mform the form being built.
     * @return array of form fields.
     */
    protected function variable_group($mform) {
        $grouparray = array();
        $grouparray[] = $mform->createElement('text', $this->form_field_name('variable'), get_string('variablename', 'qtype_algebra'), array('size' => 10));
        $grouparray[] = $mform->createElement('text', $this->form_field_name('varmin'), get_string('varmin', 'qtype_algebra'), array('size' => 10));
        $grouparray[] = $mform->createElement('text', $this->form_field_name('varmax'), get_string('varmax', 'qtype_algebra'), array('size' => 10));

        return $grouparray;
    }

    /**
     * @param moodleform      $combinedform
     * @param MoodleQuickForm $mform
     * @param                 $repeatenabled
     * @return mixed
     */
    public function add_form_fragment(moodleform $combinedform, MoodleQuickForm $mform, $repeatenabled) {
        global $CFG;
        $mform->addElement('select', $this->form_field_name('compareby'), get_string('compareby', 'qtype_algebra'),
                   array( "sage"  => get_string('comparesage', 'qtype_algebra'),
                          "eval"  => get_string('compareeval', 'qtype_algebra'),
                          "equiv" => get_string('compareequiv', 'qtype_algebra')
                         ));
        $mform->setDefault($this->form_field_name('compareby'), $CFG->qtype_algebra_method);
        $chkarray = array(  '1',   '2',   '3',   '5',   '7',
                          '10',  '20',  '30',  '50',  '70',
                         '100', '200', '300', '500', '700', '1000');
        $mform->addElement('select', $this->form_field_name('nchecks'), get_string('nchecks', 'qtype_algebra'),
                            array_combine($chkarray, $chkarray));
        $mform->setDefault($this->form_field_name('nchecks'), '10');
        $mform->addElement('text', $this->form_field_name('tolerance'), get_string('tolerance', 'qtype_algebra'));
        $mform->setType($this->form_field_name('tolerance'), PARAM_NUMBER);
        $mform->setDefault($this->form_field_name('tolerance'), '0.001');
        // Add an entry for a disallowed expression.
        $mform->addElement('text', $this->form_field_name('disallow'), get_string('disallow', 'qtype_algebra'), array('size' => 55));
        $mform->setType($this->form_field_name('disallow'), PARAM_RAW);
        if ($this->questionrec !== null) {
            $countvars = count($this->questionrec->options->variables);
            $repeatsatstart = max($countvars + SYMB_QUESTION_NUMVARS_ADD, SYMB_QUESTION_NUMVARS_START);
        } else {
            $countvars = 0;
            $repeatsatstart = SYMB_QUESTION_NUMVARS_START;
        }

        $variablefields = array($mform->createElement('group', $this->form_field_name('variables'),
                 get_string('variablex', 'qtype_algebra'), $this->variable_group($mform), null, false));
        $repeatedoptions[$this->form_field_name('variable')]['type'] = PARAM_RAW;
        $repeatedoptions[$this->form_field_name('varmin')]['type'] = PARAM_RAW;
        $repeatedoptions[$this->form_field_name('varmax')]['type'] = PARAM_RAW;
        $combinedform->repeat_elements($variablefields, $repeatsatstart, $repeatedoptions, $this->form_field_name('novariables'), $this->form_field_name('addvariables'),
                               SYMB_QUESTION_NUMVARS_ADD, get_string('addmorevariableblanks', 'qtype_algebra'), true);
        $answerel = array($mform->createElement('text',
                                                $this->form_field_name('answer'),
                                                get_string('answerx', 'qtype_algebra'),
                                                array('size' => 57, 'class' => 'tweakcss')));
        if ($this->questionrec !== null) {
            $countanswers = count($this->questionrec->options->answers);
        } else {
            $countanswers = 0;
        }

        if ($repeatenabled) {
            $defaultstartnumbers = SYMB_QUESTION_NUMANS_START;
            $repeatsatstart = max($defaultstartnumbers, $countanswers + SYMB_QUESTION_NUMANS_ADD);
        } else {
            $repeatsatstart = $countanswers;
        }

        $combinedform->repeat_elements($answerel,
                                        $repeatsatstart,
                                        array(),
                                        $this->form_field_name('noofchoices'),
                                        $this->form_field_name('morechoices'),
                                        SYMB_QUESTION_NUMANS_ADD,
                                        get_string('addmoreanswerblanks', 'qtype_algebra'),
                                        true);
        $mform->setType($this->form_field_name('answer'), PARAM_RAW_TRIMMED);
    }

    public function data_to_form($context, $fileoptions) {
        $answers = array('answer' => array());
        if ($this->questionrec !== null) {
            foreach ($this->questionrec->options->answers as $answer) {
                $answers['answer'][] = $answer->answer;
            }

            foreach ($this->questionrec->options->variables as $variable) {
                $variables['variable'][] = $variable->name;
                $variables['varmin'][] = $variable->min;
                $variables['varmax'][] = $variable->max;
            }
        }
        $data = parent::data_to_form($context, $fileoptions) + $answers + $variables;
        return $data;
    }

    public function validate() {
        $errors = array();
        // Regular expression string to match a number.
        $renumber = '/([+-]*(([0-9]+\.[0-9]*)|([0-9]+)|(\.[0-9]+))|'.
            '(([0-9]+\.[0-9]*)|([0-9]+)|(\.[0-9]+))E([-+]?\d+))/A';

        // Perform sanity checks on the variables.
        $vars = $this->formdata->variable;;
        // Create an array of defined variables.
        $varlist = array();
        foreach ($vars as $key => $var) {
            $trimvar = trim($var);
            $trimmin = trim($this->formdata->varmin[$key]);
            $trimmax = trim($this->formdata->varmax[$key]);
            // Check that there is a non empty variable name otherwise skip.
            if ($trimvar == '') {
                continue;
            }
            // Check that this variable does not have the same name as a function.
            if (in_array($trimvar, qtype_algebra_parser::$functions) or in_array($trimvar, qtype_algebra_parser::$specials)) {
                $errors[$this->form_field_name('variables['.$key.']')] = get_string('illegalvarname', 'qtype_algebra', $trimvar);
            }
            // Check that this variable has not been defined before.
            if (in_array($trimvar, $varlist)) {
                $errors[$this->form_field_name('variables['.$key.']')] = get_string('duplicatevar', 'qtype_algebra', $trimvar);
            } else {
                // Add the variable to the list of defined variables.
                $varlist[] = $trimvar;
            }
            // If the comparison algorithm selected is evaluate then ensure that each variable
            // has a valid minimum and maximum defined. For the other types of comparison we can
            // ignore the range.
            if ($this->formdata->compareby == 'eval') {
                // Check that a minimum has been defined.
                if ($trimmin == '') {
                    $errors[$this->form_field_name('variables['.$key.']')] = get_string('novarmin', 'qtype_algebra');
                } else if (!preg_match($renumber, $trimmin)) {
                    // If there is one check that it's a number.
                    $errors[$this->form_field_name('variables['.$key.']')] = get_string('notanumber', 'qtype_algebra');
                }
                if ($trimmax == '') {
                    $errors[$this->form_field_name('variables['.$key.']')] = get_string('novarmax', 'qtype_algebra');
                } else if (!preg_match($renumber, $trimmax)) {
                    // If there is one check that it is a number.
                    $errors[$this->form_field_name('variables['.$key.']')] = get_string('notanumber', 'qtype_algebra');
                }
                // Check that the minimum is less that the maximum!
                if ((float)$trimmin > (float)$trimmax) {
                    $errors[$this->form_field_name('variables['.$key.']')] = get_string('varmingtmax', 'qtype_algebra');
                }
            } // End check for eval type.
        }     // End loop over variables.
        // Check that at least one variable is defined.
        if (count($varlist) == 0) {
            $errors[$this->form_field_name('variables|0]')] = get_string('notenoughvars', 'qtype_algebra');
        }

        // Now perform the sanity checks on the answers.
        // Create a parser which we will use to check that the answers are understandable.
        $p = new qtype_algebra_parser;
        $answers = $this->formdata->answer;
        $answercount = 0;
        $maxgrade = false;
        // Create an empty array to store the used variables.
        $ansvars = array();
        // Create an empty array to store the used functions.
        $ansfuncs = array();
        // Loop over all the answers in the form.
        foreach ($answers as $key => $answer) {
            // Try to parse the answer string using the parser. If this fails it will
            // throw an exception which we catch to generate the associated error string
            // for the expression.
            try {
                $expr = $p->parse($answer);
                // Add any new variables to the list we are keeping. First we get the list
                // of variables in this answer. Then we get the array of variables which are
                // in this answer that are not in any previous answer (using array_diff).
                // Finally we merge this difference array with the list of all variables so far.
                $tmpvars = $expr->get_variables();
                $ansvars = array_merge($ansvars, array_diff($tmpvars, $ansvars));
                // Check that all the variables in this answer have been declared.
                // Do this by looking for a non-empty array to be returned from the array_diff
                // between the list of all declared variables and the variables in this answer.
                if ($d = array_diff($tmpvars, $varlist)) {
                    $errors[$this->form_field_name('answer['.$key.']')] = get_string('undefinedvar', 'qtype_algebra', "'".implode("', '", $d)."'");
                }
                // Do the same for functions which we did for variables.
                $ansfuncs = array_merge($ansfuncs, array_diff($expr->get_functions(), $ansfuncs));
                // Check that this is not an empty answer.
                if (!is_a($expr, "qtype_algebra_parser_nullterm")) {
                    // Increase the number of answers.
                    $answercount++;
                }
            } catch (Exception $e) {
                $errors[$this->form_field_name('answer['.$key.']')] = $e->getMessage();
                // Return here because subsequent errors may be wrong due to not counting the answer
                // which just failed to parse.
                return $errors;
            }
        }
        // Check that we have at least one answer.
        if ($answercount == 0) {
            $errors[$this->form_field_name('answer[0]')] = get_string('notenoughanswers', 'qtype_algebra');
        }

        // Check for variables which are defined but never used.
        // Do this by looking for a non-empty array to be returned from array_diff.
        if ($d = array_diff($varlist, $ansvars)) {
            // Loop over all the variables in the form.
            foreach ($vars as $key => $var) {
                $trimvar = trim($var);
                // If the variable is in the unused array then add the error message to that variable.
                if (in_array($trimvar, $d)) {
                    $errors[$this->form_field_name('variables['.$key.']')] = get_string('unusedvar', 'qtype_algebra');
                }
            }
        }

        // Check that the tolerance is greater than or equal to zero.
        if ($this->formdata->tolerance < 0) {
            $errors[$this->form_field_name('tolerance')] = get_string('toleranceltzero', 'qtype_algebra');
        }

        return $errors;
    }

    public function get_sup_sub_editor_option() {
        return null;
    }

    public function has_submitted_data() {
        return $this->submitted_data_array_not_empty('answer') || parent::has_submitted_data();
    }
}
