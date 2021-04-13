<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace mod_minilesson\local\rsquestion;

use function Aws\constantly;
use \mod_minilesson\constants;

class speechcardsform extends baseform
{

    public $type = constants::TYPE_SPEECHCARDS;

    public function custom_definition() {
        //nothing here
        $this->add_static_text('instructions','',get_string('phraseresponses',constants::M_COMPONENT));
        $this->add_textarearesponse(1,get_string('sentenceprompts',constants::M_COMPONENT),true);
    }

}