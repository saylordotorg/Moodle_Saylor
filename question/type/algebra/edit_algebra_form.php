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
 * @package    qtype_algebra
 * @copyright  Roger Moore <rwmoore@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/algebra/questiontype.php');
require_once($CFG->dirroot . '/question/type/algebra/parser.php');

// Override the default number of answers and the number to add to avoid clutter.
// Algebra questions will likely not have huge number of different answers.
define("SYMB_QUESTION_NUMANS_START", 2);
define("SYMB_QUESTION_NUMANS_ADD", 1);

/**
 * symoblic editing form definition.
 */
class qtype_algebra_edit_form extends question_edit_form {
    /** @var int we always show at least this many sets of unit fields. */
    const VARIABLES_MIN_REPEATS = 1;
    const VARIABLES_TO_ADD = 2;
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {
        global $CFG;
        // Algebra questions options.
        $mform->addElement('header', 'algebraoptions',
                                                get_string('algebraoptions', 'qtype_algebra'));
        // Add the select control which will select the comparison type to use.
        $mform->addElement('select', 'compareby', get_string('compareby', 'qtype_algebra'),
                           array( "sage"  => get_string('comparesage', 'qtype_algebra'),
                                  "eval"  => get_string('compareeval', 'qtype_algebra'),
                                  "equiv" => get_string('compareequiv', 'qtype_algebra')
                                 ));
        $mform->addHelpButton('compareby', 'compareby', 'qtype_algebra');
        $mform->setDefault('compareby', $CFG->qtype_algebra_method);

        // Add the control to select the number of checks to perform.
        // First create an array with all the allowed values. We will then use this array
        // with the array_combine function to create a single array where the keys are the
        // same as the array values.
        $chkarray = array(  '1',   '2',   '3',   '5',   '7',
                          '10',  '20',  '30',  '50',  '70',
                         '100', '200', '300', '500', '700', '1000');
        // Add the select element using the array_combine method discussed above.
        $mform->addElement('select', 'nchecks', get_string('nchecks', 'qtype_algebra'),
                            array_combine($chkarray, $chkarray));
        $mform->addHelpButton('nchecks', 'nchecks', 'qtype_algebra');
        // Set the default number of checks to perform.
        $mform->setDefault('nchecks', '10');

        // Add the box to set the tolerance to use when performing evaluation checks.
        $mform->addElement('text', 'tolerance', get_string('tolerance', 'qtype_algebra'));
        $mform->addHelpButton('tolerance', 'tolerance', 'qtype_algebra');
        $mform->setType('tolerance', PARAM_NUMBER);
        $mform->setDefault('tolerance', '0.001');

        // Add an entry for the answer box prefix.
        $mform->addElement('text', 'answerprefix', get_string('answerprefix', 'qtype_algebra'), array('size' => 55));
        $mform->addHelpButton('answerprefix', 'answerprefix', 'qtype_algebra');
        $mform->setType('answerprefix', PARAM_RAW);

        // Add an entry for a disallowed expression.
        $mform->addElement('text', 'disallow', get_string('disallow', 'qtype_algebra'), array('size' => 55));
        $mform->addHelpButton('disallow', 'disallow', 'qtype_algebra');
        $mform->setType('disallow', PARAM_RAW);

        // Create an array which will store the function checkboxes.
        $funcgroup = array();
        // Create an array to add spacers between the boxes.
        $spacers = array('<br>');
        // Add the initial all functions box to the list of check boxes.
        $funcgroup[] =& $mform->createElement('checkbox', 'all', '', get_string('allfunctions', 'qtype_algebra'));
        // Create a checkbox element for each function understood by the parser.
        for ($i = 0; $i < count(qtype_algebra_parser::$functions); $i++) {
            $func = qtype_algebra_parser::$functions[$i];
            $funcgroup[] =& $mform->createElement('checkbox', $func, '', $func);
            if (($i % 6) == 5) {
                $spacers[] = '<br>';
            } else {
                $spacers[] = str_repeat('&nbsp;', 8 - strlen($func));
            }
        }
        // Create and add the group of function controls to the form.
        $mform->addGroup($funcgroup, 'allowedfuncs', get_string('allowedfuncs', 'qtype_algebra'), $spacers, true);
        $mform->addHelpButton('allowedfuncs', 'allowedfuncs', 'qtype_algebra');
        $mform->disabledif ('allowedfuncs', 'allowedfuncs[all]', 'checked');
        $mform->setDefault('allowedfuncs[all]', 'checked');

        $mform->addElement('static', 'variablesinstruct',
                get_string('variables', 'qtype_algebra'),
                get_string('filloutonevariable', 'qtype_algebra'));

        $this->add_variable_fields($mform);

        $mform->addElement('static', 'answersinstruct',
                get_string('correctanswers', 'qtype_algebra'),
                get_string('filloutoneanswer', 'qtype_algebra'));
        $mform->closeHeaderBefore('answersinstruct');

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_algebra', '{no}'),
                question_bank::fraction_options(), SYMB_QUESTION_NUMANS_START, SYMB_QUESTION_NUMANS_ADD);

