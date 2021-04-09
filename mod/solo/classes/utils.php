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
 * Grade Now for solo plugin
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_solo;
defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;


/**
 * Functions used generally across this mod
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils{

    //  const CLOUDPOODLL = 'http://localhost/moodle';
    const CLOUDPOODLL = 'https://cloud.poodll.com';

    //fetch the latest compeleted state
    public static function fetch_latest_finishedattempt($solo,$userid=false) {
        global $DB, $USER;
        if(!$userid){
            $userid = $USER->id;
        }
        $attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,
                array('solo'=>$solo->id,'userid'=>$userid,'completedsteps'=>constants::STEP_SELFTRANSCRIBE),'timemodified DESC','*',0,1);
        if($attempts){
            $attempt=  array_shift($attempts);
        }else {
            $attempt = false;
        }
        return $attempt;
    }

    //Fetch latest attempt regardless of its completed state
    public static function fetch_latest_attempt($solo,$userid=false) {
        global $DB, $USER;
        if(!$userid){
            $userid = $USER->id;
        }
        $attempts = $DB->get_records(constants::M_ATTEMPTSTABLE,
                array('solo'=>$solo->id,'userid'=>$userid),'timemodified DESC','*',0,1);
        if($attempts){
            $attempt=  array_shift($attempts);
        }else {
            $attempt = false;
        }
        return $attempt;
    }


    //are we willing and able to transcribe submissions?
    public static function can_transcribe($instance) {

        //we default to true
        //but it only takes one no ....
        $ret = true;

        //The regions that can transcribe
        switch($instance->region){
            default:
                $ret = true;
        }

        //if user disables ai, we do not transcribe
        if (!$instance->enableai) {
            $ret = false;
        }

        return $ret;
    }

    public static function can_streaming_transcribe($instance){

        $ret = false;

        //The instance languages
        switch($instance->ttslanguage){
            case constants::M_LANG_ENAU:
            case constants::M_LANG_ENGB:
            case constants::M_LANG_ENUS:
            case constants::M_LANG_ESUS:
            case constants::M_LANG_FRFR:
            case constants::M_LANG_FRCA:
                $ret =true;
                break;
            default:
                $ret = false;
        }

        //The supported regions
        if($ret) {
            switch ($instance->region) {
                case "useast1":
                case "useast2":
                case "uswest2":
                case "sydney":
                case "dublin":
                case "ottawa":
                    $ret =true;
                    break;
                default:
                    $ret = false;
            }
        }

        return $ret;
    }

    //streaming results are not the same format as non streaming, we massage the streaming to look like a non streaming
    //to our code that will go on to process it.
    public static function parse_streaming_results($streaming_results){
        $results = json_decode($streaming_results);
        $alltranscript = '';
        $allitems=[];
        foreach($results as $result){
            foreach($result as $completion) {
                foreach ($completion->Alternatives as $alternative) {
                    $alltranscript .= $alternative->Transcript . ' ';
                    foreach ($alternative->Items as $item) {
                        $processeditem = new \stdClass();
                        $processeditem->alternatives = [['content' => $item->Content, 'confidence' => "1.0000"]];
                        $processeditem->end_time = "" . round($item->EndTime,3);
                        $processeditem->start_time = "" . round($item->StartTime,3);
                        $processeditem->type = $item->Type;
                        $allitems[] = $processeditem;
                    }
                }
            }
        }
        $ret = new \stdClass();
        $ret->jobName="streaming";
        $ret->accountId="streaming";
        $ret->results =[];
        $ret->status='COMPLETED';
        $ret->results['transcripts']=[['transcript'=>$alltranscript]];
        $ret->results['items']=$allitems;

        return json_encode($ret);
    }


    //check if curl return from transcript url is valid
    public static function is_valid_transcript($transcript){
        if(strpos($transcript,"<Error><Code>AccessDenied</Code>")>0){
            return false;
        }
        return true;
    }

    public static function transcripts_are_ready_on_s3($attempt) {
        //if the audio filename is empty or wrong, its hopeless ...just return false
        if(!$attempt->filename || empty($attempt->filename)){
            return false;
        }
        $transcripturl = $attempt->filename . '.txt';
        $postdata = array();
        //fetch transcripts, and bail out of they are not ready or wrong
        $transcript = self::curl_fetch($transcripturl,$postdata);
        return self::is_valid_transcript($transcript);
    }

    public static function retrieve_transcripts_from_s3($attempt){
        global $DB;

        //if the audio filename is empty or wrong, its hopeless ...just return false
        if(!$attempt->filename || empty($attempt->filename)){
            return false;
        }

        $jsontranscripturl = $attempt->filename . '.json';
        $vtttranscripturl = $attempt->filename . '.vtt';
        $transcripturl = $attempt->filename . '.txt';
        $postdata = array();
        //fetch transcripts, and bail out of they are not ready or wrong
        $jsontranscript = self::curl_fetch($jsontranscripturl,$postdata);
        if(!self::is_valid_transcript($jsontranscript)){return false;}

        $vtttranscript = self::curl_fetch($vtttranscripturl,$postdata);
        if(!self::is_valid_transcript($vtttranscript)){return false;}

        $transcript = self::curl_fetch($transcripturl,$postdata);
        if(!self::is_valid_transcript($transcript)){return false;}

        //if we got here, we have transcripts and we do not need to come back
        if($jsontranscript && $vtttranscript && $transcript) {
            $updateattempt = new \stdClass();
            $updateattempt->id=$attempt->id;
            $updateattempt->jsontranscript = $jsontranscript;
            $updateattempt->vtttranscript = $vtttranscript;
            $updateattempt->transcript = $transcript;
            $success = $DB->update_record(constants::M_ATTEMPTSTABLE, $updateattempt);

            if($success){
                $attempt->jsontranscript = $jsontranscript;
                $attempt->vtttranscript = $vtttranscript;
                $attempt->transcript = $transcript;

                //update auto transcript stats
                self::update_stats_for_autotranscript($attempt);

                //return attempt
                return $attempt;
            }
        }
        return false;
    }

    //fetch stats, one way or the other
    public static function update_stats_for_autotranscript($attempt) {
        global $DB;
        if($attempt->selftranscript && $attempt->transcript){
            //do some stats work

        }
        return true;
    }

    //fetch interlocutor array to string
    public static function interlocutors_array_to_string($interlocutors) {
        //the incoming data is an array, and we need to csv it.
        if($interlocutors) {
            if(is_array($interlocutors)) {
                $ret = implode(',', $interlocutors);
            }else{
                $ret = $interlocutors;
            }
        }else{
            $ret ='';
        }
        return $ret;
    }

    //fetch lang server url, services incl. 'transcribe' , 'lm', 'lt', 'spellcheck'
    public static function fetch_lang_server_url($region,$service='transcribe'){
        switch($region) {
            case 'tokyo':
                $ret = 'https://tokyo.ls.poodll.com/';
                break;
            case 'sydney':
                $ret = 'https://sydney.ls.poodll.com/';
                break;
            case 'dublin':
                $ret = 'https://dublin.ls.poodll.com/';
                break;
            case 'useast1':
            default:
                $ret = 'https://useast.ls.poodll.com/';
        }
        return $ret . $service;
    }

    //fetch self transcript parts
    public static function fetch_selftranscript_parts($attempt) {
        $sc= $attempt->selftranscript;
        if(!empty($sc)){
            $items = preg_split('/[!?.]+(?![0-9])/', $sc);
            $items = array_filter($items);
            return $items;
        }else{
            return array();
        }
    }

    public static function fetch_sentence_stats($text,$stats){

        //count sentences
        $items = preg_split('/[!?.]+(?![0-9])/', $text);
        $items = array_filter($items);
        $sentencecount = count($items);

        //longest sentence length
        //average sentence length
        $longestsentence=1;
        $averagesentence=1;
        $totallengths = 0;
        foreach($items as $sentence){
            $length = str_word_count($sentence,0);
            if($length>$longestsentence){
                $longestsentence =$length;
            }
            $totallengths+=$length;
        }
        if($totallengths>0 && $sentencecount>0){
            $averagesentence=round($totallengths / $sentencecount);
        }

        //return values
        $stats->avturn = $averagesentence;
        $stats->longestturn = $longestsentence;
        return $stats;
    }

    public static function fetch_word_stats($text,$language, $stats) {

        //prepare data
        $is_english=strpos($language,'en')===0;
        $items = \core_text::strtolower($text);
        $items = str_word_count($items, 1);
        $items = array_unique($items);

        //unique words
        $uniquewords = count($items);

        //long words
        $longwords = 0;
        foreach ($items as $item) {
            if($is_english) {
                if (self::count_syllables($item) > 2) {
                    $longwords++;
                }
            }else{
                if (\core_text::strlen($item) > 5) {
                    $longwords++;
                }
            }
        }

        //return results
        $stats->uniquewords= $uniquewords;
        $stats->longwords= $longwords;
        return $stats;
    }


    /**
     * count_syllables
     *
     * based on: https://github.com/e-rasvet/sassessment/blob/master/lib.php
     */
    public static function count_syllables($word) {
        // https://github.com/vanderlee/phpSyllable (multilang)
        // https://github.com/DaveChild/Text-Statistics (English only)
        // https://pear.php.net/manual/en/package.text.text-statistics.intro.php
        // https://pear.php.net/package/Text_Statistics/docs/latest/__filesource/fsource_Text_Statistics__Text_Statistics-1.0.1TextWord.php.html
        $str = strtoupper($word);
        $oldlen = strlen($str);
        if ($oldlen < 2) {
            $count = 1;
        } else {
            $count = 0;

            // detect syllables for double-vowels
            $vowels = array('AA','AE','AI','AO','AU',
                    'EA','EE','EI','EO','EU',
                    'IA','IE','II','IO','IU',
                    'OA','OE','OI','OO','OU',
                    'UA','UE','UI','UO','UU');
            $str = str_replace($vowels, '', $str);
            $newlen = strlen($str);
            $count += (($oldlen - $newlen) / 2);

            // detect syllables for single-vowels
            $vowels = array('A','E','I','O','U');
            $str = str_replace($vowels, '', $str);
            $oldlen = $newlen;
            $newlen = strlen($str);
            $count += ($oldlen - $newlen);

            // adjust count for special last char
            switch (substr($str, -1)) {
                case 'E': $count--; break;
                case 'Y': $count++; break;
            };
        }
        return $count;
    }


    public static function fetch_targetwords($attempt){
        $targetwords = explode(PHP_EOL,$attempt->topictargetwords);
        $mywords = explode(PHP_EOL,$attempt->mywords);
        $alltargetwords = array_unique(array_merge($targetwords, $mywords));
        return $alltargetwords;
    }

    //we leave it up to the grading logic how/if it adds the ai grades to gradebook
    public static function calc_grammarspell_stats($selftranscript, $region, $language, $stats){
        //init stats with defaults
        $stats->autospell="";
        $stats->autogrammar="";
        $stats->autospellscore=100;
        $stats->autogrammarscore=100;
        $stats->autospellerrors = 0;
        $stats->autogrammarerrors=0;


        //if we have no words for whatever reason the calc will not work
        if(!$stats->words || $stats->words<1) {
            //update spelling and grammar stats in DB
            return $stats;
        }

        //fetch grammar stats
        $lt_url = utils::fetch_lang_server_url($region,'lt');
        $postdata =array('text'=> $selftranscript,'language'=>$language);
        $autogrammar = utils::curl_fetch($lt_url,$postdata,'post');
        //default grammar score
        $autogrammarscore =100;

        //fetch spell stats
        $spellcheck_url = utils::fetch_lang_server_url($region,'spellcheck');
        $spelltranscript = diff::cleanText($selftranscript);
        $postdata =array('passage'=>$spelltranscript,'lang'=>$language);
        $autospell = utils::curl_fetch($spellcheck_url,$postdata,'post');
        //default spell score
        $autospellscore =100;



        //calc grammar score
        if(self::is_json($autogrammar)) {
            //work out grammar
            $grammarobj = json_decode($autogrammar);
            $incorrect = count($grammarobj->matches);
            $stats->autogrammarerrors= $incorrect;
            $raw = $stats->words - ($incorrect * 3);
            if ($raw < 1) {
                $autogrammarscore = 0;
            } else {
                $autogrammarscore = round($raw / $stats->words, 2) * 100;
            }

            $stats->autogrammar = $autogrammar;
            $stats->autogrammarscore = $autogrammarscore;
        }

        //calculate spell score
        if(self::is_json($autospell)) {

            //work out spelling
            $spellobj = json_decode($autospell);
            $correct = 0;
            if($spellobj->status) {
                $spellarray = $spellobj->data->results;
                foreach ($spellarray as $val) {
                    if ($val) {
                        $correct++;
                    }else{
                        $stats->autospellerrors++;
                    }
                }

                if ($correct > 0) {
                    $autospellscore = round($correct / $stats->words, 2) * 100;
                } else {
                    $autospellscore = 0;
                }
            }
        }

        //update spelling and grammar stats in data object and return
        $stats->autospell=$autospell;
        $stats->autogrammar=$autogrammar;
        $stats->autospellscore=$autospellscore;
        $stats->autogrammarscore=$autogrammarscore;
        return $stats;
    }

    //fetch stats, one way or the other
    public static function fetch_stats($attempt,$moduleinstance=false) {
        global $DB;
        //if we have stats in the database, lets use those
        $stats = $DB->get_record(constants::M_STATSTABLE,array('attemptid'=>$attempt->id));
        if(!$moduleinstance) {
            $moduleinstance = $DB->get_record(constants::M_TABLE, array('id' => $attempt->solo));
        }
        if(!$stats){
            $stats = self::calculate_stats($attempt->selftranscript, $attempt);
            //if that worked, and why wouldn't it, lets save them too.
            if ($stats) {
                $stats = utils::fetch_sentence_stats($attempt->selftranscript,$stats);
                $stats = utils::fetch_word_stats($attempt->selftranscript,$moduleinstance->ttslanguage,$stats);
                $stats = self::calc_grammarspell_stats($attempt->selftranscript,
                        $moduleinstance->region,$moduleinstance->ttslanguage,$stats);
                self::save_stats($stats, $attempt);
            }
        }
        //0 aiaccuracy means absolutely nothing was matched
        //-1 means we do not have ai data
        if($stats && $stats->aiaccuracy < 0){
            $stats->aiaccuracy='--';
        }
        return $stats;
    }

    //save / update stats
    public static function save_stats($stats, $attempt){
        global $DB;
        $stats->solo=$attempt->solo;
        $stats->attemptid=$attempt->id;
        $stats->userid=$attempt->userid;
        $stats->timemodified=time();

        $oldstats =$DB->get_record(constants::M_STATSTABLE,
                array('solo'=>$attempt->solo,'attemptid'=>$attempt->id,'userid'=>$attempt->userid));
        if($oldstats){
            $stats->id = $oldstats->id;
            $DB->update_record(constants::M_STATSTABLE,$stats);
        }else{
            $stats->timecreated=time();
            $stats->createdby=$attempt->userid;
            $DB->insert_record(constants::M_STATSTABLE,$stats);
        }
        return;
    }

    //calculate stats of transcript (no db code)
    public static function calculate_stats($usetranscript, $attempt){
        $stats= new \stdClass();
        $stats->turns=0;
        $stats->words=0;
        $stats->avturn=0;
        $stats->longestturn=0;
        $stats->targetwords=0;
        $stats->totaltargetwords=0;
        $stats->aiaccuracy=-1;

        if(!$usetranscript || empty($usetranscript)){
            return false;
        }

        $items = preg_split('/[!?.]+(?![0-9])/', $usetranscript);
        $transcriptarray = array_filter($items);
        $totalturnlengths=0;
        $jsontranscript = '';

        foreach($transcriptarray as $sentence){
            $wordcount = str_word_count($sentence,0);
            if($wordcount===0){continue;}
            $jsontranscript .= $sentence . ' ' ;
            $stats->turns++;
            $stats->words+= $wordcount;
            $totalturnlengths += $wordcount;
            if($stats->longestturn < $wordcount){$stats->longestturn = $wordcount;}
        }
        if(!$stats->turns){
            return false;
        }
        $stats->avturn= round($totalturnlengths  / $stats->turns);
        $topictargetwords = utils::fetch_targetwords($attempt);
        $mywords = explode(PHP_EOL,$attempt->mywords);
        $targetwords = array_filter(array_unique(array_merge($topictargetwords, $mywords)));
        $stats->totaltargetwords = count($targetwords);


        $searchpassage = strtolower($jsontranscript);
        foreach($targetwords as $theword){
            $searchword = self::cleanText($theword);
            if(empty($searchword) || empty($searchpassage)){
                $usecount=0;
            }else {
                $usecount = substr_count($searchpassage, $searchword);
            }
            if($usecount){$stats->targetwords++;}
        }
        return $stats;
    }


    //clear AI data
    // we might do this if the user re-records
    public static function update_stat_aiaccuracy($attemptid, $accuracy) {
        global $DB;

        $stats = $DB->get_record(constants::M_STATSTABLE,array('attemptid'=>$attemptid));
        if($stats) {
            if($stats->aiaccuracy!==$accuracy) {
                $stats->aiaccuracy = $accuracy;
                $DB->update_record(constants::M_STATSTABLE, $stats);

                //update grades in this case
                self::autograde_attempt($attemptid,$stats);

            }
        }
    }

    public static function autograde_attempt($attemptid,$stats=false){
        global $DB;

        $attempt = $DB->get_record(constants::M_ATTEMPTSTABLE, array('id'=>$attemptid));
        //if not attempt found, all is lost.
        if(!$attempt) {
            return;
        }
        //if this was human graded do not mess with it
        if($attempt->manualgraded){
            return;
        }
        //we will need our module instance too
        $moduleinstance=$DB->get_record(constants::M_TABLE,array('id'=>$attempt->solo));
        if(!$moduleinstance) {
            return;
        }
        if(!$moduleinstance->enableautograde){
            return;
        }

        //we might need AI table data too
        $airesult = $DB->get_record(constants::M_AITABLE,array('attemptid'=>$attemptid));

        //figure out the autograde
        $ag_options = json_decode($moduleinstance->autogradeoptions);

        //basescore
        $basescore = $ag_options->gradebasescore;

        //wordcount value
        $thewordcount = $ag_options->gradewordcount== 'totalunique' ? $stats->uniquewords : $stats->words;
        $gradewordgoal = $moduleinstance->gradewordgoal;
        if($gradewordgoal<1){$gradewordgoal=1;}//what kind of person would set to 0 anyway?

        //ratio to apply to start ratio
        $useratio = 100;
        switch($ag_options->graderatioitem){
            case 'spelling':
                $useratio = $stats->autospellscore;
                break;
            case 'grammar':
                $useratio = $stats->autogrammarscore;
                break;
            case 'accuracy':
                if($airesult){
                    $useratio = $stats->aiaccuracy;
                }
                break;
            case '--':
            default:
                $useratio = 100;
                break;

        }

        //get starting value from which to add/minus bonuses
        //eg 80 unique words over target words 100 :
        // round(80/100,2) = .80
        $autograde = round(($thewordcount / $gradewordgoal),2);
        if($autograde>1){$autograde=1;}

        //apply basescore (default 80%)
        //eg we allow 20% for bonuses so start at 80%. And we already have 80%
        //.80 x 80 = 64
        $autograde = $autograde * $basescore;

        //apply use ratio (default aiaccuracy)
        //eg we reduce score according to accuracy. in this case 50%
        // 64 x 50 x .01 = 32
        $autograde = $autograde * $useratio * .01;

        //apply bonuses
        for($bonusno =1;$bonusno<=4;$bonusno++){
            $factor=1;
            if($ag_options->{'bonusdirection' . $bonusno}=='minus'){
                $factor=-1;
            }
            $bonusscore=0;
            switch($ag_options->{'bonus' . $bonusno}){
                case 'spellingmistake':
                    $bonusscore=$stats->autospellerrors;
                    break;
                case 'grammarmistake':
                    $bonusscore=$stats->autogrammarerrors;
                    break;
                case 'bigword':
                    $bonusscore=$stats->longwords;
                    break;
                case 'targetwordspoken':
                    $bonusscore=$stats->targetwords;
                    break;
                case 'sentence':
                    $bonusscore=$stats->turns;
                    break;
                case '--':
                default:
                    $bonusscore=0;
                    break;

            }
            //eg 3 points minus'ed for each of 7 spelling mistakes:
            //if we had 32% : 32 - [3 points] * [-1] * [7] = 11%
            $autograde += $ag_options->{'bonuspoints' . $bonusno} * $factor * $bonusscore;
        }


        //sanitize result
        $autograde = round($autograde,0);
        if($autograde > 100){
            $autograde=100;
        }else if($autograde < 0){
            $autograde=0;
        }

        //update attempts table
        $attempt->grade = round($autograde,0);
        $DB->update_record(constants::M_ATTEMPTSTABLE, $attempt);

        //update gradebook
        $grade = new \stdClass();
        $grade->userid = $attempt->userid;
        $grade->rawgrade = $autograde;
        \solo_grade_item_update($moduleinstance,$grade);
    }

    //remove stats
    public static function remove_stats($attempt){
        global $DB;

        $oldstats =$DB->get_record(constants::M_STATSTABLE,
                array('solo'=>$attempt->solo,'attemptid'=>$attempt->id,'userid'=>$attempt->userid));
        if($oldstats) {
            $DB->delete_records(constants::M_STATSTABLE, array('id'=>$oldstats->id));
        }
    }

    //clear AI data
    // we might do this if the user re-records
    public static function clear_ai_data($activityid, $attemptid){
        global $DB;
        $record = new \stdClass();
        $record->id=$attemptid;
        $record->transcript='';
        $record->jsontranscript='';
        $record->vtttranscript='';

        //Remove AI data from attempts table
        $DB->update_record(constants::M_ATTEMPTSTABLE,$record);

        //update stats table
        self::update_stat_aiaccuracy($attemptid,-1);

        //Delete AI record
        $DB->delete_records(constants::M_AITABLE,array('attemptid'=>$attemptid, 'moduleid'=>$activityid));
    }

    //register an adhoc task to pick up transcripts
    public static function register_aws_task($activityid, $attemptid,$modulecontextid){
        $s3_task = new \mod_solo\task\solo_s3_adhoc();
        $s3_task->set_component('mod_solo');

        $customdata = new \stdClass();
        $customdata->activityid = $activityid;
        $customdata->attemptid = $attemptid;
        $customdata->modulecontextid = $modulecontextid;
        $customdata->taskcreationtime = time();

        $s3_task->set_custom_data($customdata);
        // queue it
        \core\task\manager::queue_adhoc_task($s3_task);
        return true;
    }


    public static function toggle_topic_selected($topicid, $activityid) {
        global $DB;

        // Require view and make sure the user did not previously mark as seen.
        $params = ['moduleid' => $activityid, 'topicid' => $topicid];
        $selected = $DB->record_exists(constants::M_SELECTEDTOPIC_TABLE, $params);

        if($selected){
            $DB->delete_records(constants::M_SELECTEDTOPIC_TABLE, $params);
        }else{
            $entry = new \stdClass();
            $entry->topicid=$topicid;
            $entry->moduleid=$activityid;
            $entry->timemodified=time();

            $DB->insert_record(constants::M_SELECTEDTOPIC_TABLE, $entry);
        }
        return true;
    }


    /*
   * Clean word of things that might prevent a match
    * i) lowercase it
    * ii) remove html characters
    * iii) replace any line ends with spaces (so we can "split" later)
    * iv) remove punctuation
   *
   */
    public static function cleanText($thetext){
        //lowercaseify
        $thetext=strtolower($thetext);

        //remove any html
        $thetext = strip_tags($thetext);

        //replace all line ends with empty strings
        $thetext = preg_replace('#\R+#', '', $thetext);

        //remove punctuation
        //see https://stackoverflow.com/questions/5233734/how-to-strip-punctuation-in-php
        // $thetext = preg_replace("#[[:punct:]]#", "", $thetext);
        //https://stackoverflow.com/questions/5689918/php-strip-punctuation
        $thetext = preg_replace("/[[:punct:]]+/", "", $thetext);

        //remove bad chars
        $b_open="“";
        $b_close="”";
        $b_sopen='‘';
        $b_sclose='’';
        $bads= array($b_open,$b_close,$b_sopen,$b_sclose);
        foreach($bads as $bad){
            $thetext=str_replace($bad,'',$thetext);
        }

        //remove double spaces
        //split on spaces into words
        $textbits = explode(' ',$thetext);
        //remove any empty elements
        $textbits = array_filter($textbits, function($value) { return $value !== ''; });
        $thetext = implode(' ',$textbits);
        return $thetext;
    }

    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    public static function curl_fetch($url,$postdata=false, $method='get')
    {
        global $CFG;

        require_once($CFG->libdir.'/filelib.php');
        $curl = new \curl();

        if($method=='post') {
            $result = $curl->post($url, $postdata);
        }else{
            $result = $curl->get($url, $postdata);
        }
        return $result;
    }

    //This is called from the settings page and we do not want to make calls out to cloud.poodll.com on settings
    //page load, for performance and stability issues. So if the cache is empty and/or no token, we just show a
    //"refresh token" links
    public static function fetch_token_for_display($apiuser,$apisecret){
       global $CFG;

       //First check that we have an API id and secret
        //refresh token
        $refresh = \html_writer::link($CFG->wwwroot . constants::M_URL . '/refreshtoken.php',
                get_string('refreshtoken',constants::M_COMPONENT)) . '<br>';

        $message = '';
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        if(empty($apiuser)){
           $message .= get_string('noapiuser',constants::M_COMPONENT) . '<br>';
       }
        if(empty($apisecret)){
            $message .= get_string('noapisecret',constants::M_COMPONENT);
        }

        if(!empty($message)){
            return $refresh . $message;
        }

        //Fetch from cache and process the results and display
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        //if we have no token object the creds were wrong ... or something
        if(!($tokenobject)){
            $message = get_string('notokenincache',constants::M_COMPONENT);
            //if we have an object but its no good, creds werer wrong ..or something
        }elseif(!property_exists($tokenobject,'token') || empty($tokenobject->token)){
            $message = get_string('credentialsinvalid',constants::M_COMPONENT);
        //if we do not have subs, then we are on a very old token or something is wrong, just get out of here.
        }elseif(!property_exists($tokenobject,'subs')){
            $message = 'No subscriptions found at all';
        }
        if(!empty($message)){
            return $refresh . $message;
        }

        //we have enough info to display a report. Lets go.
        foreach ($tokenobject->subs as $sub){
            $sub->expiredate = date('d/m/Y',$sub->expiredate);
            $message .= get_string('displaysubs',constants::M_COMPONENT, $sub) . '<br>';
        }
        //Is app authorised
        if(in_array(constants::M_COMPONENT,$tokenobject->apps)){
            $message .= get_string('appauthorised',constants::M_COMPONENT) . '<br>';
        }else{
            $message .= get_string('appnotauthorised',constants::M_COMPONENT) . '<br>';
        }

        return $refresh . $message;

    }

    //We need a Poodll token to make all this recording and transcripts happen
    public static function fetch_token($apiuser, $apisecret, $force=false)
    {

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');
        $tokenuser = $cache->get('recentpoodlluser');
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        $now = time();

        //if we got a token and its less than expiry time
        // use the cached one
        if($tokenobject && $tokenuser && $tokenuser==$apiuser && !$force){
            if($tokenobject->validuntil == 0 || $tokenobject->validuntil > $now){
               // $hoursleft= ($tokenobject->validuntil-$now) / (60*60);
                return $tokenobject->token;
            }
        }

        // Send the request & save response to $resp
        $token_url ="https://cloud.poodll.com/local/cpapi/poodlltoken.php";
        $postdata = array(
            'username' => $apiuser,
            'password' => $apisecret,
            'service'=>'cloud_poodll'
        );
        $token_response = self::curl_fetch($token_url,$postdata);
        if ($token_response) {
            $resp_object = json_decode($token_response);
            if($resp_object && property_exists($resp_object,'token')) {
                $token = $resp_object->token;
                //store the expiry timestamp and adjust it for diffs between our server times
                if($resp_object->validuntil) {
                    $validuntil = $resp_object->validuntil - ($resp_object->poodlltime - $now);
                    //we refresh one hour out, to prevent any overlap
                    $validuntil = $validuntil - (1 * HOURSECS);
                }else{
                    $validuntil = 0;
                }

                $tillrefreshhoursleft= ($validuntil-$now) / (60*60);


                //cache the token
                $tokenobject = new \stdClass();
                $tokenobject->token = $token;
                $tokenobject->validuntil = $validuntil;
                $tokenobject->subs=false;
                $tokenobject->apps=false;
                $tokenobject->sites=false;
                if(property_exists($resp_object,'subs')){
                    $tokenobject->subs = $resp_object->subs;
                }
                if(property_exists($resp_object,'apps')){
                    $tokenobject->apps = $resp_object->apps;
                }
                if(property_exists($resp_object,'sites')){
                    $tokenobject->sites = $resp_object->sites;
                }

                $cache->set('recentpoodlltoken', $tokenobject);
                $cache->set('recentpoodlluser', $apiuser);

            }else{
                $token = '';
                if($resp_object && property_exists($resp_object,'error')) {
                    //ERROR = $resp_object->error
                }
            }
        }else{
            $token='';
        }
        return $token;
    }

    //check token and tokenobject(from cache)
    //return error message or blank if its all ok
    public static function fetch_token_error($token){
        global $CFG;

        //check token authenticated
        if(empty($token)) {
            $message = get_string('novalidcredentials', constants::M_COMPONENT,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            return $message;
        }

        // Fetch from cache and process the results and display.
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        //we should not get here if there is no token, but lets gracefully die, [v unlikely]
        if (!($tokenobject)) {
            $message = get_string('notokenincache', constants::M_COMPONENT);
            return $message;
        }

        //We have an object but its no good, creds were wrong ..or something. [v unlikely]
        if (!property_exists($tokenobject, 'token') || empty($tokenobject->token)) {
            $message = get_string('credentialsinvalid', constants::M_COMPONENT);
            return $message;
        }
        // if we do not have subs.
        if (!property_exists($tokenobject, 'subs')) {
            $message = get_string('nosubscriptions', constants::M_COMPONENT);
            return $message;
        }
        // Is app authorised?
        if (!property_exists($tokenobject, 'apps') || !in_array(constants::M_COMPONENT, $tokenobject->apps)) {
            $message = get_string('appnotauthorised', constants::M_COMPONENT);
            return $message;
        }

        //just return empty if there is no error.
        return '';
    }


    public static function fetch_media_urls($contextid, $filearea,$itemid){
        //get question audio div (not so easy)
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid,  constants::M_COMPONENT,$filearea,$itemid);
        $urls=[];
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if($filename=='.'){continue;}
            $filepath = '/';
            $mediaurl = \moodle_url::make_pluginfile_url($contextid, constants::M_COMPONENT,
                    $filearea, $itemid,
                    $filepath, $filename);
            $urls[]= $mediaurl->__toString();

        }
        return $urls;
    }

    public static function fetch_duration_from_transcript($jsontranscript){
        $transcript = json_decode($jsontranscript);
        $titems=$transcript->results->items;
        $twords=array();
        foreach($titems as $titem){
            if($titem->type == 'pronunciation'){
                $twords[] = $titem;
            }
        }
        $lastindex = count($twords);
        if($lastindex>0){
            return $twords[$lastindex-1]->end_time;
        }else{
            return 0;
        }
    }

    public static function get_skin_options(){
        $rec_options = array( constants::SKIN_PLAIN => get_string("skinplain", constants::M_COMPONENT),
                constants::SKIN_BMR => get_string("skinbmr", constants::M_COMPONENT),
                constants::SKIN_123 => get_string("skin123", constants::M_COMPONENT),
                constants::SKIN_FRESH => get_string("skinfresh", constants::M_COMPONENT),
                constants::SKIN_ONCE => get_string("skinonce", constants::M_COMPONENT),
                constants::SKIN_UPLOAD => get_string("skinupload", constants::M_COMPONENT));
        return $rec_options;
    }

    public static function get_recorders_options(){
        $rec_options = array( constants::REC_AUDIO => get_string("recorderaudio", constants::M_COMPONENT),
               // constants::REC_VIDEO  => get_string("recordervideo", constants::M_COMPONENT)
        );
        return $rec_options;
    }

    public static function get_grade_element_options(){
        $options = [];
        for($x=0;$x<101;$x++){
            $options[$x]=$x;
        }
        return $options;
    }

    public static function get_word_count_options(){
        return array(
                "totalunique" => get_string("totalunique",constants::M_COMPONENT),
                "totalwords" => get_string("totalwords",constants::M_COMPONENT),
        );
    }

  public static function get_region_options(){
      return array(
        "useast1" => get_string("useast1",constants::M_COMPONENT),
          "tokyo" => get_string("tokyo",constants::M_COMPONENT),
          "sydney" => get_string("sydney",constants::M_COMPONENT),
          "dublin" => get_string("dublin",constants::M_COMPONENT),
          "ottawa" => get_string("ottawa",constants::M_COMPONENT),
          "frankfurt" => get_string("frankfurt",constants::M_COMPONENT),
          "london" => get_string("london",constants::M_COMPONENT),
          "saopaulo" => get_string("saopaulo",constants::M_COMPONENT),
          "singapore" => get_string("singapore",constants::M_COMPONENT),
          "mumbai" => get_string("mumbai",constants::M_COMPONENT)
      );
  }

  public static function get_expiredays_options(){
      return array(
          "1"=>"1",
          "3"=>"3",
          "7"=>"7",
          "30"=>"30",
          "90"=>"90",
          "180"=>"180",
          "365"=>"365",
          "730"=>"730",
          "9999"=>get_string('forever',constants::M_COMPONENT)
      );
  }


    public static function fetch_options_transcribers() {
        $options =
                array(constants::TRANSCRIBER_AMAZONTRANSCRIBE => get_string("transcriber_amazontranscribe", constants::M_COMPONENT),
                       // constants::TRANSCRIBER_AMAZONSTREAMING => get_string("transcriber_amazonstreaming", constants::M_COMPONENT),
                       // constants::TRANSCRIBER_GOOGLECLOUDSPEECH => get_string("transcriber_googlecloud", constants::M_COMPONENT)
              );
        return $options;
    }

    public static function get_lang_options() {
        return array(
                constants::M_LANG_ARAE => get_string('ar-ae', constants::M_COMPONENT),
                constants::M_LANG_ARSA => get_string('ar-sa', constants::M_COMPONENT),
                constants::M_LANG_DADK => get_string('da-dk', constants::M_COMPONENT),
                constants::M_LANG_DEDE => get_string('de-de', constants::M_COMPONENT),
                constants::M_LANG_DECH => get_string('de-ch', constants::M_COMPONENT),
                constants::M_LANG_ENUS => get_string('en-us', constants::M_COMPONENT),
                constants::M_LANG_ENGB => get_string('en-gb', constants::M_COMPONENT),
                constants::M_LANG_ENAU => get_string('en-au', constants::M_COMPONENT),
                constants::M_LANG_ENIN => get_string('en-in', constants::M_COMPONENT),
                constants::M_LANG_ENIE => get_string('en-ie', constants::M_COMPONENT),
                constants::M_LANG_ENWL => get_string('en-wl', constants::M_COMPONENT),
                constants::M_LANG_ENAB => get_string('en-ab', constants::M_COMPONENT),
                constants::M_LANG_ESUS => get_string('es-us', constants::M_COMPONENT),
                constants::M_LANG_ESES => get_string('es-es', constants::M_COMPONENT),
                constants::M_LANG_FAIR => get_string('fa-ir', constants::M_COMPONENT),
                constants::M_LANG_FRCA => get_string('fr-ca', constants::M_COMPONENT),
                constants::M_LANG_FRFR => get_string('fr-fr', constants::M_COMPONENT),
                constants::M_LANG_HIIN => get_string('hi-in', constants::M_COMPONENT),
                constants::M_LANG_HEIL => get_string('he-il', constants::M_COMPONENT),
                constants::M_LANG_IDID => get_string('id-id', constants::M_COMPONENT),
                constants::M_LANG_ITIT => get_string('it-it', constants::M_COMPONENT),
              //  constants::M_LANG_JAJP => get_string('ja-jp', constants::M_COMPONENT),
              //  constants::M_LANG_KOKR => get_string('ko-kr', constants::M_COMPONENT),
                constants::M_LANG_MSMY => get_string('ms-my', constants::M_COMPONENT),
                constants::M_LANG_NLNL => get_string('nl-nl', constants::M_COMPONENT),
                constants::M_LANG_PTBR => get_string('pt-br', constants::M_COMPONENT),
                constants::M_LANG_PTPT => get_string('pt-pt', constants::M_COMPONENT),
                constants::M_LANG_RURU => get_string('ru-ru', constants::M_COMPONENT),
                constants::M_LANG_TAIN => get_string('ta-in', constants::M_COMPONENT),
                constants::M_LANG_TEIN => get_string('te-in', constants::M_COMPONENT),
                constants::M_LANG_TRTR => get_string('tr-tr', constants::M_COMPONENT),
             //   constants::M_LANG_ZHCN => get_string('zh-cn', constants::M_COMPONENT)
        );
    }

    public static function fetch_topic_levels(){
        return array(
                constants::M_TOPICLEVEL_COURSE=>get_string('topiclevelcourse',constants::M_COMPONENT),
                constants::M_TOPICLEVEL_CUSTOM=>get_string('topiclevelcustom',constants::M_COMPONENT)
        );

    }

    public static function get_conversationlength_options(){
        return array(
                '0'=>get_string('notimelimit',constants::M_COMPONENT),
                '1'=>get_string('xminutes',constants::M_COMPONENT,1),
                '2'=>get_string('xminutes',constants::M_COMPONENT,2),
                '3'=>get_string('xminutes',constants::M_COMPONENT,3),
                '4'=>get_string('xminutes',constants::M_COMPONENT,4),
                '5'=>get_string('xminutes',constants::M_COMPONENT,5),
                '6'=>get_string('xminutes',constants::M_COMPONENT,6),
                '7'=>get_string('xminutes',constants::M_COMPONENT,7),
                '8'=>get_string('xminutes',constants::M_COMPONENT,8),
                '9'=>get_string('xminutes',constants::M_COMPONENT,9),
                '10'=>get_string('xminutes',constants::M_COMPONENT,10)
        );

    }

    public static function fetch_fonticon($fonticon, $size='fa-2x'){
        if(empty($fonticon)){return '';}
        if(strlen($fonticon)<5){return $fonticon;}
        return '<i class="fa ' . $fonticon . ' ' . $size . '"></i>';
    }

    //grading stuff
    public static function fetch_bonus_grade_options(){
        return array(
                '--'=>'--',
                'bigword'=>get_string('bigword',constants::M_COMPONENT),
                'spellingmistake'=>get_string('spellingmistake',constants::M_COMPONENT),
                'grammarmistake'=>get_string('grammarmistake',constants::M_COMPONENT),
                'targetwordspoken'=>get_string('targetwordspoken',constants::M_COMPONENT),
                'sentence'=>get_string('sentence',constants::M_COMPONENT)
        );
    }
    public static function fetch_ratio_grade_options(){
        return array(
                '--'=>'--',
                'spelling'=>get_string('stats_autospellscore',constants::M_COMPONENT),
                'grammar'=>get_string('stats_autogrammarscore',constants::M_COMPONENT),
                'accuracy'=>get_string('stats_aiaccuracy',constants::M_COMPONENT),
        );
    }

    public static function fetch_spellingerrors($stats, $transcript) {
        $spellingerrors=array();
        $usetranscript = diff::cleanText($transcript);
        //sanity check
        if(empty($usetranscript) ||!self::is_json($stats->autospell)){
            return $spellingerrors;
        }

        //return errors
        $spellobj = json_decode($stats->autospell);
        if($spellobj->status) {
            $spellarray = $spellobj->data->results;
            $wordarray = explode(' ', $usetranscript);
            for($index=0;$index<count($spellarray); $index++) {
                if (!$spellarray[$index]) {
                    $spellingerrors[]=$wordarray[$index];
                }
            }
        }
        return $spellingerrors;

    }
    public static function fetch_grammarerrors($stats, $transcript) {
        $usetranscript = diff::cleanText($transcript);
        //sanity check
        if(empty($usetranscript) ||!self::is_json($stats->autogrammar)){
            return [];
        }

        //return errors
        $grammarobj = json_decode($stats->autogrammar);
        return $grammarobj->matches;

    }


    /**
     * fetch a summary of rubric grade for thje student
     *
     * @param \context_module| $modulecontext
     * @param \stdClass| $moduleinstance
     * @param \stdClass| $attempt
     * @return string rubric results
     */
    public static function display_studentgrade($modulecontext, $moduleinstance, $attempt, $gradinginfo, $starrating=false){
        global  $PAGE;

        $gradingitem = null;
        $gradebookgrade = null;
        if (isset($gradinginfo->items[0])) {
            $gradingitem = $gradinginfo->items[0];
            $gradebookgrade = $gradingitem->grades[$attempt->userid];
        }

        $gradefordisplay = null;
        $gradeddate = null;
        $grader = null;
        $gradingmanager = \get_grading_manager($modulecontext, 'mod_solo', 'solo');
        $gradingdisabled = false;
        $gradeid =$attempt->id;

        $method = $gradingmanager->get_active_method();
        if($method=='rubric') {
            if ($controller = $gradingmanager->get_active_controller()) {
                $menu = make_grades_menu($moduleinstance->grade);
                $controller->set_grade_range($menu, $moduleinstance->grade > 0);
                $gradefordisplay = $controller->render_grade($PAGE,
                        $gradeid,
                        $gradingitem,
                        $gradebookgrade->str_long_grade,
                        $gradingdisabled);
            } else {
                $gradefordisplay = 'no grade available';
            }
        }else{
            //star rating
            if($starrating){
                switch(true){
                    case $attempt->grade > 79:
                        $message = get_string('rating_excellent',constants::M_COMPONENT);
                        $stars=5;
                        break;
                    case $attempt->grade > 59:
                        $message = get_string('rating_verygood',constants::M_COMPONENT);
                        $stars=4;
                        break;
                    case $attempt->grade > 39:
                        $message = get_string('rating_good',constants::M_COMPONENT);
                        $stars=3;
                        break;
                    case $attempt->grade > 19:
                        $message = get_string('rating_fair',constants::M_COMPONENT);
                        $stars=2;
                        break;
                    default:
                        $message = get_string('rating_poor',constants::M_COMPONENT);
                        $stars=1;
                }
                $displaystars ='';
                for($i=0;$i<5;$i++){
                    if($i<$stars){
                        $displaystars .= '<i class="fa fa-3x fa-star"></i>';
                    }else{
                        $displaystars .= '<i class="fa fa-3x fa-star-o"></i>';
                    }
                }
                $gradefordisplay = \html_writer::span($message . '<br>' . $displaystars,'mod_solo_evalstars');
            }else {
                $gradefordisplay = get_string('gradelabel', constants::M_COMPONENT, $attempt->grade);
            }
        }
        return $gradefordisplay;
    }



    /**
     * Get an instance of a grading form if advanced grading is enabled.
     * This is specific to the assignment, marker and student.
     *
     * @param int $userid - The student userid
     * @param stdClass|false $grade - The grade record
     * @param bool $gradingdisabled
     * @return mixed gradingform_instance|null $gradinginstance
     */
    public static function get_grading_instance($gradeid, $gradingdisabled,$moduleinstance, $context) {
        global $CFG, $USER;

        $raterid = $USER->id;

        $grademenu = make_grades_menu($moduleinstance->grade);
        $allowgradedecimals = $moduleinstance->grade > 0;

        $advancedgradingwarning = false;

        //necessary for M3.3
        require_once($CFG->dirroot .'/grade/grading/lib.php');

        $gradingmanager = \get_grading_manager($context, 'mod_solo', 'solo');
        $gradinginstance = null;
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_available()) {
                $itemid = null;
                if ($gradeid && $gradeid > 0) {
                    $itemid = $gradeid;
                }
                if ($gradingdisabled && $itemid) {
                    $gradinginstance = $controller->get_current_instance($raterid, $itemid);
                } else if (!$gradingdisabled) {
                    $instanceid = optional_param('advancedgradinginstanceid', 0, PARAM_INT);
                    $gradinginstance = $controller->get_or_create_instance($instanceid,
                        $raterid,
                        $itemid);
                }
            } else {
                $advancedgradingwarning = $controller->form_unavailable_notification();
            }
        }
        if ($gradinginstance) {
            $gradinginstance->get_controller()->set_grade_range($grademenu, $allowgradedecimals);
        }
        return $gradinginstance;
    }

    //see if this is truly json or some error
    public static function is_json($string) {
        if (!$string) {
            return false;
        }
        if (empty($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function get_tts_voices($langcode='en-US',$showall=true) {
        $alllang = array(
                constants::M_LANG_ARAE => ['Zeina'],
            //constants::M_LANG_ARSA => [],
                constants::M_LANG_DEDE => ['Hans' => 'Hans', 'Marlene' => 'Marlene', 'Vicki' => 'Vicki'],
            //constants::M_LANG_DECH => [],
                constants::M_LANG_ENUS => ['Joey' => 'Joey', 'Justin' => 'Justin', 'Matthew' => 'Matthew', 'Ivy' => 'Ivy',
                        'Joanna' => 'Joanna', 'Kendra' => 'Kendra', 'Kimberly' => 'Kimberly', 'Salli' => 'Salli'],
                constants::M_LANG_ENGB => ['Brian' => 'Brian', 'Amy' => 'Amy', 'Emma' => 'Emma'],
                constants::M_LANG_ENAU => ['Russell' => 'Russell', 'Nicole' => 'Nicole'],
                constants::M_LANG_ENIN => ['Aditi' => 'Aditi', 'Raveena' => 'Raveena'],
            // constants::M_LANG_ENIE => [],
                constants::M_LANG_ENWL => ["Geraint" => "Geraint"],
            // constants::M_LANG_ENAB => [],
                constants::M_LANG_ESUS => ['Miguel' => 'Miguel', 'Penelope' => 'Penelope'],
                constants::M_LANG_ESES => ['Enrique' => 'Enrique', 'Conchita' => 'Conchita', 'Lucia' => 'Lucia'],
            //constants::M_LANG_FAIR => [],
                constants::M_LANG_FRCA => ['Chantal' => 'Chantal'],
                constants::M_LANG_FRFR => ['Mathieu' => 'Mathieu', 'Celine' => 'Celine', 'Léa' => 'Léa'],
                constants::M_LANG_HIIN => ["Aditi" => "Aditi"],
            //constants::M_LANG_HEIL => [],
            //constants::M_LANG_IDID => [],
                constants::M_LANG_ITIT => ['Carla' => 'Carla', 'Bianca' => 'Bianca', 'Giorgio' => 'Giorgio'],
                constants::M_LANG_JAJP => ['Takumi' => 'Takumi', 'Mizuki' => 'Mizuki'],
                constants::M_LANG_KOKR => ['Seoyan' => 'Seoyan'],
            //constants::M_LANG_MSMY => [],
                constants::M_LANG_NLNL => ["Ruben" => "Ruben", "Lotte" => "Lotte"],
                constants::M_LANG_PTBR => ['Ricardo' => 'Ricardo', 'Vitoria' => 'Vitoria'],
                constants::M_LANG_PTPT => ["Ines" => "Ines", 'Cristiano' => 'Cristiano'],
                constants::M_LANG_RURU => ["Tatyana" => "Tatyana", "Maxim" => "Maxim"],
            //constants::M_LANG_TAIN => [],
            //constants::M_LANG_TEIN => [],
                constants::M_LANG_TRTR => ['Filiz' => 'Filiz'],
                constants::M_LANG_ZHCN => ['Zhiyu']
        );
        if (array_key_exists($langcode, $alllang) && !$showall) {
            return $alllang[$langcode];
        } else if ($showall) {
            $usearray = [];

            //add current language first
            foreach ($alllang[$langcode] as $v => $thevoice) {
                $usearray[$thevoice] = get_string(strtolower($langcode), constants::M_COMPONENT) . ': ' . $thevoice;
            }
            //then all the rest
            foreach ($alllang as $lang => $voices) {
                if ($lang == $langcode) {
                    continue;
                }
                foreach ($voices as $v => $thevoice) {
                    $usearray[$thevoice] = get_string(strtolower($lang), constants::M_COMPONENT) . ': ' . $thevoice;
                }
            }
            return $usearray;
        } else {
            return $alllang[constants::M_LANG_ENUS];
        }
    }

    //fetch the MP3 URL of the text we want read aloud
    public static function fetch_polly_url($token,$region,$speaktext,$texttype, $voice) {
        global $USER;

        //The REST API we are calling
        $functionname = 'local_cpapi_fetch_polly_url';

        //log.debug(params);
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['moodlewsrestformat'] = 'json';
        $params['text'] = urlencode($speaktext);
        $params['texttype'] = $texttype;
        $params['voice'] = $voice;
        $params['appid'] = constants::M_COMPONENT;
        $params['owner'] = hash('md5',$USER->username);
        $params['region'] = $region;
        $serverurl = self::CLOUDPOODLL . '/webservice/rest/server.php';
        $response = self::curl_fetch($serverurl, $params);
        if (!self::is_json($response)) {
            return false;
        }
        $payloadobject = json_decode($response);

        //returnCode > 0  indicates an error
        if (!isset($payloadobject->returnCode) || $payloadobject->returnCode > 0) {
            return false;
            //if all good, then lets do the embed
        } else if ($payloadobject->returnCode === 0) {
            $pollyurl = $payloadobject->returnMessage;
            return $pollyurl;
        } else {
            return false;
        }
    }

    public static function add_mform_elements($mform, $context,$setuptab=false) {
        global $CFG;
        $config = get_config(constants::M_COMPONENT);

        //if this is setup tab we need to add a field to tell it the id of the activity
        if($setuptab) {
            $mform->addElement('hidden', 'n');
            $mform->setType('n', PARAM_INT);
        }

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('soloname', constants::M_COMPONENT), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'soloname', constants::M_COMPONENT);

        // Adding the standard "intro" and "introformat" fields
        //we do not support this in tabs
        if(!$setuptab) {
            $label = get_string('moduleintro');
            $mform->addElement('editor', 'introeditor', $label, array('rows' => 10), array('maxfiles' => EDITOR_UNLIMITED_FILES,
                    'noclean' => true, 'context' => $context, 'subdirs' => true));
            $mform->setType('introeditor', PARAM_RAW); // no XSS prevention here, users must be trusted
            $mform->addElement('advcheckbox', 'showdescription', get_string('showdescription'));
            $mform->addHelpButton('showdescription', 'showdescription');
        }


        // Speaking topic text
        $mform->addElement('textarea', 'speakingtopic', get_string('speakingtopic', constants::M_COMPONENT, '1'),  array('rows'=>'3', 'cols'=>'80'));
        $mform->setType('speakingtopic', PARAM_TEXT);
        $mform->addHelpButton('speakingtopic', 'speakingtopic', constants::M_MODNAME);
        //$mform->addRule('speakingtopic', get_string('required'), 'required', null, 'client');

        //display media options for speaking prompt
        $m35 = $CFG->version >= 2018051700;
        $togglearray=array();
        $togglearray[] =& $mform->createElement('advcheckbox','addmedia',get_string('addmedia',constants::M_COMPONENT),'');
        $togglearray[] =& $mform->createElement('advcheckbox','addiframe',get_string('addiframe',constants::M_COMPONENT),'');
        $togglearray[] =& $mform->createElement('advcheckbox','addttsaudio',get_string('addttsaudio',constants::M_COMPONENT),'');
        $mform->addGroup($togglearray, 'togglearray', get_string('mediaoptions', constants::M_COMPONENT), array(' '), false);

        //We assume they want to use some media
        $mform->setDefault('addmedia', 1);


        //Speaking topic upload
        $filemanageroptions = solo_filemanager_options($context);
        $mform->addElement('filemanager',
                'topicmedia',
                get_string('topicmedia',constants::M_COMPONENT),
                null,
                $filemanageroptions
        );
        $mform->addHelpButton('topicmedia', 'topicmedia', constants::M_MODNAME);
        if($m35){
            $mform->hideIf('topicmedia', 'addmedia', 'neq', 1);
        }else {
            $mform->disabledIf('topicmedia', 'addmedia', 'neq', 1);
        }

        //Speaking topic iframe
        $mform->addElement('text', 'topiciframe', get_string('topiciframe', constants::M_COMPONENT), array('size'=>100));
        $mform->setType('topiciframe', PARAM_RAW);
        $mform->addHelpButton('topiciframe', 'topiciframe', constants::M_MODNAME);
        if($m35){
            $mform->hideIf('topiciframe','addiframe','neq', 1);
        }else {
            $mform->disabledIf( 'topiciframe','addiframe','neq', 1);
        }

        //Speaking topic TTS
        $mform->addElement('textarea', 'topictts', get_string('topictts', constants::M_COMPONENT), array('wrap'=>'virtual','style'=>'width: 100%;'));
        $mform->setType('topictts', PARAM_RAW);
        $voiceoptions = utils::get_tts_voices();
        $mform->addElement('select', 'topicttsvoice', get_string('topicttsvoice',constants::M_COMPONENT), $voiceoptions);
        $mform->setDefault('topicttsvoice','Amy');
        if($m35){
            $mform->hideIf('topictts', 'addttsaudio', 'neq', 1);
            $mform->hideIf('topicttsvoice', 'addttsaudio', 'neq', 1);
        }else {
            $mform->disabledIf('topictts', 'addttsaudio', 'neq', 1);
            $mform->disabledIf('topicttsvoice', 'addttsaudio', 'neq', 1);
        }


        $options = utils::get_recorders_options();
        $mform->addElement('select','recordertype',get_string('recordertype', constants::M_COMPONENT), $options,array());
        $mform->setDefault('recordertype',constants::REC_AUDIO);


        $options = utils::get_skin_options();
        $mform->addElement('select','recorderskin',get_string('recorderskin', constants::M_COMPONENT), $options,array());
        $mform->setDefault('recorderskin',constants::SKIN_ONCE);

        // Speaking Targets
        $mform->addElement('header', 'speakingtargetsheader', get_string('speakingtargetsheader', constants::M_COMPONENT));

        //time limits
        $options = utils::get_conversationlength_options();
        //the size attribute doesn't work because the attributes are applied on the div container holding the select
        $mform->addElement('select','convlength',get_string('convlength', constants::M_COMPONENT), $options,array());
        $mform->setDefault('convlength',constants::DEF_CONVLENGTH);

        //the size attribute doesn't work because the attributes are applied on the div container holding the select
        $mform->addElement('select','maxconvlength',get_string('maxconvlength', constants::M_COMPONENT), $options,array());
        $mform->setDefault('maxconvlength',constants::DEF_CONVLENGTH);


        //targetwords
        $mform->addElement('static','targetwordsexplanation','',get_string('targetwordsexplanation',constants::M_COMPONENT));
        $mform->addElement('textarea', 'targetwords', get_string('topictargetwords', constants::M_COMPONENT), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('targetwords', PARAM_TEXT);
        //$mform->addRule('targetwords', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('targetwords', 'targetwords', constants::M_MODNAME);

        //Total words goal
        $mform->addElement('text', 'gradewordgoal', get_string('gradewordgoal', constants::M_COMPONENT), array('size'=>20));
        $mform->setType('gradewordgoal', PARAM_INT);
        $mform->setDefault('gradewordgoal',60);
        $mform->addHelpButton('gradewordgoal', 'gradewordgoal', constants::M_MODNAME);

        // Language and Recording
        $mform->addElement('header', 'languageandrecordingheader', get_string('languageandrecordingheader', constants::M_COMPONENT));


        //Enable Manual Transcription [for now lets foprce this ]
        $mform->addElement('hidden', 'enabletranscription', 1);
        $mform->setType('enabletranscription',PARAM_BOOL);
        //$mform->addElement('advcheckbox', 'enabletranscription', get_string('enabletranscription', constants::M_COMPONENT), get_string('enabletranscription_details', constants::M_COMPONENT));
        //$mform->setDefault('enabletranscription',$config->enabletranscription);


        //Enable AI
        //Enable Manual Transcription [for now lets foprce this ]
        $mform->addElement('hidden', 'enableai', 1);
        $mform->setType('enableai',PARAM_BOOL);
        // $mform->addElement('advcheckbox', 'enableai', get_string('enableai', constants::M_COMPONENT), get_string('enableai_details', constants::M_COMPONENT));
        // $mform->setDefault('enableai',$config->enableai);

        //tts options
        $langoptions = \mod_solo\utils::get_lang_options();
        $mform->addElement('select', 'ttslanguage', get_string('ttslanguage', constants::M_COMPONENT), $langoptions);
        $mform->setDefault('ttslanguage',$config->ttslanguage);


        //transcriber options
        $name = 'transcriber';
        $label = get_string($name, constants::M_COMPONENT);
        $options = \mod_solo\utils::fetch_options_transcribers();
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name,constants::TRANSCRIBER_AMAZONTRANSCRIBE);// $config->{$name});

        //region
        $regionoptions = \mod_solo\utils::get_region_options();
        $mform->addElement('select', 'region', get_string('region', constants::M_COMPONENT), $regionoptions);
        $mform->setDefault('region',$config->awsregion);

        //expiredays
        $expiredaysoptions = \mod_solo\utils::get_expiredays_options();
        $mform->addElement('select', 'expiredays', get_string('expiredays', constants::M_COMPONENT), $expiredaysoptions);
        $mform->setDefault('expiredays',$config->expiredays);

        // Attempts and autograding
        $mform->addElement('header', 'attemptsandautogradingheader', get_string('attemptsandautogradingheader', constants::M_COMPONENT));

        //Enable multiple attempts (or not)
        $mform->addElement('advcheckbox', 'multiattempts', get_string('multiattempts', constants::M_COMPONENT), get_string('multiattempts_details', constants::M_COMPONENT));
        $mform->setDefault('multipleattempts',$config->multipleattempts);

        //allow post attempt edit
        $mform->addElement('advcheckbox', 'postattemptedit', get_string('postattemptedit', constants::M_COMPONENT), get_string('postattemptedit_details', constants::M_COMPONENT));
        $mform->setDefault('postattemptedit',false);

        //To auto grade or not to autograde
        $mform->addElement('advcheckbox', 'enableautograde', get_string('enableautograde', constants::M_COMPONENT), get_string('enableautograde_details', constants::M_COMPONENT));
        $mform->setDefault('enableautograde',$config->enableautograde);


        //auto grading options
        $aggroup=array();
        $wordcountoptions = utils::get_word_count_options();
        $startgradeoptions = utils::get_grade_element_options();
        $bonusgradeoptions = utils::fetch_bonus_grade_options();
        $ratiogradeoptions = utils::fetch_ratio_grade_options();
        $plusminusoptions = array('plus'=>'+','minus'=>'-');
        $points_per = get_string("ag_pointsper",constants::M_COMPONENT);
        $over_target_words = get_string("ag_overgradewordgoal",constants::M_COMPONENT);

        $aggroup[] =& $mform->createElement('static', 'stext0', '','( ');
        $aggroup[] =& $mform->createElement('select', 'gradewordcount', '', $wordcountoptions);
        $aggroup[] =& $mform->createElement('static', 'statictext00', '',$over_target_words );
        $aggroup[] =& $mform->createElement('select', 'gradebasescore', '', $startgradeoptions);
        $mform->setDefault('gradebasescore',100);


        $aggroup[] =& $mform->createElement('static', 'stext1', '','% x ');
        $aggroup[] =& $mform->createElement('select', 'graderatioitem', '', $ratiogradeoptions);
        $mform->setDefault('graderatioitem','accuracy');
        $aggroup[] =& $mform->createElement('static', 'stext11', '','% ');
        $mform->addGroup($aggroup, 'aggroup', get_string('aggroup', constants::M_COMPONENT),
                '', false);
        $mform->addHelpButton('aggroup', 'aggroup', constants::M_MODNAME);

        for ($bonusno=1;$bonusno<=4;$bonusno++){
            $bg = array();
            $bg[] =& $mform->createElement('select', 'bonusdirection' . $bonusno, '', $plusminusoptions);
            $bg[] =& $mform->createElement('static', 'stext2'. $bonusno, '',' ');
            $bg[] =& $mform->createElement('select', 'bonuspoints' . $bonusno,'', $startgradeoptions);
            $mform->setDefault('bonuspoints' . $bonusno,3);
            $bg[] =& $mform->createElement('static', 'stext22' . $bonusno, '',$points_per);
            $bg[] =& $mform->createElement('select', 'bonus' . $bonusno, '', $bonusgradeoptions);
            if($bonusno==1) {
                $mform->setDefault('bonus' . $bonusno, 'targetwordspoken');
            }else{
                $mform->setDefault('bonus' . $bonusno, '--');
            }
            $mform->addGroup($bg, 'bonusgroup' . $bonusno, '', '', false);
        }

        //grade options
        //for now we hard code this to latest attempt
        $mform->addElement('hidden', 'gradeoptions',constants::M_GRADELATEST);
        $mform->setType('gradeoptions', PARAM_INT);

        //add tips field
        $edoptions = solo_editor_no_files_options($context);
        $opts = array('rows'=>'2', 'columns'=>'80');
        $mform->addElement('editor','tips_editor',get_string('tips', constants::M_COMPONENT),$opts,$edoptions);
        $mform->setDefault('tips_editor',array('text'=>$config->speakingtips, 'format'=>FORMAT_HTML));
        $mform->setType('tips_editor',PARAM_RAW);

    } //end of add_mform_elements

    public static function prepare_file_and_json_stuff($moduleinstance, $modulecontext){
        $filemanageroptions = solo_filemanager_options($modulecontext);
        $ednofileoptions = solo_editor_no_files_options($modulecontext);
        $editors  = solo_get_editornames();
        $filemanagers  = solo_get_filemanagernames();

        $itemid = 0;
        foreach($editors as $editor){
            $form_data = file_prepare_standard_editor((object)$moduleinstance,$editor, $ednofileoptions, $modulecontext,constants::M_COMPONENT,$editor, $itemid);
        }
        foreach($filemanagers as $fm){
            $draftitemid = file_get_submitted_draft_itemid($fm);
            file_prepare_draft_area($draftitemid, $modulecontext->id, constants::M_COMPONENT,
                    $fm, $itemid,
                    $filemanageroptions);
            $moduleinstance->{$fm} = $draftitemid;
        }

        //autograde options
        if(isset($moduleinstance->autogradeoptions)) {
            $ag_options = json_decode($moduleinstance->autogradeoptions);
            $moduleinstance->graderatioitem = $ag_options->graderatioitem;
            $moduleinstance->gradewordcount = $ag_options->gradewordcount;
            $moduleinstance->gradebasescore = $ag_options->gradebasescore;
            for ($bonusno=1;$bonusno<=4;$bonusno++) {
                $moduleinstance->{'bonusdirection' . $bonusno} = $ag_options->{'bonusdirection' . $bonusno};
                $moduleinstance->{'bonuspoints' . $bonusno}  = $ag_options->{'bonuspoints' . $bonusno} ;
                $moduleinstance->{'bonus' . $bonusno} = $ag_options->{'bonus' . $bonusno};
            }
        }

        //make sure the media upload fields are in the correct state

        $fs = get_file_storage();
        $itemid=0;
        $files = $fs->get_area_files($modulecontext->id, constants::M_COMPONENT,
                'topicmedia', $itemid);
        if ($files) {
            $moduleinstance->addmedia = 1;
        } else {
            $moduleinstance->addmedia = 0;
        }
        if (!empty($moduleinstance->topictts)) {
            $moduleinstance->addttsaudio = 1;
        } else {
            $moduleinstance->addttsaudio = 0;
        }
        if (!empty($moduleinstance->topiciframe)) {
            $moduleinstance->addiframe = 1;
        } else {
            $moduleinstance->addiframe = 0;
        }

        return $moduleinstance;

  }//end of prepare_file_and_json_stuff

}
