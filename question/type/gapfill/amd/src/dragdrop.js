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
 * @copyright  2020 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'jqueryui', 'qtype_gapfill/jquery.ui.touch-punch-improved'], function($) {
  return {
    init: function(singleuse) {
      $(".droptarget").on('dblclick', function() {
        if (singleuse) {
          dragShow(this);
          $(this).val("");
        }
      });

    /**
     * Reveal draggables that are not
     * the the current one
     *
     * @param {*} that
     */
      function dragShow(that) {
        var draggables = $(".draggable");
        var targetVal = $(that).val();
        var i;
        for (i = 0; i < draggables.length; i++) {
          var sourceVal = draggables[i].textContent;
          if (sourceVal == targetVal) {
            $(draggables[i]).removeClass("hide");
          }
        }
      }

      $(".droptarget").on('keydown drop', function() {
        dragShow(this);
      });

      $(".draggable").draggable({
        revert: false,
        helper: 'clone',
        cursor: 'pointer',
        scroll: 'false',
      });

      $(".droptarget").droppable({
        hoverClass: 'active',
        drop: function(event, ui) {
          if ($(ui.draggable).hasClass('readonly')) {
            return;
          }
          this.value = $(ui.draggable).text();
          $(this).css("background-color", "white");
          $(this).addClass("dropped");
          if (singleuse) {
            $(ui.draggable).addClass("hide");
          }
        }
      });
      $(".droptarget").dblclick(function() {
        $(this).val("");
        $(this).removeClass("dropped");
        $(this).css("background-color", "white");
     });
    }
  };
});