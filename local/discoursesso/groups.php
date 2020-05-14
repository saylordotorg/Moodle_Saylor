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

require_once('../../config.php');
require_once('./locallib.php');

$confirmadd = optional_param('confirmadd', 0, PARAM_INT);
$confirmdel = optional_param('confirmdel', 0, PARAM_INT);

require_login();
// Add proper permissions check.
if (!is_siteadmin()) {
    die;
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/discoursesso/groups.php');
$PAGE->set_title(format_string(get_string('plugingrouppagename', 'local_discoursesso')));
$PAGE->set_heading(format_string(get_string('plugingrouppagename', 'local_discoursesso')));
$PAGE->navbar->add(get_string('plugingrouppagename', 'local_discoursesso'));

$returnurl = new moodle_url('/admin/search.php');

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

echo $OUTPUT->header();
format_string(get_string('plugingrouppagename', 'local_discoursesso'));


// Get the user_selector we will need.
$potentialuserselector = new discoursesso_cohort_candidate_selector('addselect', array());
$existinguserselector = new discoursesso_cohort_existing_selector('removeselect', array());

// Process incoming user assignments to the cohort

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {

        foreach ($userstoassign as $adduser) {
            if (discoursesso_add_group($adduser->id) === false) {
            	echo $OUTPUT->notification('The cohort was not added.', \core\output\notification::NOTIFY_ERROR);
            }
        }

        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Process removing user assignments to the cohort
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $existinguserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            if (discoursesso_remove_group($removeuser->id) === false) {
            	echo $OUTPUT->notification('The cohort was not removed.', \core\output\notification::NOTIFY_ERROR);
            }
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Print the form.
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
  <input type="hidden" name="returnurl" value="<?php echo $returnurl->out_as_local_url() ?>" />

  <table summary="" class="generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect"><?php print_string('currentcohorts', 'local_discoursesso'); ?></label></p>
          <?php $existinguserselector->display() ?>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.s(get_string('add')); ?>" title="<?php p(get_string('add')); ?>" /><br />
          </div>

          <div id="removecontrols">
              <input name="remove" id="remove" type="submit" value="<?php echo s(get_string('remove')).'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php p(get_string('remove')); ?>" />
          </div>
      </td>
      <td id="potentialcell">
          <p><label for="addselect"><?php print_string('potcohorts', 'local_discoursesso'); ?></label></p>
          <?php $potentialuserselector->display() ?>
      </td>
    </tr>
    <tr><td colspan="3" id='backcell'>
      <input class="btn btn-primary" type="submit" name="cancel" value="<?php p(get_string('back')); ?>" />
    </td></tr>
  </table>
</div></form>

<?php

echo $OUTPUT->footer();
