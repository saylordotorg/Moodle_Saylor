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
 * Grade Now for readaloud plugin
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_readaloud;
defined('MOODLE_INTERNAL') || die();

use \mod_readaloud\constants;

/**
 * Functions used generally across this mod
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    //const CLOUDPOODLL = 'http://localhost/moodle';
    const CLOUDPOODLL = 'https://cloud.poodll.com';

    //we need to consider legacy client side URLs and cloud hosted ones
    public static function make_audio_URL($filename, $contextid, $component, $filearea, $itemid) {
        //we need to consider legacy client side URLs and cloud hosted ones
        if (strpos($filename, 'http') === 0) {
            $ret = $filename;
        } else {
            $ret = \moodle_url::make_pluginfile_url($contextid, $component,
                    $filearea,
                    $itemid, '/',
                    $filename);
        }
        return $ret;
    }

    /*
     * Do we need to build a language model for this passage?
     *
     */
    public static function needs_lang_model($moduleinstance) {
        switch($moduleinstance->region){

            case 'capetown':
            case 'bahrain':
            case 'tokyo':
            case 'useast1':
            case 'dublin':
            case 'sydney':
            default:
                $shortlang = self::fetch_short_lang($moduleinstance->ttslanguage);
                return ($shortlang=='en' ||
                            $shortlang=='de' ||
                            $shortlang=='fr' ||
                            $shortlang=='ru' ||
                            $shortlang=='eu' ||
                            $shortlang=='pl' ||
                            $shortlang=='fi' ||
                            $shortlang=='it' ||
                            $shortlang=='pt' ||
                            $shortlang=='uk' ||
                            $shortlang=='ro' ||
                            $shortlang=='hu' ||
                            $shortlang=='es') && trim($moduleinstance->passage)!=="";
        }
    }

    /*
     * Hash the passage and compare
     *
     */
    public static function fetch_passagehash($passage,$language) {
        global $CFG;

        $cleantext = diff::cleanText($passage);
        $shortlang = self::fetch_short_lang($language);

            //find numbers in the passage, and then replace those with words in the target text
            $cleantext=alphabetconverter::numbers_to_words_convert($cleantext,$cleantext,$shortlang);

            //dealt with eszetts
            if($shortlang=='de' ){
                    $cleantext=alphabetconverter::eszett_to_ss_convert($cleantext,$cleantext);
            }


        if(!empty($cleantext)) {
            return sha1($cleantext);
        }else{
            return false;
        }
    }
    //we want to generate a phonetics if this is phonetic'able
    public static function update_create_phonetic_segments($moduleinstance, $olditem){
        //if we have an old item, set the default return value to the current phonetic value
        //we will update it if the text has changed

        if($olditem) {
            $thephonetics = $olditem->phonetic;
            $thesegments =$olditem->passagesegments;
        }else{
            $thephonetics ='';
            $thesegments ='';
        }

        $dophonetic = true;
        if($dophonetic) {
            //make sure the passage has really changed before doing an expensive call to create phonetics
            if (!$olditem || $moduleinstance->passage !== $olditem->passage || empty($thesegments)) {
                $segmented = true;
                //build a phonetics string
               list($thephonetics,$thesegments) = utils::fetch_phones_and_segments($moduleinstance->passage, $moduleinstance->ttslanguage, 'tokyo', $segmented);
            }
        }
        return [$thephonetics,$thesegments];
    }

    /*
     *  We want to upgrade all the phonetic models on occasion
     *
     */
    public static function update_all_phonetic_segments(){
        global $DB;
        $updates=0;
        $items = $DB->get_records(constants::M_TABLE);

        foreach($items as $moduleinstance) {
            $olditem = false;
            list($thephonetic,$thepassagesegments) = self::update_create_phonetic_segments($moduleinstance,$olditem);
            if(!empty($thephonetic)){
                $DB->update_record(constants::M_TABLE,array('id'=>$moduleinstance->id,'phonetic'=>$thephonetic, 'passagesegments'=>$thepassagesegments));
                $updates++;
            }
        }
    }

    public static function fetch_short_lang($longlang){
        if(\core_text::strlen($longlang)<=2){return $longlang;}
        if($longlang=="fil-PH"){return "fil";}
        $shortlang = substr($longlang,0,2);
        return $shortlang;
    }

    /*
     * Build a language model for this passage
     *
     */
    public static function fetch_lang_model($passage, $language, $region){
        global $CFG;

        $conf= get_config(constants::M_COMPONENT);
        if (!empty($conf->apiuser) && !empty($conf->apisecret)) {
            $token = self::fetch_token($conf->apiuser, $conf->apisecret);
            //$token = self::fetch_token('russell', 'Password-123',true);

            if(empty($token)){
                return false;
            }
            $url = self::CLOUDPOODLL . "/webservice/rest/server.php";
            $params["wstoken"]=$token;
            $params["wsfunction"]='local_cpapi_generate_lang_model';
            $params["moodlewsrestformat"]='json';
            $params["passage"]=diff::cleanText($passage);

        //strange char or number converter
        $shortlang = self::fetch_short_lang($language);
        //find numbers in the passage, and then replace those with words in the target text

        $params["passage"]=alphabetconverter::numbers_to_words_convert($params["passage"],$params["passage"],$shortlang);

        //other conversions
        switch ($shortlang){

            case 'de':
                //find eszetts in original passage, and convert ss words to eszetts in the target passage
                $params["passage"]=alphabetconverter::eszett_to_ss_convert($params["passage"],$params["passage"]);
                break;

        }

            $params["language"]=$language;
            $params["region"]=$region;

            $resp = self::curl_fetch($url,$params,'post');
            $respObj = json_decode($resp);
            $ret = new \stdClass();
            if(isset($respObj->returnCode)){
                $ret->success = $respObj->returnCode =='0' ? true : false;
                $ret->payload = $respObj->returnMessage;
            }else{
                $ret->success=false;
                $ret->payload = "unknown problem occurred";
            }
            return $ret;
        }else{
            return false;
        }
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

    //we might use AWS Transcribe if its strict or no hash(why)
    public static function do_strict_transcribe($instance) {

        if($instance->stricttranscribe || empty($instance->passagehash)) {
            return true;
        }else{
            return false;
        }
    }

    //De accent and other processing so our auto transcript will match the passage
    public static function remove_accents_and_poormatchchars($moduleinstance){
        switch($moduleinstance->ttslanguage){
            case constants::M_LANG_UKUA:
                $ret = str_replace(
                    array("е́","о́","у́","а́","и́","я́","ю́","Е́","О́","У́","А́","И́","Я́","Ю́","“","”","'","́"),
                    array("е","о","у","а","и","я","ю","Е","О","У","А","И","Я","Ю","\"","\"","’",""),
                    $moduleinstance->passage
                );
               break;
            default:
                $ret = $moduleinstance->passage;
        }
        return $ret;
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

    //Insert spaces in between segments in order to create "words"
    public static function segment_japanese($passage){
        $segments = \mod_readaloud\jp\Analyzer::segment($passage);
        return implode(" ",$segments);
    }


    //convert a phrase or word to a series of phonetic characters that we can use to compare text/spoken
    //the segments will usually just return the phrase , but in japanese we want to segment into words
    public static function fetch_phones_and_segments($phrase, $language, $region='tokyo', $segmented=true){
        global $CFG;

        switch($language){
            case constants::M_LANG_ENUS:
            case constants::M_LANG_ENAB:
            case constants::M_LANG_ENAU:
            case constants::M_LANG_ENPH:
            case constants::M_LANG_ENNZ:
            case constants::M_LANG_ENZA:
            case constants::M_LANG_ENIN:
            case constants::M_LANG_ENIE:
            case constants::M_LANG_ENWL:
            case constants::M_LANG_ENGB:
                $phrasebits = explode(' ',$phrase);
                $phonebits=[];
                foreach($phrasebits as $phrasebit){
                    $phonebits[] = metaphone($phrasebit);
                }
                if($segmented) {
                    $phonetic = implode(' ', $phonebits);
                    $segments=$phrase;
                }else {
                    $phonetic = implode('', $phonebits);
                    $segments=$phrase;
                }

                //the resulting phonetic string will look like this: 0S IS A TK IT IS A KT WN TW 0T IS A MNK
                // but "one" and "won" result in diff phonetic strings and non english support is not there so
                //really we want to put an IPA database on services server and poll as we do for katakanify
                //see: https://github.com/open-dict-data/ipa-dict
                //and command line searchable dictionaries https://github.com/open-dsl-dict/ipa-dict-dsl based on those
                // gdcl :    https://github.com/dohliam/gdcl
                break;
            case constants::M_LANG_JAJP:

                //fetch katakana/hiragana if the JP
                $katakanify_url = utils::fetch_lang_server_url($region,'katakanify');

                //results look like this:

                /*
                    {
                        "status": true,
                        "message": "Katakanify complete.",
                        "data": {
                            "status": true,
                            "results": [
                                "元気な\t形容詞,*,ナ形容詞,ダ列基本連体形,元気だ,げんきな,代表表記:元気だ/げんきだ",
                                "男の子\t名詞,普通名詞,*,*,男の子,おとこのこ,代表表記:男の子/おとこのこ カテゴリ:人 ドメイン:家庭・暮らし",
                                "は\t助詞,副助詞,*,*,は,は,連語",
                                "いい\t動詞,*,子音動詞ワ行,基本連用形,いう,いい,連語",
                                "こ\t接尾辞,動詞性接尾辞,カ変動詞,未然形,くる,こ,連語",
                                "です\t判定詞,*,判定詞,デス列基本形,だ,です,連語",
                                "。\t特殊,句点,*,*,。,。,連語",
                                "EOS",
                                ""
                            ]
                        }
                    }
                */


                //for Japanese we want to segment it into "words"
             //   $passage = utils::segment_japanese($phrase);
                $phrase = mb_convert_kana($phrase,"n");
                $postdata =array('passage'=>$phrase);
                $results = self::curl_fetch($katakanify_url,$postdata,'post');
                if(!self::is_json($results)){return false;}

                $jsonresults = json_decode($results);
                $nodes=[];
                $words=[];
                if($jsonresults && $jsonresults->status==true){
                    foreach($jsonresults->data->results as $result){
                        $bits = preg_split("/\t+/", $result);
                        if(count($bits)>1) {
                            $nodes[] = $bits[1];
                            $words[] = $bits[0];
                        }
                    }
                }

                //process nodes
                $katakanaarray=[];
                $segmentarray=[];
                $nodeindex=-1;
                foreach ($nodes as $n) {
                    $nodeindex++;
                    $analysis = explode(',',$n);
                    if(count($analysis) > 5) {
                        switch($analysis[0]) {
                            case '記号':
                                $segmentcount = count($segmentarray);
                                if($segmentcount>0){
                                    $segmentarray[$segmentcount-1].=$words[$nodeindex];
                                }
                                break;
                            default:
                                $reading = '*';
                                if(count($analysis) > 7) {
                                    $reading = $analysis[7];
                                }
                                if ($reading != '*') {
                                    $katakanaarray[] = $reading;
                                } else if($analysis[1]=='数'){
                                    //numbers dont get phoneticized
                                    $katakanaarray[] = $words[$nodeindex];
                                }
                                $segmentarray[]=$words[$nodeindex];
                        }
                    }
                }
                if($segmented) {
                    $phonetic = implode(' ',$katakanaarray);
                    $segments = implode(' ',$segmentarray);
                }else {
                    $phonetic = implode('',$katakanaarray);
                    $segments = implode('',$segmentarray);
                }
                break;

            default:
                $phonetic = '';
                $segments = $phrase;
        }
        return [$phonetic,$segments];

    }

    //fetch lang server url, services incl. 'transcribe' , 'lm', 'lt', 'spellcheck', 'katakanify'
    public static function fetch_lang_server_url($region,$service='transcribe'){
        switch($region) {
            case 'useast1':
                $ret = 'https://useast.ls.poodll.com/';
                break;
            default:
                $ret = 'https://' . $region . '.ls.poodll.com/';
        }
        return $ret . $service;
    }


    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    public static function curl_fetch($url, $postdata = false, $method='get') {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
       // $curl->setopt(array('CURLOPT_ENCODING' => ""));
        if($method=='get') {
            $result = $curl->get($url, $postdata);
        }else{
            $result = $curl->post($url, $postdata);
        }
        return $result;
    }

    //fetch slightly slower version of speech
    public static function fetch_speech_ssml($text, $ttsspeed){

        switch($ttsspeed){
            case constants::TTSSPEED_SLOW:
                $speed='slow';
                break;
            case constants::TTSSPEED_XSLOW:
                $speed='x-slow';
                break;
            case constants::TTSSPEED_MEDIUM:
            default:
            $speed='medium';
        }

        //deal with SSML reserved characters
        $text = str_replace('&','&amp;',$text);
        $text = str_replace("'",'&apos;',$text);
        $text = str_replace('"','&quot;',$text);
        $text = str_replace('<','&lt;',$text);
        $text = str_replace('>','&gt;',$text);

        $slowtemplate='<speak><break time="1000ms"></break><prosody rate="@@speed@@">@@text@@</prosody></speak>';
        $slowtemplate = str_replace('@@text@@',$text,$slowtemplate);
        $slowtemplate = str_replace('@@speed@@',$speed,$slowtemplate);

        return $slowtemplate;
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
        $params['appid'] = constants::M_COMPONENT;;
        $params['owner'] = hash('md5',$USER->username);
        $params['region'] = $region;
        $params['engine'] = self::can_speak_neural($voice, $region)?'neural' : 'standard';
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

    //fetch and process speech marks
    public static function fetch_polly_speechmarks($token,$region,$speaktext,$texttype, $voice) {
        global $USER;

        //The REST API we are calling
        $functionname = 'local_cpapi_fetch_polly_speechmarks';

        //log.debug(params);
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['moodlewsrestformat'] = 'json';
        $params['text'] = urlencode($speaktext);
        $params['texttype'] = $texttype;
        $params['voice'] = $voice;
        $params['appid'] = constants::M_COMPONENT;;
        $params['owner'] = hash('md5',$USER->username);
        $params['region'] = $region;
        $params['engine'] = self::can_speak_neural($voice, $region)?'neural' : 'standard';
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
        } else if ($payloadobject->returnCode === 0 && $payloadobject->returnMessage ) {
            $pollyspeechmarks = json_decode($payloadobject->returnMessage);
            return $pollyspeechmarks;
        } else {
            return false;
        }
    }

    //can speak neural?
    public static function can_speak_neural($voice,$region){
        //check if the region is supported
        switch($region){
            case "useast1":
            case "tokyo":
            case "sydney":
            case "dublin":
            case "ottawa":
            case "frankfurt":
            case "london":
            case "singapore":
            case "capetown":
                //ok
               break;
            default:
                return false;
        }

        //check if the voice is supported
        if(in_array($voice,constants::M_NEURALVOICES)){
            return true;
        }else{
            return false;
        }
    }

    //Turn a set of speechmarks into a matches format that we use for marking up text in Readaloud
    public static function speechmarks_to_matches($passage,$speechmarks,$language){

        //clean the punctuation
        $passage=diff::cleanText($passage);
        //prepare arrays of words to in transcript and passage to match on
        $passagebits = self::fetch_passage_as_words($passage,$language);
        $transcriptbits =[];
        $transcriptobjects =[];

        //for speechmarks we need to throw away ssml tags and punctuation so we do that,
        // and prepare a matching array of the full speechmarks data object for use after the diff has been run
        foreach($speechmarks as $rawmark) {
            $speechmark = json_decode($rawmark);

            //opt out of current speechmark if its not a word(eg sentence, viseme, ssml) or is ssml mark up
            if (!isset($speechmark->type) || $speechmark->type != 'word') {
                continue;
            }
            //if the word begins with "<" then its ssml so we skip it
            if (\core_text::strlen($speechmark->value) > 1 && \core_text::substr($speechmark->value, 0, 1) == '<') {
                continue;
            }
            $transcriptbits[] = diff::cleanText($speechmark->value);
            $transcriptobjects[] =$speechmark;
        }

        //Most of this is just to keep the diff function happy (it is used to different sets of data from real transcripts)
        $alternatives = diff::fetchAlternativesArray('');
        $wildcards = diff::fetchWildcardsArray($alternatives );
        $passagephonetic_bits = diff::fetchWordArray('');
        $transcript_phonetic ='';
        $transcriptphonetic_bits = diff::fetchWordArray('');

        //fetch sequences of transcript/passage matched words
        // then prepare an array of "differences"
        $passagecount = count($passagebits);
        $transcriptcount = count($transcriptbits);
        $sequences = diff::fetchSequences($passagebits, $transcriptbits, $alternatives, $language,$transcriptphonetic_bits,$passagephonetic_bits);
        $debug=false;
        $diffs = diff::fetchDiffs($sequences, $passagecount, $transcriptcount, $debug);


        //from the array of differences build the matches and pull in audio stamps from speechmark data
        $matches = new \stdClass();
        $currentword = -1;
        $lastword = -1;
        //loop through diffs
        // (could do a for loop here .. since diff count = passage words count for now index is $currentword
        foreach ($diffs as $diff) {
            $currentword++;
            switch ($diff[0]) {
                case Diff::UNMATCHED:
                    break;

                case Diff::MATCHED:
                    //we collect match info so we can play audio from selected word
                    $match = new \stdClass();
                    $match->word = $passagebits[$currentword];
                    $match->pposition = $currentword;
                    $match->tposition = $diff[1]-1;
                    $match->audiostart = ($transcriptobjects[$match->tposition]->time * .001)-.3;
                    $match->audioend = $match->audiostart + .05; //provisional end
                    $match->altmatch = $diff[2];//was this match an alternatives match?
                    $matches->{$currentword} = $match;
                    //set the last audio end to the start of the current one
                    if($lastword > -1){
                        $matches->{$lastword}->audioend=$match->audiostart-.2;
                    }
                    $lastword = $currentword;
                    break;

                default:
                    //do nothing
                    //should never get here
            }
        }
        return $matches;
    }

    //sometimes there are colons and semi colons and quotes in one or the other word, we ignore these and compare
    public static function are_different_words($word1,$word2){
        return    \mod_readaloud\diff::cleanText( \core_text::strtolower($word1)) !==
                \mod_readaloud\diff::cleanText( \core_text::strtolower($word2));
    }

    //This is part of speechmarks processing to create matches.
    //while looping we need to look forward to fetch upcoming data. That forward search is done in this function
    public static function forward_search($speechmarks, $smindex ,$passagewords, $passageindex, $thingtoreturn) {
        for ($i = $smindex; $i < count($speechmarks); $i++) {
            $smindex++;
            if ($smindex >= count($speechmarks)) {
                return [false,false];
            }

            $futuremark = json_decode($speechmarks[$smindex]);
            if (!isset($futuremark->type)) {
                continue;
            } else if ($futuremark->type == 'word') {
                if (\core_text::strlen($futuremark->value) > 1 && \core_text::substr($futuremark->value, 0, 1) == '<') {
                    continue;
                }
                switch ($thingtoreturn) {
                    case 'audioend':
                        return [$futuremark,$smindex];
                    case 'lastpassagemismatch':
                        if ($passageindex+1 >= count($passagewords)) {
                            return [false,false];
                        }
                        if (!self::are_different_words($futuremark->value,$passagewords[$passageindex+1])) {
                            $returnindex=$smindex-1;
                            $returnmark = json_decode($speechmarks[$returnindex]);
                            return [$returnmark,$returnindex];
                        }
                }//end of switch
            }//end of if
        }//end of for
        return [false,false];
    }//end of function




    //This is called from the settings page and we do not want to make calls out to cloud.poodll.com on settings
    //page load, for performance and stability issues. So if the cache is empty and/or no token, we just show a
    //"refresh token" links
    public static function fetch_token_for_display($apiuser, $apisecret) {
        global $CFG;

        //First check that we have an API id and secret
        //refresh token
        $refresh = \html_writer::link($CFG->wwwroot . constants::M_URL . '/refreshtoken.php',
                        get_string('refreshtoken', constants::M_COMPONENT)) . '<br>';

        $message = '';
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);
        if (empty($apiuser)) {
            $message .= get_string('noapiuser', constants::M_COMPONENT) . '<br>';
        }
        if (empty($apisecret)) {
            $message .= get_string('noapisecret', constants::M_COMPONENT);
        }

        if (!empty($message)) {
            return $refresh . $message;
        }

        //Fetch from cache and process the results and display
        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');

        //if we have no token object the creds were wrong ... or something
        if (!($tokenobject)) {
            $message = get_string('notokenincache', constants::M_COMPONENT);
            //if we have an object but its no good, creds werer wrong ..or something
        } else if (!property_exists($tokenobject, 'token') || empty($tokenobject->token)) {
            $message = get_string('credentialsinvalid', constants::M_COMPONENT);
            //if we do not have subs, then we are on a very old token or something is wrong, just get out of here.
        } else if (!property_exists($tokenobject, 'subs')) {
            $message = 'No subscriptions found at all';
        }
        if (!empty($message)) {
            return $refresh . $message;
        }

        //we have enough info to display a report. Lets go.
        foreach ($tokenobject->subs as $sub) {
            $sub->expiredate = date('d/m/Y', $sub->expiredate);
            $message .= get_string('displaysubs', constants::M_COMPONENT, $sub) . '<br>';
        }
        //Is app authorised
        if (in_array(constants::M_COMPONENT, $tokenobject->apps)) {
            $message .= get_string('appauthorised', constants::M_COMPONENT) . '<br>';
        } else {
            $message .= get_string('appnotauthorised', constants::M_COMPONENT) . '<br>';
        }

        return $refresh . $message;

    }

    //We need a Poodll token to make all this recording and transcripts happen
    public static function fetch_token($apiuser, $apisecret, $force = false) {

        $cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, constants::M_COMPONENT, 'token');
        $tokenobject = $cache->get('recentpoodlltoken');
        $tokenuser = $cache->get('recentpoodlluser');
        $apiuser = trim($apiuser);
        $apisecret = trim($apisecret);

        //if we got a token and its less than expiry time
        // use the cached one
        if ($tokenobject && $tokenuser && $tokenuser == $apiuser && !$force) {
            if ($tokenobject->validuntil == 0 || $tokenobject->validuntil > time()) {
                return $tokenobject->token;
            }
        }

        // Send the request & save response to $resp
        $token_url = self::CLOUDPOODLL . "/local/cpapi/poodlltoken.php";
        $postdata = array(
                'username' => $apiuser,
                'password' => $apisecret,
                'service' => 'cloud_poodll'
        );
        $token_response = self::curl_fetch($token_url, $postdata);
        if ($token_response) {
            $resp_object = json_decode($token_response);
            if ($resp_object && property_exists($resp_object, 'token')) {
                $token = $resp_object->token;
                //store the expiry timestamp and adjust it for diffs between our server times
                if ($resp_object->validuntil) {
                    $validuntil = $resp_object->validuntil - ($resp_object->poodlltime - time());
                    //we refresh one hour out, to prevent any overlap
                    $validuntil = $validuntil - (1 * HOURSECS);
                } else {
                    $validuntil = 0;
                }

                //cache the token
                $tokenobject = new \stdClass();
                $tokenobject->token = $token;
                $tokenobject->validuntil = $validuntil;
                $tokenobject->subs = false;
                $tokenobject->apps = false;
                $tokenobject->sites = false;
                if (property_exists($resp_object, 'subs')) {
                    $tokenobject->subs = $resp_object->subs;
                }
                if (property_exists($resp_object, 'apps')) {
                    $tokenobject->apps = $resp_object->apps;
                }
                if (property_exists($resp_object, 'sites')) {
                    $tokenobject->sites = $resp_object->sites;
                }
                if(property_exists($resp_object,'awsaccesssecret')){
                    $tokenobject->awsaccesssecret = $resp_object->awsaccesssecret;
                }
                if(property_exists($resp_object,'awsaccessid')){
                    $tokenobject->awsaccessid = $resp_object->awsaccessid;
                }

                $cache->set('recentpoodlltoken', $tokenobject);
                $cache->set('recentpoodlluser', $apiuser);

            } else {
                $token = '';
                if ($resp_object && property_exists($resp_object, 'error')) {
                    //ERROR = $resp_object->error
                }
            }
        } else {
            $token = '';
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

    /*
    * Turn a passage with text "lines" into html "brs"
    *
    * @param String The passage of text to convert
    * @param String An optional pad on each replacement (needed for processing when marking up words as spans in passage)
    * @return String The converted passage of text
    */
    public static function lines_to_brs($passage, $seperator = '') {
        //see https://stackoverflow.com/questions/5946114/how-to-replace-newline-or-r-n-with-br
        return str_replace("\r\n", $seperator . '<br>' . $seperator, $passage);
        //this is better but we can not pad the replacement and we need that
        //return nl2br($passage);
    }

    public static function fetch_duration_from_transcript($fulltranscript) {
        //if we do not have the full transcript return 0
        if (!$fulltranscript || empty($fulltranscript)) {
            return 0;
        }

        $transcript = json_decode($fulltranscript);
        if (isset($transcript->results)) {
            $duration = self::fetch_duration_from_transcript_json($fulltranscript);
        } else {
            $duration = self::fetch_duration_from_transcript_gjson($fulltranscript);
        }
        return $duration;

    }

    public static function fetch_duration_from_transcript_json($fulltranscript) {
        //if we do not have the full transcript return 0
        if (!$fulltranscript || empty($fulltranscript)) {
            return 0;
        }

        $transcript = json_decode($fulltranscript);
        $titems = $transcript->results->items;
        $twords = array();
        foreach ($titems as $titem) {
            if ($titem->type == 'pronunciation') {
                $twords[] = $titem;
            }
        }
        $lastindex = count($twords);
        if ($lastindex > 0) {
            return round($twords[$lastindex - 1]->end_time, 0);
        } else {
            return 0;
        }
    }

    public static function fetch_duration_from_transcript_gjson($fulltranscript) {
        //if we do not have the full transcript return 0
        if (!$fulltranscript || empty($fulltranscript)) {
            return 0;
        }

        $transcript = json_decode($fulltranscript);
        $twords = [];
        //create a big array of 'words' from gjson sentences
        foreach ($transcript as $sentence) {
            $twords = array_merge($twords, $sentence->words);

        }//end of sentence
        $twordcount = count($twords);
        if ($twordcount > 0) {
            $tword = $twords[$twordcount - 1];
            $ms = round(floatval($tword->endTime->nanos * .000000001), 2);
            return round($tword->endTime->seconds + $ms, 0);
        } else {
            return 0;
        }
    }

    public static function fetch_audio_points($fulltranscript, $matches, $alternatives) {

        //first check if we have a fulltranscript (we might only have a transcript in some cases)
        //if not we just return dummy audio points. Que sera sera
        if (!self::is_json($fulltranscript)) {
            foreach ($matches as $matchitem) {
                $matchitem->audiostart = 0;
                $matchitem->audioend = 0;
            }
            return $matches;
        }
        $transcript = json_decode($fulltranscript);
        if (isset($transcript->results)) {
            $matches = self::fetch_audio_points_json($transcript, $matches, $alternatives);
        } else {
            $matches = self::fetch_audio_points_gjson($transcript, $matches, $alternatives);
        }
        return $matches;
    }

    //fetch start-time and end-time points for each word
    public static function fetch_audio_points_json($transcript, $matches, $alternatives) {

        //get type 'pronunciation' items from full transcript. The other type is 'punctuation'.
        $titems = $transcript->results->items;
        $twords = array();
        foreach ($titems as $titem) {
            if ($titem->type == 'pronunciation') {
                $twords[] = $titem;
            }
        }
        $twordcount = count($twords);

        //loop through matches and fetch audio start from word item
        foreach ($matches as $matchitem) {
            if ($matchitem->tposition <= $twordcount) {
                //pull the word data object from the full transcript, at the index of the match
                $tword = $twords[$matchitem->tposition - 1];

                //trust or be sure by matching ...
                $trust = false;
                if ($trust) {
                    $matchitem->audiostart = $tword->start_time;
                    $matchitem->audioend = $tword->end_time;
                } else {
                    //format the text of the word to lower case no punc, to match the word in the matchitem
                    $tword_text = strtolower($tword->alternatives[0]->content);
                    $tword_text = preg_replace("#[[:punct:]]#", "", $tword_text);
                    //if we got it, fetch the audio position from the word data object
                    if ($matchitem->word == $tword_text) {
                        $matchitem->audiostart = $tword->start_time;
                        $matchitem->audioend = $tword->end_time;

                        //do alternatives search for match
                    } else if (diff::check_alternatives_for_match($matchitem->word,
                            $tword_text,
                            $alternatives)) {
                        $matchitem->audiostart = $tword->start_time;
                        $matchitem->audioend = $tword->end_time;
                    }
                }
            }
        }
        return $matches;
    }

    //fetch start-time and end-time points for each word
    public static function fetch_audio_points_gjson($transcript, $matches, $alternatives) {
        $twords = [];
        //create a big array of 'words' from gjson sentences
        foreach ($transcript as $sentence) {
            $twords = array_merge($twords, $sentence->words);

        }//end of sentence
        $twordcount = count($twords);

        //loop through matches and fetch audio start from word item
        foreach ($matches as $matchitem) {
            if ($matchitem->tposition <= $twordcount) {
                //pull the word data object from the full transcript, at the index of the match
                $tword = $twords[$matchitem->tposition - 1];
                //make startTime and endTime match the regular format
                $start_time = $tword->startTime->seconds + round(floatval($tword->startTime->nanos * .000000001), 2);
                $end_time = $tword->endTime->seconds + round(floatval($tword->endTime->nanos * .000000001), 2);

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


    //compare passage and transcript and return errors and matches
    //this is called from aigrade.php and modelaudio.php
    public static function fetch_diff($passage, $alternatives, $transcript,$fulltranscript, $language,$passagephonetic, $debug = false) {
        global $DB, $CFG;

        //turn the passage and transcript into an array of words
        $passagebits = diff::fetchWordArray($passage);
        $alternatives = diff::fetchAlternativesArray($alternatives);
        $transcriptbits = diff::fetchWordArray($transcript);
        $wildcards = diff::fetchWildcardsArray($alternatives);
        $passagephonetic_bits = diff::fetchWordArray($passagephonetic);

        //If this is Japanese we want to segment it into "words"
        if($language == constants::M_LANG_JAJP) {
            $region='tokyo'; //TO DO: should pass region in and not hard code it
            list($transcript_phonetic,$transcript_segments) = utils::fetch_phones_and_segments($transcript,constants::M_LANG_JAJP,$region);
            $transcriptbits = diff::fetchWordArray($transcript_segments);
        }else{
            $transcript_phonetic ='';
            $transcript_segments='';
        }
        $transcriptphonetic_bits = diff::fetchWordArray($transcript_phonetic);

        //fetch sequences of transcript/passage matched words
        // then prepare an array of "differences"
        $passagecount = count($passagebits);
        $transcriptcount = count($transcriptbits);
        $sequences = diff::fetchSequences($passagebits, $transcriptbits, $alternatives, $language,$transcriptphonetic_bits,$passagephonetic_bits);

        $debugsequences = array();
        if ($debug) {
            $diff_info = diff::fetchDiffs($sequences, $passagecount, $transcriptcount, $debug);
            $diffs = diff::applyWildcards($diff_info[0], $passagebits, $wildcards);
            $debugsequences = $diff_info[1];
        } else {
            $diffs = diff::fetchDiffs($sequences, $passagecount, $transcriptcount, $debug);
            $diffs = diff::applyWildcards($diffs, $passagebits, $wildcards);
        }

        //from the array of differences build error data, match data, markers, scores and metrics
        $errors = new \stdClass();
        $matches = new \stdClass();
        $currentword = 0;
        $lastunmodified = 0;
        //loop through diffs
        // (could do a for loop here .. since diff count = passage words count for now index is $currentword
        foreach ($diffs as $diff) {
            $currentword++;
            switch ($diff[0]) {
                case Diff::UNMATCHED:
                    //we collect error info so we can count and display them on passage
                    $error = new \stdClass();
                    $error->word = $passagebits[$currentword - 1];
                    $error->wordnumber = $currentword;
                    $errors->{$currentword} = $error;
                    break;

                case Diff::MATCHED:
                    //we collect match info so we can play audio from selected word
                    $match = new \stdClass();
                    $match->word = $passagebits[$currentword - 1];
                    $match->pposition = $currentword;
                    $match->tposition = $diff[1];
                    $match->audiostart = 0;//we will assess this from full transcript shortly
                    $match->audioend = 0;//we will assess this from full transcript shortly
                    $match->altmatch = $diff[2];//was this match an alternatives match?
                    $matches->{$currentword} = $match;
                    $lastunmodified = $currentword;
                    break;

                default:
                    //do nothing
                    //should never get here

            }
        }
        $sessionendword = $lastunmodified;

        //discard errors that happen after session end word.
        $errorcount = 0;
        $finalerrors = new \stdClass();
        foreach ($errors as $key => $error) {
            if ($key < $sessionendword) {
                $finalerrors->{$key} = $error;
                $errorcount++;
            }
        }
        //finalise and serialise session errors
        $sessionerrors = json_encode($finalerrors);

        //also  capture match information for debugging and audio point matching
        //we can only map transcript to audio from match data
        $matches = utils::fetch_audio_points($fulltranscript, $matches, $alternatives);

        return[$matches,$sessionendword,$sessionerrors,$errorcount,$debugsequences];

    }


    //this is a server side implementation of the same name function in gradenowhelper.js
    //we need this when calculating adjusted grades(reports/machinegrading.php) and on making machine grades(aigrade.php)
    //the WPM adjustment based on accadjust only applies to machine grades, so it is NOT in gradenowhelper
    public static function processscores($sessiontime, $sessionendword, $errorcount, $activitydata) {

        ////wpm score
        $wpmerrors = $errorcount;
        switch ($activitydata->accadjustmethod) {

            case constants::ACCMETHOD_FIXED:
                $wpmerrors = $wpmerrors - $activitydata->accadjust;
                if ($wpmerrors < 0) {
                    $wpmerrors = 0;
                }
                break;

            case constants::ACCMETHOD_NOERRORS:
                $wpmerrors = 0;
                break;

            case constants::ACCMETHOD_AUTO:
                $adjust = \mod_readaloud\utils::estimate_errors($activitydata->id);
                $wpmerrors = $wpmerrors - $adjust;
                if ($wpmerrors < 0) {
                    $wpmerrors = 0;
                }
                break;

            case constants::ACCMETHOD_NONE:
            default:
                $wpmerrors = $errorcount;
                break;
        }
        if ($sessiontime > 0) {
            //regular WPM
            $totalwords = $sessionendword - $wpmerrors;
            $wpmscore = round(($totalwords * 60) / $sessiontime);

            //strict WPM
            $totalwords = $totalwords - $wpmerrors;
            if($totalwords < 0){$totalwords =0;}
            $strictwpmscore = round(($totalwords * 60) / $sessiontime);

        } else {
            $wpmscore = 0;
            $strictwpmscore = 0;
        }

        //accuracy score
        if ($sessionendword > 0) {
            $accuracyscore = round(($sessionendword - $errorcount) / $sessionendword * 100);
        } else {
            $accuracyscore = 0;
        }

        //sessionscore
        $targetwpm = $activitydata->targetwpm;
        if($targetwpm && $targetwpm >0) {
            if ($activitydata->sessionscoremethod == constants::SESSIONSCORE_STRICT) {
                $usewpmscore = $strictwpmscore;
            } else {
                $usewpmscore = $wpmscore;
            }

            if ($usewpmscore > $targetwpm) {
                $usewpmscore = $targetwpm;
            }
            $sessionscore = round($usewpmscore / $targetwpm * 100);
        }else{
            $sessionscore=100;
        }

        $scores = new \stdClass();
        $scores->wpmscore = $wpmscore;
        $scores->accuracyscore = $accuracyscore;
        $scores->sessionscore = $sessionscore;
        return $scores;

    }

    //take a json string of session errors, anmd count how many there are.
    public static function count_sessionerrors($sessionerrors) {
        $errors = json_decode($sessionerrors);
        if ($errors) {
            $errorcount = count(get_object_vars($errors));
        } else {
            $errorcount = 0;
        }
        return $errorcount;
    }

    //get all the aievaluations for a user
    public static function get_aieval_byuser($readaloudid, $userid) {
        global $DB;
        $sql = "SELECT tai.*  FROM {" . constants::M_AITABLE . "} tai INNER JOIN  {" . constants::M_USERTABLE . "}" .
                " tu ON tu.id =tai.attemptid AND tu.readaloudid=tai.readaloudid WHERE tu.readaloudid=? AND tu.userid=?";
        $result = $DB->get_records_sql($sql, array($readaloudid, $userid));
        return $result;
    }

    //get average difference between human graded attempt error count and AI error count
    //we only fetch if A) have machine grade and B) sessiontime> 0(has been manually graded)
    public static function estimate_errors($readaloudid) {
        global $DB;
        $errorestimate = 0;
        $sql = "SELECT AVG(tai.errorcount - tu.errorcount) as errorestimate  FROM {" . constants::M_AITABLE .
                "} tai INNER JOIN  {" . constants::M_USERTABLE . "}" .
                " tu ON tu.id =tai.attemptid AND tu.readaloudid=tai.readaloudid WHERE tu.sessiontime > 0 AND tu.readaloudid=?";
        $result = $DB->get_field_sql($sql, array($readaloudid));
        if ($result !== false) {
            $errorestimate = round($result);
        }
        return $errorestimate;
    }

    /*
  * Per passageword, an object with mistranscriptions and their frequency will be returned
    * To be consistent with how data is stored in matches/errors, we return a 1 based array of mistranscriptions
     * @return array an array of stdClass (1 item per passage word) with the passage index(1 based), passage word and array of mistranscription=>count
   */
    public static function fetch_all_mistranscriptions($readaloudid) {
        global $DB;
        $attempts = $DB->get_records(constants::M_AITABLE, array('readaloudid' => $readaloudid));
        $activity = $DB->get_record(constants::M_TABLE, array('id' => $readaloudid));
        $passagewords = diff::fetchWordArray($activity->passage);
        $passagecount = count($passagewords);
        //$alternatives = diff::fetchAlternativesArray($activity->alternatives);

        $results = array();
        $mistranscriptions = array();
        foreach ($attempts as $attempt) {
            $transcriptwords = diff::fetchWordArray($attempt->transcript);
            $matches = json_decode($attempt->sessionmatches);
            $mistranscriptions[] = self::fetch_attempt_mistranscriptions($passagewords, $transcriptwords, $matches);
        }
        //aggregate results
        for ($wordnumber = 1; $wordnumber <= $passagecount; $wordnumber++) {
            $aggregate_set = array();
            foreach ($mistranscriptions as $mistranscript) {
                if (!$mistranscript[$wordnumber]) {
                    continue;
                }
                if (array_key_exists($mistranscript[$wordnumber], $aggregate_set)) {
                    $aggregate_set[$mistranscript[$wordnumber]]++;
                } else {
                    $aggregate_set[$mistranscript[$wordnumber]] = 1;
                }
            }
            $result = new \stdClass();
            $result->mistranscriptions = $aggregate_set;
            $result->passageindex = $wordnumber;
            $result->passageword = $passagewords[$wordnumber - 1];
            $results[] = $result;
        }//end of for loop
        return $results;
    }

    /*
   * This will return an array of mistranscript strings for a single attemot. 1 entry per passageword.
     * To be consistent with how data is stored in matches/errors, we return a 1 based array of mistranscriptions
     * @return array a 1 based array of mistranscriptions(string) or false. i item for each passage word
    */
    public static function fetch_attempt_mistranscriptions($passagewords, $transcriptwords, $matches) {
        $passagecount = count($passagewords);
        if (!$passagecount) {
            return false;
        }
        $mistranscriptions = array();
        for ($wordnumber = 1; $wordnumber <= $passagecount; $wordnumber++) {
            $mistranscription = self::fetch_one_mistranscription($wordnumber, $transcriptwords, $matches);
            if ($mistranscription) {
                $mistranscriptions[$wordnumber] = $mistranscription;
            } else {
                $mistranscriptions[$wordnumber] = false;
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
    public static function fetch_one_mistranscription($passageindex, $transcriptwords, $matches) {

        //if we have a problem with matches (bad data?) just return
        if (!$matches) {
            return false;
        }

        //count transcript words
        $transcriptlength = count($transcriptwords);
        if ($transcriptlength == 0) {
            return false;
        }

        //build a quick to search array of matched words
        $passagematches = array();
        foreach ($matches as $match) {
            $passagematches[$match->pposition] = $match->word;
        }

        //find startindex
        $startindex = -1;
        for ($wordnumber = $passageindex; $wordnumber > 0; $wordnumber--) {

            $ismatched = array_key_exists($wordnumber, $passagematches);
            if ($ismatched) {
                $startindex = $matches->{$wordnumber}->tposition + 1;
                break;
            }
        }//end of for loop

        //find endindex
        $endindex = -1;
        for ($wordnumber = $passageindex; $wordnumber <= $transcriptlength; $wordnumber++) {

            $ismatched = array_key_exists($wordnumber, $passagematches);
            //if we matched then the previous transcript word is the last unmatched one in the checkindex sequence
            if ($ismatched) {
                $endindex = $matches->{$wordnumber}->tposition - 1;
                break;
            }
        }//end of for loop --

        //if there was no previous matched word, we set start to 1
        if ($startindex == -1) {
            $startindex = 1;
        }
        //if there was no subsequent matched word we flag the end as the -1
        if ($endindex == $transcriptlength) {
            $endindex = -1;
            //an edge case is where the first word is not in transcript and first match is the second or later passage
            //word. It might not be possible for endindex to be lower than start index, but we don't want it anyway
        } else if ($endindex == 0 || $endindex < $startindex) {
            return false;
        }

        //up until this point the indexes have started from 1, since the passage word numbers start from 1
        //but the transcript array is 0 based so we adjust. array_slice function does not include item and endindex
        ///so it needs to be one more then start index. hence we do not adjust that
        $startindex--;

        //finally we return the section of transcript
        if ($endindex > 0) {
            $chunklength = $endindex - $startindex;
            $retarray = array_slice($transcriptwords, $startindex, $chunklength);
        } else {
            $retarray = array_slice($transcriptwords, $startindex);
        }

        $ret = implode(" ", $retarray);
        if (trim($ret) == '') {
            return false;
        } else {
            return $ret;
        }
    }

    /**
     * Returns the link for the related activity
     *
     * @return string
     */
    public static function fetch_next_activity($activitylink) {
        global $DB;
        $ret = new \stdClass();
        $ret->url = false;
        $ret->label = false;
        if (!$activitylink) {
            return $ret;
        }

        $module = $DB->get_record('course_modules', array('id' => $activitylink));
        if ($module) {
            $modname = $DB->get_field('modules', 'name', array('id' => $module->module));
            if ($modname) {
                $instancename = $DB->get_field($modname, 'name', array('id' => $module->instance));
                if ($instancename) {
                    $ret->url = new \moodle_url('/mod/' . $modname . '/view.php', array('id' => $activitylink));
                    $ret->label = get_string('activitylinkname', constants::M_COMPONENT, $instancename);
                }
            }
        }
        return $ret;
    }

    public static function fetch_attempt_chartdata($moduleinstance,$userid=0){
        global $DB, $USER;

        //use current user if not passed in
        if($userid==0){$userid = $USER->id;}
        //init return value
        $chartdata = false;

        $sql =
                "SELECT tu.*,tai.accuracy as aiaccuracy,tai.wpm as aiwpm, tai.sessionscore as aisessionscore,tai.fulltranscript as fulltranscript FROM {" .
                constants::M_USERTABLE . "} tu INNER JOIN {user} u ON tu.userid=u.id " .
                "INNER JOIN {" . constants::M_AITABLE . "} tai ON tai.attemptid=tu.id " .
                "WHERE tu.readaloudid=? AND u.id=?" .
                " ORDER BY tu.id ASC";

        $alldata = $DB->get_records_sql($sql, array($moduleinstance->id, $userid));

        //if we have data, yay
        if ($alldata) {

            //init our data set
            $chartdata = new \stdClass();
            $wpmdata=[];
            $accuracydata=[];
            $sessionscoredata=[];
            $labelsdata=[];
            $attemptno=0;

            //loop through each attempt
            foreach ($alldata as $thedata) {


                //sessiontime is our indicator that a human grade has been saved.
                //use aidata if no human grade or machinegrades only
                if (!$thedata->sessiontime || $moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY) {
                    $wpmdata[]= $thedata->aiwpm;
                    $accuracydata[] = $thedata->aiaccuracy;
                    $sessionscoredata[] = $thedata->aisessionscore;
                }else{
                    $wpmdata[]= $thedata->wpm;
                    $accuracydata[] = $thedata->accuracy;
                    $sessionscoredata[] = $thedata->sessionscore;

                }
                $attemptno++;
                $labelsdata[] =get_string('attemptno', constants::M_COMPONENT, $attemptno);
            }
            $chartdata->accuracyseries = new \core\chart_series(get_string('accuracy_p', constants::M_COMPONENT),$accuracydata);
            $chartdata->wpmseries = new \core\chart_series(get_string('wpm', constants::M_COMPONENT),$wpmdata);
            $chartdata->sessionscoreseries = new \core\chart_series(get_string('grade_p', constants::M_COMPONENT),$sessionscoredata);
            $chartdata->labelsdata=$labelsdata;

        }
        return $chartdata;
    }

    public static function fetch_attempt_summary($moduleinstance,$userid=0){
        global $DB, $USER;

        //use current user if not passed in
        if($userid==0){$userid = $USER->id;}
        //init return value
        $attemptsummary = false;

        $sql =
                "SELECT tu.*,tai.accuracy as aiaccuracy,tai.wpm as aiwpm, tai.sessionscore as aisessionscore,tai.fulltranscript as fulltranscript FROM {" .
                constants::M_USERTABLE . "} tu INNER JOIN {user} u ON tu.userid=u.id " .
                "INNER JOIN {" . constants::M_AITABLE . "} tai ON tai.attemptid=tu.id " .
                "WHERE tu.readaloudid=? AND u.id=? AND tu.dontgrade = 0 " .
                " ORDER BY u.lastnamephonetic,u.firstnamephonetic,u.lastname,u.firstname,u.middlename,u.alternatename,tu.id DESC";

        $alldata = $DB->get_records_sql($sql, array($moduleinstance->id, $userid));

        //if we have data, yay
        if ($alldata) {

            //initialise our return object
            $attemptsummary = new \stdClass();
            $attemptsummary->totalattempts = count($alldata);
            $attemptsummary->total_wpm = 0;
            $attemptsummary->h_wpm = 0;
            $attemptsummary->total_accuracy = 0;
            $attemptsummary->h_accuracy = 0;
            $attemptsummary->total_sessionscore = 0;
            $attemptsummary->h_sessionscore = 0;


           //loop through each attempt
            foreach ($alldata as $thedata) {

                //sessiontime is our indicator that a human grade has been saved.
                //use aidata if no human grade or machinegrades only
                if (!$thedata->sessiontime || $moduleinstance->machgrademethod == constants::MACHINEGRADE_MACHINEONLY) {
                    $thedata->wpm = $thedata->aiwpm;
                    $thedata->accuracy = $thedata->aiaccuracy;
                    $thedata->sessionscore = $thedata->aisessionscore;
                }
                //calc totals and highest
                $attemptsummary->total_wpm += $thedata->wpm;
                $attemptsummary->h_wpm = max($attemptsummary->h_wpm, $thedata->wpm);
                $attemptsummary->total_accuracy += $thedata->accuracy;
                $attemptsummary->h_accuracy = max($attemptsummary->h_accuracy, $thedata->accuracy);
                $attemptsummary->total_sessionscore += $thedata->sessionscore;
                $attemptsummary->h_sessionscore = max($attemptsummary->h_sessionscore, $thedata->sessionscore);

            }
            //finally calc averages
            $attemptsummary->av_wpm = round($attemptsummary->total_wpm / $attemptsummary->totalattempts,1);
            $attemptsummary->av_accuracy = round($attemptsummary->total_accuracy / $attemptsummary->totalattempts,1);
            $attemptsummary->av_sessionscore = round($attemptsummary->total_sessionscore / $attemptsummary->totalattempts,1);

        }
        return $attemptsummary;
    }

    //save the data to Moodle.
    public static function create_attempt($filename, $rectime, $readaloud,$gradeable) {
        global $USER, $DB;

        //correct filename which has probably been massaged to get through mod_security
        $filename = str_replace('https___', 'https://', $filename);

        //Add a blank attempt with just the filename  and essential details
        $newattempt = new \stdClass();
        $newattempt->courseid = $readaloud->course;
        $newattempt->readaloudid = $readaloud->id;
        $newattempt->userid = $USER->id;
        $newattempt->status = 0;
        $newattempt->filename = $filename;
        $newattempt->sessionscore = 0;
        //$newattempt->sessiontime=$rectime;  //.. this would work. But sessiontime is used as flag of human has graded ...so needs more thought
        $newattempt->sessionerrors = '';
        $newattempt->errorcount = 0;
        $newattempt->wpm = 0;
        $newattempt->dontgrade = $gradeable ? 0 : 1 ;
        $newattempt->timecreated = time();
        $newattempt->timemodified = time();
        $attemptid = $DB->insert_record(constants::M_USERTABLE, $newattempt);
        if (!$attemptid) {
            return false;
        }
        $newattempt->id = $attemptid;

        //if we are machine grading we need an entry to AI table too
        //But ... there is the chance a user will CHANGE the machgrademethod value after submissions have begun,
        //If they do, INNER JOIN SQL in grade related logic will mess up gradebook if aigrade record is not available.
        //So for prudence sake we ALWAYS create an aigrade record
        if (true ||
                $readaloud->machgrademethod == constants::MACHINEGRADE_HYBRID ||
                $readaloud->machgrademethod == constants::MACHINEGRADE_MACHINEONLY) {
            aigrade::create_record($newattempt, $readaloud->timelimit);
        }

        //return the attempt id
        return $attemptid;
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

    //Make a good effort to retain breaks even if Model Audio has changed
    public static function sync_modelaudio_breaks($breaks,$matches) {
        if(count($breaks)>1) {
            for ($i = 0; $i < count($breaks); $i++) {
                $wordnumber = $breaks[$i]['wordnumber'];
                if(isset($matches->{$wordnumber})) {
                    $breaks[$i]['audiotime'] = $matches->{$wordnumber}->audiostart;//or audio end? ...
                }else{
                   //what to do here?
                }
            }//end of for
        }//end of if count > 0
        return $breaks;
    }//end of function

    //Make a good effort to mark up the passage from scratch
    //the break occurs after the current word.  matches array  is 0 based and words array is 0 based
    //So if break 1: word tapped is wordnumber 2, break->3 we want the audiostart position of next as audiotime. That is matches[3].audiostart
    public static function guess_modelaudio_breaks($passage,$matches,$language) {
        $breaks=[];
        $words = self::fetch_passage_as_words($passage,$language);
        $lastbreak=0;
        if(count($words)>1) {
            for ($i = 0; $i < count($words); $i++) {

                //if this word does not have a match, just continue
                if (!isset($matches->{$i + 1})) {
                    continue;
                }
                //look for some sort of phrase ender and register a break if found.
                $letsbreak=false;
                $lastcharofword = \core_text::substr($words[$i], -1);
                switch ($lastcharofword ) {
                    case '!':
                    case '?':
                    case '.':
                    case '？':
                    case '。':
                    case '！':
                    case '：':
                    case ':':
                        //definite break
                        $letsbreak =true;
                        break;
                    case ',':
                    case ';':
                    case '、':
                    case '；':
                        if(($matches->{$i + 1}->audioend - $lastbreak)>2){
                            $letsbreak =true;
                        }
                        break;
                    default:
                }//end of switch
                //we add the new break
                if($letsbreak){
                    $newbreak = ['wordnumber' => $i + 1, 'audiotime' => $matches->{$i + 1}->audioend];
                    $breaks[] = $newbreak;
                    $lastbreak = $matches->{$i + 1}->audioend;
                }
            }//end of for
        }//end of if count > 0

        return $breaks;
    }//end of function

    //This is a semi duplicate of passage_renderer::render_passage
    // but its for the purpose of marking up a passage automatically so we need an array of words not with any html markup on it.
    public static function fetch_passage_as_words($passage,$language){

            // load the HTML document
            $doc = new \DOMDocument;
            // it will assume ISO-8859-1  encoding, so we need to hint it:
            //see: http://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
            @$doc->loadHTML(mb_convert_encoding($passage, 'HTML-ENTITIES', 'UTF-8'));


            // select all the text nodes
            $xpath = new \DOMXPath($doc);
            $nodes = $xpath->query('//text()');
            //init the text count
            $wordcount = 0;
            $allwords=[];
            foreach ($nodes as $node) {

                //if its empty space, move on
                $trimmednode = trim($node->nodeValue);
                if (empty($trimmednode)) {
                    continue;
                }

                //deal with new lines by making them period char out in space
                $nodevalue = str_replace("\r\n", '. ' , $node->nodeValue );

                //split each node(line) on words. preg_split messed up with double byte characters
                //$words = preg_split('/\s+/', $nodevalue);
                //so we use mb_split
                $words = mb_split('\s+', $nodevalue);

                foreach ($words as $word) {

                    //if its a non word character eg : in 'chapter one : beginning'
                    //we append it to prior word
                    if(\mod_readaloud\diff::cleanText($word)==='') {
                        if($wordcount > 0){$allwords[$wordcount-1].=$word;}
                        continue;
                    }
                    $allwords[]=$word;
                    $wordcount++;

                }
                $node->nodeValue = "";
            }

            return $allwords;
    }

    //register an adhoc task to pick up model audio transcripts
    public static function register_modelaudio_task($activityid, $filename, $modulecontextid) {
        $modelaudio_task = new \mod_readaloud\task\readaloud_modelaudio_adhoc();
        $modelaudio_task->set_component('mod_readaloud');

        $customdata = new \stdClass();
        $customdata->activityid = $activityid;
        $customdata->filename = $filename;
        $customdata->modulecontextid = $modulecontextid;
        $customdata->taskcreationtime = time();

        $modelaudio_task->set_custom_data($customdata);
        // queue it
        \core\task\manager::queue_adhoc_task($modelaudio_task);
        return true;
    }

    //register an adhoc task to pick up transcripts
    public static function register_aws_task($activityid, $attemptid, $modulecontextid) {
        $s3_task = new \mod_readaloud\task\readaloud_s3_adhoc();
        $s3_task->set_component('mod_readaloud');

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

    //What to show students after an attempt
    public static function get_postattempt_options() {
        return array(
                constants::POSTATTEMPT_NONE => get_string("postattempt_none", constants::M_COMPONENT),
                constants::POSTATTEMPT_EVAL => get_string("postattempt_eval", constants::M_COMPONENT),
                constants::POSTATTEMPT_EVALERRORS => get_string("postattempt_evalerrors", constants::M_COMPONENT),
                constants::POSTATTEMPT_EVALERRORSNOGRADE => get_string("postattempt_evalerrorsnograde", constants::M_COMPONENT)
        );
    }

    //What multi-attempt grading approach
    public static function get_grade_options() {
        return array(
                constants::M_GRADELATEST => get_string("gradelatest", constants::M_COMPONENT),
                constants::M_GRADEHIGHEST => get_string("gradehighest", constants::M_COMPONENT)
        );
    }

    //for error estimate and accuracy adjustment, we can auto estimate errors, never estimate errors, or use a fixed error estimate, or ignore errors
    public static function get_accadjust_options() {
        return array(
                constants::ACCMETHOD_NONE => get_string("accmethod_none", constants::M_COMPONENT),
            //constants::ACCMETHOD_AUTO  => get_string("accmethod_auto",constants::M_COMPONENT),
                constants::ACCMETHOD_FIXED => get_string("accmethod_fixed", constants::M_COMPONENT),
                constants::ACCMETHOD_NOERRORS => get_string("accmethod_noerrors", constants::M_COMPONENT),
        );
    }

    public static function get_region_options() {
        return array(
                "useast1" => get_string("useast1", constants::M_COMPONENT),
                "tokyo" => get_string("tokyo", constants::M_COMPONENT),
                "sydney" => get_string("sydney", constants::M_COMPONENT),
                "dublin" => get_string("dublin", constants::M_COMPONENT),
                "ottawa" => get_string("ottawa", constants::M_COMPONENT),
                "frankfurt" => get_string("frankfurt", constants::M_COMPONENT),
                "london" => get_string("london", constants::M_COMPONENT),
                "saopaulo" => get_string("saopaulo", constants::M_COMPONENT),
                "singapore" => get_string("singapore",constants::M_COMPONENT),
                "mumbai" => get_string("mumbai",constants::M_COMPONENT),
                "bahrain" => get_string("bahrain", constants::M_COMPONENT),
                "capetown" => get_string("capetown", constants::M_COMPONENT)
        );
    }

    //return a rating from 0 - 5 (inclusive)
    public static function fetch_rating($attempt,$aigrade){
        $have_humaneval = $attempt->sessiontime != null;
        $have_aieval = $aigrade && $aigrade->has_transcripts();
        if(!$have_humaneval && !$have_aieval){
            return -1;
        }elseif($have_humaneval){
            if($attempt->sessionscore==0){return 0;}
            if($attempt->sessionscore==100){return 5;}
            return floor($attempt->sessionscore / 20) + 1;

        }else{
            if($aigrade->aidata->sessionscore==0){return 0;}
            if($aigrade->aidata->sessionscore==100){return 5;}
            return floor($aigrade->aidata->sessionscore / 20) + 1;
        }
    }

    public static function fetch_options_recorders(){
        $rec_options = array( constants::REC_READALOUD => get_string("rec_readaloud", constants::M_COMPONENT),
                constants::REC_ONCE => get_string("rec_once", constants::M_COMPONENT),
                constants::REC_UPLOAD => get_string("rec_upload", constants::M_COMPONENT));
        return $rec_options;
    }

    public static function fetch_options_guidedtranscription(){
        $options = array( constants::GUIDEDTRANS_PASSAGE => get_string("guidedtrans_passage", constants::M_COMPONENT),
            constants::GUIDEDTRANS_CORPUS => get_string("guidedtrans_corpus", constants::M_COMPONENT));
        return $options;
    }
    public static function fetch_options_corpusrange(){
        $options = array( constants::CORPUSRANGE_SITE => get_string("corpusrange_site", constants::M_COMPONENT),
            constants::CORPUSRANGE_COURSE => get_string("corpusrange_course", constants::M_COMPONENT));
        return $options;
    }

    public static function fetch_options_applyrange(){
        $options = array( constants::APPLY_ACTIVITY => get_string("apply_activity", constants::M_COMPONENT),
            constants::APPLY_COURSE => get_string("apply_course", constants::M_COMPONENT),
            constants::APPLY_SITE => get_string("apply_site", constants::M_COMPONENT)
        );
        return $options;
    }

    public static function get_machinegrade_options() {
        return array(
                constants::MACHINEGRADE_NONE => get_string("machinegradenone", constants::M_COMPONENT),
                constants::MACHINEGRADE_HYBRID => get_string("machinegradehybrid", constants::M_COMPONENT),
                constants::MACHINEGRADE_MACHINEONLY => get_string("machinegrademachineonly", constants::M_COMPONENT)
        );
    }

    public static function get_sessionscore_options() {
        return array(
                constants::SESSIONSCORE_NORMAL => get_string("sessionscorenormal", constants::M_COMPONENT),
                constants::SESSIONSCORE_STRICT => get_string("sessionscorestrict", constants::M_COMPONENT)
        );
    }

    public static function get_ttsspeed_options() {
        return array(
                constants::TTSSPEED_MEDIUM => get_string("mediumspeed", constants::M_COMPONENT),
                constants::TTSSPEED_SLOW => get_string("slowspeed", constants::M_COMPONENT),
                constants::TTSSPEED_XSLOW => get_string("extraslowspeed", constants::M_COMPONENT)
        );
    }

    public static function get_timelimit_options() {
        return array(
                0 => get_string("notimelimit", constants::M_COMPONENT),
                15 => get_string("xsecs", constants::M_COMPONENT, '15'),
                30 => get_string("xsecs", constants::M_COMPONENT, '30'),
                45 => get_string("xsecs", constants::M_COMPONENT, '45'),
                60 => get_string("onemin", constants::M_COMPONENT),
                90 => get_string("oneminxsecs", constants::M_COMPONENT, '30'),
                120 => get_string("xmins", constants::M_COMPONENT, '2'),
                150 => get_string("xminsecs", constants::M_COMPONENT, array('minutes' => 2, 'seconds' => 30)),
                180 => get_string("xmins", constants::M_COMPONENT, '3')
        );
    }

    public static function get_expiredays_options() {
        return array(
                "1" => "1",
                "3" => "3",
                "7" => "7",
                "30" => "30",
                "90" => "90",
                "180" => "180",
                "365" => "365",
                "730" => "730",
                "9999" => get_string('forever', constants::M_COMPONENT)
        );
    }

    public static function fetch_options_transcribers() {
        $options =
                array(constants::TRANSCRIBER_STRICT => get_string("transcriber_strict", constants::M_COMPONENT),
                        constants::TRANSCRIBER_GUIDED => get_string("transcriber_guided", constants::M_COMPONENT));
        return $options;
    }
    
    public static function fetch_ttsvoice_options($langcode=''){
        $alllang= array(
                constants::M_LANG_ARAE => ['Zeina'=>'Zeina'],
                //constants::M_LANG_ARSA => [],
                constants::M_LANG_DADK => ["Naja"=>"Naja","Mads"=>"Mads"],
                constants::M_LANG_DEDE => ['Hans'=>'Hans','Marlene'=>'Marlene', 'Vicki'=>'Vicki'],
                //constants::M_LANG_DECH => [],
                constants::M_LANG_ENUS => ['Joey'=>'Joey','Justin'=>'Justin','Kevin'=>'Kevin','Matthew'=>'Matthew','Ivy'=>'Ivy',
                'Joanna'=>'Joanna','Kendra'=>'Kendra','Kimberly'=>'Kimberly','Salli'=>'Salli'],
                constants::M_LANG_ENGB => ['Brian'=>'Brian','Amy'=>'Amy', 'Emma'=>'Emma'],
                constants::M_LANG_ENAU => ['Russell'=>'Russell','Nicole'=>'Nicole','Olivia'=>'Olivia'],
                constants::M_LANG_ENNZ => ['Aria'=>'Aria'],
                constants::M_LANG_ENZA => ['Ayanda'=>'Ayanda'],
                constants::M_LANG_ENIN => ['Aditi'=>'Aditi', 'Raveena'=>'Raveena'],
               // constants::M_LANG_ENIE => [],
                constants::M_LANG_ENWL => ["Geraint"=>"Geraint"],
               // constants::M_LANG_ENAB => [],
                constants::M_LANG_ESUS => ['Miguel'=>'Miguel','Penelope'=>'Penelope','Lupe'=>'Lupe'],
                constants::M_LANG_ESES => [ 'Enrique'=>'Enrique', 'Conchita'=>'Conchita', 'Lucia'=>'Lucia'],
                //constants::M_LANG_FAIR => [],
                constants::M_LANG_FRCA => ['Chantal'=>'Chantal', 'Gabrielle'=>'Gabrielle'],
                constants::M_LANG_FRFR => ['Mathieu'=>'Mathieu','Celine'=>'Celine', 'Lea'=>'Lea'],
                constants::M_LANG_HIIN => ["Aditi"=>"Aditi"],
                //constants::M_LANG_HEIL => [],
                //constants::M_LANG_IDID => [],
                constants::M_LANG_ITIT => ['Carla'=>'Carla',  'Bianca'=>'Bianca', 'Giorgio'=>'Giorgio'],
                constants::M_LANG_JAJP => ['Takumi'=>'Takumi','Mizuki'=>'Mizuki'],
                constants::M_LANG_KOKR => ['Seoyeon'=>'Seoyeon'],
                //constants::M_LANG_MSMY => [],
                constants::M_LANG_NLNL => ["Ruben"=>"Ruben","Lotte"=>"Lotte"],
                constants::M_LANG_PTBR => ['Ricardo'=>'Ricardo', 'Vitoria'=>'Vitoria','Camila'=>'Camila'],
                constants::M_LANG_PTPT => ["Ines"=>"Ines",'Cristiano'=>'Cristiano'],
                constants::M_LANG_RURU => ["Tatyana"=>"Tatyana","Maxim"=>"Maxim"],
                //constants::M_LANG_TAIN => [],
                //constants::M_LANG_TEIN => [],
                constants::M_LANG_TRTR => ['Filiz'=>'Filiz'],
                constants::M_LANG_ZHCN => ['Zhiyu'=>'Zhiyu'],

            constants::M_LANG_NBNO => ['Liv'=>'Liv','nb-NO-Wavenet-B'=>'Lars_g'],
            constants::M_LANG_PLPL => ['Ewa'=>'Ewa','Maja'=>'Maja','Jacek'=>'Jacek','Jan'=>'Jan'],
            constants::M_LANG_RORO => ['Carmen'=>'Carmen','ro-RO-Wavenet-A'=>'Sorina_g'],
            constants::M_LANG_SVSE => ['Astrid'=>'Astrid'],
            constants::M_LANG_UKUA => ['uk-UA-Wavenet-A'=>'Katya_g'],
            constants::M_LANG_FILPH => ['fil-PH-Wavenet-A'=>'Darna_g','fil-PH-Wavenet-B'=>'Reyna_g','fil-PH-Wavenet-C'=>'Bayani_g','fil-PH-Wavenet-D'=>'Ernesto_g'],
            constants::M_LANG_FIFI => ['fi-FI-Wavenet-A'=>'Kaarina_g'],
            constants::M_LANG_HUHU => ['hu-HU-Wavenet-A'=>'Eszter_g']
        );


        $lang_options = self::get_lang_options();
        $ret=[];
        foreach($alllang as $lang=>$voices){
            foreach($voices as $v=>$voicename){
             $neuraltag = in_array($v,constants::M_NEURALVOICES) ? ' (+)' : '';
             $ret[$v]=$voicename . $neuraltag . ' - (' . $lang_options[$lang] . ')';
            }
        }
        $ret = array_merge(array(constants::TTS_NONE => get_string('nottsvoice',constants::M_COMPONENT)), $ret);
        return $ret;
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
                constants::M_LANG_ENPH => get_string('en-ph', constants::M_COMPONENT),
                constants::M_LANG_ENNZ => get_string('en-nz', constants::M_COMPONENT),
                constants::M_LANG_ENZA => get_string('en-za', constants::M_COMPONENT),
                constants::M_LANG_ENIN => get_string('en-in', constants::M_COMPONENT),
                constants::M_LANG_ENIE => get_string('en-ie', constants::M_COMPONENT),
                constants::M_LANG_ENWL => get_string('en-wl', constants::M_COMPONENT),
                constants::M_LANG_ENAB => get_string('en-ab', constants::M_COMPONENT),
                constants::M_LANG_ESUS => get_string('es-us', constants::M_COMPONENT),
                constants::M_LANG_ESES => get_string('es-es', constants::M_COMPONENT),
                constants::M_LANG_FAIR => get_string('fa-ir', constants::M_COMPONENT),
                constants::M_LANG_FILPH => get_string('fil-ph', constants::M_COMPONENT),
                constants::M_LANG_FRCA => get_string('fr-ca', constants::M_COMPONENT),
                constants::M_LANG_FRFR => get_string('fr-fr', constants::M_COMPONENT),
                constants::M_LANG_HIIN => get_string('hi-in', constants::M_COMPONENT),
                constants::M_LANG_HEIL => get_string('he-il', constants::M_COMPONENT),
                constants::M_LANG_IDID => get_string('id-id', constants::M_COMPONENT),
                constants::M_LANG_ITIT => get_string('it-it', constants::M_COMPONENT),
                constants::M_LANG_JAJP => get_string('ja-jp', constants::M_COMPONENT),
                constants::M_LANG_KOKR => get_string('ko-kr', constants::M_COMPONENT),
                constants::M_LANG_MSMY => get_string('ms-my', constants::M_COMPONENT),
                constants::M_LANG_NLNL => get_string('nl-nl', constants::M_COMPONENT),
                constants::M_LANG_PTBR => get_string('pt-br', constants::M_COMPONENT),
                constants::M_LANG_PTPT => get_string('pt-pt', constants::M_COMPONENT),
                constants::M_LANG_RURU => get_string('ru-ru', constants::M_COMPONENT),
                constants::M_LANG_TAIN => get_string('ta-in', constants::M_COMPONENT),
                constants::M_LANG_TEIN => get_string('te-in', constants::M_COMPONENT),
                constants::M_LANG_TRTR => get_string('tr-tr', constants::M_COMPONENT),
                constants::M_LANG_ZHCN => get_string('zh-cn', constants::M_COMPONENT),

                constants::M_LANG_NBNO => get_string('nb-no', constants::M_COMPONENT),
                constants::M_LANG_PLPL => get_string('pl-pl', constants::M_COMPONENT),
                constants::M_LANG_RORO => get_string('ro-ro', constants::M_COMPONENT),
                constants::M_LANG_SVSE => get_string('sv-se', constants::M_COMPONENT),
                constants::M_LANG_UKUA => get_string('uk-ua',constants::M_COMPONENT),
                constants::M_LANG_EUES => get_string('eu-es',constants::M_COMPONENT),
                constants::M_LANG_FIFI => get_string('fi-fi',constants::M_COMPONENT),
                constants::M_LANG_HUHU => get_string('hu-hu',constants::M_COMPONENT)
        );
        /*
          return array(
                "none"=>"No TTS",
                "af"=>"Afrikaans",
                "sq"=>"Albanian",
                "am"=>"Amharic",
                "ar"=>"Arabic",
                "hy"=>"Armenian",
                "az"=>"Azerbaijani",
                "eu"=>"Basque",
                "be"=>"Belarusian",
                "bn"=>"Bengali",
                "bh"=>"Bihari",
                "bs"=>"Bosnian",
                "br"=>"Breton",
                "bg"=>"Bulgarian",
                "km"=>"Cambodian",
                "ca"=>"Catalan",
                "zh-CN"=>"Chinese (Simplified)",
                "zh-TW"=>"Chinese (Traditional)",
                "co"=>"Corsican",
                "hr"=>"Croatian",
                "cs"=>"Czech",
                "da"=>"Danish",
                "nl"=>"Dutch",
                "en"=>"English",
                "eo"=>"Esperanto",
                "et"=>"Estonian",
                "fo"=>"Faroese",
                "tl"=>"Filipino",
                "fi"=>"Finnish",
                "fr"=>"French",
                "fy"=>"Frisian",
                "gl"=>"Galician",
                "ka"=>"Georgian",
                "de"=>"German",
                "el"=>"Greek",
                "gn"=>"Guarani",
                "gu"=>"Gujarati",
                "xx-hacker"=>"Hacker",
                "ha"=>"Hausa",
                "iw"=>"Hebrew",
                "hi"=>"Hindi",
                "hu"=>"Hungarian",
                "is"=>"Icelandic",
                "id"=>"Indonesian",
                "ia"=>"Interlingua",
                "ga"=>"Irish",
                "it"=>"Italian",
                "ja"=>"Japanese",
                "jw"=>"Javanese",
                "kn"=>"Kannada",
                "kk"=>"Kazakh",
                "rw"=>"Kinyarwanda",
                "rn"=>"Kirundi",
                "xx-klingon"=>"Klingon",
                "ko"=>"Korean",
                "ku"=>"Kurdish",
                "ky"=>"Kyrgyz",
                "lo"=>"Laothian",
                "la"=>"Latin",
                "lv"=>"Latvian",
                "ln"=>"Lingala",
                "lt"=>"Lithuanian",
                "mk"=>"Macedonian",
                "mg"=>"Malagasy",
                "ms"=>"Malay",
                "ml"=>"Malayalam",
                "mt"=>"Maltese",
                "mi"=>"Maori",
                "mr"=>"Marathi",
                "mo"=>"Moldavian",
                "mn"=>"Mongolian",
                "sr-ME"=>"Montenegrin",
                "ne"=>"Nepali",
                "no"=>"Norwegian",
                "nn"=>"Norwegian(Nynorsk)",
                "oc"=>"Occitan",
                "or"=>"Oriya",
                "om"=>"Oromo",
                "ps"=>"Pashto",
                "fa"=>"Persian",
                "xx-pirate"=>"Pirate",
                "pl"=>"Polish",
                "pt-BR"=>"Portuguese(Brazil)",
                "pt-PT"=>"Portuguese(Portugal)",
                "pa"=>"Punjabi",
                "qu"=>"Quechua",
                "ro"=>"Romanian",
                "rm"=>"Romansh",
                "ru"=>"Russian",
                "gd"=>"Scots Gaelic",
                "sr"=>"Serbian",
                "sh"=>"Serbo-Croatian",
                "st"=>"Sesotho",
                "sn"=>"Shona",
                "sd"=>"Sindhi",
                "si"=>"Sinhalese",
                "sk"=>"Slovak",
                "sl"=>"Slovenian",
                "so"=>"Somali",
                "es"=>"Spanish",
                "su"=>"Sundanese",
                "sw"=>"Swahili",
                "sv"=>"Swedish",
                "tg"=>"Tajik",
                "ta"=>"Tamil",
                "tt"=>"Tatar",
                "te"=>"Telugu",
                "th"=>"Thai",
                "ti"=>"Tigrinya",
                "to"=>"Tonga",
                "tr"=>"Turkish",
                "tk"=>"Turkmen",
                "tw"=>"Twi",
                "ug"=>"Uighur",
                "uk"=>"Ukrainian",
                "ur"=>"Urdu",
                "uz"=>"Uzbek",
                "vi"=>"Vietnamese",
                "cy"=>"Welsh",
                "xh"=>"Xhosa",
                "yi"=>"Yiddish",
                "yo"=>"Yoruba",
                "zu"=>"Zulu"
            );
        */
    }

    public static function add_mform_elements($mform, $context,$cmid,$setuptab=false) {
        global $CFG, $COURSE;
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
        $mform->addElement('text', 'name', get_string('readaloudname', constants::M_COMPONENT), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'readaloudname', constants::M_COMPONENT);

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

        //time target
        $timelimit_options = \mod_readaloud\utils::get_timelimit_options();
        $mform->addElement('select', 'timelimit', get_string('timelimit', constants::M_COMPONENT),
                $timelimit_options);
        //$mform->addElement('duration', 'timelimit', get_string('timelimit',constants::M_COMPONENT)));
        $mform->setDefault('timelimit', 60);
        $mform->addHelpButton('timelimit', 'timelimit', constants::M_COMPONENT);

        //add other editors
        //could add files but need the context/mod info. So for now just rich text
        $config = get_config(constants::M_COMPONENT);

        //The passage
        //we stopped allowing rich text. It does not show anyway.
        /*
        $ednofileoptions = readaloud_editor_no_files_options($context);
        $opts = array('rows' => '15', 'columns' => '80');
        $mform->addElement('editor', 'passage_editor', get_string('passagelabel', constants::M_COMPONENT), $opts, $ednofileoptions);
        $mform->addHelpButton('passage_editor', 'passage_editor', constants::M_COMPONENT);
        */
        //The alternatives declaration
        $mform->addElement('textarea', 'passage', get_string("passagelabel", constants::M_COMPONENT),
            'wrap="virtual" rows="15" cols="100"');
        $mform->setDefault('passage', '');
        $mform->setType('passage', PARAM_RAW);
        $mform->addElement('static', 'passagedescr', '',
            get_string('passage_descr', constants::M_COMPONENT));
        $mform->addHelpButton('passage', 'passage', constants::M_COMPONENT);

        //tts options
        $langoptions = \mod_readaloud\utils::get_lang_options();
        $mform->addElement('select', 'ttslanguage', get_string('ttslanguage', constants::M_COMPONENT), $langoptions);
        $mform->setDefault('ttslanguage', $config->ttslanguage);
        $mform->addHelpButton('ttslanguage', 'ttslanguage', constants::M_COMPONENT);

        //tts voice
        $langoptions = \mod_readaloud\utils::fetch_ttsvoice_options();
        $mform->addElement('select', 'ttsvoice', get_string('ttsvoice', constants::M_COMPONENT), $langoptions);
        $mform->setDefault('ttsvoice', $config->ttsvoice);
        $mform->addHelpButton('ttsvoice', 'ttsvoice', constants::M_COMPONENT);

        $speedoptions = \mod_readaloud\utils::get_ttsspeed_options();
        $mform->addElement('select', 'ttsspeed', get_string('ttsspeed', constants::M_COMPONENT), $speedoptions);
        $mform->setDefault('ttsspeed', constants::TTSSPEED_SLOW);
        $mform->addHelpButton('ttsspeed', 'ttsspeed', constants::M_COMPONENT);

        //The alternatives declaration
        $mform->addElement('textarea', 'alternatives', get_string("alternatives", constants::M_COMPONENT),
                'wrap="virtual" rows="6" cols="50"');
        $mform->setDefault('alternatives', '');
        $mform->setType('alternatives', PARAM_RAW);
        $mform->addElement('static', 'alternativesdescr', '',
                get_string('alternatives_descr', constants::M_COMPONENT));
        $mform->addHelpButton('alternatives', 'alternatives', constants::M_COMPONENT);

        //welcome and feedback
        $ednofileoptions = readaloud_editor_no_files_options($context);
        $opts = array('rows' => '6', 'columns' => '80');
        $mform->addElement('editor', 'welcome_editor', get_string('welcomelabel', constants::M_COMPONENT), $opts, $ednofileoptions);
        $mform->addElement('editor', 'feedback_editor', get_string('feedbacklabel', constants::M_COMPONENT), $opts,
                $ednofileoptions);

        //defaults
        $mform->setDefault('passage_editor', array('text' => '', 'format' => FORMAT_PLAIN));
        $mform->setDefault('welcome_editor', array('text' => $config->defaultwelcome, 'format' => FORMAT_MOODLE));
        $mform->setDefault('feedback_editor', array('text' => $config->defaultfeedback, 'format' => FORMAT_MOODLE));

        //types
        $mform->setType('passage_editor', PARAM_RAW);
        $mform->setType('welcome_editor', PARAM_RAW);
        $mform->setType('feedback_editor', PARAM_RAW);

        // Adding targetwpm field
        $mform->addElement('text', 'targetwpm', get_string('targetwpm', constants::M_COMPONENT), array('size' => '8'));
        $mform->setType('targetwpm', PARAM_INT);
        $mform->setDefault('targetwpm', $config->targetwpm);
        $mform->addHelpButton('targetwpm', 'targetwpm', constants::M_COMPONENT);

        //allow early exit
        $mform->addElement('advcheckbox', 'allowearlyexit', get_string('allowearlyexit', constants::M_COMPONENT),
                get_string('allowearlyexit_details', constants::M_COMPONENT));
        $mform->setDefault('allowearlyexit', $config->allowearlyexit);

        $mform->addElement('advcheckbox', 'enablepreview', get_string('enablepreview', constants::M_COMPONENT),
                get_string('enablepreview_details', constants::M_COMPONENT));
        $mform->setDefault('enablepreview', $config->enablepreview);


        $mform->addElement('advcheckbox', 'enablelandr', get_string('enablelandr', constants::M_COMPONENT),
                get_string('enablelandr_details', constants::M_COMPONENT));
        $mform->setDefault('enablelandr', $config->enablelandr);


        $mform->addElement('advcheckbox', 'enableshadow', get_string('enableshadow', constants::M_COMPONENT),
                get_string('enableshadow_details', constants::M_COMPONENT));
        $mform->setDefault('enableshadow', $config->enableshadow);


        //Attempts
        $attemptoptions = array(0 => get_string('unlimited', constants::M_COMPONENT),
                1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5',);
        $mform->addElement('select', 'maxattempts', get_string('maxattempts', constants::M_COMPONENT), $attemptoptions);


        // Appearance.
        $mform->addElement('header', 'advancedheader', get_string('advancedheader', constants::M_COMPONENT));

        // Adding the customfont field
        $mform->addElement('text', 'customfont', get_string('customfont', constants::M_COMPONENT), array('size'=>'64'));
        $mform->addHelpButton('customfont', 'customfont', constants::M_COMPONENT);
        $mform->setType('customfont', PARAM_TEXT);


        //sessionscore options
        $sessionscoreoptions = \mod_readaloud\utils::get_sessionscore_options();
        $mform->addElement('select', 'sessionscoremethod', get_string('sessionscoremethod', constants::M_COMPONENT),
                $sessionscoreoptions);
        $mform->setDefault('sessionscoremethod', $config->sessionscoremethod);
        $mform->addHelpButton('sessionscoremethod', 'sessionscoremethod', constants::M_COMPONENT);


        //human vs machine grade options
        $machinegradeoptions = \mod_readaloud\utils::get_machinegrade_options();
        $mform->addElement('select', 'machgrademethod', get_string('machinegrademethod', constants::M_COMPONENT),
                $machinegradeoptions);
        $mform->setDefault('machgrademethod', $config->machinegrademethod);
        $mform->addHelpButton('machgrademethod', 'machinegrademethod', constants::M_COMPONENT);

        //master instance or not
        if(!has_capability('mod/readaloud:pushtoclones', $context)){
            $mform->addElement('hidden','masterinstance');
            $mform->setType('masterinstance', PARAM_INT);
        }else {
            $mform->addElement('advcheckbox', 'masterinstance', get_string('masterinstance', constants::M_COMPONENT),
                    get_string('masterinstance_details', constants::M_COMPONENT));
        }
        $mform->setDefault('masterinstance', 0);

        // Appearance.
        $mform->addElement('header', 'recordingaiheader', get_string('recordingaiheader', constants::M_COMPONENT));

        //recorder choice
        $recorder_options =  \mod_readaloud\utils::fetch_options_recorders();
        $mform->addElement('select', 'recorder', get_string("recorder", constants::M_COMPONENT), $recorder_options);
        $mform->setDefault('recorder', $config->defaultrecorder);
        $mform->addHelpButton('recorder', 'recorder', constants::M_COMPONENT);


        //Enable AI
        $mform->addElement('advcheckbox', 'enableai', get_string('enableai', constants::M_COMPONENT),
                get_string('enableai_details', constants::M_COMPONENT));
        $mform->setDefault('enableai', $config->enableai);

        //line transcriber options
        $name = 'transcriber';
        $label = get_string($name, constants::M_COMPONENT);
        $options = \mod_readaloud\utils::fetch_options_transcribers();
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, $config->{$name});
        $mform->addElement('static', 'transcriber_details', '',
                get_string('transcriber_details', constants::M_COMPONENT));

        //passage transcriber options
        $name = 'stricttranscribe';
        $label = get_string($name, constants::M_COMPONENT);
        $options = array(0=>get_string('transcriber_guided',constants::M_COMPONENT),
            1=>get_string('transcriber_strict',constants::M_COMPONENT));
        $mform->addElement('select', $name, $label, $options);
        $mform->setDefault($name, $config->{$name});
        $mform->addElement('static', 'stricttranscribe_details', '',
            get_string('stricttranscribe_details', constants::M_COMPONENT));


        //region
        $regionoptions = \mod_readaloud\utils::get_region_options();
        $mform->addElement('select', 'region', get_string('region', constants::M_COMPONENT), $regionoptions);
        $mform->setDefault('region', $config->awsregion);

        //expiredays
        $expiredaysoptions = \mod_readaloud\utils::get_expiredays_options();
        $mform->addElement('select', 'expiredays', get_string('expiredays', constants::M_COMPONENT), $expiredaysoptions);
        $mform->setDefault('expiredays', $config->expiredays);

        $mform->addElement('static', 'accadjustdetails', get_string('accadjustmethod', constants::M_COMPONENT),
                get_string('accadjustmethod_details', constants::M_COMPONENT));

        // Error estimate method field
        $autoacc_options = \mod_readaloud\utils::get_accadjust_options();
        $mform->addElement('select', 'accadjustmethod', get_string('accadjustmethod', constants::M_COMPONENT),
                $autoacc_options);
        $mform->setType('accadjustmethod', PARAM_INT);
        $mform->setDefault('accadjustmethod', $config->accadjustmethod);
        $mform->addHelpButton('accadjustmethod', 'accadjustmethod', constants::M_COMPONENT);

        // Fixed Error estimate field
        $mform->addElement('text', 'accadjust', get_string('accadjust', constants::M_COMPONENT), array('size' => '8'));
        $mform->setType('accadjust', PARAM_INT);
        $mform->setDefault('accadjust', $config->accadjust);
        $mform->disabledIf('accadjust', 'accadjustmethod', 'neq', constants::ACCMETHOD_FIXED);
        $mform->addHelpButton('accadjust', 'accadjust', constants::M_COMPONENT);

        //Submit Raw Audio //no longer used
        $mform->addElement('hidden','submitrawaudio');
        $mform->setType('submitrawaudio', PARAM_INT);
        $mform->setDefault('submitrawaudio', 0);


        //Corpus Fields
        $mform->addElement('hidden','corpusrange');
        $mform->setType('corpusrange', PARAM_INT);
        $mform->setDefault('corpusrange', constants::CORPUSRANGE_SITE);

        $mform->addElement('hidden','corpushash');
        $mform->setType('corpushash', PARAM_RAW);
        $mform->setDefault('corpushash', null);

        $mform->addElement('hidden','usecorpus');
        $mform->setType('usecorpus', PARAM_INT);
        $mform->setDefault('usecorpus', constants::GUIDEDTRANS_PASSAGE);

        // Add passagekey
        $mform->addElement('text', 'passagekey', get_string('passagekey', constants::M_COMPONENT), array('size' => '8'));
        $mform->setType('passagekey', PARAM_TEXT);
        $mform->setDefault('passagekey', '');
        $mform->addHelpButton('passagekey', 'passagekey', constants::M_COMPONENT);

        $name = 'activityopenscloses';
        $label = get_string($name, 'readaloud');
        $mform->addElement('header', $name, $label);
        $mform->setExpanded($name, false);

        $name = 'viewstart';
        $label = get_string($name, "readaloud");
        $mform->addElement('date_time_selector', $name, $label, array('optional' => true));

        $mform->addHelpButton($name, $name,constants::M_COMPONENT);


        $name = 'viewend';
        $label = get_string($name, "readaloud");
        $mform->addElement('date_time_selector', $name, $label,  array('optional' => true));
        $mform->addHelpButton($name, $name,constants::M_COMPONENT);


        // Post attempt
        $mform->addElement('header', 'postattemptheader', get_string('postattemptheader', constants::M_COMPONENT));

        // Get the modules.
        if(!$setuptab) {
            if ($mods = get_course_mods($COURSE->id)) {
                $modinstances = array();
                foreach ($mods as $mod) {
                    // Get the module name and then store it in a new array.
                    if ($module = get_coursemodule_from_instance($mod->modname, $mod->instance, $COURSE->id)) {
                        // Exclude this ReadAloud activity (if it's already been saved.)
                        if ($cmid && $cmid != $mod->id) {
                            $modinstances[$mod->id] = $mod->modname . ' - ' . $module->name;
                        }
                    }
                }
                asort($modinstances); // Sort by module name.
                $modinstances = array(0 => get_string('none')) + $modinstances;

                $mform->addElement('select', 'activitylink', get_string('activitylink', 'lesson'), $modinstances);
                $mform->addHelpButton('activitylink', 'activitylink', 'lesson');
                $mform->setDefault('activitylink', 0);
            }
        }

        // Post attempt evaluation display (human)
        $postattempt_options = \mod_readaloud\utils::get_postattempt_options();
        $mform->addElement('select', 'humanpostattempt', get_string('evaluationview', constants::M_COMPONENT),
                $postattempt_options);
        $mform->setType('humanpostattempt', PARAM_INT);
        $mform->setDefault('humanpostattempt', $config->humanpostattempt);



    } //end of add_mform_elements

    //fetch_current_corpushash / push_corpus
    public static function fetch_current_corpus($moduleinstance,$corpusrange){
        global $DB;

        $conditions = array('ttslanguage'=>$moduleinstance->ttslanguage,'region'=>$moduleinstance->region);
        if($corpusrange==constants::CORPUSRANGE_COURSE){
            $conditions['course'] = $moduleinstance->course;
        }
        $ra_set = $DB->get_records(constants::M_TABLE,$conditions);
        $allpassages = '';
        foreach($ra_set as $ra){
            $allpassages .= $ra->passage . ' ';
        }
        return $allpassages;

    }//end of fetch_current_corpushash

    //fetch_current_corpushash / push_corpus
    public static function fetch_current_corpushash($moduleinstance,$corpusrange){
        global $DB;
        //first return the existing corpushash , for the course or for the site, if we have one
        $conditions = array('ttslanguage'=>$moduleinstance->ttslanguage,'region'=>$moduleinstance->region,'corpusrange'=>$corpusrange);
        if($corpusrange==constants::CORPUSRANGE_COURSE){
            $conditions['course'] = $moduleinstance->course;
        }
        $ra_set = $DB->get_records(constants::M_TABLE,$conditions);
        $corpushash = null;
        foreach($ra_set as $ra){
            //we ignore the current activity because its changing, so probably wrong(?)
            if($ra->id == $moduleinstance->id){continue;}
            if(!empty($ra->corpushash)){
                $corpushash=$ra->corpushash;
                break;
            }
        }
        //if we dont have one, then lets make one
        if($corpushash==null || empty($corpushash)){
            $corpushash = self::push_corpus($moduleinstance,$corpusrange);
        }
        return $corpushash;

    }//end of fetch_current_corpushash

    //fetch_current_corpushash / push_corpus
    public static function push_corpus($moduleinstance,$corpusrange){
        global $DB;

        //if its not lang modelable, dont do it
        if(!self::needs_lang_model($moduleinstance)){return null;}

        $allpassages = self::fetch_current_corpus($moduleinstance,$corpusrange);
        $corpushash = null;
        $temphash = utils::fetch_passagehash($allpassages,$moduleinstance->ttslanguage);
        if($temphash){
            //build a lang model
            $result = self::fetch_lang_model($allpassages,$moduleinstance->ttslanguage,$moduleinstance->region);
            if ($result && isset($result->success) && $result->success){
                $corpushash =$moduleinstance->region . '|'  . $temphash;
                $conditions = array('ttslanguage'=>$moduleinstance->ttslanguage,'region'=>$moduleinstance->region, 'usecorpus'=>constants::GUIDEDTRANS_CORPUS);
                if($corpusrange==constants::CORPUSRANGE_COURSE){
                    $conditions['course'] = $moduleinstance->course;
                }
                $DB->set_field(constants::M_TABLE,'corpushash',$corpushash,$conditions);
            }
        }
        return $corpushash;
    }

    public static function prepare_file_and_json_stuff($moduleinstance, $context){

        $ednofileoptions = readaloud_editor_no_files_options($context);
        $editors = readaloud_get_editornames();
        $itemid = 0;
        foreach ($editors as $editor) {
             $moduleinstance = file_prepare_standard_editor((object) $moduleinstance, $editor, $ednofileoptions, $context,
                   constants::M_COMPONENT, $editor, $itemid);
        }
        return $moduleinstance;

    }//end of prepare_file_and_json_stuff

}
