<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace filter_poodll\output;

use renderable;


class renderer extends \plugin_renderer_base implements renderable {


    /*
     * Takes data from webservice about usage and renders it on page
     */

    public function display_usage_report($usagedata){
        $reportdata=[];

        $mysubscriptions = array();
        $mysubscription_name_txt = array();
        $mysubscriptions_names = array();

        if($usagedata->usersubs) {
            foreach ($usagedata->usersubs as $subdata) {
                $subscription_name = ($subdata->subscriptionname == ' ') ? "na" : strtolower(trim($subdata->subscriptionname));
                $mysubscription_name_txt[] = $subscription_name;
                $mysubscriptions_names[] = $subscription_name;
                $mysubscriptions[] = array('name' => $subscription_name,
                        'start_date' => date("m-d-Y", $subdata->timemodified),
                        'end_date' => date("m-d-Y", $subdata->expiredate));
            }
        }//end of if user subs

        $reportdata['subscription_check'] = false;
        if(count($mysubscriptions)>0){
            $reportdata['subscription_check']= true;
        } else {
            $reportdata['subscription_check']= false;
        }

        $reportdata['subscriptions']=$mysubscriptions;
        $reportdata['pusers']=array();
        $reportdata['record']=array();
        $reportdata['recordmin']=array();
        $reportdata['recordtype']=array();

        $threesixtyfive_recordtype_video = 0;
        $oneeighty_recordtype_video = 0;
        $ninety_recordtype_video = 0;
        $thirty_recordtype_video = 0;

        $threesixtyfive_recordtype_audio = 0;
        $oneeighty_recordtype_audio = 0;
        $ninety_recordtype_audio = 0;
        $thirty_recordtype_audio = 0;

        $threesixtyfive_recordmin = 0;
        $oneeighty_recordmin = 0;
        $ninety_recordmin = 0;
        $thirty_recordmin = 0;

        $threesixtyfive_record = 0;
        $oneeighty_record = 0;
        $ninety_record = 0;
        $thirty_record = 0;

        $threesixtyfive_puser = 0;
        $oneeighty_puser = 0;
        $ninety_puser = 0;
        $thirty_puser = 0;

        //monthlymax
        $monthusertotals=[0,0,0,0,0,0,0,0,0,0,0,0];
        $monthpusers=['','','','','','','','','','','',''];
        $monthminutetotals=[0,0,0,0,0,0,0,0,0,0,0,0];
        $monthrecordtotals=[0,0,0,0,0,0,0,0,0,0,0,0];
        $monthaudiototals=[0,0,0,0,0,0,0,0,0,0,0,0];
        $monthvideototals=[0,0,0,0,0,0,0,0,0,0,0,0];

        $plugin_types_arr = "[";

        if($usagedata->usersubs_details) {
            foreach ($usagedata->usersubs_details as $subdatadetails) {

                $timecreated = $subdatadetails->timecreated;


                for($x=0;$x<12;$x++){
                    $upperdays=-1 * $x * 30 . ' days';
                    $lowerdays=-1 * ($x+1) * 30 . ' days';
                    if (($timecreated <= strtotime($upperdays)) && ($timecreated > strtotime($lowerdays) )) {
                        $monthminutetotals[$x] = $monthminutetotals[$x] + ($subdatadetails->audio_min + $subdatadetails->video_min);
                        $monthaudiototals[$x] = $monthaudiototals[$x] + $subdatadetails->audio_file_count;
                        $monthvideototals[$x] = $monthvideototals[$x] + $subdatadetails->video_file_count;
                        $monthrecordtotals[$x] = $monthrecordtotals[$x] + $subdatadetails->video_file_count + $subdatadetails->audio_file_count;
                        $monthvideototals[$x] = $monthvideototals[$x] + $subdatadetails->video_min;
                        $monthpusers[$x] = $monthpusers[$x] .= $subdatadetails->pusers;

                    }
                }

                //if(($timecreated > strtotime('-180 days'))&&($timecreated <= strtotime('-365 days'))) {
                if (($timecreated >= strtotime('-365 days'))) {
                    $threesixtyfive_recordtype_video += $subdatadetails->video_file_count;
                    $threesixtyfive_recordtype_audio += $subdatadetails->audio_file_count;
                    $threesixtyfive_recordmin += ($subdatadetails->audio_min + $subdatadetails->video_min);
                    $threesixtyfive_record += ($subdatadetails->video_file_count + $subdatadetails->audio_file_count);
                    $threesixtyfive_puser .= $subdatadetails->pusers;
                }

                //if(($timecreated > strtotime('-90 days'))&&($timecreated <= strtotime('-180 days'))){
                if (($timecreated >= strtotime('-180 days'))) {
                    $oneeighty_recordtype_video += $subdatadetails->video_file_count;
                    $oneeighty_recordtype_audio += $subdatadetails->audio_file_count;
                    $oneeighty_recordmin += ($subdatadetails->audio_min + $subdatadetails->video_min);
                    $oneeighty_record += ($subdatadetails->video_file_count + $subdatadetails->audio_file_count);
                    $oneeighty_puser .= $subdatadetails->pusers;
                }

                //if(($timecreated > strtotime('-30 days'))&&($timecreated <= strtotime('-90 days'))){
                if (($timecreated >= strtotime('-90 days'))) {
                    $ninety_recordtype_video += $subdatadetails->video_file_count;
                    $ninety_recordtype_audio += $subdatadetails->audio_file_count;
                    $ninety_recordmin += ($subdatadetails->audio_min + $subdatadetails->video_min);
                    $ninety_record += ($subdatadetails->video_file_count + $subdatadetails->audio_file_count);
                    $ninety_puser .= $subdatadetails->pusers;
                }

                if ($timecreated >= strtotime('-30 days')) {
                    $thirty_recordtype_video += $subdatadetails->video_file_count;
                    $thirty_recordtype_audio += $subdatadetails->audio_file_count;
                    $thirty_recordmin += ($subdatadetails->audio_min + $subdatadetails->video_min);
                    $thirty_record += ($subdatadetails->video_file_count + $subdatadetails->audio_file_count);
                    $thirty_puser .= $subdatadetails->pusers;
                }

            }//end of for loop
        }//end of if usagedata

        //calc max month totals
        $maxmonth_pusers = 0;
        $maxmonth_minutes = 0;
        $maxmonth_audio = 0;
        $maxmonth_video = 0;
        $maxmonth_recordings = 0;
        for($x=0;$x<12;$x++){
            $monthusertotals[$x]=$this->count_pusers($monthpusers[$x]);
            if($maxmonth_pusers<$monthusertotals[$x]){$maxmonth_pusers=$monthusertotals[$x];}
            if($maxmonth_minutes<$monthminutetotals[$x]){$maxmonth_minutes=$monthminutetotals[$x];}
            if($maxmonth_audio<$monthaudiototals[$x]){$maxmonth_audio=$monthaudiototals[$x];}
            if($maxmonth_video<$monthvideototals[$x]){$maxmonth_video=$monthvideototals[$x];}
            if($maxmonth_recordings<$monthrecordtotals[$x]){$maxmonth_recordings=$monthrecordtotals[$x];}
        }

        //calculate report summaries
        $reportdata['pusers']=array_values(array(
                array('name'=>'30','value'=>$this->count_pusers($thirty_puser)),
                array('name'=>'90','value'=>$this->count_pusers($ninety_puser)),
                array('name'=>'180','value'=>$this->count_pusers($oneeighty_puser)),
                array('name'=>'365','value'=>$this->count_pusers($threesixtyfive_puser)),
                array('name'=>'maxmonth','value'=>$maxmonth_pusers)
        ));

        $reportdata['record']=array_values(array(
                array('name'=>'30','value'=>$thirty_record),
                array('name'=>'90','value'=>$ninety_record),
                array('name'=>'180','value'=>$oneeighty_record),
                array('name'=>'365','value'=>$threesixtyfive_record),
                array('name'=>'maxmonth','value'=>$maxmonth_recordings)
        ));

        $reportdata['recordmin']=array_values(array(
                array('name'=>'30','value'=>$thirty_recordmin),
                array('name'=>'90','value'=>$ninety_recordmin),
                array('name'=>'180','value'=>$oneeighty_recordmin),
                array('name'=>'365','value'=>$threesixtyfive_recordmin),
                array('name'=>'maxmonth','value'=>$maxmonth_minutes)
        ));

        $reportdata['recordtype']=array_values(array(
                array('name'=>'30','video'=>$thirty_recordtype_video,'audio'=>$thirty_recordtype_audio),
                array('name'=>'90','video'=>$ninety_recordtype_video,'audio'=>$ninety_recordtype_audio),
                array('name'=>'180','video'=>$oneeighty_recordtype_video,'audio'=>$oneeighty_recordtype_audio),
                array('name'=>'365','video'=>$threesixtyfive_recordtype_video,'audio'=>$threesixtyfive_recordtype_audio),
                array('name'=>'maxmonth','video'=>$maxmonth_video,'audio'=>$maxmonth_audio),
        ));

        $plugin_types_arr = [];

        if($usagedata->usersubs_details) {
            foreach ($usagedata->usersubs_details as $subdatadetails) {
                $json_arr = json_decode($subdatadetails->file_by_app, true);
                foreach ($json_arr as $key => $val) {
                    $label = $key;
                    $val = $json_arr[$key]['audio'] + $json_arr[$key]['video'];
                    if (isset($plugin_types_arr[$label])) {
                        $plugin_types_arr[$label] += $val;
                    } else {
                        $plugin_types_arr[$label] = $val;
                    }
                }
            }
        }//end of if usersubs details

        echo $this->output->render_from_template('filter_poodll/mysubscriptionreport', $reportdata);

        if ($reportdata['subscription_check'] == true){
            $plugin_types = new \core\chart_series('Plugin Usage', array_values($plugin_types_arr));
            $pchart = new \core\chart_pie();
            $pchart->add_series($plugin_types);
            $pchart->set_labels(array_keys($plugin_types_arr));
            echo $this->output->heading(get_string('per_plugin', 'filter_poodll'), 4);
            echo $this->output->render($pchart);
        }
    }

