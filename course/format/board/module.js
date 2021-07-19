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
 * format_board
 *
 * @package    format_board
 * @author     Rodrigo Brandão (rodrigobrandao.com.br)
 * @copyright  2017 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.format_board = M.format_board || {
    ourYUI: null,
    numsections: 0
};

M.format_board.init = function(Y, numsections) {
    this.ourYUI = Y;
    this.numsections = parseInt(numsections);
    //document.getElementById('boardectioncontainer').style.display = 'table';
};

M.format_board.hide = function () {
    for (i = 1; i <= this.numsections; i++) {
        var boardection = document.getElementById('boardection-'+i);
        boardection.setAttribute('class', boardection.getAttribute('class').replace('sectioncurrent', ''));
        document.getElementById('section-'+i).style.display = 'none';
    }
};

M.format_board.show = function (i) {
    this.hide();
    var boardection = document.getElementById('boardection-'+i);
    boardection.setAttribute('class', boardection.getAttribute('class') + ' sectioncurrent');
    document.getElementById('section-'+i).style.display = 'block';
    document.cookie = 'sectioncurrent='+i+'; path=/';
};
