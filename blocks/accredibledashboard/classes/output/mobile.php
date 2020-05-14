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
 *
 * @package   block_accredibledashboard
 * @copyright 2019 Saylor Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_accredibledashboard\output;
 
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/accredibledashboard/locallib.php');
 
class mobile {
    public static function mobile_view($args) {
        global $OUTPUT, $USER, $CFG;
 
        $sealurl = $CFG->wwwroot."/blocks/accredibledashboard/assets/icon/certificate_seal.png";

        // Get the user's credentials.
        $returnedcredentials = accredibledashboard_get_credentials(null, $USER->email);

        if (isset($returnedcredentials)) {
            $credentials = array();

            foreach ($returnedcredentials as $returnedcredential) {
                $credential = new \stdClass();
                $credential->url = $returnedcredential->url;
                $credential->sso_url = $returnedcredential->sso_url;
                $credential->name = $returnedcredential->name;
                $credential->image = $sealurl;
                $credential->wallet_url = $returnedcredential->wallet_url;


                $credentials[] = $credential;
            }
        }

        $html = '';
        if (isset($credentials)) {

            reset($credentials);
            $credential = current($credentials);

            $wallet = new \stdClass();
            $wallet->label = get_string('viewall', 'block_accredibledashboard');
            $wallet->url = $credential->wallet_url;

            $data = array(
                'credentials' => $credentials,
                'wallet' => $wallet
            );

            $html = $OUTPUT->render_from_template('block_accredibledashboard/mobile_view', $data);
        }
 
        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $html,
                ],
            ],
            'javascript' => '',
            'otherdata' => '',
            'files' => '',
        ];
    }
}