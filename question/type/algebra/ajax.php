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
 * @package    qtype_algebra
 * @copyright  2018 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/parser.php');

$p = new qtype_algebra_parser;

$vars  = required_param('vars', PARAM_RAW);
$expr  = required_param('expr', PARAM_RAW);

// This should be accessed by only valid logged in user.
require_login(null, false);

/*
if (!confirm_sesskey()) {
    header('HTTP/1.1 403 Forbidden');
    die();
}
*/

try {
    $vars = explode(',', $vars);
    if (empty($expr)) {
        $texexp = ' ';
    } else {
        $exp = $p->parse($expr, $vars);
        $texexp = $exp->tex();
    }
} catch (Exception $e) {
    $texexp = ' ';
}

$delimiters = $CFG->qtype_algebra_texdelimiters;
switch($delimiters) {
    case 'old':
        $texexp = '$$' . $texexp . '$$';
        break;
    case 'new':
        $texexp = '\\[' . $texexp . '\\]';
        break;
    case 'simple';
        $texexp = '$' . $texexp . '$';
        break;
    case 'inline':
        $texexp = '\\(' . $texexp . '\\)';
        break;
}

header('Content-Type: application/json; charset: utf-8');
echo json_encode($texexp);
