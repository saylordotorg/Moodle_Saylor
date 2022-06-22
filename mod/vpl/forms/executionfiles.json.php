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
 * Processes AJAX to edit execution files
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

define( 'AJAX_SCRIPT', true );

require(__DIR__ . '/../../../config.php');

global $PAGE, $OUTPUT, $USER;

$result = new stdClass();
$result->success = true;
$result->response = new stdClass();
$result->error = '';
try {
    require_once(dirname( __FILE__ ) . '/../locallib.php');
    require_once(dirname( __FILE__ ) . '/../vpl.class.php');
    require_once(dirname( __FILE__ ) . '/edit.class.php');
    if (! isloggedin()) {
        throw new Exception( get_string( 'loggedinnot' ) );
    }
    $id = required_param( 'id', PARAM_INT ); // Course id.
    $action = required_param( 'action', PARAM_ALPHANUMEXT );
    $vpl = new mod_vpl( $id );
    // TODO use or not sesskey "require_sesskey();".
    require_login( $vpl->get_course(), false );
    $vpl->require_capability( VPL_MANAGE_CAPABILITY );
    $PAGE->set_url( new moodle_url( '/mod/vpl/forms/executionfiles.json.php', array (
            'id' => $id,
            'action' => $action
    ) ) );
    echo $OUTPUT->header(); // Send headers.
    $actiondata = json_decode( file_get_contents( 'php://input' ) );
    switch ($action) {
        case 'save' :
            $postfiles = mod_vpl_edit::filesfromide($actiondata->files);
            $result->response->requestsconfirmation = false;
            $fgm = $vpl->get_execution_fgm();
            $oldversion = $fgm->getversion();
            if ($actiondata->version != 0 && $actiondata->version != $oldversion) {
                $result->response->question = get_string('replacenewer', VPL);
                $result->response->requestsconfirmation = true;
                $result->response->version = $oldversion;
                break;
            }
            $fgm->deleteallfiles();
            $fgm->addallfiles($postfiles);
            $result->response->version = $fgm->getversion();
            $vpl->update();
            break;
        case 'load' :
            $fgm = $vpl->get_execution_fgm();
            $files = $fgm->getallfiles();
            $result->response->files = mod_vpl_edit::filestoide( $files );
            $result->response->version = $fgm->getversion();
            break;
        case 'run' :
        case 'debug' :
        case 'evaluate' :
            $result->response = mod_vpl_edit::execute( $vpl, $USER->id, $action, $actiondata );
            break;
        case 'retrieve' :
            $result->response = mod_vpl_edit::retrieve_result( $vpl, $USER->id );
            break;
        case 'cancel' :
            $result->response = mod_vpl_edit::cancel( $vpl, $USER->id );
            break;
        case 'getjails' :
            $result->response->servers = vpl_jailserver_manager::get_https_server_list( $vpl->get_instance()->jailservers );
            break;
        default :
            throw new Exception( 'ajax action error: ' + $action);
    }
} catch ( Exception $e ) {
    $result->success = false;
    $result->error = $e->getMessage();
}
echo json_encode( $result );
die();
