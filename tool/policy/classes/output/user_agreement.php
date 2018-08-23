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
 * Provides {@link tool_policy\output\user_agreement} class.
 *
 * @package     tool_policy
 * @category    output
 * @copyright   2018 Marina Glancy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_policy\output;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use renderable;
use renderer_base;
use single_button;
use templatable;

/**
 * List of users and their acceptances
 *
 * @copyright 2018 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_agreement implements \templatable, \renderable {

    /** @var array */
    protected $data;

    /**
     * user_agreement constructor
     *
     * @param int $userid
     * @param bool $status
     * @param moodle_url $pageurl
     * @param \stdClass|null $version object with fields 'id' and 'name' or null if this is an "overall" status
     * @param bool $onbehalf whether accepted on behalf of the user
     */
    public function __construct($userid, $status, moodle_url $pageurl, $version, $onbehalf = false) {
        $this->data = [
            'userid' => $userid,
            'status' => (int)(bool)$status,
            'versionid' => $version ? $version->id : 0,
            'versionname' => $version ? $version->name : 0,
            'onbehalf' => $onbehalf,
            'pageurl' => $pageurl
        ];
    }

    /**
     * Export data to be rendered.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = [
            'status' => $this->data['status'],
            'onbehalf' => $this->data['onbehalf'],
            'versionname' => $this->data['versionname'],
        ];
        if ($this->data['versionid'] && !$this->data['status'] &&
                has_capability('tool/policy:acceptbehalf', \context_user::instance($this->data['userid']))) {
            $link = new \moodle_url('/admin/tool/policy/user.php',
                ['acceptforversion' => $this->data['versionid'], 'userid' => $this->data['userid'],
                    'returnurl' => $this->data['pageurl']->out_as_local_url(false)]);
            $data['canaccept'] = 1;
            $data['acceptlink'] = $link->out(false);
        }
        return $data;
    }
}