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
 * algebra answer question definition class.
 *
 * @package    qtype_algebra
 * @author  Roger Moore <rwmoore@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/algebra/questiontype.php');
require_once($CFG->dirroot . '/question/type/algebra/parser.php');
require_once($CFG->dirroot . '/question/type/algebra/xmlrpc-utils.php');

/**
 * Represents an algebra question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebra_question extends question_graded_by_strategy
        implements question_response_answer_comparer {

    /** @var array of question_answer. */
    public $answers = array();
    /** @var array of question_answer. */
    public $variables = array();
    public $compareby;
    public $nchecks;
    public $tolerance;
    public $allowedfuncs;
    public $disallow;
    public $answerprefix;

    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
                ($response['answer'] || $response['answer'] === '0');
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_algebra');
    }

    /**
     * Parses the given expression with the parser if required.
     *
     * This method will check to see if the argument it is given is already a parsed
     * expression and if not will attempt to parse it.
     *
     * @param $expr expression which will be parsed
     * @return top term of the parse tree or a string if an exception is thrown
     */
    public function parse_expression($expr) {
        // Check to see if this is already a parsed expression.
        if (is_a($expr, 'qtype_algebra_parser_term')) {
            // It is a parsed expression so simply return it.
            return $expr;
        }

        // Create an array of variable names for the parser from the question if defined.
        $varnames = array();
        if (isset($this->variables)) {
            foreach ($this->variables as $var) {
                $varnames[] = $var->name;
            }
        }
        // We now assume that we have a string to parse. Create a parser instance to
        // to this and return the parser expression at the top of the parse tree.
        $p = new qtype_algebra_parser;
        // Perform the actual parsing inside a try-catch block so that any exceptions
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

    /**
     * Parses the given expression with the parser if required.
     *
     * This method will parse the expression and return a TeX string
     * or empty string
     *
     * @param $expr expression which will be parsed
     * @return top term of the parse tree or a string if an exception is thrown
     */
    public function formated_expression($text, $vars = null) {
        global $CFG;
        if ($vars == null) {
            // Create an array of variable names for the parser from the question if defined.
            $vars = array();
            if (isset($this->variables)) {
                foreach ($this->variables as $var) {
                    $vars[] = $var->name;
                }
            }
        }
        // We now assume that we have a string to parse. Create a parser instance to
        // to this and return the parser expression at the top of the parse tree.
        $p = new qtype_algebra_parser;
        // Perform the actual parsing inside a try-catch block so that any exceptions
        // can be caught and converted into errors.
        try {
            $exp = $p->parse($text, $vars);
            $texexp = $exp->tex();
        } catch (Exception $e) {
            $texexp = ' ';
        }

        $delimiters = $CFG->qtype_algebra_texdelimiters;
        switch($delimiters) {
            case 'old':
                return '$$' . $texexp . '$$';
            case 'new':
                return '\\[' . $texexp . '\\]';
            case 'simple';
                return '$' . $texexp . '$';
            case 'inline':
                return '\\(' . $texexp . '\\)';
        }

    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        // Check that both states have valid responses.
        if (!isset($prevresponse['answer']) or !isset($newresponse['answer'])) {
            // At last one of the states did not have a response set so return false by default.
            return false;
        }
        // Parse the previous response.
        $expr = $this->parse_expression($prevresponse['answer']);
        // Parse the new response.
        $testexpr = $this->parse_expression($newresponse['answer']);
        // The type of comparison done depends on the comparision algorithm selected by
        // the question. Use the defined algorithm to select which comparison function
        // to call...
        if ($this->compareby == 'sage') {
            // Uses an XML-RPC server with SAGE to perform a full symbollic comparision.
            return self::test_response_by_sage($expr, $testexpr);
        } else if ($this->compareby == 'eval') {
            // Tests the response by evaluating it for a certain range of each variable.
            return self::test_response_by_evaluation($expr, $testexpr);
        } else {
            // Tests the response by performing a simple parse tree equivalence algorithm.
            return self::test_response_by_equivalence($expr, $testexpr);
        }
    }

    public function get_answers() {
        return $this->answers;
    }

    public function compare_response_with_answer(array $response, question_answer $answer) {
        $expr = $this->parse_expression($response['answer']);
        // Check that there is a response and if not return false. We do this here
        // because even an empty response should match a widlcard answer.
        if (is_a($expr, 'qtype_algebra_parser_nullterm')) {
            return false;
        }

        // Now parse the answer.
        $ansexpr = $this->parse_expression($answer->answer);
        // The type of comparison done depends on the comparision algorithm selected by
        // the question. Use the defined algorithm to select which comparison function
        // to call...
        if ($this->compareby == 'sage') {
            // Uses an XML-RPC server with SAGE to perform a full symbollic comparision.
            return self::test_response_by_sage($expr, $ansexpr);
        } else if ($this->compareby == 'eval') {
            // Tests the response by evaluating it for a certain range of each variable.
            return self::test_response_by_evaluation($expr, $ansexpr);
        } else {
            // Tests the response by performing a simple parse tree equivalence algorithm.
            return self::test_response_by_equivalence($expr, $ansexpr);
        }
    }

    /**
     * Checks whether a response matches a given answer using SAGE
     *
     * This method will compare the given response to the given answer using the SAGE
     * open source algebra computation software. The software is run by a remote
     * XML-RPC server which is called with both the asnwer and the response and told to
     * compare the two algebraic expressions.
     *
     * @return boolean true if the response matches the answer, false otherwise
     */
    public function test_response_by_sage($response, $answer) {
        global $CFG;
        $request = array(
                       'host'   => $CFG->qtype_algebra_host,
                       'port'   => $CFG->qtype_algebra_port,
                       'uri'    => $CFG->qtype_algebra_uri,
        );
        // Sets the name of the method to call to full_symbolic_compare.
        $request['method'] = 'full_symbolic_compare';
        // Get a list of all the variables to declare.
        $vars = $response->get_variables();
        $vars = array_merge($vars, array_diff($vars, $answer->get_variables()));
        // Sets the arguments to the sage string of the response and the list of variables.
        $request['args'] = array($answer->sage(), $response->sage(), $vars);
        // Calls the XML-RPC method on the server and returns the response.
        return xu_rpc_http_concise($request) == 0;
    }

    /**
     * Checks whether a response matches a given answer using an evaluation method
     *
     * This method will compare the given response to the given answer by evaluating both
     * for given values of the variables. Each variable must have a predefined range over
     * which it can be checked and then both expressions will be evalutated several times
     * using values randomly chosen to be within the range.
     *
     * @return boolean true if the response matches the answer, false otherwise
     */
    public function test_response_by_evaluation($response, $answer) {
        // Flag used to denote mismatch in response and answer.
        $same = true;
        // Run the evaluation loop 10 times with different random variables...
        for ($i = 0; $i < $this->nchecks; $i++) {
            // Create an array to store the values of all the variables.
            $values = array();
            // Loop over all the variables in the question.
            foreach ($this->variables as $var) {
                // Set the value of the variable to a random number between the min and max.
                $values[$var->name] = $var->min + lcg_value() * abs($var->max - $var->min);
            }
            $respvalue = $response->evaluate($values);
            $ansvalue = $answer->evaluate($values);
            // Return false if only one of the reponse or answer gives NaN.
            if (is_nan($respvalue) xor is_nan($ansvalue)) {
                return false;
            }
            // Return false if only one of the reponse or answer is infinite.
            if (is_infinite($respvalue) xor is_infinite($ansvalue)) {
                return false;
            }
            // Use the fractional difference method if the answer has a value
            // which is clearly distinguishable from zero.
            if (abs($ansvalue) > 1e-300) {
                // Get the difference between the response and answer evaluations.
                $diff = abs(($respvalue - $ansvalue) / $ansvalue);
            } else {
                // Otherwise use an arbitrary minimum value.
                $diff = abs(($respvalue - $ansvalue) / 1e-300);
            }
            // Check to see if the difference is greater than tolerance.
            if ($diff > $this->tolerance) {
                // Return false since the formulae have been shown not to be the same.
                return false;
            }
        }
        // We made it through the loop so now return true.
        return true;
    }

    /**
     * Checks whether a response matches a given answer using a simple equivalence algorithm
     *
     * This method will compare the given response to the given answer by simply checking to
     * see if the two parse trees are equivalent. This allows for a slightly more sophisticated
     * check than a simple text compare but will not, neccessarily, catch two equivalent but
     * different algebraic expressions.
     *
     * @return boolean true if the response matches the answer, false otherwise
     */
    public function test_response_by_equivalence($response, $answer) {
        // Use the parser's equivalent method to see if the response is the same as the answer.
        return $response->equivalent($answer);
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $currentanswer = $qa->get_last_qt_var('answer');
            $answer = $this->get_matching_answer(array('answer' => $currentanswer));
            $answerid = reset($args); // Parameter itemid is answer id.
            return $options->feedback && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}

/**
 * Class to represent an algebra question variable, loaded from the qtype_algebra_variables table
 * in the database.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_algebra_variable {
    /** @var integer the answer id. */
    public $id;

    /** @var string the name. */
    public $name;

    /** @var string minimum value. */
    public $min = '-';

    /** @var string maximum value. */
    public $max = '-';

    /**
     * Constructor.
     * @param int $id the variable.
     * @param string $name the name.
     * @param string $min the minimum value.
     * @param string $maximum value.
     */
    public function __construct($id, $name, $min, $max) {
        $this->id = $id;
        $this->name = $name;
        $this->min = $min;
        $this->max = $max;
    }
}
