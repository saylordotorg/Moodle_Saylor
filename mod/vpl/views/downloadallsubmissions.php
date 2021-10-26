<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Download all submissions of an activity in zip file
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

global $CFG, $USER;

require_once dirname(__FILE__).'/../../../config.php';
require_once dirname(__FILE__).'/../locallib.php';
require_once dirname(__FILE__).'/../vpl.class.php';
require_once dirname(__FILE__).'/../vpl_submission_CE.class.php';

function vpl_selzipdirname($name){
    static $names = null;
    if($names == null){
        $names = array();
    }
    //prepare name
    $name = trim($name);
    $name = iconv('UTF-8', 'ASCII//TRANSLIT',$name);
    $name = str_replace('?', '_', $name);
    $name = str_replace('.', ' ', $name);
    $name = str_replace(',', ' ', $name);
    //TODO Select by plugin configuration if it must reduced to initials or not the names
    $ret = '';
    $word = false;
    for($i=0; $i < strlen($name); $i++){
        $c = $name[$i];
        if($c != ' ' && !$word){
            $ret .= strtoupper($c);
        }
        if($c != ' '){
            $word = true;
        }else{
            $word = false;
        }
    }
    if(isset($names[$ret])){
        $names[$ret]++;
        $ret .= $names[$ret];
    }else{
        $names[$ret]=0;
    }
    return $ret;
}

require_login();
$id = required_param('id', PARAM_INT);
$group = optional_param('group', -1, PARAM_INT);
//Undocumented feature, add &CE=1 to the query string
$includeCE = optional_param('CE', 0, PARAM_INT);
$subselection = vpl_get_set_session_var('subselection','allsubmissions','selection');
$vpl = new mod_vpl($id);
$cm = $vpl->get_course_module();
$vpl->require_capability(VPL_SIMILARITY_CAPABILITY);
\mod_vpl\event\vpl_all_submissions_downloaded::log($vpl);
//get students
$currentgroup = groups_get_activity_group($cm);
if(!$currentgroup){
    $currentgroup='';
}
$list = $vpl->get_students($currentgroup);
$submissions = $vpl->all_last_user_submission();
//Get all information
$all_data = array();
foreach ($list as $userinfo) {
    if($vpl->is_group_activity() && $userinfo->id != $vpl->get_group_leaderid($userinfo->id)){
        continue;
    }
    $submission = null;
    if(!isset($submissions[$userinfo->id])){
        continue;
    }
    else{
        $subinstance = $submissions[$userinfo->id];
        $submission = new mod_vpl_submission_CE($vpl,$subinstance);
    }
    $data = new stdClass();
    $data->userinfo = $userinfo;
    $data->submission = $submission;
    //When group activity => change leader object lastname to groupname for order porpouse
    if($vpl->is_group_activity()){
        $data->userinfo->firstname = '';
        $data->userinfo->lastname = $vpl->fullname($userinfo);
    }
    $all_data[] = $data;
}
//Unblock user session
session_write_close();
$zip = new ZipArchive();
$zipfilename=tempnam($CFG->dataroot . '/temp/'  , 'vpl_zipdownloadall' );
if($zip->open($zipfilename,ZIPARCHIVE::CREATE)){
    foreach ($all_data as $data){
        $user = $data->userinfo;
        $fgm = $data->submission->get_submitted_fgm();
        $zipdirname = vpl_selzipdirname($user->lastname.' '.$user->firstname);
        //Create directory
        $zip->addEmptyDir($zipdirname);
        $zipdirname .= '/';
        $sourcedir=$data->submission->get_submission_directory().'/';
        foreach ($fgm->getFileList() as $filename) {
            $source= file_group_process::encodeFileName($filename);
            $zip->addFile($sourcedir.$source,$zipdirname.$filename);
        }
        if($includeCE){
            $CE=$data->submission->getCE();
            if(!($CE['compilation']===0)){
                $zip->addFromString($zipdirname.'vpl_data/compilation.txt',$CE['compilation']);
                if($CE['executed']){
                    $zip->addFromString($zipdirname.'vpl_data/execution.txt',$CE['execution']);
                }
            }
        }
    }
    $zip->close();
    //Get zip data
    $data=file_get_contents($zipfilename);
    //remove zip file
    unlink($zipfilename);
    $name = $vpl->get_instance()->name;
    //Send zipdata
    @header('Content-Length: '.strlen($data));
    @header('Content-Disposition: attachment; filename="'.$name.'.zip"');
    @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    @header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
    @header('Pragma: no-cache');
    @header('Accept-Ranges: none');
    echo $data;
}
