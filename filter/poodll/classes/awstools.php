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

use Aws\Common\Facade\SimpleDb;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Aws\S3\S3Client;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Polly\PollyClient;
use Aws\TranscribeService\TranscribeServiceClient;

/**
 *
 * This is a class for working with AWS
 *
 * @package   filter_poodll
 * @since      Moodle 2.7
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class awstools {

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

    const URLSTUB_POLLYFILE = 'https://pollyfile.poodll.net';
    const BUCKET_NAME_POLLYFILE = 'amazon-cloudfront-secure-static-site-s3bucketroot-1dw3pilpa7ci3';
    const BUCKET_NAME_VIDEOIN = 'poodll-videoprocessing-in';
    const BUCKET_NAME_VIDEOOUT = 'poodll-videoprocessing-out';
    const BUCKET_NAME_VIDEOTHUMBS = 'poodll-video-thumbs';
    const BUCKET_NAME_AUDIOIN = 'poodll-audioprocessing-in';
    const BUCKET_NAME_AUDIOOUT = 'poodll-audioprocessing-out';
    const BUCKET_NAME_AUDIOTHUMBS = 'poodll-audio-thumbs';

    protected $awsversion = "2.x";//3.x
    protected $transcoder = false; //the single transcoder object
    protected $s3client = false; //the single S3 object
    protected $dynamodbclient = false; //the single transcribe object
    protected $transcribeclient = false; //the single transcribe object
    protected $pollyclient = false; //the single Polly object
    protected $default_segment_size = 4;
    protected $region = self::REGION_APN1;
    protected $convfolder = 'transcoded/';
    protected $thirtydayfolder = '30day/';
    protected $accesskey = '';
    protected $secretkey = '';

    protected $bucket_video_in = ''; //video in bucket (pathname)
    protected $bucket_video_out = ''; //video out bucket (pathname)
    protected $bucket_audio_in = ''; //audio in bucket (pathname)
    protected $bucket_audio_out = ''; //audio out bucket (pathname)
    protected $bucket_thumbnails = ''; //video thumbs bucket (pathname)
    protected $pipeline_video = ''; // ID of video pipeline (region specific)
    protected $pipeline_audio = ''; // ID of audio pipeline (region specific)
    protected $preset_mp3 = ""; // ID of MP3 preset (region specific)
    protected $preset_mp4 = ""; // ID of MP4 preset (region specific)

    /**
     * Constructor
     */
    public function __construct($region = false) {
        global $CFG;
        $lm = new \filter_poodll\licensemanager();
        $this->accesskey = $lm->get_cloud_access_key($CFG->filter_poodll_registrationkey);
        $this->secretkey = $lm->get_cloud_access_secret($CFG->filter_poodll_registrationkey);

        //once we are set up we enable this
        if (!$region) {
            $this->region = $CFG->filter_poodll_aws_region;
        } else {
            $this->region = self::parse_region($region);
        }
        $bucketsuffix = '-' . $this->region;

        switch ($this->region) {
            case self::REGION_APN1:
                $this->pipeline_video = '1467090278549-scfvbk';
                $this->pipeline_audio = '1467090404312-4yquey';
                $this->preset_mp3 = "1467090564863-tc7k8e";
                $this->preset_mp4 = "1467090505514-0gibkw";
                //there are no suffixes on the ap-northeast-1 buckets
                //so we clear it here
                $bucketsuffix = '';
                break;

            case self::REGION_EUW1:
                $this->pipeline_video = '1498301226886-igwgt8';
                $this->pipeline_audio = '1498301312122-rfjmqr';
                $this->preset_mp3 = "1498301559020-465p9k";
                $this->preset_mp4 = "1498301496472-zlun4d";
                break;

            case self::REGION_APSE2:
                $this->pipeline_video = '1498354812258-1t97j4';
                $this->pipeline_audio = '1498354852450-l4ysr7';
                $this->preset_mp4 = "1498353281271-mpkox5";
                $this->preset_mp3 = "1498353323686-ql5apg";
                break;

            case self::REGION_APSE1:
                $this->pipeline_video = '1580381783238-vnfloa';
                $this->pipeline_audio = '1580381828495-ogbn8a';
                $this->preset_mp4 = "1580381885190-ccobzi";
                $this->preset_mp3 = "1580381920554-zv7v5b";
                break;

            case self::REGION_APS1:
                $this->pipeline_video = '1580374511824-o6i0ft';
                $this->pipeline_audio = '1580374593293-ydsqvi';
                $this->preset_mp4 = "1580381534804-v6ew2u";
                $this->preset_mp3 = "1580381692821-gq8yc7";
                break;


            case self::REGION_USE1:
                $this->pipeline_video = '1498352710890-ybul2c';
                $this->pipeline_audio = '1498352776647-bgnr1z';
                $this->preset_mp4 = "1498353119116-pdxliz";
                $this->preset_mp3 = "1498353166217-bfalp1";
                break;

            case self::REGION_CAC1:
            case self::REGION_EUW2:
            case self::REGION_SAE1:
            case self::REGION_EUC1:
            case self::REGION_AFS1:
            case self::REGION_MES1:
            default:
                $this->pipeline_video ='ffmpegtranscoder';
                $this->pipeline_audio ='ffmpegtranscoder';
                $this->preset_mp4 = "mp4";
                $this->preset_mp3 = "mp3";
                break;

        }

        //set buckets
        $this->bucket_video_in = self::BUCKET_NAME_VIDEOIN . $bucketsuffix; //video in bucket
        $this->bucket_video_out = self::BUCKET_NAME_VIDEOOUT . $bucketsuffix; //video out bucket
        $this->bucket_audio_in = self::BUCKET_NAME_AUDIOIN . $bucketsuffix; //audio in bucket
        $this->bucket_audio_out = self::BUCKET_NAME_AUDIOOUT . $bucketsuffix; //audio out bucket
        $this->bucket_thumbnails = self::BUCKET_NAME_VIDEOTHUMBS . $bucketsuffix; //video thumbs bucket

        //Poodll no longer carries its own AWS SDK. It is loaded from local_aws. But it is not used for normal installations.
        $catalyst_s3_loader = $CFG->dirroot . '/local/aws/sdk/aws-autoloader.php';
        $PHP55 = version_compare(phpversion(), '5.5.0', '>=');
         if (file_exists($catalyst_s3_loader) && $PHP55) {
             require_once($catalyst_s3_loader);
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

    /**
     *
     *
     *  TRANSCODING CODE STARTS HERE
     *
     */
    //fetch or create the transcoder object 
    function fetch_transcoder() {
        global $CFG;
        if (!$this->transcoder) {
            $config = array();
            $config['region'] = $this->region;
            $config['version'] = '2012-09-25';
            $config['default_caching_config'] = $CFG->tempdir . '/tmp';
            $config['credentials'] = array('key' => $this->accesskey, 'secret' => $this->secretkey);
            //add proxy settings if necessary
            if (!empty($CFG->proxyhost)) {
                $proxy = $CFG->proxytype . '://' . $CFG->proxyhost;
                if ($CFG->proxyport > 0) {
                    $proxy = $proxy . ':' . $CFG->proxyport;
                }
                if (!empty($CFG->proxyuser)) {
                    $proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
                }
                $config['http'] = array('proxy' => $proxy);

            }
            $this->transcoder = ElasticTranscoderClient::factory($config);
        }
        return $this->transcoder;
    }


    //create a single job
    //Called from CloudPoodll AND Classic Poodll
    function create_one_transcoding_job($mediatype, $input_key, $output_key, $output_key_prefix = false) {

        $transcoder_client = $this->fetch_transcoder();

        //create the output prefix, by default its just in the conv folder of the media and region
        //but if passed in it contains parent/expiry/owner identifiers
        if (!$output_key_prefix) {
            $output_key_prefix = $this->convfolder;
        }

        # Setup the job input using the provided input key.
        $input = array('Key' => $input_key);

        # Specify the outputs
        $outputs = array();
        switch ($mediatype) {

            case 'video':
                $pipeline_id = $this->pipeline_video;
                $one_output = array('Key' => $output_key, 'PresetId' => $this->preset_mp4);
                $outputs[] = $one_output;
                break;

            case 'audio':
            default:
                $pipeline_id = $this->pipeline_audio;
                $one_output = array('Key' => $output_key, 'PresetId' => $this->preset_mp3);
                $outputs[] = $one_output;
                break;
        }

        // We should never get here with an ffmpegtrancoder pipleline, so just cancel out in that case
        if($pipeline_id=='ffmpegtranscoder'){return 'no ET pipeline for ths region';}

        # Create the job.
        $create_job_request = array(
                'PipelineId' => $pipeline_id,
                'Input' => $input,
                'Outputs' => $outputs,
                'OutputKeyPrefix' => $output_key_prefix
        );
        $create_job_result = $transcoder_client->createJob($create_job_request)->toArray();
        return $job = $create_job_result['Job'];
    }

    function stage_remote_process_job($host, $mediatype, $appid, $s3path, $s3outfilename,
            $transcode, $transcoder, $transcribe, $subtitle, $language, $vocab, $notificationurl, $sourcemimetype,$owner='poodll' ) {

        global $USER;

        $dbclient = $this->fetch_dynamoDBClient();
        $marshaler = new Marshaler();
        $tablename = 'poodll_jobs';
        $itemarray = Array();
        $itemarray['host'] = $host;
        $itemarray['filename'] = $s3outfilename;
        $itemarray['appid'] = $appid;
        $itemarray['owner'] = $owner;
        $itemarray['clientid'] = $USER->username;
        $itemarray['transcode'] = $transcode ? 'yes' : 'no';
        $itemarray['transcoder'] = $transcoder;
        if ($transcribe) {
            switch ($transcribe) {
                case 1:
                    $itemarray['transcribe'] = 'amazontranscribe';
                    break;
                case 2:
                    $itemarray['transcribe'] = 'googlecloudspeech';
                    break;
                default:
                    $itemarray['transcribe'] = 'no';
            }
        } else {
            $itemarray['transcribe'] = 'no';
        }
        $itemarray['subtitle'] = $subtitle ? 'yes' : 'no';
        $itemarray['language'] = $language;
        $itemarray['vocab'] = $vocab;
        $itemarray['s3path'] = $s3path;
        $itemarray['mediatype'] = $mediatype;
        $itemarray['timecreated'] = date("Y-m-d H:i:s");
        $itemarray['expiretime'] = strtotime('+24 hours');
        $itemarray['notificationurl'] = $notificationurl;
        $itemarray['sourcemimetype'] = $sourcemimetype;

        try {
            $dbclient->putItem([
                    'TableName' => $tablename,
                    'Item' => $marshaler->marshalItem($itemarray)
            ]);
            return true;
        } catch (\Exception $e) {
            echo "Unable to put item:\n";
            echo $e->getMessage() . "\n";
            return $e->getMessage();
        }
    }



    function fetch_pollyspeech($text, $texttype = "text", $voice = "Justin") {
        $params = $this->make_pollyparams($text, $texttype, $voice);
        $pollyclient = $this->fetch_pollyclient();
      //  $preSignedURL = $pollyclient->createSynthesizeSpeechPreSignedUrl($params);
        return $pollyclient->synthesizeSpeech($params);
    }

    /**
     *
     *
     *  COMMON CODE STARTS HERE
     *
     */

    function does_file_exist($mediatype, $filename, $in_out = 'in') {
        $s3client = $this->fetch_s3client();
        $bucket = '';
        switch ($mediatype) {
            case 'audio':
                if ($in_out == 'out') {
                    $bucket = $this->bucket_audio_out;
                } else {
                    $bucket = $this->bucket_audio_in;
                }
                break;

            case 'video':
                if ($in_out == 'out') {
                    $bucket = $this->bucket_video_out;
                } else {
                    $bucket = $this->bucket_video_in;
                }
                break;
        }
        return $s3client->doesObjectExist($bucket, $filename);
    }


    //fetch or create the S3 object 
    function fetch_s3client() {
        global $CFG;

        if (!$this->s3client) {
            $config = array();
            $config['region'] = $this->region;
            $config['version'] = '2006-03-01';
            $config['credentials'] = array('key' => $this->accesskey, 'secret' => $this->secretkey);
            //add proxy settings if necessary
            if (!empty($CFG->proxyhost)) {
                $proxy = $CFG->proxytype . '://' . $CFG->proxyhost;
                if ($CFG->proxyport > 0) {
                    $proxy = $proxy . ':' . $CFG->proxyport;
                }
                if (!empty($CFG->proxyuser)) {
                    $proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
                }
                $config['http'] = array('proxy' => $proxy);

            }
            $this->s3client = S3Client::factory($config);
        }
        return $this->s3client;
    }

    function get_presigned_upload_url($mediatype, $minutes, $key, $iosvideo = false) {
        $s3client = $this->fetch_s3client();
        if(!$minutes){$minutes=30;}
        //Get bucket
        $bucket = '';
        switch ($mediatype) {
            case 'audio':
                $bucket = $this->bucket_audio_in;
                break;
            case 'video':
                $bucket = $this->bucket_video_in;
                break;
        }
        //options
        $options = array();
        $options['Bucket'] = $bucket;
        $options['Key'] = $key;
        $options['Body'] = '';
        //$options['ContentMD5']=false;
        if ($iosvideo) {
            $options['ContentType'] = 'video/quicktime';
        } else {
            $options['ContentType'] = 'application/octet-stream';
        }

        //we will use accelerated uploads for video in eu-west-1 ap-northeast-1 us-east-1 and ap-southeast-2
        if($mediatype=='video'){
            switch($this->region){
                //some regions do not support accelerated upload ...sad
                case self::REGION_AFS1:
                case self::REGION_MES1:
                    $options['@use_accelerate_endpoint'] = false;
                    break;

                case self::REGION_EUW1:
                case self::REGION_EUW2:
                case self::REGION_APN1:
                case self::REGION_USE1:
                case self::REGION_APSE2:
                case self::REGION_APSE1:
                case self::REGION_EUC1:
                case self::REGION_SAE1:
                case self::REGION_APS1:
                case self::REGION_CAC1:
                default:
                    $options['@use_accelerate_endpoint'] = true;
                    break;
            }
        }

        $cmd = $s3client->getCommand('PutObject', $options);

        //this can fail with SDK loading issues we return an error message in that case
        try {
            $request = $s3client->createPresignedRequest($cmd, '+' . $minutes . ' minutes');
            $theurl = (string) $request->getUri();
        } catch (\Exception $e) {
            print_error($e->getMessage());
            $theurl = $e->getMessage();
        }
        return $theurl;
    }

    //Called from CloudPoodll
    public function complete_multipart_upload($mediatype, $key, $uploadid){
        $s3client = $this->fetch_s3client();
        $ret= new \stdClass();
        $ret->success=true;
        $ret->payload='';

        //Get bucket
        $bucket = '';
        switch ($mediatype) {
            case 'audio':
                $bucket = $this->bucket_audio_in;
                break;
            case 'video':
                $bucket = $this->bucket_video_in;
                break;
        }

        //options
        $options = array();
        $options['Bucket'] = $bucket;
        $options['Key'] = $key;
        $options['UploadId'] = $uploadid;

        try {
            $partsModel = $s3client->listParts($options);
            $options['MultipartUpload'] =["Parts"=>$partsModel["Parts"]];
            $s3client->completeMultipartUpload($options);
        } catch (\Exception $e) {
            print_error($e->getMessage());
            $ret->success=false;
            $ret->payload= $e->getMessage();
        }
        return $ret;
    }

    //Called from CloudPoodll
    public function fetch_multipart_upload_details($mediatype, $minutes = 60, $key, $iosvideo = false) {
        $s3client = $this->fetch_s3client();

        //Get bucket
        $bucket = '';
        switch ($mediatype) {
            case 'audio':
                $bucket = $this->bucket_audio_in;
                break;
            case 'video':
                $bucket = $this->bucket_video_in;
                break;
        }


        //options
        $options = array();
        $options['Bucket'] = $bucket;
        $options['Key'] = $key;
        if ($iosvideo) {
            $options['ContentType'] = 'video/quicktime';
        } else {
            $options['ContentType'] = 'application/octet-stream';
        }

        //return etails + succes code
        $ret= new \stdClass();
        $ret->success=true;
        $ret->payload='';
        try {
            $ret->payload= $s3client->createMultipartUpload($options);
        } catch (\Exception $e) {
            print_error($e->getMessage());
            $ret->success=false;
            $ret->payload= $e->getMessage();
        }
        return $ret;
    }

    function fetch_singlepart_upload_url($mediatype,$key,$uploadid,$partnumber,$contentlength, $minutes = 30) {
        $s3client = $this->fetch_s3client();

        //Get bucket
        switch ($mediatype) {
            case 'video':
                $bucket = $this->bucket_video_in;
                break;
            case 'audio':
            default:
                $bucket = $this->bucket_audio_in;
                break;
        }

         //options
        $options = array();
        $options['Bucket'] = $bucket;
        $options['Key'] = $key;
        $options['UploadId'] = $uploadid;
        $options['PartNumber'] = $partnumber;
        $options['ContentLength'] = $contentlength;

        $cmd = $s3client->getCommand('PutObject', $options);
        //this can fail with SDK loading issues we return an error message in that case
        try {
           $request = $s3client->createPresignedRequest($cmd, '+' . $minutes . ' minutes');
           $theurl = (string) $request->getUri();
        } catch (\Exception $e) {
            print_error($e->getMessage());
            $theurl = $e->getMessage();
        }
        return $theurl;
    }


    function s3copy($sourcebucket, $sourceitemname, $targetbucket, $targetitemname, $ispublic = false) {
        $s3client = $this->fetch_s3client();

        //delete if it exists 
        if ($s3client->doesObjectExist($targetbucket, $targetitemname)) {
            echo "deleting";
            $this->s3remove($targetbucket, $targetitemname);
        }

        //do the copy 
        $acl = $ispublic ? 'public-read' : 'private';
        $s3client->copyObject(array(
                'Bucket' => $targetbucket,
                'Key' => $targetitemname,
                'CopySource' => "{$sourcebucket}/{$sourceitemname}",
                'ACL' => $acl
        ));
    }

    function s3remove($bucket, $itemname) {
        $s3client = $this->fetch_s3client();
        $s3client->deleteObject(array(
                'Bucket' => $bucket,
                'Key' => $itemname,
        ));
    }





    //Cloud and Classic Poodll
    function fetch_dynamoDBClient() {
        global $CFG;

        if (!$this->dynamodbclient) {
            $config = array();
            $config['region'] = $this->region;
            $config['version'] = 'latest';
            $config['credentials'] = array('key' => $this->accesskey, 'secret' => $this->secretkey);
            //add proxy settings if necessary
            if (!empty($CFG->proxyhost)) {
                $proxy = $CFG->proxytype . '://' . $CFG->proxyhost;
                if ($CFG->proxyport > 0) {
                    $proxy = $proxy . ':' . $CFG->proxyport;
                }
                if (!empty($CFG->proxyuser)) {
                    $proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
                }
                $config['http'] = array('proxy' => $proxy);

            }

            $this->dynamodbclient = DynamoDbClient::factory($config);
        }
        return $this->dynamodbclient;

    }

    //fetch or create the Transcribe object
    function fetch_transcribeclient() {
        global $CFG;

        if (!$this->transcribeclient) {
            $config = array();
            $config['region'] = $this->region;
            $config['version'] = '2017-10-26';
            $config['credentials'] = array('key' => $this->accesskey, 'secret' => $this->secretkey);
            //add proxy settings if necessary
            if (!empty($CFG->proxyhost)) {
                $proxy = $CFG->proxytype . '://' . $CFG->proxyhost;
                if ($CFG->proxyport > 0) {
                    $proxy = $proxy . ':' . $CFG->proxyport;
                }
                if (!empty($CFG->proxyuser)) {
                    $proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
                }
                $config['http'] = array('proxy' => $proxy);

            }

            $this->transcribeclient = TranscribeServiceClient::factory($config);
        }
        return $this->transcribeclient;
    }

    //fetch or create the Polly object
    function fetch_pollyclient() {
        global $CFG;

        if (!$this->pollyclient) {
            $config = array();
            $config['region'] = $this->region;
            $config['version'] = '2016-06-10';
            $config['credentials'] = array('key' => $this->accesskey, 'secret' => $this->secretkey);
            //add proxy settings if necessary
            if (!empty($CFG->proxyhost)) {
                $proxy = $CFG->proxytype . '://' . $CFG->proxyhost;
                if ($CFG->proxyport > 0) {
                    $proxy = $proxy . ':' . $CFG->proxyport;
                }
                if (!empty($CFG->proxyuser)) {
                    $proxy = $CFG->proxyuser . ':' . $CFG->proxypassword . '@' . $proxy;
                }
                $config['http'] = array('proxy' => $proxy);

            }
            $this->pollyclient = PollyClient::factory($config);
        }
        return $this->pollyclient;
    }

    function make_pollyparams($text, $texttype = "text", $voice = "Justin",$engine="standard",$output="mp3") {
        $params = [
                'OutputFormat' => $output,
                'Text' => $text,
                'TextType' => $texttype,
                'VoiceId' => $voice,
                'Engine' => $engine,
        ];
        if($output=='json'){
            $params['SpeechMarkTypes']=['sentence','word'];
        }
        return $params;
    }

}//end of class