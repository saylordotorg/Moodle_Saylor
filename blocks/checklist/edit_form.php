<?php

class block_checklist_edit_form extends block_edit_form {
    /**
     * @param MoodleQuickForm $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        global $DB, $COURSE;


        if ($COURSE->format != 'site') {
            $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
            $mform->addElement('selectyesno', 'config_checklistoverview', get_string('checklistoverview', 'block_checklist'));
            $options = array();
            $checklists = $DB->get_records('checklist', array('course'=>$COURSE->id));
            foreach ($checklists as $checklist) {
                $options[$checklist->id] = s($checklist->name);
            }
            $mform->addElement('select', 'config_checklistid', get_string('choosechecklist', 'block_checklist'), $options);
            $mform->disabledIf('config_checklistid', 'config_checklistoverview', 'eq', 1);

            $options = array(0 => get_string('allparticipants'));
            $groups = $DB->get_records('groups', array('courseid'=>$COURSE->id));
            foreach ($groups as $group) {
                $options[$group->id] = s($group->name);
            }
            $mform->addElement('select', 'config_groupid', get_string('choosegroup', 'block_checklist'), $options);
            $mform->disabledIf('config_groupid', 'config_checklistoverview', 'eq', 1);
        }
    }

    function set_data($defaults) {
        parent::set_data($defaults);
    }
}
