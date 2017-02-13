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
 * Defines authors tool widgets class.
 *
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author Pahomov Dmitry, Terechov Grigory, Volgograd State Technical University
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questions
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/form/textarea.php');

MoodleQuickForm::registerElementType('qtype_poasquestion_text_and_button',
    $CFG->dirroot.'/question/type/poasquestion/poasquestion_text_and_button.php',
    'qtype_poasquestion_text_and_button');

class qtype_poasquestion_text_and_button extends MoodleQuickForm_textarea {

    protected $buttonName = '';
    protected $linkToPage = '';
    protected $linkToBtnImage = '';
    protected $jsmodule = array('name' => 'poasquestion_text_and_button',
                                'fullpath' => '/question/type/poasquestion/poasquestion_text_and_button.js');

    protected static $_poasquestion_text_and_button_included = false;

    /**
     * Constructor
     * @param string $textareaName (optional) name of the text field
     * @param string $textareaLabel (optional) text field label
     * @param array $attributes (optional) Either a typical HTML attribute string or an associative array
     * @param string $buttonName (optional) name of the button
     * @param array $elementLinks (optional) link on button image and link on new page
     */
    public function qtype_poasquestion_text_and_button($textareaName = null, $textareaLabel = null, $attributes = null,
                                                       $buttonName = null, $elementLinks = null, $dialogWidth = null) {
        global $PAGE;

        parent::MoodleQuickForm_textarea($textareaName, $textareaLabel, $attributes);

        $this->buttonName = $buttonName;
        $this->linkToPage = $elementLinks['link_to_page'];
        $this->linkToBtnImage = $elementLinks['link_to_button_image'];
        if ($dialogWidth === null) {
            $dialogWidth = '90%';
        }

        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');

        $PAGE->requires->string_for_js('savechanges', 'moodle');
        $PAGE->requires->string_for_js('cancel', 'moodle');
        $PAGE->requires->string_for_js('close', 'editor');

        // dependencies
        //$PAGE->requires->js('/question/type/poasquestion/jquery.elastic.1.6.11.js');
        $PAGE->requires->jquery_plugin('poasquestion-jquerymodule', 'qtype_poasquestion');

        if (!self::$_poasquestion_text_and_button_included) {
            $jsargs = array(
                $dialogWidth,
                $this->getDialogTitle()
            );
            $PAGE->requires->js_init_call('M.poasquestion_text_and_button.init', $jsargs, true, $this->jsmodule);
            self::$_poasquestion_text_and_button_included = true;
        }
    }

    public function getDialogTitle() {
        return 'someone forgot to set the title :(';
    }

    public function getTextareaId() {
        return $this->getAttribute('id');
    }

    public function getButtonId() {
        return $this->getAttribute('id') . '_btn';
    }

    public function getTooltip() {
        return '';
    }

    /**
     * Returns HTML for this form element.
     */
    public function toHtml() {
        global $PAGE;

        $jsargs = array(
            $this->getButtonId(),
            $this->getTextareaId()
        );

        $PAGE->requires->js_init_call('M.poasquestion_text_and_button.set_handler', $jsargs, true, $this->jsmodule);

        return parent::toHtml() . '<a href="#" name="button_' . $this->getTextareaId() . '" id="' . $this->getButtonId() . '" title="' . $this->getTooltip() . '" style="margin-left: 5px" >' .
                                      '<img src="' . $this->linkToBtnImage . '" />' .
                                  '</a>';
    }
}
