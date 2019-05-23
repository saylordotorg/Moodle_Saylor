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
 * local_discoursesso
 *
 *
 * @package    local
 * @subpackage discoursesso
 * @copyright  2017 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$string['adminsynclabel'] = 'Sync administrators?';
$string['adminsynchelp'] = 'When enabled, Moodle site administrators will be an admin in Discourse.';
$string['apikeylabel'] = 'API Key';
$string['apikeyhelp'] = 'Your Discourse API key. This is different from the SSO shared secret key!';
$string['currentcohorts'] = 'Current cohorts';
$string['currentcohortsmatching'] = 'Current matching cohorts';
$string['discourseurllabel'] = 'Discourse URL';
$string['discourseurlhelp'] = 'The URL of your Discourse instance, i.e. \'https://meta.discourse.org\'.';
$string['errorcreaterecorddb'] = 'There was an error creating the record in the database.';
$string['errordeleterecorddb'] = 'There was an error deleting the record from the database.';
$string['errorcreategroupdiscourse'] = 'Something went wrong creating the group in Discourse. Does a group with the name (${a}) already exist in Discourse?';
$string['errornoapikey'] = 'No API key found. Please enter an API key on the DiscourseSSO settings page!';
$string['localelabel'] = 'Sync user locale?';
$string['localehelp'] = 'This setting will send a user\'s locale in Moodle to the Discourse server on log in. This will change the user\'s language in Discourse to match their language in Moodle.';
$string['modulename'] = 'Discourse SSO';
$string['modulename_help'] = 'A plugin to use Moodle as a SSO provider for Discourse.';
$string['modulenameplural'] = 'Discourse SSOs';
$string['pluginadministration'] = 'Discourse SSO Administration';
$string['pluginname'] = 'Discourse SSO';
$string['plugingrouppagename'] = 'Assign cohorts';
$string['potcohortsmatching'] = 'Potential matching cohorts';
$string['potcohorts'] = 'Potential cohorts';
$string['secretkeylabel'] = 'Shared SSO Secret Key';
$string['secretkeyhelp'] = 'Secret key that is shared with Discourse. Use the same key as in \'sso_secret\' of your Discourse settings.';
$string['settingstitle'] = 'Settings';
$string['toomanycohortsmatchsearch'] = 'Too many cohorts ({$a->count}) match \'{$a->search}\'';
$string['toomanycohortstoshow'] = 'Too many cohorts ({$a}) to show';