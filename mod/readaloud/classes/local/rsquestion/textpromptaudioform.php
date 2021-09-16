<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace mod_readaloud\local\rsquestion;

use \mod_readaloud\constants;

class textpromptaudioform extends baseform
{

    public $type = constants::AUDIORESPONSE;
    public $typestring = constants::AUDIORESPONSE;

    public function custom_definition() {
        //nothing here
    }

}