    /*
    * Count the unique users from CSV list of users. Used by Display usage repor
    *
    */
    public function count_pusers($pusers){
        $pusers=trim($pusers);
        return count(array_unique(explode(',',$pusers)));

    }

    public function fetchLiterallyCanvas($html) {
        global $PAGE;
        //The strings we need for js
        $PAGE->requires->strings_for_js(array('insert',
                'cancel',
                'recui_record',
                'recui_recordorchoose',
                'recui_pause',
                'recui_play',
                'recui_stop',
                'recui_save',
                'recui_upload',
                'recui_testmic',
                'recui_recordagain',
                'recui_readytorecord',
                'recui_continue',
                'recui_uploading',
                'recui_converting',
                'recui_uploading',
                'recui_uploadafile',
                'recui_uploadsuccess',
                'recui_openrecorderapp',
                'recui_awaitingconfirmation',
                'recui_uploaderror',
                'recui_takesnapshot',
                'recui_cancelsnapshot',
                'recui_nothingtosaveerror',
        ),
                'filter_poodll');
        return $html;

    }

    public function fetchDrawingBoard($html) {
        global $PAGE;
        //The strings we need for js

        $PAGE->requires->strings_for_js(array('insert',
                'cancel',
                'recui_record',
                'recui_restart',
                'recui_recordorchoose',
                'recui_pause',
                'recui_play',
                'recui_stop',
                'recui_save',
                'recui_continue',
                'recui_uploading',
                'recui_converting',
                'recui_uploading',
                'recui_uploadafile',
                'recui_uploadsuccess',
                'recui_openrecorderapp',
                'recui_awaitingconfirmation',
                'recui_uploaderror',
                'recui_takesnapshot',
                'recui_cancelsnapshot',
                'recui_nothingtosaveerror',
        ),
                'filter_poodll');
        return $html;

    }

