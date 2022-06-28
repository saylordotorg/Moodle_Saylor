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
 * Icon that opens legacy form in modal dialog.
 *
 * @package    local_openlms
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class icon extends action {
    /** @var \pix_icon */
    protected $pixicon;

    public function __construct(\moodle_url $formurl, string $pix, $title, string $component = 'moodle') {
        parent::__construct($formurl, $title);
        $this->pixicon = new \pix_icon($pix, $title, $component);
    }

    public function get_icon(): \pix_icon {
        return $this->pixicon;
    }
}
