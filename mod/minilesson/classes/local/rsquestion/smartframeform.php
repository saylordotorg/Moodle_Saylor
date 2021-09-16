<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:31
 */

namespace mod_minilesson\local\rsquestion;

use \mod_minilesson\constants;

class smartframeform extends baseform
{

    public $type = constants::TYPE_SMARTFRAME;

    public function custom_definition() {

        $this->add_textboxresponse(1,'smartframeurl',true);

    }

}