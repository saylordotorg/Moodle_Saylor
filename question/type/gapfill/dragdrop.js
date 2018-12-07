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
 * JavaScript code for the gapfill question type.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* This should be called script.js and go through the Moodle minify process but that seems to break it */
/* global $ */
/* jshint unused:false*/
$(function() {
    $(".draggable").draggable({
        revert: false,
        helper: 'clone',
        cursor: 'pointer',
        scroll: 'false',
        start: function() {
            $(this).fadeTo('fast', 0.5);
        },
        stop: function() {
            $(this).fadeTo(0, 1);
        }

    });

    $(".droptarget").droppable({
        hoverClass: 'active',
        drop: function(event, ui) {
            if ($(ui.draggable).hasClass('readonly')) {
                return;
            }
            this.value = $(ui.draggable).text();
            $(this).css("background-color", "white");
        }
    });
});