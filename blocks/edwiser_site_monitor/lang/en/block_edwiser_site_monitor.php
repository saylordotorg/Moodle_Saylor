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
 * Strings for component 'block_edwiser_site_monitor'.
 *
 * @package    block_edwiser_site_monitor
 * @copyright  2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yogesh Shirsath
 */

defined('MOODLE_INTERNAL') || die();

$string['configtitle'] = 'Edwiser Site Monitor block title';
$string['pluginname'] = 'Edwiser Site Monitor';
$string['privacy:metadata'] = 'The site monitor block shows information about server.';
$string['liveusage'] = 'Live usage';
$string['last24hoursusage'] = 'Last 24 hours usage';
$string['edwiserplugins'] = 'Edwiser Plugins';
$string['otherplugins'] = 'Other Plugins';
$string['cpu'] = 'CPU';
$string['memory'] = 'Memory';
$string['storage'] = 'Storage';
$string['cpuusage'] = 'CPU usage';
$string['memoryusage'] = 'Memory usage';
$string['storageusage'] = 'Storage usage';
$string['users'] = 'Users';
$string['activeusers'] = 'Active';
$string['suspendedusers'] = 'Suspended';
$string['deletedusers'] = 'Deleted';
$string['liveusers'] = 'Live users';
$string['crontask'] = 'Edwiser Site Monitor - Collect 24 hours usage statistics';
$string['crontaskwarning'] = 'To see 24 hours usage data you need to run cron task for every 5 minutes. Follow this <a target="_blank" href="{$a}">link</a> to setup cron.';
$string['today'] = 'Today';
$string['cannotshowonsidebar'] = 'Cannot show usage chart in sidebar. Please move block to content region to see usage chart.';
$string['lowusage'] = 'Low usage';
$string['moderateusage'] = 'Moderate usage';
$string['highusage'] = 'High usage';
$string['yaxistitle'] = 'Usage(%)';
$string['xaxistitle'] = 'Time';
$string['messageprovider:notifications'] = 'Edwiser News and Updates';
$string['news'] = 'Edwiser News and Updates';

// Privacy strings.
$string['privacy:metadata:block_edwiser_site_monitor:tableexplanation'] = 'Stores the cpu, memory and storage usage of site.';
$string['privacy:metadata:block_edwiser_site_monitor:time'] = 'Time when usage is recorded.';
$string['privacy:metadata:block_edwiser_site_monitor:cpu'] = 'Cpu usage of site when it is recorded.';
$string['privacy:metadata:block_edwiser_site_monitor:memory'] = 'Memory usage of site when it is recorded.';
$string['privacy:metadata:block_edwiser_site_monitor:storage'] = 'Storage usage of site when it is recorded.';


// Config page strings.
$string['edwiser_site_monitor:addinstance'] = 'Add a new site monitor block';
$string['edwiser_site_monitor:myaddinstance'] = 'Add a new site monitor to Dashboard';
$string['livestatusrefreshrate'] = 'Refresh rate';
$string['livestatusrefreshrate_help'] = 'Refresh rate for live status tab in seconds';
$string['livestatusrefreshrate_invalid'] = 'Please provide valid refresh rate in seconds';

$string['thresholdheader'] = 'Threshold limit';
$string['usageemailsubject'] = '{$a}: Edwiser Site Monitor - Usage Warning';
$string['header-type'] = 'Type';
$string['hader-threshold'] = 'Threshold';
$string['header-expected'] = 'Low/High';
$string['header-current'] = 'Current';
$string['low'] = 'Low';
$string['high'] = 'High';

$string['enablethreshold'] = 'Enable threshold limits';
$string['enablethreshold_help'] = 'This will enable threshold checks and send email if usage is less than lower limit or more than highest limit';

$string['cpulowerlimit'] = 'CPU lower limit';
$string['cpuhigherlimit'] = 'CPU higher limit';
$string['cpulowerlimit_help'] = 'Lower limit of CPU usage';
$string['cpuhigherlimit_help'] = 'Higher limit of CPU usage';
$string['cpulimit_invalid'] = 'Please provide valid CPU limit';
$string['cpulimit_overlap'] = 'Please provide valid CPU limit. Higher limit cannot be equal or less than lower limit';
$string['cpucommandnotallowed'] = 'Command line <strong>/proc/cpuinfo</strong> --Permission Denied.';

