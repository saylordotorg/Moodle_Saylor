<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace mod_minilesson\local\rsquestion;

use \mod_minilesson\constants;

class multichoiceform extends baseform
{

    public $type = constants::TYPE_MULTICHOICE;

    public function custom_definition() {
        $this->add_showlistorreadoptions(constants::LISTENORREAD,get_string('listenorread',constants::M_COMPONENT),constants::LISTENORREAD_READ);
        $this->add_voiceselect(constants::POLLYVOICE,get_string('choosemultiaudiovoice',constants::M_COMPONENT),
            constants::LISTENORREAD,constants::LISTENORREAD_READ);
        $this->add_voiceoptions(constants::POLLYOPTION,get_string('choosevoiceoption',constants::M_COMPONENT),
            constants::LISTENORREAD,constants::LISTENORREAD_READ);
        $this->add_confirmchoice(constants::CONFIRMCHOICE,get_string('confirmchoice_formlabel',constants::M_COMPONENT));

        $this->add_correctanswer();
        $this->add_textboxresponse(1,'answer1',true);
        $this->add_textboxresponse(2,'answer2',true);
        $this->add_textboxresponse(3,'answer3',false);
        $this->add_textboxresponse(4,'answer4',false);

       // $this->add_repeating_textboxes('sentence',5);
    }

}