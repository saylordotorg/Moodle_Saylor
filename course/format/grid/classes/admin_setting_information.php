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
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a
 *               light box of the section.
 *
 * @package    format_grid
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2022 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid;

/**
 * Setting that displays information.  Based on admin_setting_description in adminlib.php.
 *
 * @copyright  &copy; 2022-onwards G J Barnard.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class admin_setting_information extends \admin_setting {

    /** @var int The branch this Grid format is for. */
    protected $mbranch;

    /**
     * Not a setting, just information.
     *
     * @param string $name Setting name.
     * @param string $visiblename Setting name on the device.
     * @param string $description Setting description on the device.
     * @param string $mbranch The branch this Grid format is for.
     */
    public function __construct($name, $visiblename, $description, $mbranch) {
        $this->nosave = true;
        $this->mbranch = $mbranch;
        return parent::__construct($name, $visiblename, $description, '');
    }

    /**
     * Always returns true.
     *
     * @return bool Always returns true.
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true.
     *
     * @return bool Always returns true.
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Never write settings
     *
     * @param mixed $data Gets converted to str for comparison against yes value.
     * @return string Always returns an empty string.
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Returns an HTML string
     *
     * @param string $data
     * @param string $query
     * @return string Returns an HTML string
     */
    public function output_html($data, $query='') {
        global $CFG, $OUTPUT;

        $formats = \core_plugin_manager::instance()->get_present_plugins('format');
        if (!empty($formats['grid'])) {
            $plugininfo = $formats['grid'];
        } else {
            $plugininfo = \core_plugin_manager::instance()->get_plugin_info('format_grid');
            $plugininfo->version = $plugininfo->versiondisk;
        }

        $context['versioninfo'] = get_string('versioninfo', 'format_grid',
            array(
                'moodle' => $CFG->release,
                'release' => $plugininfo->release,
                'version' => $plugininfo->version
            )
        );

        if (!empty($plugininfo->maturity)) {
            switch ($plugininfo->maturity) {
                case MATURITY_ALPHA:
                    $context['maturity'] = get_string('versionalpha', 'format_grid');
                    $context['maturityalert'] = 'danger';
                break;
                case MATURITY_BETA:
                    $context['maturity'] = get_string('versionbeta', 'format_grid');
                    $context['maturityalert'] = 'danger';
                break;
                case MATURITY_RC:
                    $context['maturity'] = get_string('versionrc', 'format_grid');
                    $context['maturityalert'] = 'warning';
                break;
                case MATURITY_STABLE:
                    $context['maturity'] = get_string('versionstable', 'format_grid');
                    $context['maturityalert'] = 'info';
                break;
            }
        }

        if ($CFG->branch != $this->mbranch) {
            $context['versioncheck'] = 'Release '.$plugininfo->release.', version '.$plugininfo->version.' is incompatible with Moodle '.$CFG->release;
            $context['versioncheck'] .= ', please get the correct version from ';
            $context['versioncheck'] .= '<a href="https://moodle.org/plugins/format_grid" target="_blank">Moodle.org</a>.  ';
            $context['versioncheck'] .= 'If none is available, then please consider supporting the format by funding it.  ';
            $context['versioncheck'] .= 'Please contact me via \'gjbarnard at gmail dot com\' or my ';
            $context['versioncheck'] .= '<a href="http://moodle.org/user/profile.php?id=442195">Moodle dot org profile</a>.  ';
            $context['versioncheck'] .= 'This is my <a href="http://about.me/gjbarnard">\'Web profile\'</a> if you want ';
            $context['versioncheck'] .= 'to know more about me.';
        }

        return $OUTPUT->render_from_template('format_grid/grid_admin_setting_information', $context);
    }
}
