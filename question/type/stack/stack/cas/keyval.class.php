<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../maximaparser/utils.php');
require_once(__DIR__ . '/../maximaparser/MP_classes.php');
require_once(__DIR__ . '/cassession2.class.php');
require_once(__DIR__ . '/../utils.class.php');

/**
 * Class to parse user-entered data into CAS sessions.
 *
 * @copyright  2012 University of Birmingham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stack_cas_keyval {

    /** @var Holds the raw text as entered by a question author. */
    private $raw;

    /** @var array of stack_ast_container_silent */
    private $statements;

    /** @var bool */
    private $valid;

    /** @var array of error messages that can be displayed to the user. */
    private $errors;

    /** @var stack_cas_security shared security to use for validation */
    private $security;

    // For those using keyvals as a generator for sessions.
    private $options;
    private $seed;

    public function __construct($raw, $options = null, $seed=null) {
        $this->raw          = $raw;
        $this->statements   = array();
        $this->errors       = array();
        $this->options      = $options;
        $this->seed         = $seed;
        $this->security     = new stack_cas_security();

        if (!is_string($raw)) {
            throw new stack_exception('stack_cas_keyval: raw must be a string.');
        }

        if (!is_null($options) && !is_a($options, 'stack_options')) {
            throw new stack_exception('stack_cas_keyval: options must be null or stack_options.');
        }

        if (!is_null($seed) && !is_int($seed)) {
            throw new stack_exception('stack_cas_keyval: seed must be a null or an integer.');
        }

    }

    private function validate($inputs) {

        if (empty($this->raw) or '' == trim($this->raw)) {
            $this->valid = true;
            return true;
        }

        // Protect things inside strings before we do QMCHAR tricks, and check for @, $.
        $str = $this->raw;
        $strings = stack_utils::all_substring_strings($str);
        foreach ($strings as $key => $string) {
            $str = str_replace('"'.$string.'"', '[STR:'.$key.']', $str);
        }

        $str = str_replace('?', 'QMCHAR', $str);

        // CAS keyval may not contain @ or $ outside strings.
        // We should certainly prevent the $ to make sure statements are separated by ;, although Maxima does allow $.
        if (strpos($str, '@') !== false || strpos($str, '$') !== false) {
            $this->errors[] = stack_string('illegalcaschars');
            $this->valid = false;
            return false;
        }

        foreach ($strings as $key => $string) {
            $str = str_replace('[STR:'.$key.']', '"' .$string . '"', $str);
        }

        // 6/10/18 No longer split by line change, split by statement.
        // Allow writing of loops and other long statements onto multiple lines.
        $ast = maxima_parser_utils::parse_and_insert_missing_semicolons($str);
        if (!$ast instanceof MP_Root) {
            // If not then it is a SyntaxError.
            $syntaxerror = $ast;
            $error = $syntaxerror->getMessage();
            if (isset($syntaxerror->grammarLine) && isset($syntaxerror->grammarColumn)) {
                $error .= ' (' . stack_string('stackCas_errorpos',
                        ['line' => $syntaxerror->grammarLine, 'col' => $syntaxerror->grammarColumn]) . ')';
            }
            $this->errors[] = $error;
            $this->valid = false;
            return false;
        }

        $ast = maxima_parser_utils::strip_comments($ast);

        $vallist = array();
        // Update the types and values for future insert-stars and other logic.
        $config = stack_utils::get_config();
        if ($config->caspreparse == 'true') {
            $vallist = maxima_parser_utils::identify_identifier_values($ast, $this->security->get_context());
        }
        if (isset($vallist['% TIMEOUT %'])) {
            $this->errors[] = stack_string('stackCas_overlyComplexSubstitutionGraphOrRandomisation');
            $this->valid = false;
        } else {
            // Mark inputs as specific type.
            if (is_array($inputs)) {
                foreach ($inputs as $name) {
                    if (!isset($vallist[$name])) {
                        $vallist[$name] = [];
                    }
                    $vallist[$name][-2] = -2;
                }
            }
            $this->security->set_context($vallist);
        }

        $this->valid   = true;
        $this->statements   = array();
        foreach ($ast->items as $item) {
            $cs = stack_ast_container::make_from_teacher_ast($item, '', $this->security);
            if ($item instanceof MP_Statement) {
                $op = '';
                if ($item->statement instanceof MP_Operation) {
                    $op = $item->statement->op;
                }
                if ($item->statement instanceof MP_FunctionCall) {
                    $op = $item->statement->name->value;
                }
                // Context variables should always be silent.  We might need a separate feature "silent" in future.
                if (stack_cas_security::get_feature($op, 'contextvariable') !== null) {
                    $cs = stack_ast_container_silent::make_from_teacher_ast($item, '',
                            $this->security);
                }
            }
            $this->valid = $this->valid && $cs->get_valid();
            $this->errors = array_merge($this->errors, $cs->get_errors(true));
            $this->statements[] = $cs;
        }

        // Allow reference to inputs in the values of the question variables (otherwise we can't use them)!
        // Prevent reference to inputs in the keys.
        if (is_array($inputs)) {
            $usage = $this->get_variable_usage();
            foreach ($usage['write'] as $key => $used) {
                if (in_array($key, $inputs)) {
                    $this->valid = false;
                    $this->errors[] = stack_string('stackCas_inputsdefined', $key);
                }
            }
        }

        return $this->valid;
    }

    /** Specify non default security, do this before validation. */
    public function set_security(stack_cas_security $security) {
        $this->security = clone $security;
    }

    /** Extract a security object with type related context information, do this after validation. */
    public function get_security(): stack_cas_security {
        return $this->security;
    }


    /*
     * @array $inputs Holds an array of the input names which are forbidden as keys.
     * @bool $inputstrict Decides if we should forbid any reference to the inputs in the values of variables.
     */
    public function get_valid($inputs = null) {
        if (null === $this->valid || is_array($inputs)) {
            $this->validate($inputs);
        }
        return $this->valid;
    }

    public function get_errors($casdebug = false) {
        if (null === $this->valid) {
            $this->validate(null);
        }
        if ($casdebug) {
            $this->errors[] = $this->session->get_debuginfo();
        }
        return $this->errors;
    }

    public function instantiate() {
        if (null === $this->valid) {
            $this->validate(null);
        }
        $cs = new stack_cas_session2($this->statements, $this->options, $this->seed);
        if ($cs->get_valid()) {
            $cs->instantiate();
        }
        // Return any runtime errors.
        return $cs->get_errors(true);
    }

    public function get_session() {
        if (null === $this->valid) {
            $this->validate(null);
        }
        return new stack_cas_session2($this->statements, $this->options, $this->seed);
    }

    public function get_variable_usage(array $updatearray = array()): array {
        if (!array_key_exists('read', $updatearray)) {
            $updatearray['read'] = array();
        }
        if (!array_key_exists('write', $updatearray)) {
            $updatearray['write'] = array();
        }
        foreach ($this->statements as $statement) {
            $updatearray = $statement->get_variable_usage($updatearray);
        }
        return $updatearray;
    }

    /**
     * Remove the ast, and other clutter from casstrings, so we can test equality cleanly and dump values.
     */
    public function test_clean() {
        $this->session->test_clean();
        return true;
    }

    /**
     * Compiles the keyval to a single statement with substatement
     * error tracking wrappings. The wrappings can contain a context-name
     * so that one can read the error messages with references like:
     *     "question-variables line 4".
     *
     * Returns the statement as well as a listing of referenced
     * variables and functions for other tasks to use. Also splits
     * out so called "blockexternals".
     *
     * Note that one must have done validation in advance.
     */
    public function compile(string $contextname): array {
        $bestatements = [];
        $statements = [];
        $contextvariables = [];

        $referenced = ['read' => [], 'write' => [], 'calls' => []];

        if (null === $this->valid) {
            throw new stack_exception('stack_cas_keyval: must validate before compiling.');
        }
        if (false === $this->valid) {
            throw new stack_exception('stack_cas_keyval: must validate true before compiling.');
        }

        if (count($this->statements) == 0) {
            // If nothing return nothing, the logic outside will deal with null.
            return ['blockexternal' => null,
                    'statement' => null,
                    'references' => $referenced];
        }

        // Now we start from the RAW form as rebuilding the line
        // references for AST-containers is not a simple thing and as
        // we plan for the future where we might include logic dealing
        // with comments as well.
        $str = $this->raw;
        // Similar QMCHAR protection as previously.
        $strings = stack_utils::all_substring_strings($str);
        foreach ($strings as $key => $string) {
            $str = str_replace('"'.$string.'"', '[STR:'.$key.']', $str);
        }

        $str = str_replace('?', 'QMCHAR', $str);

        foreach ($strings as $key => $string) {
            $str = str_replace('[STR:'.$key.']', '"' .$string . '"', $str);
        }

        // And then the parsing.
        $ast = maxima_parser_utils::parse_and_insert_missing_semicolons($str);

        // Then we will build the normal filter chain for the syntax-candy. Repeat security checks just in case.
        $errors = [];
        $answernotes = [];
        $filteroptions = ['998_security' => ['security' => 't']];
        $pipeline = stack_parsing_rule_factory::get_filter_pipeline(['998_security', '999_strict'], $filteroptions, true);
        $tostringparams = ['nosemicolon' => true, 'pmchar' => 1];
        $securitymodel = $this->security;

        // Process the AST.
        foreach ($ast->items as $item) {
            if ($item instanceof MP_Statement) {
                // As this was already validated no need to check for parse errors.
                // However we want to change positioning so that exceptions make sense.
                if (isset($ast->position['fixedsemicolons'])) {
                    $item = maxima_parser_utils::position_remap($item, $ast->position['fixedsemicolons']);
                } else {
                    $item = maxima_parser_utils::position_remap($item, $str);
                }

                // Here we could process comments or do other rewriting.
                // Probably the first use will be extracting units realted details for cas-security configuration.

                // Apply the normal filters.
                $item = $pipeline->filter($item, $errors, $answernotes, $securitymodel);

                // Render to statement.
                $scope = stack_utils::php_string_to_maxima_string($contextname . ': ' .
                        $item->position['start'] . '-' . $item->position['end']);
                $statement = '_EC(errcatch(' . $item->toString($tostringparams) . '),' . $scope . ')';

                // Update references.
                $referenced = maxima_parser_utils::variable_usage_finder($item, $referenced);

                // Check if it is one of the block externals.
                $op = '';
                if ($item->statement instanceof MP_Operation) {
                    $op = $item->statement->op;
                }
                if ($item->statement instanceof MP_FunctionCall) {
                    $op = $item->statement->name->value;
                }
                if (stack_cas_security::get_feature($op, 'blockexternal') !== null) {
                    $bestatements[] = $statement;
                } else if (stack_cas_security::get_feature($op, 'contextvariable') !== null) {
                    $contextvariables[] = $statement;
                } else {
                    $statements[] = $statement;
                }
            }
        }

        // Construct the return value.
        if (count($bestatements) == 0) {
            $bestatements = null;
        } else {
            // These statement groups always end with a 'true' to ensure minimal output.
            $bestatements = '(' . implode(',', $bestatements) . ',true)';
        }
        if (count($statements) == 0) {
            $statements = null;
        } else {
            // These statement groups always end with a 'true' to ensure minimal output.
            $statements = '(' . implode(',', $statements) . ',true)';
        }
        if (count($contextvariables) == 0) {
            $contextvariables = null;
        } else {
            // These statement groups always end with a 'true' to ensure minimal output.
            $contextvariables = '(' . implode(',', $contextvariables) . ',true)';
        }

        // Now output them for use elsewhere.
        return ['blockexternal' => $bestatements,
            'statement' => $statements,
            'contextvariables' => $contextvariables,
            'references' => $referenced];
    }
}
