<?php
// This file is part of Poasquestion question type - https://code.google.com/p/oasychev-moodle-plugins/
//
// Poasquestion question type is free software: you can redistribute it and/or modify
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
 * Defines a unicode string class.
 *
 * @package    qtype_poasquestion
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Valeriy Streltsov <vostreltsov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_poasquestion;
defined('MOODLE_INTERNAL') || die();

class string extends \core_text implements \ArrayAccess {
    /** @var string the utf-8 string itself. */
    private $fstring;
    /**@var int length of the string, calculated when the string is modified. */
    private $flength;

    public function __construct($str = '') {
        $this->set_string($str);
    }

    /**
     * Replaces $search with $replace in $subject string.
     */
    public static function replace($search, $replace, $subject) {
        $searchlen = self::strlen($search);

        if ($searchlen == 0) {
            return $subject;
        }

        $result = '';
        do {
            $to = self::strpos($subject, $search);
            if ($to === false) {
                $result .= $subject;
                $subject = '';
            } else {
                $result .= self::substr($subject, 0, $to) . $replace;
                $subject = self::substr($subject, $to + $searchlen);
            }

        }
        while ($subject != '');

        return $result;
    }

    public function __toString() {
        return $this->fstring;
    }

    public function set_string($str) {
        if (is_string($str)) {
            $this->fstring = $str;
        } else {
            $this->fstring = '';
        }
        $this->flength = self::strlen($this->fstring);
    }

    public function string() {
        return $this->fstring;
    }

    public function length() {
        return $this->flength;
    }

    /**
     * Converts this string to lowercase.
     */
    public function tolower() {
        $this->fstring = self::strtolower($this->fstring);
    }

    /**
     * Converts this string to uppercase.
     */
    public function toupper() {
        $this->fstring = self::strtoupper($this->fstring);
    }

    /**
     * Checks if this string contains another string.
     * @param str substring.
     * @return mixed the position of the substring if found, false otherwise.
     */
    public function contains($str, $offset = 0) {
        $str = (string)$str;
        return self::strpos($this->fstring, $str, $offset);
    }

    /**
     * Returns a substring of this string.
     * @param int start starting index of the substring.
     * @param int length length of the substring.
     * @return object an instance of qtype_poasquestion\string.
     */
    public function substring($start, $length = null) {
        return new string(self::substr($this->fstring, $start, $length));
    }

    /**
     *
     */
    public function startsWith($needle) {
        if ($needle == '') {
            return true;
        }
        return strncmp($this->fstring, $needle, strlen($needle)) === 0;
    }

    /**
     *
     */
    public function endsWith($needle) {
        if ($needle == '') {
            return true;
        }
        $len = self::strlen($needle);
        if ($len > $this->flength) {
            return false;
        }
        $substr = self::substr($this->fstring, $this->flength - $len);
        return $substr === $needle;
    }

    /**
     * Concatenates a string to this string.
     * @param mixed a string to concatenate (can be either an instance of qtype_poasquestion\string or a simple native string).
     */
    public function concatenate($str) {
        if (is_a($str, 'qtype_poasquestion\string')) {
            $this->fstring .= $str->fstring;
            $this->flength += $str->flength;
        } else {
            $this->fstring .= $str;
            $this->flength += self::strlen($str);
        }
    }

    public function offsetExists($offset) {
        return ($offset >= 0 && $offset < $this->flength);
    }

    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return self::substr($this->fstring, $offset, 1);
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value) {
        if ($this->offsetExists($offset)) {
            // Modify a character.
            $part1 = self::substr($this->fstring, 0, $offset);
            $part2 = self::substr($this->fstring, $offset + 1, $this->flength - $offset - 1);
            $this->fstring = $part1 . $value . $part2;
        } else if ($offset === $this->flength) {
            // Concatenate a character.
            $this->concatenate($value);
        }
    }

    public function offsetUnset($offset) {
        // Do nothing.
    }
}
