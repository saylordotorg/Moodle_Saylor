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
 * auth_nsdc
 *
 *
 * @package    auth
 * @subpackage nsdc
 * @copyright  2020 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['auth_nsdcdescription']                     = 'Provide SSO support for logging in through the NSDC portal.';
$string['pluginname']                               = 'NSDC';
$string['settingsnsdc']             	            = '';
$string['settingsnsdcproductionlabel']				= 'Use production settings';
$string['settingsnsdcproductionhelp']				= 'Check this if you are connecting to the NSDC production environment.';
$string['settingsnsdckeylabel']						= 'SSO Key';
$string['settingsnsdckeydescription']				= 'Enter the SSO key provided by NSDC. Keep it base64 encoded.';
$string['settingsnsdcapikeylabel']					= 'API Key';
$string['settingsnsdcapikeydescription']			= 'Enter the API key provided by NSDC.';
$string['settingsnsdcivlabel']						= 'SSO IV';
$string['settingsnsdcivdescription']				= 'Enter the SSO IV provided by NSDC. Keep it base64 encoded.';
$string['settingsnsdcbaseemaillabel']				= 'Base email';
$string['settingsnsdcbaseemaildescription']			= "This is the base email address that is used when generating fake email addresses for users that don't have an email address listed with NSDC. These users only have a phone number.<br>It is assumed that this base email will behave like a Gmail address: generated emails will be in the format baseemail+{nsdcid}@basedomain.com";


$string['could_not_create_account']					= 'There was an error creating an account.';
$string['could_not_create_account_exists']			= 'There was an error creating an account. An account already exists using this email address.';
$string['nsdc_no_key']								= 'The auth_nsdc plugin is not configured. Please add the key from NSDC to the plugin configuration.';
$string['enrolerror']								= 'There was an error enrolling this student into the course.';
$string['nsdc_api_returned_error']					= 'The NSDC API reported an error:{$a}';
$string['nsdc_no_iv']								= 'The auth_nsdc plugin is not configured. Please add the IV from NSDC to the plugin configuration.';
$string['nsdc_no_baseemail']						= 'The auth_nsdc plugin is not configured. Please add an email to the plugin configuration';
$string['timeverificationfailed']					= 'Time stamp verification has failed.';