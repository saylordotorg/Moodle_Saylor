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
 * Defines forms.
 *
 * @package    local_mb2notices
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

require_once( $CFG->libdir . '/formslib.php' );
require_once( __DIR__ . '/classes/api.php' );
require_once( __DIR__ . '/classes/helper.php' );

class service_edit_form extends moodleform {

    /**
     * Defines the standard structure of the form.
     *
     * @throws \coding_exception
     */
    protected function definition()
    {

        $mform =& $this->_form;
        $sepAttr = ' class="mb2form-separator" style="height:1px;border-top:solid 1px #e5e5e5;margin:46px 0;"';
        $size = array('size' => 60 );
        $context = context_system::instance();
        $editoroptions = array('subdirs' => false, 'maxfiles' => -1, 'context' => $context);
        $langArr = array_merge(array('' => get_string('all','local_mb2notices')), get_string_manager()->get_list_of_translations());
        //$roleArr = Mb2noticesHelper::get_roles_to_select();

        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'timecreated');
        $mform->addElement('hidden', 'timemodified');
        $mform->addElement('hidden', 'createdby');
        $mform->addElement('hidden', 'modifiedby');
        $mform->setType('id', PARAM_INT);
        $mform->setType('timecreated', PARAM_INT);
        $mform->setType('timemodified', PARAM_INT);
        $mform->setType('createdby', PARAM_INT);
        $mform->setType('modifiedby', PARAM_INT);

        $mform->addElement('header', 'editgeneralhdr', get_string('general', 'core'));

        $mform->addElement('text', 'title', get_string('title', 'local_mb2notices'), $size);
        $mform->addRule('title', null, 'required');
        $mform->setType('title', PARAM_NOTAGS);

        $mform->addElement('select', 'attribs[showtitle]', get_string('showtitle', 'local_mb2notices'), array(
            999 => get_string('useglobal', 'local_mb2slides'),
            1 => get_string('yes', 'local_mb2slides'),
            0 => get_string('no', 'local_mb2slides')
        ));
        $mform->setType('attribs[showtitle]', PARAM_INT);

        $mform->addElement('select', 'enable', get_string('enable', 'local_mb2notices'), array(
            1 => get_string('show', 'local_mb2notices'),
            0 => get_string('hide', 'local_mb2notices')
        ));
        $mform->setType('enable', PARAM_BOOL);
        $mform->setDefault('enable', 1);

        $mform->addElement('editor', 'content_editor', get_string('content', 'moodle'), null, $editoroptions);
        $mform->setType('content_editor', PARAM_RAW);

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $mform->addElement('date_time_selector', 'timestart', get_string( 'timestart', 'local_mb2notices' ), array( 'optional' => 1) );
        $mform->setType('timestart', PARAM_INT);
        $mform->addElement('date_time_selector', 'timeend', get_string( 'timeend', 'local_mb2notices' ), array( 'optional' => 1) );
        $mform->setType('timeend', PARAM_INT);

        // $mform->addElement('filemanager', 'attachments', get_string('image','local_mb2notices'), null, array('subdirs'=>false,'maxfiles'=>1,'context' => context_system::instance()));
        // $mform->addRule('attachments', null, 'required');

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $mform->addElement('select', 'attribs[showon]', get_string('showon', 'local_mb2notices'), array(
            0 => get_string('showoneverywhere', 'local_mb2notices'),
            1 => get_string('showonfrontpage', 'local_mb2notices'),
            2 => get_string('showoncourse', 'local_mb2notices'),
            5 => get_string('showoncalendarpage', 'local_mb2notices'),
            3 => get_string('showondashboard', 'local_mb2notices'),
            4 => get_string('showonloginpage', 'local_mb2notices')
        ));
        $mform->setType('attribs[showon]', PARAM_INT);

        $mform->addElement('text', 'attribs[courseids]', get_string('courseids', 'local_mb2notices'), array( 'data-mb2showon' => 'attribs[showon]', 'data-mb2showonval' => '2' ) );
        $mform->addHelpButton('attribs[courseids]', 'courseids', 'local_mb2notices');
        $mform->setType('attribs[courseids]', PARAM_TEXT);

