<?php

/**
 * internal library of functions and constants for Poodll modules
 * accessed directly by poodll flash wdgets on web pages.
 *
 * @package filter_poodll
 * @category mod
 * @author Justin Hunt
 *
 */

/**
 * Includes and requires
 */

global $CFG;

//we need to do this, because when called from a widet, cfg is not set
//but the relative path fails from a quiz but it has alreadconvert_with_ffmpeg_bgy been set in that case
//, so we check before we call it, to cover both httpsbases

if (!isset($CFG)) {
    require_once("../../config.php");
}

use mod_solo\constants;

require_login();


//we do file operations here, so we need this
require_once($CFG->libdir . '/filelib.php');

$datatype = optional_param('datatype', "", PARAM_TEXT);
$filedata = optional_param('filedata', "", PARAM_RAW);
$recorderid = optional_param('id', "", PARAM_TEXT);
$posturl = optional_param('posturl', "", PARAM_TEXT);


switch ($datatype) {
    case "register":
        register_upload_details($recorderid,$posturl);
        break;

    case "upload":
    default:
        post_file_data($recorderid,$filedata);
        break;

}//end of switch

return;



//this turns our results array into an xml string for returning to browser
function register_upload_details($recorderid,$posturl) {
    global $CFG, $USER;

    //Fetch from cache and process the results and display
    $cache = \cache::make_from_params(\cache_store::MODE_SESSION, constants::M_COMPONENT, 'upload_details');
    //$cache->set($recorderid . '_posturl', $posturl);
    $cache->set('posturl', $posturl);
}



//For uploading a file direct from an HTML5 or SWF widget
function post_file_data($recorderid, $filedata) {
    global $CFG, $USER;
    $cache = \cache::make_from_params(\cache_store::MODE_SESSION, constants::M_COMPONENT, 'upload_details');
    //$posturl = $cache->get($recorderid .'_posturl');
    $posturl = $cache->get('posturl');
    if(!$posturl) {return;}


    $handle = fopen('php://input', 'r');
    $postdata = '';
    while ($chunk = fread($handle, 1024)) {
        $postdata .= $chunk;
    }
    if(!$postdata) { return;}
    $ret = curl_put($posturl,$postdata);
}

//we use curl
 function curl_put($url, $postdata) {
    global $CFG;

     //determine the temp directory
     $tempdir = $CFG->tempdir . "/";
     $filename = constants::M_RECORDERID . random_string(8);
     $filepath= $tempdir . $filename;
     $ret = file_put_contents($filepath, $postdata);

     if($ret) {
         //Get CURL and prepare it.
         require_once($CFG->libdir . '/filelib.php');
         $curl = new \curl();
         $params = array();
         $params['file']=$filepath;

         $options = array();
         $options['CURLOPT_USERPWD'] = null;

         $result = $curl->put($url, $params, $options);
         if (empty($result)) {
             $result = true;
         }
         return $result;
     }
}
