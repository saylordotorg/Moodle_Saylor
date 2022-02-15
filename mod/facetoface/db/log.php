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

$logs = array(
    array('module' => 'facetoface', 'action' => 'add', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'delete', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'update', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'view', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'view all', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'add session', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'copy session', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'delete session', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'update session', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'view session', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'view attendees', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'take attendance', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'signup', 'mtable' => 'facetoface', 'field' => 'name'),
    array('module' => 'facetoface', 'action' => 'cancel', 'mtable' => 'facetoface', 'field' => 'name'),
);
