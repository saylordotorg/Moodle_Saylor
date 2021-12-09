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

namespace filter_poodll;

defined('MOODLE_INTERNAL') || die();


/**
 *
 * This is a class for working with AWS
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2020 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class awsremote {

    protected $convfolder = 'transcoded/';


    const REGION_APS1 = 'ap-south-1'; //Asia Pacific (Mumbai)
    const REGION_APN2 = 'ap-northeast-2'; //Asia Pacific (Seoul)
    const REGION_APSE1 = 'ap-southeast-1'; //Asia Pacific (Singapore)
    const REGION_APSE2 = 'ap-southeast-2'; //Asia Pacific (Sydney)
    const REGION_APN1 = 'ap-northeast-1'; //Asia Pacific (Tokyo)
    const REGION_USE2 = 'us-east-2'; //US East (Ohio)
    const REGION_USE1 = 'us-east-1'; //US East (N. Virginia)
    const REGION_USW1 = 'us-west-1'; //US West (N. California)
    const REGION_USW2 = 'us-west-2'; //US West (Oregon)
    const REGION_CAC1 = 'ca-central-1'; //Canada (Central)
    const REGION_EUC1 = 'eu-central-1'; //EU (Frankfurt)
    const REGION_EUW1 = 'eu-west-1'; //EU (Ireland)
    const REGION_EUW2 = 'eu-west-2'; //EU (London)
    const REGION_SAE1 = 'sa-east-1'; //South America (São Paulo)
    const REGION_AFS1 = 'af-south-1'; //South Africa
    const REGION_MES1 = 'me-south-1'; //Middle East (Bahrain)



    /**
     * Constructor
     */
    public function __construct($region = false) {
        global $CFG;
        //once we are set up we enable this
        if (!$region) {
            $this->region = $CFG->filter_poodll_aws_region;
        } else {
            $this->region = self::parse_region($region);
        }
    }

    public static function parse_region($region) {
        $ret = false;
        //REGION
        switch ($region) {
            case 'useast1':
                $ret = self::REGION_USE1;
                break;
            case 'dublin':
                $ret = self::REGION_EUW1;
                break;
            case 'sydney':
                $ret = self::REGION_APSE2;
                break;
            case 'ottawa':
                $ret = self::REGION_CAC1;
                break;
            case 'saopaulo':
                $ret = self::REGION_SAE1;
                break;
            case 'frankfurt':
                $ret = self::REGION_EUC1;
                break;
            case 'london':
                $ret = self::REGION_EUW2;
                break;
            case 'singapore':
                $ret = self::REGION_APSE1;
                break;
            case 'mumbai':
                $ret = self::REGION_APS1;
                break;
            case 'tokyo':
                $ret = self::REGION_APN1;
                break;
            case 'capetown':
                $ret = self::REGION_AFS1;
                break;
            case 'bahrain':
                $ret = self::REGION_MES1;
                break;
            default:
                //the region might already be good
                $ret = $region;
        }
        return $ret;
    }



    function call_cloudpoodll($functionname, $params){
        $conf = get_config('filter_poodll');

        if (!empty($conf->cpapiuser) && !empty($conf->cpapisecret)) {
            $lm = new \filter_poodll\licensemanager();
            $tokenobject = $lm->fetch_token($conf->cpapiuser, $conf->cpapisecret);
            if(isset($tokenobject->token)){
                $token=$tokenobject->token;
            }else{
                return false;
            }
            $url = constants::CLOUDPOODLL . "/webservice/rest/server.php";
            $params["wstoken"]=$token;
            $params["wsfunction"]=$functionname;
            $params["moodlewsrestformat"]='json';
           // $paramstring = http_build_query($params);
            $resp = \filter_poodll\poodlltools::curl_fetch($url,$params);
            return json_decode($resp);
        }else{
            return false;
        }
    }


    //called from adhoc mov task
    function fetch_s3_converted_file($mediatype, $infilename, $outfilename, $filename) {
        global $CFG;
        $params = [];
        $params['mediatype']=$mediatype;
        $params['infilename']=$infilename;
        $params['outfilename']=$outfilename;
        $params['convfolder']=$this->convfolder;
        $params['region']=$this->region;
        $ret = poodlltools::call_cloudpoodll('local_cpapi_fetch_convfile_details',$params);
        if(!$ret || !isset($ret->returnCode)) {return 'failed to get aws remote result';}
            if ($ret->returnCode=="0") {
                $tempfilepath = $CFG->tempdir . "/" . $filename;
                $downloadurl = $ret->returnMessage;
                $this->save_from_url_to_file($downloadurl, $tempfilepath);
                return $tempfilepath;
            } else {
                if ($ret->returnMessage=='noinfile') {
                    //if we do not even have an input file then just return, somethings wrong
                    //but it can not be fixed
                    return false;
                } else {
                    return true;
                }
        }
    }

    //download file via url to temp storage
    //url could presigned or from Red5
    function save_from_url_to_file($downloadurl, $filepath) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');
        $headers=null;
        $postdata=null;
        $fullresponse=false;
        $timeout=300;
        $connecttimeout=20;
        $skipcertverify=false;
        $tofile=$filepath;
        $calctimeout=false;

        download_file_content($downloadurl, $headers, $postdata, $fullresponse, $timeout, $connecttimeout, $skipcertverify, $tofile, $calctimeout);
        return true;
    }

    //has matching in awstools. called from poodlltools -> register_remote_job
    function stage_remote_process_job($host, $mediatype, $appid, $s3path, $s3outfilename,
            $transcode, $transcoder, $transcribe, $subtitle, $language, $vocab, $notificationurl, $sourcemimetype,$owner='poodll' ) {

        global $USER;

        $params = Array();
        $params['region'] = $this->region;
        $params['host'] = $host;
        $params['s3outfilename'] = $s3outfilename;
        $params['appid'] = $appid;
        $params['owner'] = $owner;
        $params['transcode'] = $transcode ? '1' : '0';
        $params['transcoder'] = $transcoder;
        $params['transcribe'] = $transcribe ? '1' : '0';
        $params['subtitle'] = $subtitle ? '1' : '0';
        $params['language'] = $language;
        $params['vocab'] = $vocab;
        $params['s3path'] = $s3path;
        $params['mediatype'] = $mediatype;
        $params['notificationurl'] = $notificationurl;
        $params['sourcemimetype'] = $sourcemimetype;

        try {
            $ret = poodlltools::call_cloudpoodll('local_cpapi_stage_remoteprocess_job',$params);
            if(!$ret || !isset($ret->returnCode)) {return 'failed to get aws remote result';}
            if ($ret->returnCode=="0") {
                return true;
            } else {
               return $ret->returnMessage;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    //post file data directly to
    //called from instance_remote_download poodllfilelib.php
    function s3_put_filedata($mediatype, $key, $filepath) {
        global $CFG;

        //do  quick sanity checks
        if(!file_exists($filepath)){
            return false;
        }
        $size = filesize($filepath);
        if (!$size) {
            return false;
        }

        //get upload URL
        $url = $this->get_presignedupload_url($mediatype,30,$key,false);
        if(!$url || strpos($url,'http')===false){
            return false;
        }

        //init return value
        $result =false;

        //Get CURL and prepare it.
        require_once($CFG->libdir.'/filelib.php');
        $curl = new \curl();
        $params=array();
        $params['file']=$filepath;

        $options = array();
        $options['CURLOPT_USERPWD'] = null;

        $result = $curl->put($url, $params, $options);
        if(empty($result)){$result=true;}
        return $result;
    }

    //called from poodlltools->getAMDRecordercode. Has matching function in awstools
    function get_presignedupload_url($mediatype, $minutes, $key, $iosvideo = false) {
        $params = Array();
        $params['region'] = $this->region;
        $params['mediatype'] = $mediatype;
        $params['minutes'] = $minutes;
        $params['key'] = $key;
        $params['iosvideo'] = $iosvideo;
        try {
            $ret = poodlltools::call_cloudpoodll('local_cpapi_fetch_presignedupload_url',$params);
            if(!$ret || !isset($ret->returnCode)) {return 'failed to get aws remote result';}
            if ($ret->returnCode=="0") {
                return $ret->returnMessage;
            } else {
                return $ret->returnMessage;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    //also in awstools. Called from poodlltools->commence_s3_transcode and confirm_s3_arrival
    function does_file_exist($mediatype, $filename, $inout = 'in') {
        $params = Array();
        $params['region'] = $this->region;
        $params['mediatype'] = $mediatype;
        $params['filename'] = $filename;
        $params['inout'] = $inout;
        try {
            $ret = poodlltools::call_cloudpoodll('local_cpapi_does_file_exist',$params);
            if(!$ret || !isset($ret->returnCode)) {return 'failed to get aws remote result';}
            if ($ret->returnCode=="0") {
                return $ret->returnMessage == 'true';
            } else {
                return $ret->returnMessage;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * Make S3 filename (ala object key)
     * $path is likely to be a folder for the site
     * $identifier is any old prefix the uploading site wishes to use to tag users
     */
    public static function fetch_s3_filename($mediatype, $filename, $parent = false) {
        global $CFG, $USER;

        //here we encode the URL so that the lambda notifier can process it.
        //we need a notification bit (Y|N)
        //self hosted = wwwroot, otherwise parent will be passed in
        if ($parent == false) {
            $thewwwroot = strtolower($CFG->wwwroot);
        } else {
            $thewwwroot = strtolower($parent);
        }
        $wwwroot_bits = parse_url($thewwwroot);
        $bits = array();
        $lambdabit = '';
        if ($CFG->filter_poodll_cloudnotifications) {
            $lambdabit .= 'Y';
        } else {
            $lambdabit .= 'N';
        }
        $bits[] = $lambdabit;

        $bits[] = $wwwroot_bits['scheme'];
        $bits[] = $wwwroot_bits['host'];
        if (array_key_exists('port', $wwwroot_bits)) {
            $bits[] = $wwwroot_bits['port'];
        } else if ($wwwroot_bits['scheme'] == 'https') {
            $bits[] = '443';
        } else {
            $bits[] = '80';
        }
        if (array_key_exists('path', $wwwroot_bits)) {
            $bits[] = str_replace('/', '!', $wwwroot_bits['path']);
        } else {
            $bits[] = '';
        }
        $codedurl = implode('_', $bits);
        $codedurl = $codedurl . '_';
        //we stopped using session key with cloud poodll, pre upload has session key
        // post upload does not ...
        if (false && isset($USER->sesskey)) {
            $s3filename = $USER->sesskey . '_' . $mediatype . '_' . $filename;
        } else {
            $s3filename = '99999_' . $mediatype . '_' . $filename;
        }
        $s3filename = $codedurl . $s3filename;
        return $s3filename;
    }





}
