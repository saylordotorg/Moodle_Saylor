<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package mod_vpl
 * @copyright 2014 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

// TODO Organize security checks.

defined( 'MOODLE_INTERNAL' ) || die();


require_once(dirname( __FILE__ ) . '/../../lib/externallib.php');
require_once(dirname( __FILE__ ) . '/locallib.php');
require_once(dirname( __FILE__ ) . '/forms/edit.class.php');
require_once(dirname( __FILE__ ) . '/vpl_submission.class.php');

class mod_vpl_webservice extends external_api {
    private static function initial_checks($id, $password) {
        $vpl = new mod_vpl( $id );
        if (! $vpl->has_capability( VPL_GRADE_CAPABILITY )) {
            if (! $vpl->pass_network_check()) {
                $message = get_string( 'opnotallowfromclient', VPL ) . ' ' . getremoteaddr();
                throw new Exception( $message );
            }
            if (! $vpl->pass_password_check( $password )) {
                throw new Exception( get_string( 'requiredpassword', VPL ) );
            }
        }
        return $vpl;
    }
    /*
     * info function. return information of the activity
     */
    public static function info_parameters() {
        return new external_function_parameters( array (
                'id' => new external_value( PARAM_INT, 'Activity id (course_module)', VALUE_REQUIRED ),
                'password' => new external_value( PARAM_RAW, 'Activity password', VALUE_DEFAULT, '' )
        ) );
    }
    public static function info($id, $password) {
        self::validate_parameters( self::info_parameters(), array (
                'id' => $id,
                'password' => $password
        ) );
        $vpl = self::initial_checks( $id, $password );
        $vpl->require_capability( VPL_VIEW_CAPABILITY );
        if (! $vpl->is_visible()) {
            throw new Exception( get_string( 'notavailable' ) );
        }
        $instance = $vpl->get_instance();
        $ret = array (
                'name' => $instance->name,
                'shortdescription' => $instance->shortdescription,
                'intro' => $instance->intro,
                'introformat' => ( int ) $instance->introformat,
                'reqpassword' => ($instance->password > '' ? 1 : 0),
                'example' => ( int ) $instance->example,
                'restrictededitor' => ( int ) $instance->restrictededitor,
                'maxfiles' => ( int ) $instance->maxfiles,
                'reqfiles' => array ()
        );
        $files = mod_vpl_edit::get_requested_files( $vpl );
        // Adapt array of name => value content to format array of objects {name, data}.
        $files = mod_vpl_edit::files2object( $files );
        $ret['reqfiles'] = $files;
        return $ret;
    }
    public static function info_returns() {
        return new external_single_structure( array (
                'name' => new external_value( PARAM_TEXT, 'Name' ),
                'shortdescription' => new external_value( PARAM_TEXT, 'Short description' ),
                'intro' => new external_value( PARAM_RAW, 'Full description' ),
                'introformat' => new external_value( PARAM_INT, 'Description format' ),
                'reqpassword' => new external_value( PARAM_INT, 'Activity requiere password' ),
                'example' => new external_value( PARAM_INT, 'Activity is an example' ),
                'restrictededitor' => new external_value( PARAM_INT, 'Activity edition is restricted' ),
                'maxfiles' => new external_value( PARAM_INT, 'Maximum number of file acepted' ),
                'reqfiles' => new external_multiple_structure( new external_single_structure( array (
                        'name' => new external_value( PARAM_TEXT, 'File name' ),
                        'data' => new external_value( PARAM_RAW, 'File content' )
                ) ) )
        ) );
    }

    /*
     * save function. save/submit the students files
     */
    public static function save_parameters() {
        return new external_function_parameters( array (
                'id' => new external_value( PARAM_INT, 'Activity id (course_module)', VALUE_REQUIRED ),
                'files' => new external_multiple_structure( new external_single_structure( array (
                        'name' => new external_value( PARAM_RAW, 'File name' ),
                        'data' => new external_value( PARAM_RAW, 'File content' )
                ) ) ),
                'password' => new external_value( PARAM_RAW, 'Activity password', VALUE_DEFAULT, '' )
        ) );
    }
    public static function save($id, $files = array(), $password = '') {
        global $USER;
        self::validate_parameters( self::save_parameters(), array (
                'id' => $id,
                'files' => $files,
                'password' => $password
        ) );
        $vpl = self::initial_checks( $id, $password );
        $vpl->require_capability( VPL_SUBMIT_CAPABILITY );
        if (! $vpl->is_submit_able()) {
            throw new Exception( get_string( 'notavailable' ) );
        }
        $instance = $vpl->get_instance();
        if ($instance->example || ($instance->restrictededitor && ! $vpl->has_capability(VPL_MANAGE_CAPABILITY))) {
            throw new Exception( get_string( 'notavailable' ) );
        }
        // Adapts to the file format VPL3.2.
        $oldfiles = $files;
        $files = array();
        foreach ($oldfiles as $file) {
            $files[$file['name']] = $file['data'];
        }
        mod_vpl_edit::save( $vpl, $USER->id, $files );
    }

    public static function save_returns() {
        return null;
    }

