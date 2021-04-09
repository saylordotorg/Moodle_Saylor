<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:32
 */

namespace mod_solo\attempt;

use \mod_solo\constants;

class userselectionsform extends baseform
{
    public $type = constants::STEP_USERSELECTIONS;
    public $typestring = constants::T_USERSELECTIONS;
    public function custom_definition() {
        $this->moduleinstance = $this->_customdata['moduleinstance'];
        $this->cm = $this->_customdata['cm'];
        //we need the token for polly
        $this->token = $this->_customdata['token'];


        //we set the title and instructions
        $this->add_title(get_string('attempt_partone_title', constants::M_COMPONENT));
        //replace istructions with speaking topic as spec. by activity authoe
        //$this->add_instructions(get_string('attempt_partone_instructions', constants::M_COMPONENT));
        $this->add_activitycontent();

        //targettime
        $this->add_targettime_field();

        //add tips
        $this->add_tips_field();

        //add words
        $this->add_targetwords_fields();

        //add words goal
        $this->add_totalwordsgoal();

        //Conversation length
        //$this->add_conversationlength_field();
    }
    public function custom_definition_after_data() {

       $this->set_targetwords();

    }
    public function get_savebutton_text(){
        return get_string('next', constants::M_COMPONENT);
    }

}