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
 * Language strings.
 *
 * @package     factor_totp
 * @subpackage  tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action:revoke'] = 'Revoke TOTP authenticator';
$string['devicename'] = 'Device label';
$string['devicenameexample'] = 'eg "Work iPhone 11"';
$string['devicename_help'] = 'This is the device you have an authenticator app installed on. You can setup multiple devices so this label helps track which ones are being used. You should setup each device with their own unique code so they can be revoked separately.';
$string['error:wrongverification'] = 'Incorrect verification code';
$string['error:codealreadyused'] = 'This code has already been used to authenticate. Please wait for a new code to be generated, and try again.';
$string['error:oldcode'] = 'This code is too old. Please verify the time on your authenticator device is correct and try again.
    Current system time is {$a}.';
$string['error:futurecode'] = 'This code is invalid. Please verify the time on your authenticator device is correct and try again.
    Current system time is {$a}.';
$string['info'] = '<p>Use any TOTP authenticator app to get a verification code on your phone even when it is offline.</p>
eg. <ul><li><a href="https://authy.com/download/">Twilio Authy</a></li>
<li><a href="https://www.microsoft.com/en-us/account/authenticator#getapp">Microsoft Authenticator</a></li>
<li>Google Authenticator for <a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8" target="_blank">iOS</a> or <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Android</a></li></ul>
<p>Note: Please ensure your device time and date has been set to "Auto" or "Network provided".</p>';
$string['loginsubmit'] = 'Verify code';
$string['loginskip'] = 'I don\'t have my device';
$string['pluginname'] = 'Authenticator app';
$string['privacy:metadata'] = 'The TOTP factor plugin does not store any personal data';
$string['settings:secretlength'] = 'TOTP secret key length';
$string['settings:secretlength_help'] = 'Generated TOTP secret key string length';
$string['settings:totplink'] = 'Show mobile app setup link';
$string['settings:totplink_help'] = 'If enabled the user will see a 3rd setup option with a direct otpauth:// link';
$string['settings:window'] = 'TOTP verification window';
$string['settings:window_help'] = 'How long each code is valid for. You can set this to a higher value as a workaround if your users device clocks are often slightly wrong.
    Rounded down to the nearest 30 seconds, which is the time between new generated codes.';
$string['setupfactor'] = 'TOTP authenticator setup';
$string['setupfactor:account'] = 'Account:';
$string['setupfactor:link'] = '<b> OR </b> open mobile app:';
$string['setupfactor:link_help'] = 'If you are on a mobile device and already have an authenticator app installed this link may work. Note that using TOTP on the same device as you login on can weaken the benefits of MFA.';
$string['setupfactor:linklabel'] = 'Open app already installed on this device';
$string['setupfactor:mode'] = 'Mode:';
$string['setupfactor:mode:timebased'] = 'Time-based';
$string['setupfactor:scan'] = 'Scan QR code:';
$string['setupfactor:enter'] = '<b> OR </b> enter details manually:';
$string['setupfactor:enter_help'] = 'When manually adding the secret code, set the account name in the app to something that will help to identify this code to the platform, such as the site name. Ensure the selected mode is time-based.';
$string['setupfactor:key'] = 'Secret key: ';
$string['verificationcode'] = 'Enter your 6 digit verification code';
$string['verificationcode_help'] = 'Open your authenticator app such as Google Authenticator and look for the 6 digit code which matches this site and username';
$string['summarycondition'] = 'using a TOTP app';
$string['factorsetup'] = 'Setup App';
$string['systimeformat'] = '%l:%M:%S %P %Z';
