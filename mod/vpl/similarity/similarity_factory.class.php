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
 * Similarity object factory classes
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

defined('MOODLE_INTERNAL') || die();

class vpl_filetype {
    // TODO implement new types.
    private static $sstr = array (
            'h' => 'cpp',
            'hxx' => 'cpp',
            'c' => 'c',
            'js' => 'c', // JavaScript as C.
            'cc' => 'cpp',
            'C' => 'cpp',
            'cpp' => 'cpp',
            'cs' => 'cpp', // C# as C++.
            'ads' => 'ada',
            'adb' => 'ada',
            'ada' => 'ada',
            'java' => 'java',
            'Java' => 'java',
            'scm' => 'scheme',
            'pl' => 'prolog',
            'scala' => 'scala',
            'py' => 'python',
            'm' => 'matlab',
            'html' => 'html',
            'htm' => 'html'
    );
    public static function str($ext) {
        if (isset( self::$sstr[$ext] )) {
            return self::$sstr[$ext];
        } else {
            return false;
        }
    }
}
class vpl_similarity_factory {
    private static $classloaded = array ();
    private static function get_object($type) {
        if (! isset( self::$classloaded[$type] )) {
            $include = 'similarity_' . $type . '.class.php';
            require_once($include);
            self::$classloaded[$type] = true;
        }
        $class = 'vpl_similarity_' . $type;
        return new $class();
    }
    public static function get($filename) {
        $ext = pathinfo( $filename, PATHINFO_EXTENSION );
        if ($type = vpl_filetype::str( $ext )) {
            return self::get_object( $type );
        } else {
            return null;
        }
    }
}
