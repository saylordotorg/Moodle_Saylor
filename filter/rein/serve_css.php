<?php

/**
 * REIN Libraray Javascript
 * Copyright (C) 2008 onwards Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    filter-rein
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008 onwards Remote Learner.net Inc http://www.remote-learner.net
 *
 */



// disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

// we need just the values from config.php and minlib.php
define('ABORT_AFTER_CONFIG', true);

require('../../config.php'); // this stops immediately at the beginning of lib/setup.php

$candidatesheet = "$CFG->dirroot/filter/rein/css/styles.css";
if (file_exists($candidatesheet) && filesize($candidatesheet) > 100) {
    if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) || !empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        // we do not actually need to verify the etag value because our files
        // never change in cache because we increment the rev parameter
        $lifetime = 60*60*24*30; // 30 days
        header('HTTP/1.1 304 Not Modified');
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + $lifetime).' GMT');
        header('Cache-Control: max-age='.$lifetime);
        header('Content-Type: text/css; charset=utf-8');
        die;
    }
    filter_rein_send_cached_css($candidatesheet);
}

$themename = 'bootstrapbase';
if (file_exists("$CFG->dirroot/theme/$themename/config.php")) {
    // exists
} else if (!empty($CFG->themedir) and file_exists("$CFG->themedir/$themename/config.php")) {
    // exists
} else {
    header('HTTP/1.0 404 not found');
    die('Theme was not found, sorry.');
}


// =================================================================================
// ok, now we need to start normal moodle script, we need to load all libs and $DB
define('ABORT_AFTER_CONFIG_CANCEL', true);

define('NO_MOODLE_COOKIES', true); // Session not used here
define('NO_UPGRADE_CHECK', true);  // Ignore upgrade check

require("$CFG->dirroot/lib/setup.php");

$theme = theme_config::load($themename);

$cssfile = "$CFG->dirroot/filter/rein/css/styles.css";
filter_rein_store_css($theme, $candidatesheet, $cssfile);
filter_rein_send_cached_css($candidatesheet);

// =================================================================================
// === utility functions ==
// we are not using filelib because we need to fine tune all header
// parameters to get the best performance.

function filter_rein_store_css(theme_config $theme, $csspath, $cssfiles) {
    $getcss = file_get_contents($cssfiles);
    $css = $theme->post_process($getcss);
    // note: cache reset might have purged our cache dir structure,
    //       make sure we do not use stale file stat cache in the next check_dir_exists()
    clearstatcache();
    check_dir_exists(dirname($csspath));
    $fp = fopen($csspath, 'w');
    fwrite($fp, $css);
    fclose($fp);
}

function filter_rein_send_cached_css($csspath) {
    $lifetime = 60*60*24*30; // 30 days

    header('Content-Disposition: inline; filename="styles.php"');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($csspath)).' GMT');
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $lifetime).' GMT');
    header('Pragma: ');
    header('Cache-Control: max-age='.$lifetime);
    header('Accept-Ranges: none');
    header('Content-Type: text/css; charset=utf-8');
    if (!min_enable_zlib_compression()) {
        header('Content-Length: '.filesize($csspath));
    }

    readfile($csspath);
    die;
}
