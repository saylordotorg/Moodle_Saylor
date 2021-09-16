<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace mod_readaloud\local\rsquestion;

use \mod_readaloud\constants;

class textpromptlongform extends baseform
{

    public $type = constants::TEXTCHOICE;
    public $typestring = constants::TEXTCHOICE;

    public function custom_definition() {
        $this->add_correctanswer();
        $this->add_textarearesponse(1,'answer1',true);
        $this->add_textarearesponse(2,'answer2',true);
        $this->add_textarearesponse(3,'answer3',true);
        $this->add_textarearesponse(4,'answer4',true);
    }

}