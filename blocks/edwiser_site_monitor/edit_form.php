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
 * Edit form for block configuration
 *
 * @package    block_edwiser_site_monitor
 * @copyright  2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yogesh Shirsath
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define definition for block settings form
 *
 * @copyright  2019 WisdmLabs <edwiser@wisdmlabs.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_edwiser_site_monitor_edit_form extends block_edit_form {

    /**
     * Add form element specific to block settings definition
     * @param  object $mform MoodleQuickForm object
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_edwiser_site_monitor'));
        $mform->setType('config_title', PARAM_TEXT);
        $mform->setDefault('config_title', get_string('pluginname', 'block_edwiser_site_monitor'));

        // Refresh rate config.
        $mform->addElement(
            'text',
            'config_refreshrate',
            get_string('livestatusrefreshrate', 'block_edwiser_site_monitor'),
            array('size' => 2)
        );
        $mform->setType('config_refreshrate', PARAM_FLOAT);
        $mform->addHelpButton('config_refreshrate', 'livestatusrefreshrate', 'block_edwiser_site_monitor');
        $mform->setDefault('config_refreshrate', 5);

        // Threshold configs.
        $mform->addElement('header', 'thresholdheader', get_string('thresholdheader', 'block_edwiser_site_monitor'));
        $mform->addElement('checkbox', 'config_enablethreshold', get_string('enablethreshold', 'block_edwiser_site_monitor'));
        $mform->addHelpButton('config_enablethreshold', 'enablethreshold', 'block_edwiser_site_monitor');

        // CPU lower limit.
        $mform->addElement('text', 'config_cpulowerlimit', get_string('cpulowerlimit', 'block_edwiser_site_monitor'));
        $mform->setType('config_cpulowerlimit', PARAM_INT);
        $mform->setDefault('config_cpulowerlimit', 20);
        $mform->addHelpButton('config_cpulowerlimit', 'cpulowerlimit', 'block_edwiser_site_monitor');
        // CPU higher limit.
        $mform->addElement('text', 'config_cpuhigherlimit', get_string('cpuhigherlimit', 'block_edwiser_site_monitor'));
        $mform->setType('config_cpuhigherlimit', PARAM_INT);
        $mform->setDefault('config_cpuhigherlimit', 80);
        $mform->addHelpButton('config_cpuhigherlimit', 'cpuhigherlimit', 'block_edwiser_site_monitor');

        // Memory lower limit.
        $mform->addElement('text', 'config_memorylowerlimit', get_string('memorylowerlimit', 'block_edwiser_site_monitor'));
        $mform->setType('config_memorylowerlimit', PARAM_INT);
        $mform->setDefault('config_memorylowerlimit', 20);
        $mform->addHelpButton('config_memorylowerlimit', 'memorylowerlimit', 'block_edwiser_site_monitor');
        // Memory higher limit.
        $mform->addElement('text', 'config_memoryhigherlimit', get_string('memoryhigherlimit', 'block_edwiser_site_monitor'));
        $mform->setType('config_memoryhigherlimit', PARAM_INT);
        $mform->setDefault('config_memoryhigherlimit', 80);
        $mform->addHelpButton('config_memoryhigherlimit', 'memoryhigherlimit', 'block_edwiser_site_monitor');

        // Storage lower limit.
        $mform->addElement('text', 'config_storagelowerlimit', get_string('storagelowerlimit', 'block_edwiser_site_monitor'));
        $mform->setType('config_storagelowerlimit', PARAM_INT);
        $mform->setDefault('config_storagelowerlimit', 20);
        $mform->addHelpButton('config_storagelowerlimit', 'storagelowerlimit', 'block_edwiser_site_monitor');
        // Storage higher limit.
        $mform->addElement('text', 'config_storagehigherlimit', get_string('storagehigherlimit', 'block_edwiser_site_monitor'));
        $mform->setType('config_storagehigherlimit', PARAM_INT);
        $mform->setDefault('config_storagehigherlimit', 80);
        $mform->addHelpButton('config_storagehigherlimit', 'storagehigherlimit', 'block_edwiser_site_monitor');
    }

    /**
     * Storage validation
     * @param  array $data data from config form
     *
     * @return array       errors array
     */
    private function cpu_validation($data) {
        $errors = [];
        if (empty($data['config_cpulowerlimit']) || $data['config_cpulowerlimit'] < 1 || $data['config_cpulowerlimit'] > 100) {
            $errors['config_cpulowerlimit'] = get_string('cpulimit_invalid', 'block_edwiser_site_monitor');
        }
        if (empty($data['config_cpuhigherlimit']) || $data['config_cpuhigherlimit'] < 1 || $data['config_cpuhigherlimit'] > 100) {
            $errors['config_cpuhigherlimit'] = get_string('cpulimit_invalid', 'block_edwiser_site_monitor');
        } else if (!empty($data['config_cpulowerlimit']) && $data['config_cpulowerlimit'] > $data['config_cpuhigherlimit']) {
            $errors['config_cpuhigherlimit'] = get_string('cpulimit_overlap', 'block_edwiser_site_monitor');
        }
        return $errors;
    }

    /**
     * Storage validation
     * @param  array $data data from config form
     *
     * @return array       errors array
     */
    private function memory_validation($data) {
        $errors = [];
        if (empty($data['config_memorylowerlimit']) ||
            $data['config_memorylowerlimit'] < 1 ||
            $data['config_memorylowerlimit'] > 100
        ) {
            $errors['config_memorylowerlimit'] = get_string('memorylimit_invalid', 'block_edwiser_site_monitor');
        }
        if (empty($data['config_memoryhigherlimit']) ||
            $data['config_memoryhigherlimit'] < 1 ||
            $data['config_memoryhigherlimit'] > 100
        ) {
            $errors['config_memoryhigherlimit'] = get_string('memorylimit_invalid', 'block_edwiser_site_monitor');
        } else if (!empty($data['config_memorylowerlimit']) &&
            $data['config_memorylowerlimit'] > $data['config_memoryhigherlimit']
        ) {
            $errors['config_memoryhigherlimit'] = get_string('memorylimit_overlap', 'block_edwiser_site_monitor');
        }
        return $errors;
    }

    /**
     * Storage validation
     * @param  array $data data from config form
     *
     * @return array       errors array
     */
    private function storage_validation($data) {
        $errors = [];
        if (empty($data['config_storagelowerlimit']) ||
            $data['config_storagelowerlimit'] < 1 ||
            $data['config_storagelowerlimit'] > 100
        ) {
            $errors['config_storagelowerlimit'] = get_string('storagelimit_invalid', 'block_edwiser_site_monitor');
        }
        if (empty($data['config_storagehigherlimit']) ||
            $data['config_storagehigherlimit'] < 1 ||
            $data['config_storagehigherlimit'] > 100
        ) {
            $errors['config_storagehigherlimit'] = get_string('storagelimit_invalid', 'block_edwiser_site_monitor');
        } else if (!empty($data['config_storagelowerlimit']) &&
            $data['config_storagelowerlimit'] > $data['config_storagehigherlimit']
        ) {
            $errors['config_storagehigherlimit'] = get_string('storagelimit_overlap', 'block_edwiser_site_monitor');
        }
        return $errors;
    }

    /**
     * Perform minimal validation on the settings form
     *
     * @param array $data  submitted data
     * @param array $files form files
     *
     * @return array errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate refresh rate.
        if (empty($data['config_refreshrate']) || $data['config_refreshrate'] <= 0) {
            $errors['config_refreshrate'] = get_string('livestatusrefreshrate_invalid', 'block_edwiser_site_monitor');
        }

        // Validate cpu usage limit.
        $errors = array_merge($errors, $this->cpu_validation($data));

        // Validate memory usage limit.
        $errors = array_merge($errors, $this->memory_validation($data));

        // Validate storage usage limit.
        $errors = array_merge($errors, $this->storage_validation($data));
        return $errors;
    }
}
