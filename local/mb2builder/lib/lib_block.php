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
 * @package    local_mb2builder
 * @copyright  2018 - 2020 Mariusz Boloz (https://mb2themes.com/)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();




/*
 *
 * Method to render single block from region
 *
 */
function local_mb2builder_get_block($instanceid, $region, $attr = array())
{
    global $PAGE, $OUTPUT;

    // blocks_for_region
    // lib/outputrenderers.php

    $output = '';

    if (!$instanceid)
    {
        return;
    }

    $blockcontents = $PAGE->blocks->get_content_for_region($region, $PAGE);

    foreach ($blockcontents as $bc)
    {
        if ($bc->blockinstanceid == $instanceid)
        {
            $bc->title = $attr['title'] ? $bc->title : '';
            $output = $OUTPUT->block($bc, $region);

        }
    }

    return $output;

}






/*
 *
 * Method to get array of id and title of blocks
 *
 */
function local_mb2builder_get_blockinstances($region)
{
    global $PAGE, $OUTPUT;

    $output = array();

    $blockcontents = $PAGE->blocks->get_content_for_region($region, $PAGE);

    foreach ($blockcontents as $bc)
    {
        if ($bc instanceof block_contents)
        {
                $output[] = array('id'=>$bc->blockinstanceid, 'title'=>$bc->title);
        }
    }

    return $output;

}
