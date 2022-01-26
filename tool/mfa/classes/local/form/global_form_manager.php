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
 * MFA login form
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_mfa\local\form;

use stdClass;
use tool_mfa\plugininfo\factor;

class global_form_manager {
    /** @var array $activefactors factors to call hooks upon.*/
    private $activefactors;

    public function __construct() {
        $this->activefactors = factor::get_active_user_factor_types();
    }

    /**
     * Hook point for global auth form action hooks.
     *
     * @param $mform Form to inject global elements into.
     * @return void
     */
    public function definition(&$mform) {
        foreach ($this->activefactors as $factor) {
            $factor->global_definition($mform);
        }
    }

    /**
     * Hook point for global auth form action hooks.
     *
     * @param $mform Form to inject global elements into.
     * @return void
     */
    public function definition_after_data(&$mform) {
        foreach ($this->activefactors as $factor) {
            $factor->global_definition_after_data($mform);
        }
    }

    /**
     * Hook point for global auth form action hooks.
     *
     * @param array $data Data from the form.
     * @param array $files Files form the form.
     * @return array of errors from validation.
     */
    public function validation($data, $files) {
        $errors = [];
        foreach ($this->activefactors as $factor) {
            $errors = array_merge($errors, $factor->global_validation($data, $files));
        }
        return $errors;
    }

    /**
     * Hook point for global auth form submission hooks.
     *
     * @param object $data Data from the form.
     * @return void
     */
    public function submit(stdClass $data) {
        foreach ($this->activefactors as $factor) {
            $factor->global_submit($data);
        }
    }
}