    public function fetchAudioPlayer($html) {
        return $html;

    }

    public function fetchVideoPlayer($html) {
        return $html;

    }

    public function fetchIFrameSWFWidgetCode($widget, $paramsArray, $width, $height, $bgcolor = "#FFFFFF") {
        global $CFG;

        //There seems to be an internal margin on the iframe
        //which I could not cancel entirely. So we compensate here to show all the widget
        $marginadjust = 5;
        $fwidth = $marginadjust + $width;
        $fheight = $marginadjust + $height;

        //build the parameter string out of the passed in array
        $params = "?";
        foreach ($paramsArray as $key => $value) {
            $params .= '&' . $key . '=' . $value;
        }

        //add in any common params
        $params .= '&debug=false&lzproxied=false';

        //path to our js idgets folder
        $pathtoSWF = $CFG->wwwroot . '/filter/poodll/flash/';

        $retframe =
                "<iframe scrolling=\"no\" class=\"fitvidsignore\" frameBorder=\"0\" src=\"{$pathtoSWF}poodlliframe.php?widget={$widget}&paramstring=" .
                urlencode($params) .
                "&width={$width}&height={$height}&bgcolor={$bgcolor}\" width=\"{$fwidth}\" height=\"{$fheight}\"></iframe>";
        return $retframe;
    }

