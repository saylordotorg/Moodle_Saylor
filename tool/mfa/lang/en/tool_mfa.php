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
//
/**
 * Strings for component 'tool_mfa', language 'en'.
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['achievedweight'] = 'Achieved weight';
$string['areyousure'] = 'Are you sure you want to revoke factor?';
$string['combination'] = 'Combination';
$string['created'] = 'Created';
$string['createdfromip'] = 'Created from IP';
$string['debugmode:heading'] = 'Debug mode';
$string['debugmode:currentweight'] = 'Current weight: {$a}';
$string['devicename'] = 'Device';
$string['enablefactor'] = 'Enable factor';
$string['error:directaccess'] = 'This page shouldn\'t be accessed directly';
$string['error:factornotfound'] = 'MFA Factor \'{$a}\' not found';
$string['error:wrongfactorid'] = 'Factor id \'{$a}\' is incorrect';
$string['error:actionnotfound'] = 'Action \'{$a}\' not supported';
$string['error:setupfactor'] = 'Can not setup factor';
$string['error:revoke'] = 'Can not revoke factor';
$string['error:notenoughfactors'] = 'Unable to authenticate';
$string['error:reauth'] = 'We couldn\'t confirm your identity sufficiently to meet this sites authentication security policy.<br>This may be due to: <br> 1) Steps being locked - please wait a few minutes and try again.
     <br> 2) Steps being failed - please double check the details in each step. <br> 3) Steps were skipped - please reload this page or try logging in again.';
$string['error:support'] = 'If you are still unable to login, or believe you are seeing this in error,
     please send an email to the following address for support:';
$string['error:home'] = 'Click here to return home.';
$string['error:factornotenabled'] = 'MFA Factor \'{$a}\' not enabled';
$string['email:subject'] = 'Unable to login to {$a}';
$string['event:userpassedmfa'] = 'Verification passed';
$string['event:userrevokedfactor'] = 'Factor revocation';
$string['event:usersetupfactor'] = 'Factor setup';
$string['event:userdeletedfactor'] = 'Factor Deleted';
$string['event:userfailedmfa'] = 'User failed MFA authentication';
$string['event:faillockout'] = 'MFA Authentication failed due to too many attempts.';
$string['event:failnotenoughfactors'] = 'MFA Authentication failed due to not enough satisfied factors.';
$string['event:failfactor'] = 'MFA Authentication failed due to a failed factor.';
$string['factor'] = 'Factor';
$string['fallback'] = 'Fallback factor';
$string['fallback_info'] = 'This factor is a fallback if no other factors are configured. This factor will always fail.';
$string['gotourl'] = 'Go to your original URL: ';
$string['guidance'] = 'MFA user guide';
$string['inputrequired'] = 'User input';
$string['ipatcreation'] = 'IP address when factor created';
$string['lastverified'] = 'Last verified';
$string['lockedusersforfactor'] = 'Locked users: {$a}';
$string['lockedusersforallfactors'] = 'Locked users: All factors';
$string['mfa'] = 'MFA';
$string['mfasettings'] = 'Manage MFA';
$string['na'] = 'n/a';
$string['needhelp'] = 'Need help?';
$string['nologinusers'] = 'Not logged in';
$string['nonauthusers'] = 'Pending MFA';
$string['overall'] = 'Overall';
$string['pluginname'] = 'Multi-factor authentication';
$string['preferences:header'] = 'Multi-factor authentication preferences';
$string['preferences:availablefactors'] = 'Available factors';
$string['preferences:activefactors'] = 'Active factors';
$string['privacy:metadata:tool_mfa'] = 'Data with configured MFA factors';
$string['privacy:metadata:tool_mfa:id'] = 'Record ID';
$string['privacy:metadata:tool_mfa:userid'] = 'The ID of the user that factor belongs to';
$string['privacy:metadata:tool_mfa:factor'] = 'Factor type';
$string['privacy:metadata:tool_mfa:secret'] = 'Any secret data for factor';
$string['privacy:metadata:tool_mfa:label'] = 'label for factor instance, eg device or email';
$string['privacy:metadata:tool_mfa:timecreated'] = 'Time the factor instance was setup';
$string['privacy:metadata:tool_mfa:createdfromip'] = 'IP that the factor was setup from';
$string['privacy:metadata:tool_mfa:timemodified'] = 'Time factor was last modified';
$string['privacy:metadata:tool_mfa:lastverified'] = 'Time user was last verified with this factor';
$string['privacy:metadata:tool_mfa_secrets'] = 'This database table stores temporary secrets for user authentication.';
$string['privacy:metadata:tool_mfa_secrets:userid'] = 'The user this secret is associated with.';
$string['privacy:metadata:tool_mfa_secrets:factor'] = 'The factor this secret is associated with.';
$string['privacy:metadata:tool_mfa_secrets:secret'] = 'The secret security code.';
$string['privacy:metadata:tool_mfa_secrets:sessionid'] = 'The sessionid this secret is associated with.';
$string['privacy:metadata:tool_mfa_auth'] = 'This database table stores the last time a successful MFA auth was registered for a user id.';
$string['privacy:metadata:tool_mfa_auth:userid'] = 'The user this timestamp is associated with.';
$string['privacy:metadata:tool_mfa_auth:lastverified'] = 'Time user was last authenticated with';
$string['revoke'] = 'Revoke';
$string['revokefactor'] = 'Revoke factor';
$string['settings:enabled'] = 'MFA plugin enabled';
$string['settings:enabled_help'] = '';
$string['settings:combinations'] = 'Summary of good conditions for login';
$string['settings:general'] = 'General MFA settings';
$string['settings:debugmode'] = 'Enable debug mode';
$string['settings:debugmode_help'] = 'Debug mode will display a small notification banner on MFA admin pages, as well as the user preferences page
         with information on the currently enabled factors.';
$string['settings:duration'] = 'Secret validity duration';
$string['settings:duration_help'] = 'The duration that generated secrets are valid.';
$string['settings:enablefactor'] = 'Enable factor';
$string['settings:enablefactor_help'] = 'Check this control to allow the factor to be used for MFA authentication.';
$string['settings:guidancecheck'] = 'Use guidance page';
$string['settings:guidancecheck_help'] = 'Add a link to the guidance page on the MFA authentication pages, and MFA preferences page.';
$string['settings:guidancefiles'] = 'Guidance page files';
$string['settings:guidancefiles_help'] = 'Add any files here to use in the guidance page, and embed them into the page using {{filename}} (resolved path) or {{{filename}}} (html link) in the editor';
$string['settings:guidancepage'] = 'Guidance page content';
$string['settings:guidancepage_help'] = 'HTML here will be displayed on the guidance page. Enter filenames from the filearea to embed the file with the resolved path {{filename}} or as a html link using {{{filename}}}.';
$string['settings:lockout'] = 'Lockout threshold';
$string['settings:lockout_help'] = 'Amount of attempts a user has at answering input factors before they are not permitted to login.';
$string['settings:redir_exclusions'] = 'Urls which should not redirect the MFA check';
$string['settings:redir_exclusions_help'] = 'Each new line is a relative URL from the siteroot for which the MFA check will not redirect from eg. /admin/tool/securityquestions/set_responses.php';
$string['settings:weight'] = 'Factor weight';
$string['settings:weight_help'] = 'The weight of this factor if passed. A user needs at least 100 points to login.';
$string['setup'] = 'Setup';
$string['setuprequired'] = 'User setup';
$string['setupfactor'] = 'Setup factor';
$string['state:pass'] = 'Pass';
$string['state:fail'] = 'Fail';
$string['state:unknown'] = 'Unknown';
$string['state:neutral'] = 'Neutral';
$string['state:locked'] = 'Locked';
$string['totalweight'] = 'Total weight';
$string['weight'] = 'Weight';
$string['mfareports'] = 'MFA reports';
$string['factorreport'] = 'All factor report';
$string['lockoutnotification'] = 'You have {$a} verification attempts remaining for this factor.';
$string['mfa:mfaaccess'] = 'Interact with MFA';
$string['factorsetup'] = 'Factor \'{$a}\' successfully setup.';
$string['factorrevoked'] = 'Factor \'{$a}\' successfully revoked.';
$string['factorlocked'] = 'Factor \'{$a}\' has been locked due to exceeded failed attempts.';
$string['factorreset'] = 'Your MFA \'{$a->factor}\' has been reset by a site administrator. You may need to set up this factor again. {$a->url}';
$string['factorresetall'] = 'All your MFA factors have been reset by a site administrator. You may need to set up these factors again. {$a}';
$string['preferenceslink'] = 'Click here to go to user preferences.';
$string['connector'] = 'AND';
$string['pending'] = 'Pending';
$string['performbulk'] = 'Bulk action';
$string['redirecterrordetected'] = 'Unsupported redirect detected, script execution terminated. Redirection error occured between MFA and {$a}.';
$string['resetfactor'] = 'Reset user authentication factors';
$string['resetfactorconfirm'] = 'Are you sure you wish to reset this factor for {$a}?';
$string['resetuser'] = 'User:';
$string['resetsuccess'] = 'Factor \'{$a->factor}\' successfully reset for user \'{$a->username}\'.';
$string['resetsuccessbulk'] = 'Factor \'{$a}\' successfully reset for provided users.';
$string['selectfactor'] = 'Select factor to reset:';
$string['resetfactorplaceholder'] = 'Username or email';
$string['userempty'] = 'User cannot be empty.';
$string['resetconfirm'] = 'Reset user factor';
$string['usernotfound'] = 'Unable to locate user.';
$string['totalusers'] = 'Total users';
$string['usersauthedinperiod'] = 'Logged in';
$string['lookbackperiod'] = 'Showing MFA information from {$a} onwards.';
$string['alltime'] = 'All time';
$string['selectperiod'] = 'Select a lookback period for the report:';
$string['userlogs'] = 'User logs';
$string['verificationcode'] = 'Enter verification code for confirmation';
$string['verificationcode_help'] = 'The verification code provided by the current authentication factor.';
$string['viewlockedusers'] = 'View locked users';
