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

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2017052700;
$plugin->requires = 2016052306;  // Requires this Moodle version - at least 3.1
$plugin->cron     = 0;
$plugin->component = 'local_discoursesso';

$plugin->release = '1.0.0';
$plugin->maturity = MATURITY_STABLE;