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
 * View user acceptances to the policies
 *
 * @package     tool_policy
 * @copyright   2018 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/user/editlib.php');

$userid = optional_param('userid', null, PARAM_INT);
$acceptforversion = optional_param('acceptforversion', null, PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_LOCALURL);

require_login();
$userid = $userid ?: $USER->id;
if (isguestuser() || isguestuser($userid)) {
    print_error('noguest');
}
$context = context_user::instance($userid);
if ($acceptforversion) {
    // Check capability to accept the policy for oneself or on behalf of another user.
    if ($userid == $USER->id) {
        require_capability('tool/policy:accept', context_system::instance());
    } else {
        require_capability('tool/policy:acceptbehalf', $context);
    }
} else if ($userid != $USER->id) {
    // Check capability to view acceptances. No capability is needed to view your own acceptances.
    if (!has_capability('tool/policy:acceptbehalf', $context)) {
        require_capability('tool/policy:viewacceptances', $context);
    }
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/admin/tool/policy/user.php', ['userid' => $userid]));

if ($acceptforversion) {
    $user = $DB->get_record('user', ['id' => $userid], 'id,'.get_all_user_name_fields(true), MUST_EXIST);
    $returnurl = $returnurl ? new moodle_url($returnurl) : new moodle_url('/admin/tool/policy/user.php', ['userid' => $user->id]);
    $version = tool_policy\api::get_policy_version($acceptforversion);
    $form = new \tool_policy\form\accept_policy(null, ['versions' => [$version], 'users' => [$user]]);
    $form->set_data(['returnurl' => $returnurl]);

    if ($form->is_cancelled()) {
        redirect($returnurl);
    } else if ($data = $form->get_data()) {
        \tool_policy\api::accept_policies([$acceptforversion], $user->id, $data->note);
        redirect($returnurl);
    }
}

$output = $PAGE->get_renderer('tool_policy');
echo $output->header();
if ($acceptforversion) {
    echo $output->heading(get_string('consentdetails', 'tool_policy'));
    $form->display();
} else {
    echo $output->heading(get_string('policiesagreements', 'tool_policy'));
    $acceptances = new \tool_policy\output\acceptances($userid);
    echo $output->render($acceptances);
}
echo $output->footer();
