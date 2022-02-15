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
 * Copyright (C) 2007-2011 Catalyst IT (http://www.catalyst.net.nz)
 * Copyright (C) 2011-2013 Totara LMS (http://www.totaralms.com)
 * Copyright (C) 2014 onwards Catalyst IT (http://www.catalyst-eu.net)
 *
 * @package    mod
 * @subpackage facetoface
 * @copyright  2014 onwards Catalyst IT <http://www.catalyst-eu.net>
 * @author     Stacey Walker <stacey@catalyst-eu.net>
 * @author     Alastair Munro <alastair.munro@totaralms.com>
 * @author     Aaron Barnes <aaron.barnes@totaralms.com>
 * @author     Francois Marier <francois@catalyst.net.nz>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/facetoface/backup/moodle2/backup_facetoface_stepslib.php'); // Because it exists (must).

/**
 * Facetoface backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_facetoface_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {

        // Facetoface only has one structure step.
        $this->add_step(new backup_facetoface_activity_structure_step('facetoface_structure', 'facetoface.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of facetofaces.
        $search = "/(" . $base . "\/mod\/facetoface\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@FACETOFACEINDEX*$2@$', $content);

        // Link to facetoface view by moduleid.
        $search = "/(" . $base . "\/mod\/facetoface\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@FACETOFACEVIEWBYID*$2@$', $content);

        return $content;
    }
}
