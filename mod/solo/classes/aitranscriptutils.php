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
 * @copyright  2019 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_solo;
defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;


/**
 * AI transcript Functions used generally across this mod
 *
 * @package    mod_solo
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aitranscriptutils{



    //are we willing and able to transcribe submissions?
    public static function can_transcribe($instance)
    {
        //we default to true
        //but it only takes one no ....
        $ret = true;

        //The regions that can transcribe
        switch($instance->region){
            case "useast1":
            case "dublin":
            case "sydney":
            case "ottawa":
                break;
            default:
                $ret = false;
        }

        //if user disables ai, we do not transcribe
        if(!$instance->enableai){
            $ret =false;
        }

        return $ret;
    }

    //see if this is truly json or some error
    public static function is_json($string) {
     if(!$string){return false;}
     if(empty($string)){return false;}
     json_decode($string);
     return (json_last_error() == JSON_ERROR_NONE);
    }


    /*
    * Turn a passage with text "lines" into html "brs"
    *
    * @param String The passage of text to convert
    * @param String An optional pad on each replacement (needed for processing when marking up words as spans in passage)
    * @return String The converted passage of text
    */
    public static function lines_to_brs($passage,$seperator=''){
        //see https://stackoverflow.com/questions/5946114/how-to-replace-newline-or-r-n-with-br
        return str_replace("\r\n",$seperator . '<br>' . $seperator,$passage);
        //this is better but we can not pad the replacement and we need that
        //return nl2br($passage);
    }

    public static function fetch_duration_from_transcript($jsontranscript) {
        //if we do not have the full transcript return 0
        if(!$jsontranscript || empty($jsontranscript)){
            return 0;
        }

        $transcript =  json_decode($jsontranscript);
        if(isset($transcript->results)){
            $duration = self::fetch_duration_from_transcript_json($jsontranscript);
        }else{
            $duration = self::fetch_duration_from_transcript_gjson($jsontranscript);
        }
        return $duration;

    }

    public static function fetch_duration_from_transcript_json($jsontranscript){
        //if we do not have the full transcript return 0
        if(!$jsontranscript || empty($jsontranscript)){
            return 0;
        }

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
            return round($twords[$lastindex-1]->end_time,0);
        }else{
            return 0;
        }
    }

    public static function fetch_duration_from_transcript_gjson($jsontranscript){
        //if we do not have the full transcript return 0
        if(!$jsontranscript || empty($jsontranscript)){
            return 0;
        }

        $transcript =  json_decode($jsontranscript);
        $twords=[];
        //create a big array of 'words' from gjson sentences
        foreach($transcript as $sentence) {
            $twords = array_merge($twords,$sentence->words);

        }//end of sentence
        $twordcount=count($twords);
        if($twordcount>0){
            $tword = $twords[$twordcount-1];
            $ms =round(floatval($tword->endTime->nanos * .000000001),2);
            return round($tword->endTime->seconds + $ms,0);
        }else{
            return 0;
        }
    }


    public static function fetch_audio_points($jsontranscript,$matches,$alternatives) {

        //first check if we have a jsontranscript (we might only have a transcript in some cases)
        //if not we just return dummy audio points. Que sera sera
        if (!self::is_json($jsontranscript)) {
            foreach ($matches as $matchitem) {
                $matchitem->audiostart = 0;
                $matchitem->audioend = 0;
            }
            return $matches;
        }
        $transcript =  json_decode($jsontranscript);
        if(isset($transcript->results)){
            $matches = self::fetch_audio_points_json($transcript,$matches,$alternatives);
        }else{
            $matches = self::fetch_audio_points_gjson($transcript,$matches,$alternatives);
        }
        return $matches;
    }


    //fetch start-time and end-time points for each word
    public static function fetch_audio_points_json($transcript,$matches,$alternatives){

       //get type 'pronunciation' items from full transcript. The other type is 'punctuation'.
        $titems=$transcript->results->items;
        $twords=array();
        foreach($titems as $titem){
            if($titem->type == 'pronunciation'){
                $twords[] = $titem;
            }
        }
        $twordcount=count($twords);

        //loop through matches and fetch audio start from word item
        foreach ($matches as $matchitem){
            if($matchitem->tposition <= $twordcount){
                //pull the word data object from the full transcript, at the index of the match
                $tword = $twords[$matchitem->tposition - 1];

                //trust or be sure by matching ...
                $trust = false;
                if($trust){
                    $matchitem->audiostart = $tword->start_time;
                    $matchitem->audioend = $tword->end_time;
                }else {
                    //format the text of the word to lower case no punc, to match the word in the matchitem
                    $tword_text = strtolower($tword->alternatives[0]->content);
                    $tword_text = preg_replace("#[[:punct:]]#", "", $tword_text);
                    //if we got it, fetch the audio position from the word data object
                    if ($matchitem->word == $tword_text) {
                        $matchitem->audiostart = $tword->start_time;
                        $matchitem->audioend = $tword->end_time;

                    //do alternatives search for match
                    }elseif(diff::check_alternatives_for_match($matchitem->word,
                        $tword_text,
                        $alternatives)){
                        $matchitem->audiostart = $tword->start_time;
                        $matchitem->audioend = $tword->end_time;
                    }
                }
            }
        }
        return $matches;
    }

    //fetch start-time and end-time points for each word
    public static function fetch_audio_points_gjson($transcript,$matches,$alternatives){
        $twords=[];
        //create a big array of 'words' from gjson sentences
        foreach($transcript as $sentence) {
            $twords = array_merge($twords,$sentence->words);

        }//end of sentence
        $twordcount=count($twords);

        //loop through matches and fetch audio start from word item
            foreach ($matches as $matchitem) {
                if ($matchitem->tposition <= $twordcount) {
                    //pull the word data object from the full transcript, at the index of the match
                    $tword = $twords[$matchitem->tposition - 1];
                    //make startTime and endTime match the regular format
                    $start_time = $tword->startTime->seconds + round(floatval($tword->startTime->nanos * .000000001),2);
                    $end_time = $tword->endTime->seconds + round(floatval($tword->endTime->nanos * .000000001),2);

                    //trust or be sure by matching ...
                    $trust = false;
                    if ($trust) {
                        $matchitem->audiostart = $start_time;
                        $matchitem->audioend = $end_time;
                    } else {
                        //format the text of the word to lower case no punc, to match the word in the matchitem
                        $tword_text = strtolower($tword->word);
                        $tword_text = preg_replace("#[[:punct:]]#", "", $tword_text);
                        //if we got it, fetch the audio position from the word data object
                        if ($matchitem->word == $tword_text) {
                            $matchitem->audiostart = $start_time;
                            $matchitem->audioend = $end_time;

                            //do alternatives search for match
                        } else if (diff::check_alternatives_for_match($matchitem->word,
                                $tword_text,
                                $alternatives)) {
                            $matchitem->audiostart = $start_time;
                            $matchitem->audioend = $end_time;
                        }
                    }
                }
            }//end of words

        return $matches;
    }

    //this is a server side implementation of the same name function in gradenowhelper.js
    //we need this when calculating adjusted grades(reports/machinegrading.php) and on making machine grades(aigrade.php)
    //the WPM adjustment based on accadjust only applies to machine grades, so it is NOT in gradenowhelper
    public static function processscores($sessiontime,$sessionendword,$errorcount,$activitydata){

        ////wpm score
        $wpmerrors = $errorcount;

        //target WPM is a ReadaLoud feature. We probably won't use it here.
        $targetwpm = 100;

        if($sessiontime > 0) {
            $wpmscore = round(($sessionendword - $wpmerrors) * 60 / $sessiontime);
        }else{
            $wpmscore =0;
        }

        //accuracy score
        if($sessionendword > 0) {
            $accuracyscore = round(($sessionendword - $errorcount) / $sessionendword * 100);
        }else{
            $accuracyscore=0;
        }

        //sessionscore
        $usewpmscore = $wpmscore;
        if($usewpmscore > $targetwpm){
            $usewpmscore = $targetwpm;
        }
        $sessionscore = round($usewpmscore/$targetwpm * 100);

        $scores= new \stdClass();
        $scores->wpmscore = $wpmscore;
        $scores->accuracyscore = $accuracyscore;
        $scores->sessionscore=$sessionscore;
        return $scores;

    }

    //take a json string of session errors, anmd count how many there are.
    public static function count_sessionerrors($sessionerrors){
        $errors = json_decode($sessionerrors);
        if($errors){
            $errorcount = count(get_object_vars($errors));
        }else{
            $errorcount=0;
        }
        return $errorcount;
    }

    //get all the aievaluations for a user
    public static function get_aieval_byuser($moduleid,$userid){
        global $DB;
        $sql = "SELECT tai.*  FROM {" . constants::M_AITABLE . "} tai INNER JOIN  {" . constants::M_ATTEMPTSTABLE . "}" .
            " tu ON tu.id =tai.attemptid AND tu." . constants::M_AI_PARENTFIELDNAME . "=tai.moduleid WHERE tu." . constants::M_AI_PARENTFIELDNAME . "=? AND tu.userid=?";
        $result = $DB->get_records_sql($sql,array($moduleid,$userid));
        return $result;
    }

    //get average difference between human graded attempt error count and AI error count
    //we only fetch if A) have machine grade and B) sessiontime> 0(has been manually graded)
    public static function estimate_errors($moduleid){
        global $DB;
        $errorestimate =0;
        $sql = "SELECT AVG(tai.errorcount - tu.errorcount) as errorestimate  FROM {" . constants::M_AITABLE . "} tai INNER JOIN  {" . constants::M_USERTABLE . "}" .
            " tu ON tu.id =tai.attemptid AND tu." . constants::M_AI_PARENTFIELDNAME . "=tai.moduleid WHERE tu.sessiontime > 0 AND tu." . constants::M_AI_PARENTFIELDNAME . "=?";
        $result = $DB->get_field_sql($sql,array($moduleid));
        if($result!==false){
            $errorestimate = round($result);
        }
        return $errorestimate;
    }

    /*
  * Per passageword, an object with mistranscriptions and their frequency will be returned
    * To be consistent with how data is stored in matches/errors, we return a 1 based array of mistranscriptions
     * @return array an array of stdClass (1 item per passage word) with the passage index(1 based), passage word and array of mistranscription=>count
   */
    public static function fetch_all_mistranscriptions($moduleid)
    {
        global $DB;
        $attempts = $DB->get_records(constants::M_AITABLE ,array('moduleid'=>$moduleid));
        $activity = $DB->get_record(constants::M_TABLE,array('id'=>$moduleid));
        $passagewords = diff::fetchWordArray($activity->passage);
        $passagecount = count($passagewords);
        //$alternatives = diff::fetchAlternativesArray($activity->alternatives);

        $results= array();
        $mistranscriptions= array();
        foreach($attempts as $attempt){
            $transcriptwords = diff::fetchWordArray($attempt->transcript);
            $matches = json_decode($attempt->sessionmatches);
            $mistranscriptions[]= self::fetch_attempt_mistranscriptions($passagewords,$transcriptwords,$matches);
        }
        //aggregate results
        for($wordnumber=1;$wordnumber<=$passagecount;$wordnumber++){
           $aggregate_set = array();
           foreach($mistranscriptions as $mistranscript){
               if(!$mistranscript[$wordnumber]){continue;}
               if(array_key_exists($mistranscript[$wordnumber],$aggregate_set)){
                   $aggregate_set[$mistranscript[$wordnumber]]++;
               }else{
                   $aggregate_set[$mistranscript[$wordnumber]]=1;
               }
           }
           $result= new \stdClass();
           $result->mistranscriptions=$aggregate_set;
           $result->passageindex=$wordnumber;
           $result->passageword=$passagewords[$wordnumber-1];
           $results[] = $result;
        }//end of for loop
        return $results;
    }


    /*
   * This will return an array of mistranscript strings for a single attemot. 1 entry per passageword.
     * To be consistent with how data is stored in matches/errors, we return a 1 based array of mistranscriptions
     * @return array a 1 based array of mistranscriptions(string) or false. i item for each passage word
    */
    public static function fetch_attempt_mistranscriptions($passagewords,$transcriptwords,$matches)
    {
        $passagecount = count($passagewords);
        if(!$passagecount){return false;}
        $mistranscriptions=array();
        for($wordnumber=1;$wordnumber<=$passagecount;$wordnumber++){
            $mistranscription = self::fetch_one_mistranscription($wordnumber,$transcriptwords,$matches);
            if($mistranscription){
                $mistranscriptions[$wordnumber]=$mistranscription;
            }else{
                $mistranscriptions[$wordnumber]=false;
            }
        }//end of for loop
        return $mistranscriptions;
    }

    /*
   * This will take a wordindex and find the previous and next transcript indexes that were matched and
   * return all the transcript words in between those.
     *
     * @return a string which is the transcript match of a passage word, or false if the transcript=passage
    */
    public static function fetch_one_mistranscription($passageindex,$transcriptwords,$matches){

           //if we have a problem with matches (bad data?) just return
        if(!$matches){
            return false;
        }

            //count transcript words
            $transcriptlength= count($transcriptwords);
            if($transcriptlength==0){
                return false;
            }

            //build a quick to search array of matched words
            $passagematches=array();
            foreach($matches as $match){
                $passagematches[$match->pposition]=$match->word;
            }

            //find startindex
            $startindex=-1;
            for($wordnumber=$passageindex;$wordnumber>0;$wordnumber--){

                $ismatched =array_key_exists($wordnumber,$passagematches);
                if($ismatched){
                    $startindex=$matches->{$wordnumber}->tposition+1;
                    break;
                }
            }//end of for loop

            //find endindex
            $endindex=-1;
            for($wordnumber=$passageindex;$wordnumber<=$transcriptlength;$wordnumber++){

                $ismatched =array_key_exists($wordnumber,$passagematches);
                //if we matched then the previous transcript word is the last unmatched one in the checkindex sequence
                if($ismatched){
                    $endindex=$matches->{$wordnumber}->tposition-1;
                    break;
                }
            }//end of for loop --

            //if there was no previous matched word, we set start to 1
            if($startindex==-1){$startindex=1;}
            //if there was no subsequent matched word we flag the end as the -1
            if($endindex==$transcriptlength){
                $endindex=-1;
                //an edge case is where the first word is not in transcript and first match is the second or later passage
                //word. It might not be possible for endindex to be lower than start index, but we don't want it anyway
            }else if($endindex==0 || $endindex < $startindex){
                return false;
            }

            //up until this point the indexes have started from 1, since the passage word numbers start from 1
            //but the transcript array is 0 based so we adjust. array_slice function does not include item and endindex
            ///so it needs to be one more then start index. hence we do not adjust that
            $startindex--;

            //finally we return the section of transcript
            if($endindex>0) {
                $chunklength = $endindex-$startindex;
                $retarray = array_slice($transcriptwords,$startindex, $chunklength);
            }else{
                $retarray = array_slice($transcriptwords,$startindex);
            }

            $ret = implode(" ",$retarray);
            if(trim($ret)==''){
                return false;
            }else{
                return $ret;
            }
    }




    //for error estimate and accuracy adjustment, we can auto estimate errors, never estimate errors, or use a fixed error estimate, or ignore errors
    public static function get_accadjust_options(){
        return array(
            constants::ACCMETHOD_NONE => get_string("accmethod_none",constants::M_COMPONENT),
            //constants::ACCMETHOD_AUTO  => get_string("accmethod_auto",constants::M_COMPONENT),
            constants::ACCMETHOD_FIXED  => get_string("accmethod_fixed",constants::M_COMPONENT),
            constants::ACCMETHOD_NOERRORS  => get_string("accmethod_noerrors",constants::M_COMPONENT),
        );
    }

    public static function render_passage($passage,$markuptype='passage') {
        // load the HTML document
        $doc = new \DOMDocument;
        // it will assume ISO-8859-1  encoding, so we need to hint it:
        //see: http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        @$doc->loadHTML(mb_convert_encoding($passage, 'HTML-ENTITIES', 'UTF-8'));

        // select all the text nodes
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//text()');

        //base CSS class
        if($markuptype=='passage') {
            $cssword = constants::M_CLASS . '_grading_passageword';
            $cssspace = constants::M_CLASS . '_grading_passagespace';
        }else{
            $cssword = constants::M_CLASS . '_grading_correctionsword';
            $cssspace = constants::M_CLASS . '_grading_correctionsspace';
        }

        //init the text count
        $wordcount = 0;
        foreach ($nodes as $node) {
            $trimmednode = trim($node->nodeValue);
            if (empty($trimmednode)) {
                continue;
            }

            //explode missed new lines that had been copied and pasted. eg A[newline]B was not split and was one word
            //This resulted in ai selected error words, having different index to their passage text counterpart
            $seperator = ' ';
            //$words = explode($seperator, $node->nodeValue);

            $nodevalue = self::lines_to_brs($node->nodeValue, $seperator);
            $words = preg_split('/\s+/', $nodevalue);

            foreach ($words as $word) {
                //if its a new line character from lines_to_brs we add it, but not as a word
                if ($word == '<br>') {
                    $newnode = $doc->createElement('br', $word);
                    $node->parentNode->appendChild($newnode);
                    continue;
                }

                $wordcount++;
                $newnode = $doc->createElement('span', $word);
                $spacenode = $doc->createElement('span', $seperator);
                //$newnode->appendChild($spacenode);
                //print_r($newnode);
                $newnode->setAttribute('id', $cssword . '_' . $wordcount);
                $newnode->setAttribute('data-wordnumber', $wordcount);
                $newnode->setAttribute('class', $cssword);
                $spacenode->setAttribute('id', $cssspace . '_' . $wordcount);
                $spacenode->setAttribute('data-wordnumber', $wordcount);
                $spacenode->setAttribute('class', $cssspace);
                $node->parentNode->appendChild($newnode);
                $node->parentNode->appendChild($spacenode);
                //$newnode = $doc->createElement('span', $word);
            }
            $node->nodeValue = "";
        }

        $usepassage = $doc->saveHTML();
        //remove container 'p' tags, they mess up formatting in solo
        $usepassage= str_replace('<p>','',$usepassage);
        $usepassage= str_replace('</p>','',$usepassage);

        if($markuptype=='passage') {
            $ret = \html_writer::div($usepassage, constants::M_CLASS . '_grading_passagecont ' . constants::M_CLASS . '_summarytranscriptplaceholder');
        }else{
            $ret = \html_writer::div($usepassage, constants::M_CLASS . '_corrections ');
        }
        return $ret;
    }

    public static function prepare_turn_markers($attempt){
        $st = $attempt->selftranscript;
        if(empty($st)){
            return [];
        }
        $sentences = utils::fetch_selftranscript_parts($attempt);


        $markers = array();
        $nextstart=1;
        foreach ($sentences as $sentence){
            $wordcount = self::count_turn_words($sentence);
            if($wordcount) {
                $turnend = $nextstart + $wordcount - 1;
                $markers[] = ['start' => $nextstart, 'end' => $turnend];
                $nextstart = $turnend+1;
            }
        }
        return $markers;
    }

    /*
     * This function is v similar to render_passage because we need to have the same word count as render_passage would
     * for markup to be successful.
     *
     * TODO: remove the redundancy here with a helper function
     */
    public static function count_turn_words($turntext){
        // load the HTML document
        $doc = new \DOMDocument;
        // it will assume ISO-8859-1  encoding, so we need to hint it:
        //see: http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        @$doc->loadHTML(mb_convert_encoding($turntext, 'HTML-ENTITIES', 'UTF-8'));

        // select all the text nodes
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//text()');
        //init the text count
        $wordcount = 0;
        foreach ($nodes as $node) {
            $trimmednode = trim($node->nodeValue);
            if (empty($trimmednode)) {
                continue;
            }

            //explode missed new lines that had been copied and pasted. eg A[newline]B was not split and was one word
            //This resulted in ai selected error words, having different index to their passage text counterpart
            $seperator = ' ';
            //$words = explode($seperator, $node->nodeValue);

            $nodevalue = self::lines_to_brs($node->nodeValue, $seperator);
            $words = preg_split('/\s+/', $nodevalue);

            foreach ($words as $word) {
                //if its a new line character from lines_to_brs we add it, but not as a word
                if ($word == '<br>') {
                    $newnode = $doc->createElement('br', $word);
                    $node->parentNode->appendChild($newnode);
                    continue;
                }

                $wordcount++;
            }
            $node->nodeValue = "";
        }
        return $wordcount;
    }

    public static function prepare_passage_amd($attempt, $aidata) {
        global $PAGE;

        //here we set up any info we need to pass into javascript
        $passageopts = Array();
        $passageopts['sesskey'] = sesskey();
        $passageopts['activityid'] = $attempt->solo;
        $passageopts['attemptid'] = $attempt->id;
        $passageopts['sessiontime'] = $aidata->sessiontime;
        $passageopts['sessionerrors'] = $aidata->sessionerrors;
        $passageopts['sessionendword'] = $aidata->sessionendword;
        $passageopts['sessionmatches'] = $aidata->sessionmatches;
        $passageopts['aidata'] = $aidata;
        $passageopts['turns'] = self::prepare_turn_markers($attempt);
        $passageopts['opts_id'] = 'mod_solo_passageopts';



        $jsonstring = json_encode($passageopts);
        $opts_html =
                \html_writer::tag('input', '', array('id' => $passageopts['opts_id'], 'type' => 'hidden', 'value' => $jsonstring));
        $PAGE->requires->js_call_amd("mod_solo/passagemarkup", 'init', array(array('id' => $passageopts['opts_id'])));
        $PAGE->requires->strings_for_js(array('heard'),
                'mod_solo');

        //these need to be returned and echo'ed to the page
        return $opts_html;
    }

    public static function prepare_corrections_amd($sessionerrors, $sessionmatches) {
        global $PAGE;

        //here we set up any info we need to pass into javascript
        $correctionsopts = Array();
        $correctionsopts['sessionerrors'] = $sessionerrors; //these are words different from those in original
        $correctionsopts['sessionmatches'] = $sessionmatches; //these are words missing from the original
        $correctionsopts['opts_id'] = 'mod_solo_correctionopts';



        $jsonstring = json_encode($correctionsopts);
        $opts_html =
            \html_writer::tag('input', '', array('id' => $correctionsopts['opts_id'], 'type' => 'hidden', 'value' => $jsonstring));
        $PAGE->requires->js_call_amd("mod_solo/correctionsmarkup", 'init', array(array('id' => $correctionsopts['opts_id'])));

        //these need to be returned and echo'ed to the page
        return $opts_html;
    }

}