$string['memorylowerlimit'] = 'Memory lower limit';
$string['memoryhigherlimit'] = 'Memory higher limit';
$string['memorylowerlimit_help'] = 'Lower limit of memory usage';
$string['memoryhigherlimit_help'] = 'Higher limit of memory usage';
$string['memorylimit_invalid'] = 'Please provide valid memory limit';
$string['memorylimit_overlap'] = 'Please provide valid memory limit. Higher limit cannot be equal or less than lower limit';
$string['memorycommandnotallowed'] = 'Command line <strong>free</strong> --Permission Denied.';

$string['storagelowerlimit'] = 'Storage lower limit';
$string['storagehigherlimit'] = 'Storage higher limit';
$string['storagelowerlimit_help'] = 'Lower limit of storage usage';
$string['storagehigherlimit_help'] = 'Higher limit of storage usage';
$string['storagelimit_invalid'] = 'Please provide valid storage limit';
$string['storagelimit_overlap'] = 'Please provide valid storage limit. Higher limit cannot be equal or less than lower limit';

// Plugin update list.
$string['errors'] = 'Errors';
$string['invalidzip'] = 'Invalid zip file. <b>{$a}</b>';
$string['errorfetching'] = 'Error fetching plugin ZIP. <b>{$a}</b>';
$string['errorfetchingexist'] = 'Error fetching plugin ZIP: target location exists. <b>{$a}</b>';
$string['unabletounzip'] = 'Unable to unzip <b>{$a}</b>';
$string['unabletoloadplugindetails'] = 'Unable to load Plugin details <b>{$a}</b>';
$string['requirehigherversion'] = 'Requires Moodle version: <b>{$a}</b>';
$string['noupdates'] = 'Everything is up to date.';
$string['invalidjsonfile'] = 'Invalid Edwiser product list.';
$string['recommendation'] = 'Recommended Plugins';
$string['comeswith'] = 'Comes with: {$a}';
$string['changelog'] = 'Change Log';
$string['installnew'] = 'Install new plugin';

// Recommendation.
$string['knowmore'] = 'Know More';

// Contact us.
$string['contactus'] = 'Contact Us';
$string['policy'] = 'I agree to the <a href="{$a}" target="_blank">Privacy Policy</a>';
$string['emailsent'] = 'Email sent.';
$string['emailfailed'] = 'Unable to Send Email.';
$string['checksettings'] = 'Please check <a target="_blank" href="{$a->link}">{$a->text}</a>';
$string['thankssubject'] = 'Thank you for contacting us.';
$string['thanksmessage'] = '<div style="background-color: #efefef; -webkit-text-size-adjust: none !important; margin: 0; padding: 70px 70px 70px 70px;"><table id="template_container" style="text-align: center; padding-bottom: 20px; background-color: rgb(223, 223, 223); box-shadow: rgba(0, 0, 0, 0.024) 0px 0px 0px 3px !important; border-radius: 6px !important; margin: auto;" border="0" width="500" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="background-color: #1177d1;border-top-left-radius: 6px !important;border-top-right-radius: 6px !important;border-bottom: 0;font-family: Arial;font-weight: bold;line-height: 100%;vertical-align: middle;">
<center><img style="height: 60px;" src="https://edwiser.org/wp-content/uploads/2016/07/edwiser-logoalternate.png"></center>
</td>
</tr>
<tr>
<td style="padding: 20px; background-color: #dfdfdf; border-radius: 6px !important;" align="center" valign="top">
<div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">Hi {$a->user},</div><div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"><br>Thank you for contacting us.<br><br> Our support team will get back to within next 24 hours.</div><br><div style="font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;">This is an auto-generated email. For any additional queries or help, you can get in touch with us at {$a->email}</div></td></tr></tbody></table><br>
</div>';
$string['emailsuccess'] = 'Your message was sent successfully. Thanks.';
$string['failed'] = 'Failed';