        $this->add_interactive_settings();

    }

    /**
     * Add the input areas for each variable.
     * @param object $mform the form being built.
     */
    protected function add_variable_fields($mform) {
        $mform->addElement('header', 'variablehdr',
                    get_string('variables', 'qtype_algebra'), '');
        $mform->setExpanded('variablehdr', 1);

        $variablefields = array($mform->createElement('group', 'variables',
                 get_string('variablex', 'qtype_algebra'), $this->variable_group($mform), null, false));

        $repeatedoptions['variable']['type'] = PARAM_RAW;
        $repeatedoptions['varmin']['type'] = PARAM_RAW;
        $repeatedoptions['varmin']['default'] = '';
        $repeatedoptions['varmax']['type'] = PARAM_RAW;
        $repeatedoptions['varmax']['default'] = '';

        if (isset($this->question->options->variables)) {
            $repeatsatstart = max(count($this->question->options->variables), self::VARIABLES_MIN_REPEATS);
        } else {
            $repeatsatstart = self::VARIABLES_MIN_REPEATS;
        }

        $this->repeat_elements($variablefields, $repeatsatstart, $repeatedoptions, 'novariables', 'addvariables',
                               self::VARIABLES_TO_ADD, get_string('addmorevariableblanks', 'qtype_algebra'), true);
        $mform->addHelpButton('variables[0]', 'variable', 'qtype_algebra');
    }

    /**
     * Get the form fields needed to edit one variable.
     * @param MoodleQuickForm $mform the form being built.
     * @return array of form fields.
     */
    protected function variable_group($mform) {
        $grouparray = array();
        $grouparray[] = $mform->createElement('text', 'variable', get_string('variablename', 'qtype_algebra'), array('size' => 10));
        $grouparray[] = $mform->createElement('text', 'varmin', get_string('varmin', 'qtype_algebra'), array('size' => 10));
        $grouparray[] = $mform->createElement('text', 'varmax', get_string('varmax', 'qtype_algebra'), array('size' => 20));

        return $grouparray;
    }
    protected function get_more_choices_string() {
        return get_string('addmoreanswerblanks', 'qtype_algebra');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        if (!empty($question->options)) {
            $question->compareby = $question->options->compareby;
            $question->nchecks = $question->options->nchecks;
            $question->tolerance = $question->options->tolerance;
            $question->allowedfuncs = $question->options->allowedfuncs;
            $question->disallow = $question->options->disallow;
            $question->answerprefix = $question->options->answerprefix;
        }

        return $question;
    }
    /**
     * Sets the existing values into the form for the question specific data.
     *
     * This method copies the data from the existing database record into the form fields as default
     * values for the various elements.
     *
     * @param $question the question object from the database being used to fill the form
     */
    public function set_data($question) {
        // Check to see if there are any existing question options, if not then just call
        // the base class set data method and exit.
        if (!isset($question->options)) {
            return parent::set_data($question);
        }

        // Now we do exactly the same for the variables.
        $vars = $question->options->variables;
        // If we found any variables then loop over them using a numerical key to provide an index
        // to the arrays we need to access in the form.
        if (count($vars)) {
            $key = 0;
            foreach ($vars as $var) {
                // For every variable set the default values.
                $defaultvalues['variable['.$key.']'] = $var->name;
                // Only set the min and max defaults if this variable has a range.
                if ($var->min != '') {
                    $defaultvalues['varmin['.$key.']'] = $var->min;
                    $defaultvalues['varmax['.$key.']'] = $var->max;
                }
                $key++;
            }
        }

        // Add the default values for the allowed functions controls.
        // First check to see if there are any allowed functions defined.
        if (count($question->options->allowedfuncs) > 0) {
            // Clear the 'all functions' flag since functions are restricted.
            $defaultvalues['allowedfuncs[all]'] = 0;
            // Loop over all the functions which the parser understands.
            foreach (qtype_algebra_parser::$functions as $func) {
                // For each function see if the function is in the allowed function
                // list and if so set the check box otherwise remove the check box.
                if (in_array($func, $question->options->allowedfuncs)) {
                    $defaultvalues['allowedfuncs['.$func.']'] = 1;
                } else {
                    $defaultvalues['allowedfuncs['.$func.']'] = 0;
                }
            }
        } else {
            // There are no allowed functions defined so all functions are allowed.
            $defaultvalues['allowedfuncs[all]'] = 1;
        }

        // Add the default values to the question object in a form which the parent
        // set data method will be able to use to find the default values.
        $question = (object)((array)$question + $defaultvalues);

        // Finally call the parent set data method to handle everything else.
        parent::set_data($question);
    }

    /**
     * Validates the form data ensuring there are no obvious errors in the submitted data.
     *
     * This method performs some basic sanity checks on the form data before it gets converted
     * into a database record.
     *
     * @param $data the data from the form which needs to be checked
     * @param $files some files - I don't know what this is for! - files defined in the form??
     */
    public function validation($data, $files) {

        // Call the base class validation method and keep any errors it generates.
        $errors = parent::validation($data, $files);

        // Regular expression string to match a number.
        $renumber = '/([+-]*(([0-9]+\.[0-9]*)|([0-9]+)|(\.[0-9]+))|'.
            '(([0-9]+\.[0-9]*)|([0-9]+)|(\.[0-9]+))E([-+]?\d+))/A';

        // Perform sanity checks on the variables.
        $vars = $data['variable'];
        // Create an array of defined variables.
        $varlist = array();
        foreach ($vars as $key => $var) {
            $trimvar = trim($var);
            $trimmin = trim($data['varmin'][$key]);
            $trimmax = trim($data['varmax'][$key]);
            // Check that there is a nom empty variable name otherwise skip.
            if ($trimvar == '') {
                continue;
            }
            // Check that this variable does not have the same name as a function.
            if (in_array($trimvar, qtype_algebra_parser::$functions) or in_array($trimvar, qtype_algebra_parser::$specials)) {
                $errors['variables['.$key.']'] = get_string('illegalvarname', 'qtype_algebra', $trimvar);
            }
            // Check that this variable has not been defined before.
            if (in_array($trimvar, $varlist)) {
                $errors['variables['.$key.']'] = get_string('duplicatevar', 'qtype_algebra', $trimvar);
            } else {
                // Add the variable to the list of defined variables.
                $varlist[] = $trimvar;
            }
            // If the comparison algorithm selected is evaluate then ensure that each variable
            // has a valid minimum and maximum defined. For the other types of comparison we can
            // ignore the range.
            if ($data['compareby'] == 'eval') {
                // Check that a minimum has been defined.
                if ($trimmin == '') {
                    $errors['variables['.$key.']'] = get_string('novarmin', 'qtype_algebra');
                } else if (!preg_match($renumber, $trimmin)) {
                    // If there is one check that it's a number.
                    $errors['variables['.$key.']'] = get_string('notanumber', 'qtype_algebra');
                }
                if ($trimmax == '') {
                    $errors['variables['.$key.']'] = get_string('novarmax', 'qtype_algebra');
                } else if (!preg_match($renumber, $trimmax)) {
                    // If there is one check that it is a number.
                    $errors['variables['.$key.']'] = get_string('notanumber', 'qtype_algebra');
                }
                // Check that the minimum is less that the maximum!
                if ((float)$trimmin > (float)$trimmax) {
                    $errors['variables['.$key.']'] = get_string('varmingtmax', 'qtype_algebra');
                }
            } // End check for eval type.
        }     // End loop over variables.
        // Check that at least one variable is defined.
        if (count($varlist) == 0) {
            $errors['variables[0]'] = get_string('notenoughvars', 'qtype_algebra');
        }

        // Now perform the sanity checks on the answers.
        // Create a parser which we will use to check that the answers are understandable.
        $p = new qtype_algebra_parser;
        $answers = $data['answer'];
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
                    $errors['answeroptions['.$key.']'] = get_string('undefinedvar', 'qtype_algebra', "'".implode("', '", $d)."'");
                }
                // Do the same for functions which we did for variables.
                $ansfuncs = array_merge($ansfuncs, array_diff($expr->get_functions(), $ansfuncs));
                // Check that this is not an empty answer.
                if (!is_a($expr, "qtype_algebra_parser_nullterm")) {
                    // Increase the number of answers.
                    $answercount++;
                    // Check to see if the answer has the maximum grade.
                    if ($data['fraction'][$key] == 1) {
                        $maxgrade = true;
                    }
                }
            } catch (Exception $e) {
                $errors['answeroptions['.$key.']'] = $e->getMessage();
                // Return here because subsequent errors may be wrong due to not counting the answer
                // which just failed to parse.
                return $errors;
            }
        }
        // Check that we have at least one answer.
        if ($answercount == 0) {
            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_algebra');
        }
        // Check that at least one question has the maximum possible grade.
        if ($maxgrade == false) {
            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
        }

        // Check for variables which are defined but never used.
        // Do this by looking for a non-empty array to be returned from array_diff.
        if ($d = array_diff($varlist, $ansvars)) {
            // Loop over all the variables in the form.
            foreach ($vars as $key => $var) {
                $trimvar = trim($var);
                // If the variable is in the unused array then add the error message to that variable.
                if (in_array($trimvar, $d)) {
                    $errors['variables['.$key.']'] = get_string('unusedvar', 'qtype_algebra');
                }
            }
        }

        // Check that the tolerance is greater than or equal to zero.
        if ($data['tolerance'] < 0) {
            $errors['tolerance'] = get_string('toleranceltzero', 'qtype_algebra');
        }

        return $errors;
    }

    public function qtype() {
        return 'algebra';
    }
}
