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
 * Poodll Wordcards
 *
 * @package    mod_wordcards
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 namespace mod_wordcards;
defined('MOODLE_INTERNAL') || die();

use \mod_wordcards\constants;


/**
 * Functions used generally across this mod
 *
 * @package    mod_wordcards
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils{



    //are we willing and able to transcribe submissions?
    public static function can_transcribe($instance)
    {
        //we default to true
        //but it only takes one no ....
        $ret = true;

        //The regions that can transcribe
        switch($instance->region){
            default:
                $ret = true;
        }

        //if user disables ai, we do not transcribe
        if(!$instance->enableai){
            $ret =false;
        }

        return $ret;
    }

    //convert a phrase or word to a series of phonetic characters that we can use to compare text/spoken
    public static function convert_to_phonetic($phrase,$language){

        switch($language){
            case 'en':
                $phonetic = metaphone($phrase);
                break;
            case 'ja':
                //gettting phonetics for JP requires php-mecab library doc'd here
                //https://github.com/nihongodera/php-mecab-documentation
                if(extension_loaded('mecab')){
                    $mecab = new \MeCab\Tagger();
                    $nodes=$mecab->parseToNode($phrase);
                    $katakanaarray=[];
                    foreach ($nodes as $n) {
                        $f =  $n->getFeature();
                        $reading = explode(',',$f)[8];
                        if($reading!='*'){
                            $katakanaarray[] =$reading;
                        }
                    }
                    $phonetic=implode($katakanaarray,'');
                    break;
                }
            default:
                $phonetic = $phrase;
        }
        return $phonetic;
    }

    public static function update_stepgrade($modid,$correct){
        global $DB,$USER;
        $mod = \mod_wordcards_module::get_by_modid($modid);
        $records = $DB->get_records(constants::M_ATTEMPTSTABLE, ['modid' => $modid, 'userid' => $USER->id],'timecreated DESC');

        if (!$records) {return false;}
        $record = array_shift($records);
        if (!$record) {return false;}

        $field=false;
        $termcount=0;
        switch($record->state){
            case \mod_wordcards_module::STATE_STEP1:
                $termcount=$mod->get_mod()->step1termcount;
                $field = 'grade1';
                break;
            case \mod_wordcards_module::STATE_STEP2:
                $termcount=$mod->get_mod()->step2termcount;
                $field = 'grade2';
                break;
            case \mod_wordcards_module::STATE_STEP3:
                $termcount=$mod->get_mod()->step3termcount;
                $field = 'grade3';
                break;
            case \mod_wordcards_module::STATE_STEP4:
                $termcount=$mod->get_mod()->step4termcount;
                $field = 'grade4';
                break;
            case \mod_wordcards_module::STATE_STEP5:
                $termcount=$mod->get_mod()->step5termcount;
                $field = 'grade5';
                break;
            case \mod_wordcards_module::STATE_END:
            case \mod_wordcards_module::STATE_TERMS:
            default:
                //do nothing
                break;
        }
        if($field && $termcount && ($termcount>=$correct)){
            $grade = ROUND(($correct / $termcount) * 100, 0);
            $DB->set_field(constants::M_ATTEMPTSTABLE,$field,$grade,array('id'=>$record->id));
        }
        return true;
    }

    //recalculate all final grades
    public static function recalculate_final_grades($moduleinstance){
        global $DB;

        $records = $DB->get_records(constants::M_ATTEMPTSTABLE, ['modid' => $moduleinstance->id]);
        foreach($records as $record){
            self::update_finalgrade($moduleinstance->id,$record->userid);
        }
    }

    //calc and update final grade of a single user
    public static function update_finalgrade($modid, $userid=0){
        global $DB,$USER;

        //if we arrive off the finished page, we are just grading, not regrading..
        if($userid == 0){
            $userid = $USER->id;
            $regrading = false;
        }else{
            $regrading = true;
        }

        $mod = \mod_wordcards_module::get_by_modid($modid);
        $moduleinstance = $mod->get_mod();
        $updateusergradebook = false; //post new grades to gradebook, set to true if find something gradeable

        $states = array(\mod_wordcards_module::STATE_STEP1, \mod_wordcards_module::STATE_STEP2, \mod_wordcards_module::STATE_STEP3,
            \mod_wordcards_module::STATE_STEP4, \mod_wordcards_module::STATE_STEP5);

        $records = $DB->get_records(constants::M_ATTEMPTSTABLE,
            ['modid' => $modid, 'userid' => $userid, 'state'=>\mod_wordcards_module::STATE_END]);

        if (!$records) {return false;}
        foreach($records as $record) {

            ///dont redo grading unless that is what we are ding (ie from recalculate final grades)
            if ($record->totalgrade > 0 && $regrading == false) {
                continue;
            }

            $totalgrade = 0;
            $totalsteps = 0;
            foreach ($states as $state) {
                //if we have a practice type for the step and it has terms, then tally the grade
                if ($moduleinstance->{$state} != \mod_wordcards_module::PRACTICETYPE_NONE) {
                    switch ($state) {
                        case \mod_wordcards_module::STATE_STEP1:
                            $termcount = $moduleinstance->step1termcount;
                            $grade = $record->grade1;
                            break;
                        case \mod_wordcards_module::STATE_STEP2:
                            $termcount = $moduleinstance->step2termcount;
                            $grade = $record->grade2;
                            break;
                        case \mod_wordcards_module::STATE_STEP3:
                            $termcount = $moduleinstance->step3termcount;
                            $grade = $record->grade3;
                            break;
                        case \mod_wordcards_module::STATE_STEP4:
                            $termcount = $moduleinstance->step4termcount;
                            $grade = $record->grade4;
                            break;
                        case \mod_wordcards_module::STATE_STEP5:
                            $termcount = $moduleinstance->step5termcount;
                            $grade = $record->grade5;
                            break;
                        case \mod_wordcards_module::STATE_END:
                        case \mod_wordcards_module::STATE_TERMS:
                        default:
                            $grade = 0;
                            $termcount = 0;
                            break;
                    }
                    if ($termcount > 0) {
                        $totalsteps++;
                        $totalgrade += $grade;
                    }
                }
            }
            if ($totalsteps > 0) {
                $grade = ROUND(($totalgrade / $totalsteps), 0);
                $DB->set_field(constants::M_ATTEMPTSTABLE, 'totalgrade', $grade, array('id' => $record->id));
                $updateusergradebook= true;
            }
        }
        //if we have something to update, do the re-grade
        if( $updateusergradebook) {
            wordcards_update_grades($moduleinstance, $userid, false);
        }
        return true;
    }


    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    public static function curl_fetch($url,$postdata=false)
    {
        global $CFG;

        require_once($CFG->libdir.'/filelib.php');
        $curl = new \curl();

        $result = $curl->get($url, $postdata);
        return $result;
    }

    //This is called from the settings page and we do not want to make calls out to cloud.poodll.com on settings
    //page load, for performance and stability issues. So if the cache is empty and/or no token, we just show a
    //"refresh token" links
    public static function fetch_token_for_display($apiuser,$apisecret){
       global $CFG;

       //First check that we have an API id and secret
        //refresh token
        $refresh = \html_writer::link($CFG->wwwroot . '/mod/wordcards/refreshtoken.php',
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
        if(in_array(constants::M_COMPONENT,$tokenobject->apps) &&
         self::is_site_registered($tokenobject->sites,true)){
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

        //if we got a token and its less than expiry time
        // use the cached one
        if($tokenobject && $tokenuser && $tokenuser==$apiuser && !$force){
            if($tokenobject->validuntil == 0 || $tokenobject->validuntil > time()){
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
                    $validuntil = $resp_object->validuntil - ($resp_object->poodlltime - time());
                    //we refresh one hour out, to prevent any overlap
                    $validuntil = $validuntil - (1 * HOURSECS);
                }else{
                    $validuntil = 0;
                }

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
                if(property_exists($resp_object,'awsaccesssecret')){
                    $tokenobject->awsaccesssecret = $resp_object->awsaccesssecret;
                }
                if(property_exists($resp_object,'awsaccessid')){
                    $tokenobject->awsaccessid = $resp_object->awsaccessid;
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

    //check site URL is actually registered
    static function is_site_registered($sites, $wildcardok = true) {
        global $CFG;

        foreach($sites as $site) {

            //get arrays of the wwwroot and registered url
            //just in case, lowercase'ify them
            $thewwwroot = strtolower($CFG->wwwroot);
            $theregisteredurl = strtolower($site);
            $theregisteredurl = trim($theregisteredurl);

            //add http:// or https:// to URLs that do not have it
            if (strpos($theregisteredurl, 'https://') !== 0 &&
                    strpos($theregisteredurl, 'http://') !== 0) {
                $theregisteredurl = 'https://' . $theregisteredurl;
            }

            //if neither parsed successfully, that a no straight up
            $wwwroot_bits = parse_url($thewwwroot);
            $registered_bits = parse_url($theregisteredurl);
            if (!$wwwroot_bits || !$registered_bits) {
                //this is not a match
                continue;
            }

            //get the subdomain widlcard address, ie *.a.b.c.d.com
            $wildcard_subdomain_wwwroot = '';
            if (array_key_exists('host', $wwwroot_bits)) {
                $wildcardparts = explode('.', $wwwroot_bits['host']);
                $wildcardparts[0] = '*';
                $wildcard_subdomain_wwwroot = implode('.', $wildcardparts);
            } else {
                //this is not a match
                continue;
            }

            //match either the exact domain or the wildcard domain or fail
            if (array_key_exists('host', $registered_bits)) {
                //this will cover exact matches and path matches
                if ($registered_bits['host'] === $wwwroot_bits['host']) {
                    //this is a match
                    return true;
                    //this will cover subdomain matches
                } else if (($registered_bits['host'] === $wildcard_subdomain_wwwroot) && $wildcardok) {
                    //yay we are registered!!!!
                    return true;
                } else {
                    //not a match
                    continue;
                }
            } else {
                //not a match
                return false;
            }
        }
        return false;
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
          "mumbai" => get_string("mumbai",constants::M_COMPONENT),
          "capetown" => get_string("capetown",constants::M_COMPONENT),
          "bahrain" => get_string("bahrain",constants::M_COMPONENT)
      );
  }

    public static function translate_region($key){
        switch($key){
            case "useast1": return "us-east-1";
            case "tokyo": return "ap-northeast-1";
            case "sydney": return "ap-southeast-2";
            case "dublin": return "eu-west-1";
            case "ottawa": return "ca-central-1";
            case "frankfurt": return "eu-central-1";
            case "london": return "eu-west-2";
            case "saopaulo": return "sa-east-1";
            case "singapore": return "ap-southeast-1";
            case "mumbai": return "ap-south-1";
            case "capetown": return "af-south-1";
            case "bahrain": return "me-south-1";
        }
    }

    public static function get_timelimit_options(){
        return array(
            0 => get_string("notimelimit",constants::M_COMPONENT),
            15 => get_string("xsecs",constants::M_COMPONENT,'15'),
            30 => get_string("xsecs",constants::M_COMPONENT,'30'),
            45 => get_string("xsecs",constants::M_COMPONENT,'45'),
            60 => get_string("onemin",constants::M_COMPONENT),
            90 => get_string("oneminxsecs",constants::M_COMPONENT,'30'),
            120 => get_string("xmins",constants::M_COMPONENT,'2'),
            150 => get_string("xminsecs",constants::M_COMPONENT,array('minutes'=>2,'seconds'=>30)),
            180 => get_string("xmins",constants::M_COMPONENT,'3')
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
        $options = array(constants::TRANSCRIBER_AUTO => get_string("transcriber_auto", constants::M_COMPONENT),
                constants::TRANSCRIBER_POODLL => get_string("transcriber_poodll", constants::M_COMPONENT));
        return $options;
    }

    public static function fetch_filemanager_opts($mediatype){
      global $CFG;
        $file_external = 1;
        $file_internal = 2;
        return array('subdirs' => 0, 'maxbytes' => $CFG->maxbytes, 'areamaxbytes' => 10485760, 'maxfiles' => 1,
                'accepted_types' => array($mediatype), 'return_types'=> $file_internal | $file_external);
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

    //fetch the MP3 URL of the text we want transcribed
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
        $params['appid'] = 'mod_wordcards';
        $params['owner'] = hash('md5',$USER->username);
        $params['region'] = $region;
        $serverurl = 'https://cloud.poodll.com/webservice/rest/server.php';
        $response = self::curl_fetch($serverurl, $params);
        if (!self::is_json($response)) {
            return false;
        }
        $payloadobject = json_decode($response);

        //returnCode > 0  indicates an error
        if ($payloadobject->returnCode > 0) {
            return false;
            //if all good, then lets do the embed
        } else if ($payloadobject->returnCode === 0) {
            $pollyurl = $payloadobject->returnMessage;
            return $pollyurl;
        } else {
            return false;
        }
    }

  public static function fetch_auto_voice($langcode){
        $voices = self::get_tts_voices($langcode);
        $autoindex = array_rand($voices);
        return $voices[$autoindex];
  }

  public static function get_tts_voices($langcode){
      $alllang= array(
              constants::M_LANG_ARAE => ['Zeina'],
          //constants::M_LANG_ARSA => [],
              constants::M_LANG_DEDE => ['Hans'=>'Hans','Marlene'=>'Marlene', 'Vicki'=>'Vicki'],
          //constants::M_LANG_DECH => [],
              constants::M_LANG_ENUS => ['Joey'=>'Joey','Justin'=>'Justin','Matthew'=>'Matthew','Ivy'=>'Ivy',
                      'Joanna'=>'Joanna','Kendra'=>'Kendra','Kimberly'=>'Kimberly','Salli'=>'Salli'],
              constants::M_LANG_ENGB => ['Brian'=>'Brian','Amy'=>'Amy', 'Emma'=>'Emma'],
              constants::M_LANG_ENAU => ['Russell'=>'Russell','Nicole'=>'Nicole'],
              constants::M_LANG_ENIN => ['Aditi'=>'Aditi', 'Raveena'=>'Raveena'],
          // constants::M_LANG_ENIE => [],
              constants::M_LANG_ENWL => ["Geraint"=>"Geraint"],
          // constants::M_LANG_ENAB => [],
              constants::M_LANG_ESUS => ['Miguel'=>'Miguel','Penelope'=>'Penelope'],
              constants::M_LANG_ESES => [ 'Enrique'=>'Enrique', 'Conchita'=>'Conchita', 'Lucia'=>'Lucia'],
          //constants::M_LANG_FAIR => [],
              constants::M_LANG_FRCA => ['Chantal'=>'Chantal'],
              constants::M_LANG_FRFR => ['Mathieu'=>'Mathieu','Celine'=>'Celine', 'Léa'=>'Léa'],
              constants::M_LANG_HIIN => ["Aditi"=>"Aditi"],
          //constants::M_LANG_HEIL => [],
          //constants::M_LANG_IDID => [],
              constants::M_LANG_ITIT => ['Carla'=>'Carla',  'Bianca'=>'Bianca', 'Giorgio'=>'Giorgio'],
              constants::M_LANG_JAJP => ['Takumi'=>'Takumi','Mizuki'=>'Mizuki'],
              constants::M_LANG_KOKR => ['Seoyan'=>'Seoyan'],
          //constants::M_LANG_MSMY => [],
              constants::M_LANG_NLNL => ["Ruben"=>"Ruben","Lotte"=>"Lotte"],
              constants::M_LANG_PTBR => ['Ricardo'=>'Ricardo', 'Vitoria'=>'Vitoria'],
              constants::M_LANG_PTPT => ["Ines"=>"Ines",'Cristiano'=>'Cristiano'],
              constants::M_LANG_RURU => ["Tatyana"=>"Tatyana","Maxim"=>"Maxim"],
          //constants::M_LANG_TAIN => [],
          //constants::M_LANG_TEIN => [],
              constants::M_LANG_TRTR => ['Filiz'=>'Filiz'],
              constants::M_LANG_ZHCN => ['Zhiyu']
      );
      if(array_key_exists($langcode,$alllang)) {
          return $alllang[$langcode];
      }else{
          return $alllang[constants::M_LANG_ENUS];
      }
    /*
      {"lang": "English(US)", "voices":  [{name: 'Joey', mf: 'm'},{name: 'Justin', mf: 'm'},{name: 'Matthew', mf: 'm'},{name: 'Ivy', mf: 'f'},{name: 'Joanna', mf: 'f'},{name: 'Kendra', mf: 'f'},{name: 'Kimberly', mf: 'f'},{name: 'Salli', mf: 'f'}]},
      {"lang": "English(GB)", "voices":  [{name: 'Brian', mf: 'm'},{name: 'Amy', mf: 'f'},{name: 'Emma', mf: 'f'}]},
      {"lang": "English(AU)", "voices": [{name: 'Russell', mf: 'm'},{name: 'Nicole', mf: 'f'}]},
      {"lang": "English(IN)", "voices":  [{name: 'Aditi', mf: 'm'},{name: 'Raveena', mf: 'f'}]},
      {"lang": "English(WELSH)", "voices":  [{name: 'Geraint', mf: 'm'}]},
      {"lang": "Danish", "voices":  [{name: 'Mads', mf: 'm'},{name: 'Naja', mf: 'f'}]},
      {"lang": "Dutch", "voices":  [{name: 'Ruben', mf: 'm'},{name: 'Lotte', mf: 'f'}]},
      {"lang": "French(FR)", "voices":  [{name: 'Mathieu', mf: 'm'},{name: 'Celine', mf: 'f'},{name: 'Léa', mf: 'f'}]},
      {"lang": "French(CA)", "voices":  [{name: 'Chantal', mf: 'm'}]},
      {"lang": "German", "voices":  [{name: 'Hans', mf: 'm'},{name: 'Marlene', mf: 'f'},{name: 'Vicki', mf: 'f'}]},
      {"lang": "Icelandic", "voices":  [{name: 'Karl', mf: 'm'},{name: 'Dora', mf: 'f'}]},
      {"lang": "Italian", "voices":  [{name: 'Carla', mf: 'f'},{name: 'Bianca', mf: 'f'},{name: 'Giorgio', mf: 'm'}]},
      {"lang": "Japanese", "voices":  [{name: 'Takumi', mf: 'm'},{name: 'Mizuki', mf: 'f'}]},
      {"lang": "Korean", "voices":  [{name: 'Seoyan', mf: 'f'}]},
      {"lang": "Norwegian", "voices":  [{name: 'Liv', mf: 'f'}]},
      {"lang": "Polish", "voices":  [{name: 'Jacek', mf: 'm'},{name: 'Jan', mf: 'm'},{name: 'Maja', mf: 'f'},{name: 'Ewa', mf: 'f'}]},
      {"lang": "Portugese(BR)", "voices":  [{name: 'Ricardo', mf: 'm'},{name: 'Vitoria', mf: 'f'}]},
      {"lang": "Portugese(PT)", "voices":  [{name: 'Cristiano', mf: 'm'},{name: 'Ines', mf: 'f'}]},
      {"lang": "Romanian", "voices":  [{name: 'Carmen', mf: 'f'}]},
      {"lang": "Russian", "voices":  [{name: 'Maxim', mf: 'm'},{name: 'Tatyana', mf: 'f'}]},
      {"lang": "Spanish(ES)", "voices":  [{name: 'Enrique', mf: 'm'},{name: 'Conchita', mf: 'f'},{name: 'Lucia', mf: 'f'}]},
      {"lang": "Spanish(US)", "voices":  [{name: 'Miguel', mf: 'm'},{name: 'Penelope', mf: 'f'}]},
      {"lang": "Swedish", "voices":  [{name: 'Astrid', mf: 'f'}]},
      {"lang": "Turkish", "voices":  [{name: 'Filiz', mf: 'f'}]},
      {"lang": "Welsh", "voices":  [{name: 'Gwyneth', mf: 'f'}]},
    */

  }

  /* An activity typoe will be eith practice or review */
    public static function fetch_activity_tablabel($activitytype){
      switch($activitytype){
          case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT:
          case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE:
          case \mod_wordcards_module::PRACTICETYPE_DICTATION:
          case \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS:
          case \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE:
              return get_string('practice','mod_wordcards') ;
          case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV:
          case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE_REV:
          case \mod_wordcards_module::PRACTICETYPE_DICTATION_REV:
          case \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS_REV:
          case \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE_REV:
              return get_string('review','mod_wordcards');

      }
    }

    /* An activity typoe will be eith practice or review */
    public static function is_review_activity($activitytype){
        switch($activitytype){
            case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT:
            case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE:
            case \mod_wordcards_module::PRACTICETYPE_DICTATION:
            case \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS:
                return false;
            case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV:
            case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE_REV:
            case \mod_wordcards_module::PRACTICETYPE_DICTATION_REV:
            case \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS_REV:
                return true;

        }
    }

    /* Each activity shows an icon on the tab tree */
    public static function fetch_activity_tabicon($activitytype){
        switch($activitytype){
            case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT:
            case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV:
                return 'fa-bars';

            case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE:
            case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE_REV:
                return 'fa-keyboard-o';

            case \mod_wordcards_module::PRACTICETYPE_DICTATION:
            case \mod_wordcards_module::PRACTICETYPE_DICTATION_REV:
                return 'fa-headphones';

            case \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE:
            case \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE_REV:
                return 'fa-headphones';

            case \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS:
            case \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS_REV:
                return 'fa-comment-o';

            default:
                return 'fa-dot-circle-o';
        }
    }

  public static function get_practicetype_options($wordpool=false){
      $none =  array(\mod_wordcards_module::PRACTICETYPE_NONE => get_string('title_noactivity', 'mod_wordcards'));
      $learnoptions = [
              \mod_wordcards_module::PRACTICETYPE_MATCHSELECT => get_string('title_matchselect', 'mod_wordcards'),
              \mod_wordcards_module::PRACTICETYPE_MATCHTYPE => get_string('title_matchtype', 'mod_wordcards'),
              \mod_wordcards_module::PRACTICETYPE_DICTATION => get_string('title_dictation', 'mod_wordcards'),
              \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS => get_string('title_speechcards', 'mod_wordcards'),
              \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE => get_string('title_listenchoose', 'mod_wordcards')
      ];

        $reviewoptions = [
            \mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV => get_string('title_matchselect_rev', 'mod_wordcards'),
            \mod_wordcards_module::PRACTICETYPE_MATCHTYPE_REV => get_string('title_matchtype_rev', 'mod_wordcards'),
            \mod_wordcards_module::PRACTICETYPE_DICTATION_REV => get_string('title_dictation_rev', 'mod_wordcards'),
            \mod_wordcards_module::PRACTICETYPE_SPEECHCARDS_REV => get_string('title_speechcards_rev', 'mod_wordcards'),
            \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE_REV => get_string('title_listenchoose_rev', 'mod_wordcards')
            ];

      if($wordpool===\mod_wordcards_module::WORDPOOL_LEARN){
          $options=$learnoptions;
      }else{
          //We need to merge arrays this way, not with array_merge, in order to preserve keys
          $options = $none + $learnoptions + $reviewoptions;
      }
      return $options;
  }

  public static function fetch_options_fontfaceflip(){
      return array(
              constants::M_FRONTFACEFLIP_TERM=> get_string('term', constants::M_COMPONENT),
              constants::M_FRONTFACEFLIP_DEF => get_string('definition', constants::M_COMPONENT));
  }

   public static function get_lang_options() {
       return array(
               constants::M_LANG_ARAE => get_string('ar-ae', constants::M_COMPONENT),
               constants::M_LANG_ARSA => get_string('ar-sa', constants::M_COMPONENT),
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
               constants::M_LANG_ZHCN => get_string('zh-cn', constants::M_COMPONENT)
       );
   }

    /*
     * Do we need to build a language model for this passage?
     *
     */
    public static function needs_lang_model($mod) {
        $region = get_config(constants::M_COMPONENT,'awsregion');
        switch($region){
            case 'tokyo':
            case 'useast1':
            case 'dublin':
            case 'sydney':
            default:
            return (substr($mod->get_mod()->ttslanguage,0,2)=='en' ||
                            substr($mod->get_mod()->ttslanguage,0,2)=='de' ||
                            substr($mod->get_mod()->ttslanguage,0,2)=='fr' ||
                            substr($mod->get_mod()->ttslanguage,0,2)=='es') && $mod->get_terms();
        }
    }

    /*
     * Hash the passage and compare
     *
     */
    public static function fetch_passagehash($mod) {
        $cleantext = self::fetch_activity_text($mod);
        if(!empty($cleantext)) {
            return sha1($cleantext);
        }else{
            return false;
        }
    }


    /*
     * Build a language model for this passage
     *
     */
    public static function fetch_lang_model($mod){
        $conf= get_config(constants::M_COMPONENT);
        if (!empty($conf->apiuser) && !empty($conf->apisecret)) {;
            $token = self::fetch_token($conf->apiuser, $conf->apisecret);

            if(empty($token)){
                return false;
            }
            $url = constants::CLOUDPOODLL . "/webservice/rest/server.php";
            $params["wstoken"]=$token;
            $params["wsfunction"]='local_cpapi_generate_lang_model';
            $params["moodlewsrestformat"]='json';
            $params["passage"]= self::fetch_activity_text($mod);
            $params["language"]=$mod->get_mod()->ttslanguage;
            $params["region"]=$conf->awsregion;

            $resp = self::curl_fetch($url,$params);
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

    /*
    * Return all the cleaned and connected text for the activity
    * Borrowed from read aloud
    *
    */
    public static function fetch_activity_text($mod) {

        $terms = $mod->get_terms();
        if(!$terms){return "";}
        $thetext = "";
        foreach ($terms as $term){
            $thetext .= $term->term . " ";
            if(!empty($term->model_sentence)){
                $thetext .= $term->model_sentence . " ";
            }
        }

        //f we think its unicodemb4, first test and then get on with it
        $unicodemb4=self::isUnicodemb4($thetext);

        //lowercaseify
        $thetext = strtolower($thetext);

        //remove any html
        $thetext = strip_tags($thetext);

        //replace all line ends with spaces
        if($unicodemb4) {
            $thetext = preg_replace('/#\R+#/u', ' ', $thetext);
            $thetext = preg_replace('/\r/u', ' ', $thetext);
            $thetext = preg_replace('/\n/u', ' ', $thetext);
        }else{
            $thetext = preg_replace('/#\R+#/', ' ', $thetext);
            $thetext = preg_replace('/\r/', ' ', $thetext);
            $thetext = preg_replace('/\n/', ' ', $thetext);
        }

        //remove punctuation. This is where we needed the unicode flag
        //see https://stackoverflow.com/questions/5233734/how-to-strip-punctuation-in-php
        // $thetext = preg_replace("#[[:punct:]]#", "", $thetext);
        //https://stackoverflow.com/questions/5689918/php-strip-punctuation
        if($unicodemb4) {
            $thetext = preg_replace("/[[:punct:]]+/u", "", $thetext);
        }else{
            $thetext = preg_replace("/[[:punct:]]+/", "", $thetext);
        }

        //remove bad chars
        $b_open = "“";
        $b_close = "”";
        $b_sopen = '‘';
        $b_sclose = '’';
        $bads = array($b_open, $b_close, $b_sopen, $b_sclose);
        foreach ($bads as $bad) {
            $thetext = str_replace($bad, '', $thetext);
        }

        //remove double spaces
        //split on spaces into words
        $textbits = explode(' ', $thetext);
        //remove any empty elements
        $textbits = array_filter($textbits, function($value) {
            return $value !== '';
        });
        $thetext = implode(' ', $textbits);
        return $thetext;
    }

    /*
    * Regexp replace with /u will return empty text if not unicodemb4
    * some DB collations and char sets may do that to us. So we test for that here
    */
    public static function isUnicodemb4($thetext) {
        //$testtext = "test text: " . "\xf8\xa1\xa1\xa1\xa1"; //this will fail for sure

        $thetext = strtolower($thetext);
        $thetext = strip_tags($thetext);
        $testtext = "test text: " . $thetext;
        $test1 = preg_replace('/#\R+#/u', ' ', $testtext);
        if(empty($test1)){return false;}
        $test2 = preg_replace('/\r/u', ' ', $testtext);
        if(empty($test2)){return false;}
        $test3 = preg_replace('/\n/u', ' ', $testtext);
        if(empty($test3)){return false;}
        $test4 = preg_replace("/[[:punct:]]+/u", "", $testtext);
        if(empty($test4)){
            return false;
        }else{
            return true;
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
        $mform->addElement('text', 'name', get_string('modulename', constants::M_COMPONENT), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'modulename', constants::M_COMPONENT);

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

        $options = utils::get_lang_options();
        $mform->addElement('select', 'ttslanguage', get_string('ttslanguage', 'mod_wordcards'),
                $options);
        $mform->setDefault('ttslanguage',$config->ttslanguage);

        $mform->addElement('header', 'hdrappearance', get_string('appearance'));
        $mform->setExpanded('hdrappearance');

        //options for practicetype and term count
        $ptype_options_learn = utils::get_practicetype_options(\mod_wordcards_module::WORDPOOL_LEARN);
        $ptype_options_all = utils::get_practicetype_options();
        $termcount_options = [4 => 4, 5 => 5, 6 => 6, 7 => 7,8 => 8,9 => 9,10 => 10,11 => 11,12 => 12,13 => 13,14 => 14,15 => 15];

        $mform->addElement('select', 'step1practicetype', get_string('step1practicetype', 'mod_wordcards'),
                $ptype_options_learn, \mod_wordcards_module::PRACTICETYPE_MATCHSELECT);
        $mform->addElement('select', 'step1termcount', get_string('step1termcount', 'mod_wordcards'), $termcount_options, 4);

        $mform->addElement('select', 'step2practicetype', get_string('step2practicetype', 'mod_wordcards'),
                $ptype_options_all,\mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV);
        $mform->addElement('select', 'step2termcount', get_string('step2termcount', 'mod_wordcards'), $termcount_options, 4);
        $mform->disabledIf('step2termcount', 'step2practicetype', 'eq',\mod_wordcards_module::PRACTICETYPE_NONE);

        $mform->addElement('select', 'step3practicetype', get_string('step3practicetype', 'mod_wordcards'),
                $ptype_options_all,\mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV);
        $mform->addElement('select', 'step3termcount', get_string('step3termcount', 'mod_wordcards'), $termcount_options, 4);
        $mform->disabledIf('step3termcount', 'step3practicetype', 'eq',\mod_wordcards_module::PRACTICETYPE_NONE);

        $mform->addElement('select', 'step4practicetype', get_string('step4practicetype', 'mod_wordcards'),
                $ptype_options_all,\mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV);
        $mform->addElement('select', 'step4termcount', get_string('step4termcount', 'mod_wordcards'), $termcount_options, 4);
        $mform->disabledIf('step4termcount', 'step4practicetype', 'eq',\mod_wordcards_module::PRACTICETYPE_NONE);

        $mform->addElement('select', 'step5practicetype', get_string('step5practicetype', 'mod_wordcards'),
                $ptype_options_all,\mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV);
        $mform->addElement('select', 'step5termcount', get_string('step5termcount', 'mod_wordcards'), $termcount_options, 4);
        $mform->disabledIf('step5termcount', 'step5practicetype', 'eq',\mod_wordcards_module::PRACTICETYPE_NONE);

        //Attempts
        $attemptoptions = array(0 => get_string('unlimited', constants::M_COMPONENT),
                1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5',);
        $mform->addElement('select', 'maxattempts', get_string('maxattempts', constants::M_COMPONENT), $attemptoptions);

        $t_options = utils::fetch_options_transcribers();
        $mform->addElement('select', 'transcriber', get_string('transcriber', 'mod_wordcards'),
                $t_options,$config->transcriber);

        $mform->addElement('hidden', 'skipreview',0);
        $mform->setType('skipreview',PARAM_INT);
        // $mform->addElement('checkbox', 'skipreview', get_string('skipreview', 'mod_wordcards'));
        // $mform->setDefault('skipreview', 1);
        // $mform->addHelpButton('skipreview', 'skipreview', 'mod_wordcards');

        $mform->addElement('editor', 'finishedstepmsg_editor', get_string('finishedstepmsg', 'mod_wordcards'));
        $mform->setDefault('finishedstepmsg_editor', array('text' => get_string('finishscatterin', 'mod_wordcards')));
        $mform->addHelpButton('finishedstepmsg_editor', 'finishedstepmsg', 'mod_wordcards');

        $mform->addElement('editor', 'completedmsg_editor', get_string('completedmsg', 'mod_wordcards'));
        $mform->setDefault('completedmsg_editor', array('text' => get_string('congratsitsover', 'mod_wordcards')));
        $mform->addHelpButton('completedmsg_editor', 'completedmsg', 'mod_wordcards');

        //Show images on task flip screen
        $mform->addElement('selectyesno', 'showimageflip', get_string('showimagesonflipscreen', 'mod_wordcards'));
        $mform->setDefault('showimageflip', $config->showimageflip);

        $frontfaceoptions = self::fetch_options_fontfaceflip();
        $mform->addElement('select', 'frontfaceflip', get_string('frontfaceflip', 'mod_wordcards'),
                $frontfaceoptions, $config->frontfaceflip);


    } //end of add_mform_elements

    public static function prepare_file_and_json_stuff($moduleinstance, $modulecontext){
        $moduleinstance['finishedstepmsg_editor']['text'] = $moduleinstance['finishedstepmsg'];
        $moduleinstance['completedmsg_editor']['text'] = $moduleinstance['completedmsg'];

        return $moduleinstance;

    }//end of prepare_file_and_json_stuff

    //What multi-attempt grading approach
    public static function get_grade_options() {
        return array(
            constants::M_GRADELATEST => get_string("gradelatest", constants::M_COMPONENT),
            constants::M_GRADEHIGHEST => get_string("gradehighest", constants::M_COMPONENT)
        );
    }


}
