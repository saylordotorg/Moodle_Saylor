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
 * @package    local_mb2slides
 * @copyright  2019 - 2020 Mariusz Boloz (mb2themes.com)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

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
        $langArr = array_merge(array('' => get_string('all','local_mb2slides')), get_string_manager()->get_list_of_translations());

        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'timestart');
        $mform->addElement('hidden', 'timeend');
        $mform->addElement('hidden', 'timecreated');
        $mform->addElement('hidden', 'timemodified');
        $mform->addElement('hidden', 'createdby');
        $mform->addElement('hidden', 'modifiedby');
        $mform->setType('id', PARAM_INT);
        $mform->setType('timestart', PARAM_INT);
        $mform->setType('timeend', PARAM_INT);
        $mform->setType('timecreated', PARAM_INT);
        $mform->setType('timemodified', PARAM_INT);
        $mform->setType('createdby', PARAM_INT);
        $mform->setType('modifiedby', PARAM_INT);

        $mform->addElement('header', 'editgeneralhdr', get_string('general', 'core'));

        $mform->addElement('text', 'title', get_string('title', 'local_mb2slides'), $size);
        $mform->addRule('title', null, 'required');
        $mform->setType('title', PARAM_NOTAGS);

        $mform->addElement('advcheckbox', 'attribs[showtitle]', get_string('showtitle', 'local_mb2slides'));
        $mform->setType('attribs[showtitle]', PARAM_BOOL);
        //$mform->setDefault('attribs[showtitle]', 0);

        $mform->addElement('select', 'enable', get_string('enable', 'local_mb2slides'), array(
            1 => get_string('show', 'local_mb2slides'),
            0 => get_string('hide', 'local_mb2slides')
        ));
        $mform->setType('enable', PARAM_BOOL);
        $mform->setDefault('enable', 1);

        $mform->addElement('editor', 'content_editor', get_string('content', 'moodle'), null, $editoroptions);
        $mform->setType('content_editor', PARAM_RAW);

        $mform->addElement('filemanager', 'attachments', get_string('image','local_mb2slides'), null, array('subdirs'=>false,'maxfiles'=>1,'context' => context_system::instance()));
        $mform->addRule('attachments', null, 'required');

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $mform->addElement('text', 'attribs[link]', get_string('link', 'local_mb2slides'));
        $mform->setType('attribs[link]', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'attribs[linktarget]', get_string('linktarget', 'local_mb2slides'));
        $mform->setType('attribs[linktarget]', PARAM_BOOL);
        $mform->setDefault('attribs[linktarget]', 0);

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $mform->addElement('select', 'access', get_string('access', 'local_mb2slides'), array(
            0 => get_string('accesseveryone', 'local_mb2slides'),
            1 => get_string('accessusers', 'local_mb2slides'),
            2 => get_string('accessguests', 'local_mb2slides')
        ));
        $mform->setType('access', PARAM_INT);
        $mform->setDefault('access', 0);

        $mform->addElement('text', 'attribs[userids]', get_string('userids', 'local_mb2slides'), array( 'data-mb2showon' => 'access', 'data-mb2showonval' => '1' ) );
        $mform->addHelpButton('attribs[userids]', 'userids', 'local_mb2slides');
        $mform->setType('attribs[userids]', PARAM_TEXT);

        $mform->addElement('select', 'language', get_string('language','moodle'), $langArr);
        $mform->getElement('language')->setMultiple(true);
        $mform->setDefault('language', ['']);

        $mform->addElement('header', 'editappearancehdr', get_string('appearance', 'core'));

        $mform->addElement('select', 'attribs[linkbtn]', get_string('linkbtn', 'local_mb2slides'), array(
            999 => get_string('useglobal', 'local_mb2slides'),
            1 => get_string('yes', 'local_mb2slides'),
            0 => get_string('no', 'local_mb2slides')
        ));

        //$mform->setDefault('attribs[linkbtn]', '');
        $mform->setType('attribs[linkbtn]', PARAM_INT);

        $mform->addElement('text', 'attribs[linkbtncls]', get_string('linkbtncls', 'local_mb2slides'));
        $mform->addHelpButton('attribs[linkbtncls]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[linkbtncls]', PARAM_TEXT);

        $mform->addElement('text', 'attribs[linkbtntext]', get_string('linkbtntext', 'local_mb2slides'));
        $mform->addHelpButton('attribs[linkbtntext]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[linkbtntext]', PARAM_TEXT);

        $mform->addElement('html', '<div' . $sepAttr . '></div>');

        $cstylepre_arr = array(
            '' => get_string('useglobal', 'local_mb2slides'),
            'border'=>get_string('border', 'local_mb2slides'),
            'gradient'=>get_string('gradient', 'local_mb2slides'),
			'circle'=>get_string('circle', 'local_mb2slides'),
			'strip-light'=>get_string('striplight', 'local_mb2slides'),
			'strip-dark'=>get_string('stripdark', 'local_mb2slides'),
            'fullwidth'=>get_string('fullwidth', 'local_mb2slides'),
			//'fromtheme'=>get_string('fromtheme', 'local_mb2slides'),
			'custom'=>get_string('custom', 'local_mb2slides')
		);

        $mform->addElement('select', 'attribs[chalign]', get_string('chalign', 'local_mb2slides'), array(
            '' => get_string('useglobal', 'local_mb2slides'),
			'left' => get_string('left','local_mb2slides'),
			'right' => get_string('right','local_mb2slides'),
			'center' => get_string('center','local_mb2slides')
		));
        $mform->setType('attribs[chalign]', PARAM_TEXT);
        //$mform->setDefault('attribs[chalign]', '');


        $mform->addElement('select', 'attribs[cvalign]', get_string('cvalign', 'local_mb2slides'), array(
            '' => get_string('useglobal', 'local_mb2slides'),
			'top' => get_string('top','local_mb2slides'),
			'bottom' => get_string('bottom','local_mb2slides'),
			'center' => get_string('center','local_mb2slides')
		));
        $mform->setType('attribs[cvalign]', PARAM_TEXT);
        //$mform->setDefault('attribs[cvalign]', '');

        $mform->addElement('text', 'attribs[captionw]', get_string('captionw', 'local_mb2slides'));
        $mform->addHelpButton('attribs[captionw]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[captionw]', PARAM_TEXT);
        //$mform->setDefault('attribs[captionw]', '');


        $mform->addElement('select', 'attribs[cstylepre]', get_string('cstylepre', 'local_mb2slides'), $cstylepre_arr);
        $mform->setType('attribs[cstylepre]', PARAM_TEXT);
        //$mform->setDefault('attribs[cstylepre]', '');

        $mform->addElement('select', 'attribs[cshadow]', get_string('cshadow', 'local_mb2slides'), array(
            999 => get_string('useglobal', 'local_mb2slides'),
            1 => get_string('yes', 'local_mb2slides'),
            0 => get_string('no', 'local_mb2slides')
        ));

        //$mform->setDefault('attribs[linkbtn]', '');
        $mform->setType('attribs[cshadow]', PARAM_INT);


        $mform->addElement('html', '<div' . $sepAttr . '></div>');


        $mform->addElement('text', 'attribs[imagecolor]', get_string('imagecolor', 'local_mb2slides'), array('class'=>'mb2color'));
        $mform->addHelpButton('attribs[imagecolor]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[imagecolor]', PARAM_TEXT);
        //$mform->setDefault('attribs[imagecolor]', '');


        $mform->addElement('text', 'attribs[cbgcolor]', get_string('cbgcolor', 'local_mb2slides'), array('class'=>'mb2color'));
        $mform->addHelpButton('attribs[cbgcolor]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[cbgcolor]', PARAM_TEXT);
        //$mform->setDefault('attribs[cbgcolor]', '');

        $mform->addElement('text', 'attribs[cbordercolor]', get_string('cbordercolor', 'local_mb2slides'), array('class'=>'mb2color'));
        $mform->addHelpButton('attribs[cbordercolor]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[cbordercolor]', PARAM_TEXT);
        //$mform->setDefault('attribs[cbgcolor]', '');

        $mform->addElement('text', 'attribs[titlecolor]', get_string('titlecolor', 'local_mb2slides'), array('class'=>'mb2color'));
        $mform->addHelpButton('attribs[titlecolor]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[titlecolor]', PARAM_TEXT);
        //$mform->setDefault('attribs[titlecolor]', '');


        $mform->addElement('text', 'attribs[desccolor]', get_string('desccolor', 'local_mb2slides'), array('class'=>'mb2color'));
        $mform->addHelpButton('attribs[desccolor]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[desccolor]', PARAM_TEXT);
        //$mform->setDefault('attribs[desccolor]', '');


        $mform->addElement('text', 'attribs[btncolor]', get_string('btncolor', 'local_mb2slides'), array('class'=>'mb2color'));
        $mform->addHelpButton('attribs[btncolor]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[btncolor]', PARAM_TEXT);
        //$mform->setDefault('attribs[btncolor]', '');


        $mform->addElement('html', '<div' . $sepAttr . '></div>');


        $mform->addElement('text', 'attribs[titlefs]', get_string('titlefs', 'local_mb2slides'));
        $mform->addHelpButton('attribs[titlefs]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[titlefs]', PARAM_TEXT);
        //$mform->setDefault('attribs[titlefs]', '');


        $mform->addElement('text', 'attribs[descfs]', get_string('descfs', 'local_mb2slides'));
        $mform->addHelpButton('attribs[descfs]', 'useglobal', 'local_mb2slides');
        $mform->setType('attribs[descfs]', PARAM_TEXT);
        //$mform->setDefault('attribs[descfs]', '');


        $this->add_action_buttons();
    }
}
