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
 * Simple clock block language strings
 *
 * @package    block_heatmap
 * @copyright  2016 Michael de Raadt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cache0min'] = 'Disable caching';
$string['cache1min'] = '1 minute';
$string['cache5min'] = '5 minutes';
$string['cache10min'] = '10 minutes';
$string['cache30min'] = '30 minutes';
$string['cache60min'] = '1 hour';
$string['cache120min'] = '2 hours';
$string['cachedef_cachedlogs'] = 'Log query cache for course activity';
$string['cachelife'] = 'Cache lifespan';
$string['cachelife_help'] = 'A cache is used to retain results and reduce impact on the log table. Changes may not appear immediately unless you purge caches.';
$string['checkforactivity'] = 'Check for activity';
$string['checkforactivity_help'] = 'How far back in logs should be checked? Changes may not appear immediately unless you purge caches.';
$string['distinctusers'] = 'Distinct users';
$string['distinctuserviews'] = '<em>Distinct user views:</em> {$a}';
$string['heatmap:addinstance'] = 'Add a Heatmap block to a course';
$string['heatmap:view'] = 'View the Heatmap';
$string['nologreaderenabled'] = 'No usable log reader is enabled.';
$string['nologentries'] = 'No user participation was found.';
$string['notstarted'] = 'No activity shown as the course has not started yet.';
$string['pluginname'] = 'Heatmap';
$string['sincecoursestart'] = '(Since course start date)';
$string['showbackground'] = 'Show both background colouring only';
$string['showboth'] = 'Show both background and count icons';
$string['showicons'] = 'Show count icons only';
$string['sinceforever'] = 'All past logs';
$string['sincestart'] = 'Since course start date';
$string['toggleheatmap'] = 'Toggle heatmap';
$string['totalviews'] = '<em>Total views:</em> {$a}';
$string['updated'] = 'Updated: {$a}';
$string['views'] = 'Views';
$string['whattoshow'] = 'What to show on course page';
