<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/08/29
 * Time: 16:12
 */

namespace mod_readaloud;


class comprehensiontest
{
    protected $cm;
    protected $context;
    protected $mod;
    protected $items;

    public function __construct($cm) {
        global $DB;
        $this->cm = $cm;
        $this->mod = $DB->get_record(constants::M_TABLE, ['id' => $cm->instance], '*', MUST_EXIST);
        $this->context = \context_module::instance($cm->id);
    }

    public function fetch_media_url($filearea,$item){
        //get question audio div (not so easy)
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id,  constants::M_COMPONENT,$filearea,$item->id);
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if($filename=='.'){continue;}
            $filepath = '/';
            $mediaurl = \moodle_url::make_pluginfile_url($this->context->id, constants::M_COMPONENT,
                $filearea, $item->id,
                $filepath, $filename);
            return $mediaurl->__toString();

        }
        //We always take the first file and if we have none, thats not good.
        return "";
       // return "$this->context->id pp $filearea pp $item->id";
    }

    public function fetch_items()
    {
        global $DB;
        if (!$this->items) {
            $this->items = $DB->get_records(constants::M_QTABLE, ['readaloudid' => $this->mod->id],'itemorder ASC');
        }
        if($this->items){
            return $this->items;
        }else{
            return [];
        }
    }

    public function fetch_latest_attempt($userid){
        global $DB;

        $attempts = $DB->get_records(constants::M_USERTABLE,array('readaloudid' => $this->mod->id,'userid'=>$userid),'id DESC');
        if($attempts){
            $attempt = array_shift($attempts);
            return $attempt;
        }else{
            return false;
        }
    }

    /*we will probably never need to use this again */
    public function fetch_test_data_for_js_files(){

        $items = $this->fetch_items();

        //prepare data array for test
        $testitems=array();
        $currentitem=0;
        $itemcount=count($items);
        $itemid= $this->cm->instance;
        foreach($items as $item) {
            $currentitem++;
            $testitem= new \stdClass();
            $testitem->number =  $currentitem;
            $testitem->text =  file_rewrite_pluginfile_urls($item->{constants::TEXTQUESTION},
                'pluginfile.php', $this->context->id,constants::M_COMPONENT,
                constants::TEXTQUESTION_FILEAREA, $itemid);

            for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
                $testitem->{'answer' . $anumber} = file_rewrite_pluginfile_urls($item->{constants::TEXTANSWER . $anumber},
                    'pluginfile.php', $this->context->id,constants::M_COMPONENT,
                    constants::TEXTANSWER_FILEAREA . $anumber, $itemid);
            }
            $testitem->correctanswer =  $item->correctanswer;
            $testitem->id = $item->id;
            $testitem->type=$item->type;
            $testitems[]=$testitem;
        }
        return $testitems;
    }

    /* return the test items suitable for js to use */
    public function fetch_test_data_for_js(){

        $items = $this->fetch_items();

        //prepare data array for test
        $testitems=array();
        $currentitem=0;
        foreach($items as $item) {
            $currentitem++;
            $testitem= new \stdClass();
            $testitem->number =  $currentitem;
            $testitem->text =  $item->{constants::TEXTQUESTION};
            for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
                $testitem->{'answer' . $anumber} = $item->{constants::TEXTANSWER . $anumber};
            }
            $testitem->correctanswer =  $item->correctanswer;
            $testitem->id = $item->id;
            $testitem->type=$item->type;
            $testitems[]=$testitem;
        }
        return $testitems;
    }

    /* called from ajaxhelper to grade test */
    public function grade_test($answers){

        $items = $this->fetch_items();
        $currentitem=0;
        $score=0;
        foreach($items as $item) {
            $currentitem++;
            if (isset($answers->{'' . $currentitem})) {
                if ($item->correctanswer == $answers->{'' . $currentitem}) {
                    $score++;
                }
            }
        }
        if($score==0 || count($items)==0){
            return 0;
        }else{
            return floor(100 * $score/count($items));
        }
    }


}//end of class