        $mform->addElement('select', 'attribs[cansee]', get_string('access', 'local_mb2notices'), array(
            '0' => get_string('accesseveryone', 'local_mb2notices'),
            '1' => get_string('accessusers', 'local_mb2notices'),
            '2' => get_string('accessguests', 'local_mb2notices'),
            '3' => get_string('accesstudents', 'local_mb2notices'),
            '4' => get_string('accesteachers', 'local_mb2notices'),
            '5' => get_string('rolecustom','local_mb2notices', array( 'num'=> 1 ) ),
            '6' => get_string('rolecustom','local_mb2notices', array( 'num'=> 2 ) ),
            '7' => get_string('rolecustom','local_mb2notices', array( 'num'=> 3 ) )
        ) );
        $mform->setType('attribs[cansee]', PARAM_TEXT);

        $mform->addElement('text', 'attribs[userids]', get_string('userids', 'local_mb2notices'), array( 'data-mb2showon' => 'attribs[cansee]', 'data-mb2showonval' => '1' ) );
        $mform->addHelpButton('attribs[userids]', 'userids', 'local_mb2notices');
        $mform->setType('attribs[userids]', PARAM_TEXT);

        $mform->addElement('select', 'attribs[canclose]', get_string('canclose', 'local_mb2notices'), array(
            999 => get_string('useglobal', 'local_mb2slides'),
            1 => get_string('yes', 'local_mb2slides'),
            0 => get_string('no', 'local_mb2slides')
        ));
        $mform->setType('attribs[canclose]', PARAM_INT);

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $mform->addElement('select', 'language', get_string('language','moodle'), $langArr);
        $mform->getElement('language')->setMultiple(true);
        $mform->setDefault('language', ['']);

        $mform->addElement('header', 'editappearancehdr', get_string('appearance', 'core'));

        $mform->addElement('select', 'attribs[noticetype]', get_string('noticetype', 'local_mb2notices'), array(
            999 => get_string('useglobal', 'local_mb2slides'),
            'primary' => get_string('primarytype','local_mb2notices'),
    		'secondary' => get_string('secondarytype','local_mb2notices'),
            'info' => get_string('infotype','local_mb2notices'),
            'warning' => get_string('warningtype','local_mb2notices'),
            'danger' => get_string('dangertype','local_mb2notices'),
            'success' => get_string('successtype','local_mb2notices')
        ) );
        $mform->setType('attribs[noticetype]', PARAM_TEXT);

        $mform->addElement('select', 'attribs[position]', get_string('position', 'local_mb2notices'), array(
            999 => get_string('useglobal', 'local_mb2slides'),
            'top' => get_string('top','local_mb2notices'),
        	'content' => get_string('content','local_mb2notices'),
        	'bottom' => get_string('bottom','local_mb2notices')
        ));
        $mform->setType('attribs[position]', PARAM_TEXT);

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $mform->addElement('text', 'attribs[textcolor]', get_string('textcolor', 'local_mb2notices'), array( 'class' => 'mb2color' ) );
        $mform->addHelpButton('attribs[textcolor]', 'useglobal', 'local_mb2notices');
        $mform->setType('attribs[textcolor]', PARAM_TEXT);

        $mform->addElement('text', 'attribs[bgcolor]', get_string('bgcolor', 'local_mb2notices'), array( 'class' => 'mb2color' ) );
        $mform->addHelpButton('attribs[bgcolor]', 'useglobal', 'local_mb2notices');
        $mform->setType('attribs[bgcolor]', PARAM_TEXT);

        $this->add_action_buttons();
    }





    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     */
    function validation( $data, $files)
    {
        global $DB;

        $errors = parent::validation( $data, $files );

        if ( $errorcode = Mb2noticesApi::notice_validate_dates( $data ) )
        {
            $errors['timeend'] = get_string( $errorcode, 'local_mb2notices' );
        }

        return $errors;
    }
}
