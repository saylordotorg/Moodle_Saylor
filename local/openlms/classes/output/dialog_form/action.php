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

namespace local_openlms\output\dialog_form;

/**
 * Action to open legacy form in modal dialog.
 *
 * @package    local_openlms
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class action implements \renderable {
    /** @var string reload the current page */
    public const AFTER_SUBMIT_RELOAD = 'reload';
    /** @var string go to page that the legacy form would redirect to */
    public const AFTER_SUBMIT_REDIRECT = 'redirect';
    /** @var string do nothing, this is for special cases that override onSubmitSuccess in template */
    public const AFTER_SUBMIT_NOTHING = 'nothing';

    /** @var string name of action */
    protected $title;
    /** @var string heading of dialog, defaults to action title */
    protected $dialogname = null;
    /** @var \moodle_url legacy form URL */
    protected $formurl;
    /** @var bool false means use redirection URL from form page, true means just reload current page on submission */
    protected $aftersubmit = self::AFTER_SUBMIT_RELOAD;
    /** @var bool set to true to use redirect to full page form */
    public $legacyformtest = false;

    public function __construct(\moodle_url $formurl, $title) {
        $this->formurl = $formurl;
        $this->title = (string)$title;
        $this->dialogname = (string)$title;
    }

    public function set_after_submit(string $value): void {
        $this->aftersubmit = $value;
    }

    public function get_after_submit(): string {
        return $this->aftersubmit;
    }

    public function get_form_url(): \moodle_url {
        return $this->formurl;
    }

    public function get_title(): string {
        return $this->title;
    }

    public function set_dialog_name(string $name): void {
        $this->dialogname = $name;
    }

    public function get_dialog_name(): string {
        return $this->dialogname;
    }
}
