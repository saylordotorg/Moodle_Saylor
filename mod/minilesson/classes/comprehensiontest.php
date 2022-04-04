<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 17/08/29
 * Time: 16:12
 */

namespace mod_minilesson;


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
        $this->course =$DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    }

    public function fetch_item_count()
    {
        global $DB;
        if (!$this->items) {
            $this->items = $DB->get_records(constants::M_QTABLE, ['minilesson' => $this->mod->id],'itemorder ASC');
        }
        if($this->items){
            return count($this->items);
        }else{
            return 0;
        }
    }

    public function fetch_media_urls($filearea,$item){
        //get question audio div (not so easy)
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->context->id,  constants::M_COMPONENT,$filearea,$item->id);
        $urls=[];
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if($filename=='.'){continue;}
            $filepath = '/';
            $mediaurl = \moodle_url::make_pluginfile_url($this->context->id, constants::M_COMPONENT,
                $filearea, $item->id,
                $filepath, $filename);
            $urls[]= $mediaurl->__toString();

        }
        return $urls;
       // return "$this->context->id pp $filearea pp $item->id";
    }

    public function fetch_items()
    {
        global $DB;
        if (!$this->items) {
            $this->items = $DB->get_records(constants::M_QTABLE, ['minilesson' => $this->mod->id],'itemorder ASC');
        }
        if($this->items){
            return $this->items;
        }else{
            return [];
        }
    }

    public function fetch_latest_attempt($userid){
        global $DB;

        $attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,array('moduleid' => $this->mod->id,'userid'=>$userid),'id DESC');
        if($attempts){
            $attempt = array_shift($attempts);
            return $attempt;
        }else{
            return false;
        }
    }

    /* return the test items suitable for js to use */
    public function fetch_test_data_for_js($forcetitles=false){
        global $CFG, $USER;

        $items = $this->fetch_items();

        //first confirm we are authorised before we try to get the token
        $config = get_config(constants::M_COMPONENT);
        if(empty($config->apiuser) || empty($config->apisecret)){
            $errormessage = get_string('nocredentials',constants::M_COMPONENT,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            //return error?
            $token=false;
        }else {
            //fetch token
            $token = utils::fetch_token($config->apiuser,$config->apisecret);

            //check token authenticated and no errors in it
            $errormessage = utils::fetch_token_error($token);
            if(!empty($errormessage)){
                //return error?
                //return $this->show_problembox($errormessage);
            }
        }

        //editor options
        $editoroptions = \mod_minilesson\local\rsquestion\helper::fetch_editor_options($this->course, $this->context);

        //prepare data array for test
        $testitems=array();
        $currentitem=0;
        foreach($items as $item) {
            $currentitem++;
            $testitem= new \stdClass();
            $testitem->number =  $currentitem;
            $testitem->correctanswer =  $item->correctanswer;
            $testitem->id = $item->id;
            $testitem->type=$item->type;
            if($this->mod->showqtitles||$forcetitles){$testitem->title=$item->name;}
            $testitem->uniqueid=$item->type . $testitem->number;

            //The question header area (audio, video, tts, image iframe , text instrucions etc)
            switch($testitem->type) {
                case constants::TYPE_DICTATION:
                case constants::TYPE_DICTATIONCHAT:
                case constants::TYPE_SPEECHCARDS:
                case constants::TYPE_LISTENREPEAT:
                case constants::TYPE_MULTICHOICE:
                case constants::TYPE_MULTIAUDIO:
                case constants::TYPE_PAGE:
                case constants::TYPE_SMARTFRAME:
                case constants::TYPE_SHORTANSWER:

                    //Question Text
                    $testitem->text =  file_rewrite_pluginfile_urls($item->{constants::TEXTQUESTION},
                            'pluginfile.php', $this->context->id,constants::M_COMPONENT,
                            constants::TEXTQUESTION_FILEAREA, $testitem->id);
                    $testitem->text =format_text($testitem->text,FORMAT_MOODLE ,$editoroptions);

                    //Question media embed
                    if(!empty(trim($item->{constants::MEDIAIFRAME}))){
                        $testitem->itemiframe=$item->{constants::MEDIAIFRAME};
                    }

                    //Question media items (upload)
                    $mediaurls =$this->fetch_media_urls(constants::MEDIAQUESTION,$item);
                    if($mediaurls && count($mediaurls)>0){
                        foreach($mediaurls as $mediaurl){
                            $file_parts = pathinfo(strtolower($mediaurl));
                            switch($file_parts['extension'])
                            {
                                case "jpg":
                                case "jpeg":
                                case "png":
                                case "gif":
                                case "bmp":
                                case "svg":
                                    $testitem->itemimage = $mediaurl;
                                    break;

                                case "mp4":
                                case "mov":
                                case "webm":
                                case "ogv":
                                    $testitem->itemvideo = $mediaurl;
                                    break;

                                case "m4a":
                                case "mp3":
                                case "ogg":
                                case "wav":
                                    $testitem->itemaudio = $mediaurl;
                                    break;

                                default:
                                    //do nothing
                            }//end of extension switch
                        }//end of for each
                    }//end of if mediaurls

                //TTS Question
                if(!empty(trim($item->{constants::TTSQUESTION}))){
                    $testitem->itemttsaudio=$item->{constants::TTSQUESTION};
                    $testitem->itemttsaudiovoice=$item->{constants::TTSQUESTIONVOICE};
                    $testitem->itemttsoption=$item->{constants::TTSQUESTIONOPTION};
                    $testitem->itemttsautoplay=$item->{constants::TTSAUTOPLAY};
                }
                //YT Clip
                if(!empty(trim($item->{constants::YTVIDEOID}))){
                    $testitem->itemytvideoid=$item->{constants::YTVIDEOID};
                    $testitem->itemytvideostart=$item->{constants::YTVIDEOSTART};
                    $testitem->itemytvideoend=$item->{constants::YTVIDEOEND};
                }
                //TTS Dialog
                if(!empty(trim($item->{constants::TTSDIALOG}))){
                    $item = utils::unpack_ttsdialogopts($item);
                    $testitem->itemttsdialog=true;
                    $testitem->itemttsdialogvisible=$item->{constants::TTSDIALOGVISIBLE};
                    $dialoglines = explode(PHP_EOL,$item->{constants::TTSDIALOG});
                    $linesdata=[];
                    foreach($dialoglines as $theline){
                        if(\core_text::strlen($theline)>1) {
                            $startchars = \core_text::substr($theline, 0, 2);
                            switch($startchars){
                                case 'A)':
                                    $speaker="a";
                                    $voice=$item->{constants::TTSDIALOGVOICEA};
                                    $thetext = \core_text::substr($theline, 2);
                                    break;
                                case 'B)':
                                    $speaker="b";
                                    $voice=$item->{constants::TTSDIALOGVOICEB};
                                    $thetext = \core_text::substr($theline, 2);
                                    break;
                                case 'C)':
                                    $speaker="c";
                                    $voice=$item->{constants::TTSDIALOGVOICEC};
                                    $thetext = \core_text::substr($theline, 2);
                                    break;
                                case '>>':
                                    $speaker="soundeffect";
                                    $voice="soundeffect";
                                    $thetext = \core_text::substr($theline, 2);
                                    break;
                                default:
                                    //if it's just a new line for the previous voice
                                    if(count($linesdata)>0){
                                        $voice=$linesdata[count($linesdata)-1]->voice;
                                        $speaker=$linesdata[count($linesdata)-1]->actor;
                                    //if they never entered A) B) or C)
                                    }else{
                                        $voice=$item->{constants::TTSDIALOGVOICEA};
                                        $speaker="a";
                                    }
                                    $thetext = $theline;

                            }
                            if(empty(trim($thetext))){continue;}
                            $lineset=new \stdClass();
                            $lineset->speaker=$speaker;
                            $lineset->speakertext=$thetext;
                            $lineset->voice=$voice;
                            if($lineset->voice=="soundeffect"){
                                $lineset->audiourl = $CFG->wwwroot  . '/' . constants::M_PATH . '/sounds/' . trim($thetext) . '.mp3';
                            }else {
                                $lineset->audiourl = utils::fetch_polly_url($token, 'useast1', $thetext, 'text', $voice);
                            }
                            $linesdata[] = $lineset;

                        }

                    }
                    $testitem->ttsdialoglines = $linesdata;

                }// end of tts dialog

                //Question TextArea
                if(!empty(trim($item->{constants::QUESTIONTEXTAREA}))){
                    $testitem->itemtextarea=nl2br($item->{constants::QUESTIONTEXTAREA});
                }

                //show text prompt or dots, for listen and repeat really
                $testitem->show_text=$item->{constants::SHOWTEXTPROMPT};

                    break;
                default:
                    $testitem->text =  $item->{constants::TEXTQUESTION};
                    $testitem->text =format_text($testitem->text);
                    break;
            }

            //Text answer fields
            for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
                if(!empty(trim($item->{constants::TEXTANSWER . $anumber}))) {
                    $testitem->{'customtext' . $anumber} = $item->{constants::TEXTANSWER . $anumber};
                }
            }

            //if we need polly then lets do that
            $testitem->usevoice=$item->{constants::POLLYVOICE};
            $testitem->voiceoption=$item->{constants::POLLYOPTION};

            //vertical layout or horizontal layout determined by content options
            $textset = isset($testitem->itemtextarea) && !empty($testitem->itemtextarea);
            $imageset = isset($testitem->itemimage) && !empty($testitem->itemimage);
            $videoset =isset($testitem->itemvideo) && !empty($testitem->itemvideo);
            $iframeset =isset($testitem->itemiframe) && !empty($testitem->itemiframe);
            $ytclipset =isset($testitem->itemytvideoid) && !empty($testitem->itemytvideoid);

            //layout
            $testitem->layout=$item->{constants::LAYOUT};
            if($testitem->layout==constants::LAYOUT_AUTO) {
                //if its not a page or shortanswer, any big content item will make it horizontal layout
                if ($testitem->type !== constants::TYPE_PAGE && $testitem->type !== constants::TYPE_SHORTANSWER) {
                    if ($textset || $imageset || $videoset || $iframeset || $ytclipset) {
                        $testitem->horizontal = true;
                    }
                }
            }else{
                switch($testitem->layout){
                    case constants::LAYOUT_HORIZONTAL:
                        $testitem->horizontal = true;
                        break;
                    case constants::LAYOUT_VERTICAL:
                        $testitem->vertical = true;
                        break;
                    case constants::LAYOUT_MAGAZINE:
                        $testitem->magazine = true;
                        break;
                }
            }

            //Dictation, we might choose to ignore punctuation for dictation
            if($testitem->type == constants::TYPE_DICTATION){
                $testitem->ignorepunctuation=$item->{constants::IGNOREPUNCTUATION}==1;
            }


            //Sentences and Audio Recorder Logic
            switch($testitem->type){
                case constants::TYPE_DICTATION:
                case constants::TYPE_DICTATIONCHAT:
                case constants::TYPE_SPEECHCARDS:
                case constants::TYPE_LISTENREPEAT:
                case constants::TYPE_MULTIAUDIO:
                case constants::TYPE_SHORTANSWER:

                    //phonetic
                    $testitem->phonetic=$item->phonetic;
                    if(!empty($testitem->phonetic)) {
                        $phonetics = explode(PHP_EOL, $testitem->phonetic);
                    }else{
                        $phonetics=[];
                    }

                    //multi audio stores answers differently, and
                   // at least for now there should be no difference between display and sentence
                    if($testitem->type === constants::TYPE_MULTIAUDIO) {
                        $sentences = [];
                        for($anumber=1;$anumber<=constants::MAXANSWERS;$anumber++) {
                            if(!empty(trim($item->{constants::TEXTANSWER . $anumber}))) {
                                $sentences[] = $item->{constants::TEXTANSWER . $anumber};
                            }
                        }
                    }else {
                        $sentences = explode(PHP_EOL, $testitem->customtext1);
                    }

                    //build a sentences object for mustache and JS
                    $index = 0;
                    $testitem->sentences = [];
                    foreach ($sentences as $sentence) {
                        $sentence = trim($sentence);
                        if (empty($sentence)) {
                            continue;
                        }

                        if($testitem->type==constants::TYPE_MULTIAUDIO){
                            if($item->{constants::SHOWTEXTPROMPT}==constants::TEXTPROMPT_DOTS){
                                $prompt = $this->dottify_text($sentence);
                                $displayprompt = $prompt;
                            }else{
                                $prompt = $sentence;
                                $displayprompt = $sentence;
                            }
                        }else{
                            //if we have a pipe prompt = array[0] and response = array[1]
                            $sentencebits = explode('|', $sentence);
                            if (count($sentencebits) > 1) {
                                $prompt = trim($sentencebits[0]);
                                $sentence = trim($sentencebits[1]);
                                if(count($sentencebits) >2){
                                    $displayprompt = trim($sentencebits[2]);
                                }else{
                                    $displayprompt = $prompt;
                                }
                                
                            } else {
                                $prompt = $sentence;
                                $displayprompt = $sentence;
                            }
                        }

                        //If this is Japanese and a'chat' activity, the display sentence will be read as is
                        // but the sentence we show on screen as the students entry needs to be broken into "words"
                        //so we process it. In listen and speak it still shows the target, so its word'ified.
                        //speechcards we do not give word level feedback. so we do nothing special
                        //key point is to pass unwordified passage to compare_passage_transcipt ajax.
                        //if short answer we want to convert zankaku numbers to hankaku for comparison (probably also for other types too)
                        if ($this->mod->ttslanguage == constants::M_LANG_JAJP) {
                            if ($testitem->type == constants::TYPE_LISTENREPEAT ||$testitem->type == constants::TYPE_DICTATIONCHAT) {
                              // sadly this segmentation algorithm mismatches with server based one we need for phonetics
                              //so we are not using it. We ought to save the segment rather than call each time
                              // 初めまして =>(1) はじめまし て　＆　(2) はじめま　して
                                //はなしてください=>(1)はな　して　く　だ　さい & (2)はな　して　ください
                              //  $sentence = utils::segment_japanese($sentence);
                              //TO DO save segments and not collect them at runtime
                              list($phones,$sentence) = utils::fetch_phones_and_segments($sentence,$this->mod->ttslanguage,$this->mod->region);
                            }elseif($testitem->type == constants::TYPE_SHORTANSWER){
                                $sentence =  mb_convert_kana($sentence,"n");
                            }
                        }




                        $s = new \stdClass();
                        $s->index = $index;
                        $s->indexplusone = $index + 1;
                        $s->sentence = $sentence;
                        $s->prompt = $prompt;
                        $s->displayprompt = $displayprompt;
                        $s->length = \core_text::strlen($s->sentence);

                        //add phonetics if we have them
                        if(isset($phonetics[$index]) && !empty($phonetics[$index])){
                            $s->phonetic=$phonetics[$index];
                        }else{
                            $s->phonetic='';
                        }

                        $index++;
                        $testitem->sentences[] = $s;
                    }


                   //cloudpoodll stuff
                   $testitem->region =$config->awsregion;
                   $testitem->cloudpoodlltoken = $token;
                   $testitem->wwwroot=$CFG->wwwroot;
                   $testitem->language=$this->mod->ttslanguage;
                   $testitem->hints='';
                   $testitem->owner=hash('md5',$USER->username);
                   $testitem->usevoice=$item->{constants::POLLYVOICE};
                   $testitem->voiceoption=$item->{constants::POLLYOPTION};

                   //TT Recorder stuff
                   $testitem->waveheight = 75;
                   //passagehash for several reasons could rightly be empty
                   //if its full it will be region|hash eg tokyo|2353531453415134545
                   //we just want the hash here
                   $testitem->passagehash="";
                   if(!empty($item->passagehash)){
                        $hashbits = explode('|',$item->passagehash,2);
                        if(count($hashbits)==2){
                            $testitem->passagehash  = $hashbits[1];
                        }
                    }

                    //API gateway URL
                   $testitem->asrurl = utils::fetch_lang_server_url($this->mod->region,'transcribe');


                   $testitem->maxtime = 15000;
                    break;

                case constants::TYPE_MULTICHOICE:
                    //multichoice also needs sentences if we are listening. Its a bit of double up but we do that here.
                    $testitem->sentences = [];
                    if($item->{constants::LISTENORREAD}==constants::LISTENORREAD_LISTEN) {
                        $testitem->audiocontent = 1;
                    }
                    for ($anumber = 1; $anumber <= constants::MAXANSWERS; $anumber++) {
                        if (!empty(trim($item->{constants::TEXTANSWER . $anumber}))) {
                            $sentence = trim($item->{constants::TEXTANSWER . $anumber});

                            $s = new \stdClass();
                            $s->index = $anumber - 1;
                            $s->indexplusone = $anumber;
                            $s->sentence = $sentence;
                            $s->length = \core_text::strlen($sentence);

                            if($item->{constants::LISTENORREAD}==constants::LISTENORREAD_LISTEN) {
                                $s->prompt = $this->dottify_text($sentence);
                            }else {
                                $s->prompt =$sentence;
                            }

                            $testitem->sentences[] = $s;
                        }
                    }

                    break;
                case constants::TYPE_PAGE:
                case constants::TYPE_SMARTFRAME:
            }

            //if its a smart frame set the host name so we can pass data around
            if($testitem->type==constants::TYPE_SMARTFRAME){
                $testitem->smartframehost='';
                if(!empty($testitem->customtext1)) {
                    $hostbits = parse_url($testitem->customtext1);
                    if($hostbits) {
                        $testitem->smartframehost = $hostbits['scheme'] . "://" . $hostbits['host'];
                    }
                    //if username is requested, could set it here, any -usersname- in iframe url will be replaced with url encoded name
                    //as test use this url in smartframe instance  [site root]/mod/minilesson/framemessagetest.html?someid=1234&usersname=-usersname-
                    $users_name = fullname($USER);
                    $testitem->customtext1 = str_replace('-usersname-',urlencode($users_name),$testitem->customtext1);
                }
            }

            //add out item to test
            $testitems[]=$testitem;

        }//end of loop



        return $testitems;
    }

    public function dottify_text($rawtext){
        $re = '/[^\'!"#$%&\\\\\'()\*+,\-\.\/:;<=> ?@\[\\\\\]\^_`{|}~\']/u';
        $subst = '•';

        $dots = preg_replace($re, $subst, $rawtext);
        return $dots;
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