    public function fetchJSWidgetiFrame($widget, $rawparams, $width, $height, $bgcolor = "#FFFFFF", $usemastersprite = "false") {
        global $CFG;

        //build the parameter string out of the passed in array
        $params = "?";
        foreach ($rawparams as $key => $value) {
            $params .= '&' . $key . '=' . $value;
        }

        //add in any common params
        $params .= '&debug=false&lzproxied=false';

        //path to our js idgets folder
        $pathtoJS = $CFG->wwwroot . '/filter/poodll/js/';
        $pathtowidgetfolder = $CFG->wwwroot . '/filter/poodll/js/' . $widget . '/';

        $retframe = "<iframe scrolling=\"no\" frameBorder=\"0\" src=\"{$pathtoJS}poodlliframe.php?widget={$widget}&paramstring=" .
                urlencode($params) .
                "&width={$width}&height={$height}&bgcolor={$bgcolor}&usemastersprite={$usemastersprite}\" width=\"{$width}\" height=\"{$height}\"></iframe>";
        return $retframe;
    }

    /* TO DO: make this more generic. ie not just poodllrecorder */
    public function fetchAMDRecorderEmbedCode($widgetopts, $widgetid) {
        global $CFG, $PAGE;

        $widgetopts->widgetid = $widgetid;

        //The CSS selector string
        $container = $widgetid . 'Container';
        $selector = '#' . $container;
        $widgetopts->selector = $selector;

        //The strings we need for js
        $PAGE->requires->strings_for_js(array('insert',
                'cancel',
                'recui_finished',
                'recui_ready',
                'recui_playing',
                'recui_recording',
                'recui_record',
                'recui_restart',
                'recui_recordorchoose',
                'recui_pause',
                'recui_play',
                'recui_stop',
                'recui_save',
                'recui_continue',
                'recui_uploading',
                'recui_converting',
                'recui_uploading',
                'recui_uploadafile',
                'recui_downloadfile',
                'recui_uploadsuccess',
                'recui_awaitingconversion',
                'recui_openrecorderapp',
                'recui_awaitingconfirmation',
                'recui_uploaderror',
                'recui_nothingtosaveerror',
                'recui_takesnapshot',
                'recui_cancelsnapshot',
                'recui_pushtospeak',
                'recui_waitwaitstilluploading',
                'recui_upload',
                'recui_testmic',
                'recui_recordagain',
                'recui_readytorecord',
                'recui_clicktofinish',
            //media errors
                'recui_mediaaborterror',
                'recui_medianotallowederror',
                'recui_medianotfounderror',
                'recui_medianotreadableerror',
                'recui_medianotsupportederror',
                'recui_mediaoverconstrainederror',
                'recui_mediasecurityerror',
                'recui_mediatypeerror',
                'recui_unsupportedbrowser',
                'recui_choosefile'
        ),
                'filter_poodll');

        //convert opts to json
        $jsonstring = json_encode($widgetopts);
        //we put the opts in html on the page because moodle/AMD doesn't like lots of opts in js
        $opts_html = \html_writer::tag('input', '',
                array('id' => 'amdopts_' . $widgetopts->widgetid, 'type' => 'hidden', 'value' => $jsonstring));
        $PAGE->requires->js_call_amd("filter_poodll/poodllrecorder", 'init', array(array('widgetid' => $widgetid)));
        $returnhtml = $opts_html . \html_writer::div('', 'filter_poodll_recorder_placeholder', array('id' => $container));
        return $returnhtml;
    }

    //This is used for all the flash widgets
    public function fetchLazloEmbedCode($widgetopts, $widgetid, $jsmodule) {
        global $CFG, $PAGE;
        echo "You should not get here.";
        die;
    }

    public function fetchTemplateSelector($conf, $templatecount) {
        global $CFG, $OUTPUT;
        $options = Array();
        for ($i = 1; $i <= $templatecount; $i++) {
            $options['filter_poodll_templatepage_' . $i] = $conf->{'templatename_' . $i};
        }
        // $options = array(1 => 'Page 1', 2 => 'Page 2', 3 => 'Page 3');
        $select = $OUTPUT->single_select($CFG->wwwroot . '/admin/settings.php', 'section', $options, 'template_selector');
        echo $select;
    }
}