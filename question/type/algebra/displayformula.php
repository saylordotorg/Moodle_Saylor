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
 * @copyright  Roger Moore <rwmoore@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Script which converts the given formula text into LaTeX code and then
 * displays the appropriate image file. It relies on the LaTeX filter or
 * the mathJax filter to be present.
 */


require_once('../../../config.php');
require_once("$CFG->dirroot/question/type/algebra/parser.php");

global $PAGE, $CFG;
require_login();

$p = new qtype_algebra_parser;
$validanswer = true;

try {
    $query = urldecode($_SERVER['QUERY_STRING']);
    $m = array();

    if (!preg_match('/vars=([^&]*)&expr=(.*)$/A', $query, $m)) {
        throw new Exception('Invalid query string received from http server!');
    }
    $vars = explode(',', $m[1]);
    if (empty($m[2])) {
        $texexp = '';
    } else {
        $exp = $p->parse($m[2], $vars);
        $texexp = $exp->tex();
        switch($CFG->qtype_algebra_texdelimiters) {
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

    }
} catch (Exception $e) {
    $validanswer = false;
    $texexp = get_string('parseerror', 'qtype_algebra', $e->getMessage());
}
$formatoptions = new stdClass;
$formatoptions->para = false;
$PAGE->set_context(context_system::instance());
if ($validanswer) {
    $text = format_text($texexp, FORMAT_MOODLE, $formatoptions);
} else {
    $text = get_string('invalidanswer', 'qtype_algebra');
}
?>
<html>
    <head>
        <title>Formula</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php

if (!empty($CFG->additionalhtmlhead) && stripos($CFG->additionalhtmlhead, 'MathJax') !== false) {
    // For website where Mathjax is enabled using additional HTML in head.
    echo $CFG->additionalhtmlhead;
} else {
    // For other website directly include MathJax.
    echo "<script type=\"text/javascript\" async
  src=\"https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML\">
</script>";
}
?>
    </head>
    <body bgcolor="#ffffff">
        <?php echo $text; ?>
    </body>
</html>
