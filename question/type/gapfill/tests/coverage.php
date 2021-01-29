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

defined('MOODLE_INTERNAL') || die();

 // Coverage information for the gapfill question type.
return new class extends phpunit_coverage_info {
    // List of folders relative to the plugin root to whitelist in coverage generation.
    protected $whitelistfolders = [
        '.'
    ];

    // List of files relative to plugin root to whitelist in coverage generation.
    protected $whitelistfiles = [
        'questiontype.php'
    ];

    // List of folders relative to the plugin root to excludelist in coverage generation.
    protected $excludelistfolders = [
        'db',
        'lang',
        'tests'
    ];

    // List of files relative to the plugin root to excludelist in coverage generation.
    protected $excludelistfiles = [
        'settings.php',
        'version.php'
    ];
};
