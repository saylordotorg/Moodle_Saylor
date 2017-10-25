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
 * Sayonara
 *
 * This fork of Goodbye is designed to work with Moodle 3.2+ and the Boost theme.
 * The option to delete will be in the user's profile.
 *
 * @package    local
 * @subpackage sayonara
 * @copyright  2017 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Goodbye
 *
 * This module has been created to provide users the option to delete their account
 *
 * @package    local
 * @subpackage goodbye, delete your moodle account
 * @copyright  2013 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function local_sayonara_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE, $USER;

    $enabled = get_config('local_sayonara', 'enabled');
    $systemcontext = context_system::instance();
    $title = get_string('manageaccount', 'local_sayonara');
    $url = new moodle_url('/local/sayonara/index.php', array('sesskey'=>sesskey()));

    // Should be the same capability checks as editing your own profile in myprofile lib.
    if ((isloggedin() && !isguestuser($user) && !is_mnet_remote_user($user)) && $iscurrentuser && has_capability('moodle/user:editownprofile', $systemcontext)) {
        // Only show deletion option if the plugin is enabled and user auth is email or manual.
        if ($enabled && ($USER->auth == 'email' || $USER->auth == 'manual')) {

            $node = new core_user\output\myprofile\node('miscellaneous', 'sayonara', $title, null,
                $url);
            $tree->add_node($node);

            return true;
        }
    }

    return false;
}
