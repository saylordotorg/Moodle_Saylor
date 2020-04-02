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

// Designed to be redirected from moodle/login/index.php

require_once('../../config.php');
require_once($CFG->dirroot . '/auth/nsdc/locallib.php'); 


$context = context_system::instance();
$PAGE->set_url('/auth/nsdc/test.php');
$PAGE->set_context($context);

$pluginconfig = get_config('auth_nsdc');  

// Check whether keys are set up in the settings
if (empty($pluginconfig->key)) {
    throw new \moodle_exception('nsdc_no_key', 'auth_nsdc');
}
if (empty($pluginconfig->iv)) {
    throw new \moodle_exception('nsdc_no_iv', 'auth_nsdc');
}
if (empty($pluginconfig->baseemail)) {
    throw new \moodle_exception('nsdc_no_baseemail', 'auth_nsdc');
}

?>

<p>Key -- <?php echo $pluginconfig->key; ?></p>
<br>
<p>IV -- <?php echo $pluginconfig->iv; ?></p>
<br>
<p>Base email -- <?php echo $pluginconfig->baseemail; ?></p>
