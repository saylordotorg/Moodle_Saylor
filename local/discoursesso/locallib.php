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
 * @copyright  2019 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

function get_discourse_locale($moodleuserlang) {
    switch ($moodleuserlang) {
        // For these specific locales, map to Moodle's lang code.
        // For everything else, cut off anything after underscore ie
        // es_mx and es_co will return es.
        // TODO: Moodle has A LOT more languages than Discourse... how to handle those?
        case "bs":
            $discourselocale = "bs_BA";
            break;
        case "fa":
            $discourselocale = "fa_IR";
            break;
        case "no":
            $discourselocale = "nb_NO";
            break;
        case "pl":
            $discourselocale = "pl_PL";
            break;
        case "pt_br":
            $discourselocale = "pt_BR";
            break;
        case "tr":
            $discourselocale = "tr_TR";
            break;
        case "zh_cn":
            $discourselocale = "zh_CN";
            break;
        case "zh_tw":
            $discourselocale = "zh_TW";
            break;
         default:
            $discourselocale = preg_replace('~(_[a-zA-Z0-9]+)+~s', '', $moodleuserlang);
    }
    return $discourselocale;
}

