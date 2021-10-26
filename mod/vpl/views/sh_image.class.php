<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Syntaxhighlighters for images
 *
 * @package mod_vpl
 * @copyright 2014 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodriguez-del-Pino
 **/

require_once dirname ( __FILE__ ) . '/sh_base.class.php';
class vpl_sh_image extends vpl_sh_base {
    private $MIME;
    function __construct(){
        $this->MIME = array (
                'jpg' => 'jpeg',
                'jpeg' => 'jpeg',
                'gif' => 'gif',
                'png' => 'png',
                'ico' => 'vnd.microsoft.icon'
        );
    }
    function getMIME($name) {
        $ext = strtolower(vpl_fileExtension($name));
        return $this->MIME[$ext];
    }
    function print_file($name, $data) {
        echo '<div class="vpl_sh vpl_g">';
        echo '<img src="data:image/'.$this->getMIME($name).';base64,';
        echo base64_encode($data);
        echo '" alt="'.s($name).'" />';
        echo '</div>';
    }
}