    /*
     * open function. return the student's submitted files
     */
    public static function open_parameters() {
        return new external_function_parameters( array (
                'id' => new external_value( PARAM_INT, 'Activity id (course_module)', VALUE_REQUIRED ),
                'password' => new external_value( PARAM_RAW, 'Activity password', VALUE_DEFAULT, '' ),
                'userid' => new external_value( PARAM_INT, 'User ID', VALUE_DEFAULT, -1 )
        ) );
    }
    public static function open($id, $password, $userid) {
        global $USER;
        self::validate_parameters( self::open_parameters(), [
                'id' => $id,
                'password' => $password,
                'userid' => $userid
        ] );
        $vpl = self::initial_checks( $id, $password );
        $vpl->require_capability( VPL_VIEW_CAPABILITY );
        if ($userid == -1) {
            $userid = $USER->id;
        } else {
            $vpl->require_capability( VPL_GRADE_CAPABILITY );
        }
        if (! $vpl->is_visible()) {
            throw new Exception( get_string( 'notavailable' ) );
        }
        $compilationexecution = new stdClass();
        $files = mod_vpl_edit::get_submitted_files( $vpl, $userid, $compilationexecution );
        // Adapt array of name => value content to format array of objects {name, data}.
        $files = mod_vpl_edit::files2object( $files );
        $ret = [
                'files' => $files,
                'compilation' => '',
                'evaluation' => '',
                'grade' => ''
        ];
        $attributes = ['compilation', 'evaluation', 'grade'];
        foreach ($attributes as $attribute) {
            if (isset($compilationexecution->$attribute)) {
                $ret[$attribute] = $compilationexecution->$attribute;
            }
        }
        return $ret;
    }
    public static function open_returns() {
        return new external_single_structure( array (
                'files' => new external_multiple_structure( new external_single_structure( array (
                        'name' => new external_value( PARAM_TEXT, 'File name' ),
                        'data' => new external_value( PARAM_RAW, 'File content' )
                ) ) ),
                'compilation' => new external_value( PARAM_RAW, 'Compilation result' ),
                'evaluation' => new external_value( PARAM_RAW, 'Evaluation result' ),
                'grade' => new external_value( PARAM_RAW, 'Proposed or final grade' )
        ) );
    }

    /*
     * evaluate function. evaluate the student's submitted files
     */
    public static function evaluate_parameters() {
        return new external_function_parameters( array (
                'id' => new external_value( PARAM_INT, 'Activity id (course_module)', VALUE_REQUIRED ),
                'password' => new external_value( PARAM_RAW, 'Activity password', VALUE_DEFAULT, '' )
        ) );
    }
    public static function evaluate($id, $password) {
        global $USER;
        self::validate_parameters( self::evaluate_parameters(), array (
                'id' => $id,
                'password' => $password
        ) );
        $vpl = self::initial_checks( $id, $password );
        $vpl->require_capability( VPL_SUBMIT_CAPABILITY );
        $instance = $vpl->get_instance();
        if (! $vpl->has_capability(VPL_GRADE_CAPABILITY)) {
            if (! $vpl->is_submit_able()) {
                throw new Exception( get_string( 'notavailable' ) );
            }
            if ($instance->example || ! $instance->evaluate) {
                throw new Exception( get_string( 'notavailable' ) );
            }
        }
        $res = mod_vpl_edit::execute( $vpl, $USER->id, 'evaluate' );
        $monitorurl = 'ws://' . $res->server . ':' . $res->port . '/' . $res->monitorPath;
        $smonitorurl = 'wss://' . $res->server . ':' . $res->securePort . '/' . $res->monitorPath;
        return array ( 'monitorURL' => $monitorurl, 'smonitorURL' => $smonitorurl  );
    }
    public static function evaluate_returns() {
        $desc = "URL to the service that monitor the evaluation in the jail server.
Protocol WebSocket may be ws: or wss: (SSL).
The jail send information as text in this format:
    (message|retrieve|close):(state(:detail)?)?
'message': the jail server reports about the changes to the client.
           With 'state' and optional 'detail?'
'retrieve': the client must get the results of the evaluation
            (call mod_vpl_get_result, the server is waiting).
'close': the conection is to be closed.
if the websocket client send something to the server then the evaluation is stopped.";
        return new external_single_structure( array (
                'monitorURL' => new external_value( PARAM_RAW, $desc ),
                'smonitorURL' => new external_value( PARAM_RAW, $desc ),
        ) );
    }

    /*
     * get_result function. retrieve the result of the evaluation
     */
    public static function get_result_parameters() {
        return new external_function_parameters( array (
                'id' => new external_value( PARAM_INT, 'Activity id (course_module)', VALUE_REQUIRED ),
                'password' => new external_value( PARAM_RAW, 'Activity password', VALUE_DEFAULT, '' )
        ) );
    }
    public static function get_result($id, $password) {
        global $USER;
        self::validate_parameters( self::get_result_parameters(), array (
                'id' => $id,
                'password' => $password
        ) );
        $vpl = self::initial_checks( $id, $password );
        $vpl->require_capability( VPL_SUBMIT_CAPABILITY );
        $instance = $vpl->get_instance();
        if (! $vpl->has_capability(VPL_GRADE_CAPABILITY)) {
            if (! $vpl->is_submit_able()) {
                throw new Exception( get_string( 'notavailable' ) );
            }
            if ($instance->example || ! $instance->evaluate) {
                throw new Exception( get_string( 'notavailable' ) );
            }
        }
        $compilationexecution = mod_vpl_edit::retrieve_result( $vpl, $USER->id );
        $ret = [
            'compilation' => '',
            'evaluation' => '',
            'grade' => ''
        ];
        $attributes = ['compilation', 'evaluation', 'grade'];
        foreach ($attributes as $attribute) {
            if (isset($compilationexecution->$attribute)) {
                $ret[$attribute] = $compilationexecution->$attribute;
            }
        }
        return $ret;
    }
    public static function get_result_returns() {
        return new external_single_structure( array (
                'compilation' => new external_value( PARAM_RAW, 'Compilation result' ),
                'evaluation' => new external_value( PARAM_RAW, 'Evaluation result' ),
                'grade' => new external_value( PARAM_RAW, 'Proposed or final grade' )
        ) );
    }
}
