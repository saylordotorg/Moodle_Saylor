<?php
/**
 * Renderer.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

/**
 * Renderer class.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

namespace mod_wordcards\output;

use mod_wordcards\utils;
use mod_wordcards\constants;

class renderer extends \plugin_renderer_base {

    public function definitions_page(\mod_wordcards_module $mod) {
        global $PAGE, $OUTPUT,$USER;

        $definitions = $mod->get_terms();
        if (empty($definitions)) {
            return $OUTPUT->notification(get_string('nodefinitions', 'mod_wordcards'));
        }

        //make sure each definition has a voice
        foreach($definitions as $def){
            if($def->ttsvoice=='Auto' || $def->ttsvoice==''){
                $def->ttsvoice = utils::fetch_auto_voice($mod->get_mod()->ttslanguage);
            }
        }


        // Get whe the student has seen.
        $seen = $mod->get_terms_seen();
        foreach ($seen as $s) {
            if (!isset($definitions[$s->termid])) {
                // Shouldn't happen.
                continue;
            }
            $definitions[$s->termid]->seen = true;
        }

        //attempt info
        $canattempt=$mod->can_attempt();
        $attempts = $mod->get_attempts();
        $canmanage=$mod->can_manage();
        if($attempts){
            $attemptcount=count($attempts);
        }else{
            $attemptcount=0;
        }
        $maxattempts=$mod->get_mod()->maxattempts;
        $isreattempt = $attemptcount>0 && $canattempt && !$canmanage;
        if($isreattempt){
            $nextaction='reattempt';
            $reattempt=1;
            $nextbuttontext=get_string('reattempt',constants::M_COMPONENT);
        }elseif($attemptcount==0 || $canattempt){
            $nextaction='attempt';
            $reattempt=0;
            $nextbuttontext=get_string('continue',constants::M_COMPONENT);
        }else{
            $nextaction='none';
            $reattempt=0;
            $nextbuttontext=get_string('continue',constants::M_COMPONENT);
        }


        //config
        $config = get_config('mod_wordcards');
        $token = utils::fetch_token($config->apiuser, $config->apisecret);


        $data = [
            'uniqid'=> \html_writer::random_id('wordcards'),
            'canmanage' => $mod->can_manage(),
            'canattempt'=>$canattempt,
            'attemptcount'=>$attemptcount,
            'maxattempts'=>$maxattempts,
            'nextaction'=>$nextaction,
            'nextbuttontext'=>$nextbuttontext,
            'isreattempt'=>$isreattempt,
            'str_definition' => get_string('definition', 'mod_wordcards'),
            'definitions' => array_values($definitions),
            'gotit' => get_string('gotit', 'mod_wordcards'),
            'loading' => get_string('loading', 'mod_wordcards'),
            'loadingurl' => $this->image_url('i/loading_small')->out(true),
            'markasseen' => get_string('markasseen', 'mod_wordcards'),
            'modid' => $mod->get_id(),
            'mustseealltocontinue' => get_string('mustseealltocontinue', 'mod_wordcards'),
            'nexturl' => (new \moodle_url('/mod/wordcards/activity.php', ['id' => $mod->get_cmid(), 'state'=>\mod_wordcards_module::STATE_STEP1,'reattempt'=>$reattempt]))->out(true),
            'noteaboutseenforteachers' => get_string('noteaboutseenforteachers', 'mod_wordcards'),
            'notseenurl' => $this->image_url('not-seen', 'mod_wordcards')->out(true),
            'definition_grid' => $this->image_url('grid', 'mod_wordcards')->out(true),
            'definition_flashcards' => $this->image_url('flashcards', 'mod_wordcards')->out(true),
            'seenall' => count($definitions) == count($seen),
            'seenurl' => $this->image_url('seen', 'mod_wordcards')->out(true),
            'str_term' => get_string('term', 'mod_wordcards'),
            'termnotseen' => get_string('termnotseen', 'mod_wordcards'),
            'termseen' => get_string('termseen', 'mod_wordcards'),
            'token'=>$token,
            'region'=>$config->awsregion,
            'owner'=>hash('md5',$USER->username)
        ];


        $jsonstring=json_encode($data);
        $opts_html = \html_writer::tag('input', '', array('id' => $data['uniqid'], 'type' => 'hidden', 'value' => $jsonstring));
        $jsdata=array('widgetid'=> $data['uniqid']);
        $this->page->requires->js_call_amd("mod_wordcards/definitions", 'init', array($jsdata));

        return  $opts_html . $this->render_from_template('mod_wordcards/definitions_page', $data);
    }


    private function make_json_string($definitions,$mod){

        $defs = array();
        foreach ($definitions as $definition){
            $def = new \stdClass();
            $def->image=$definition->image;
            $def->audio=$definition->audio;
            $def->alternates=$definition->alternates;
            $def->ttsvoice=$definition->ttsvoice;
            $def->id=$definition->id;
            $def->term =$definition->term;
            $def->definition =$definition->definition;
            if($mod->get_mod()->showimageflip){
                $def->showimageflip=true;
            }
            //which face to tag as front and which as back
            if($mod->get_mod()->frontfaceflip == constants::M_FRONTFACEFLIP_DEF) {
                $def->frontfacedef = true;
            }
            $defs[]=$def;
        }
        $defs_object = new \stdClass();
        $defs_object->terms = $defs;
        return json_encode($defs_object);
    }


    public function a4e_page(\mod_wordcards_module $mod, $practicetype, $wordpool, $currentstep ) {
        global $USER, $PAGE, $OUTPUT;

        //config
        $config = get_config('mod_wordcards');

        //get state
        list($state) = $mod->get_state();

        //if we are in review state, we use different words
        if($wordpool == \mod_wordcards_module::WORDPOOL_REVIEW) {
            $definitions = $mod->get_review_terms($currentstep);
        }else{
            $definitions = $mod->get_learn_terms($currentstep);
        }

        //make sure each definition has a voice
        foreach($definitions as $def){
            if($def->ttsvoice=='Auto' || $def->ttsvoice==''){
                $def->ttsvoice = utils::fetch_auto_voice($mod->get_mod()->ttslanguage);
            }
        }

        $widgetid = \html_writer::random_id();
        $jsonstring=$this->make_json_string($definitions, $mod);
        $opts_html = \html_writer::tag('input', '', array('id' => $widgetid, 'type' => 'hidden', 'value' => $jsonstring));


    $nextstep = $mod->get_next_step($currentstep);
    $nexturl =  (new \moodle_url('/mod/wordcards/activity.php', ['id' => $mod->get_cmid(),'oldstep'=>$currentstep,'nextstep'=>$nextstep]))->out(true);
    $token = utils::fetch_token($config->apiuser, $config->apisecret);

        $opts=array('widgetid'=>$widgetid,'ttslanguage'=>$mod->get_mod()->ttslanguage,
                'dryRun'=> $mod->can_manage(),'nexturl'=>$nexturl, 'region'=>$config->awsregion,
                'token'=>$token,'owner'=>hash('md5',$USER->username),'modid'=>$mod->get_id());

        $data = [];
        switch($practicetype){
            case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT:
            case \mod_wordcards_module::PRACTICETYPE_MATCHSELECT_REV:
                $this->page->requires->js_call_amd("mod_wordcards/matchselect", 'init', array($opts));
                $activity_html = $this->render_from_template('mod_wordcards/matchselect_page', $data);
                break;
            case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE:
            case \mod_wordcards_module::PRACTICETYPE_MATCHTYPE_REV:
                $this->page->requires->js_call_amd("mod_wordcards/matchtype", 'init', array($opts));
                $activity_html = $this->render_from_template('mod_wordcards/matchtype_page', $data);
                break;
            case \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE:
            case \mod_wordcards_module::PRACTICETYPE_LISTENCHOOSE_REV:
                $this->page->requires->js_call_amd("mod_wordcards/listenchoose", 'init', array($opts));
                $activity_html = $this->render_from_template('mod_wordcards/listenchoose_page', $data);
                break;
            case \mod_wordcards_module::PRACTICETYPE_DICTATION:
            case \mod_wordcards_module::PRACTICETYPE_DICTATION_REV:
            default:
                $this->page->requires->js_call_amd("mod_wordcards/dictation", 'init', array($opts));
                $activity_html = $this->render_from_template('mod_wordcards/dictation_page', $data);
        }

        return $opts_html . $activity_html;
    }

    public function finish_page(\mod_wordcards_module $mod) {

        $scattertimemsg = $mod->get_finishedstepmsg();
        //$scattertimemsg = str_replace('[[time]]', gmdate("i:s:00", $scattertime), $scattertimemsg);

        $data = [
            'canmanage' => $mod->can_manage(),
            'finishtext' => $scattertimemsg .  ' <br/> ' . $mod->get_completedmsg(),
            'modid' => $mod->get_id(),
        ];
        return $this->render_from_template('mod_wordcards/finish_page', $data);
    }

    public function push_recorder(){
        $data = [];
        return $this->render_from_template('mod_wordcards/pushrecorder', $data);
    }



    public function speechcards_page(\mod_wordcards_module $mod, $wordpool, $currentstep){
        global $CFG,$USER;

        //get state
        list($state) = $mod->get_state();

        //fitst confirm we have the cloud poodll token and can show the cards
        $api_user = get_config(constants::M_COMPONENT,'apiuser');
        $api_secret = get_config(constants::M_COMPONENT,'apisecret');
        $region = get_config(constants::M_COMPONENT,'awsregion');

        //check user has entered api credentials
        if(empty($api_user) || empty($api_secret)){
            $errormessage = get_string('nocredentials',constants::M_COMPONENT,
                    $CFG->wwwroot . constants::M_PLUGINSETTINGS);
            return ($this->show_problembox($errormessage));
        }else {
            $token = utils::fetch_token($api_user, $api_secret);

            //check token authenticated and no errors in it
            $errormessage = utils::fetch_token_error($token);
            if(!empty($errormessage)){
                return ($this->show_problembox($errormessage));
            }
        }

        //ok we now have a token and can continue to set up the cards
        $widgetid = \html_writer::random_id();

        //next url
        $nextstep = $mod->get_next_step($currentstep);
        $nexturl =  (new \moodle_url('/mod/wordcards/activity.php', ['id' => $mod->get_cmid(),'oldstep'=>$currentstep,'nextstep'=>$nextstep]))->out(true);

        //if we are in review state, we use different words and the next page is a finish page
        if($wordpool == \mod_wordcards_module::WORDPOOL_REVIEW) {
            $definitions = $mod->get_review_terms($currentstep);
        }else{
            $definitions = $mod->get_learn_terms($currentstep);

        }

        //make sure each definition has a voice
        foreach($definitions as $def){
            if($def->ttsvoice=='Auto' || $def->ttsvoice==''){
                $def->ttsvoice = utils::fetch_auto_voice($mod->get_mod()->ttslanguage);
            }
        }

        $jsonstring=$this->make_json_string($definitions,$mod);
        $opts_html = \html_writer::tag('input', '', array('id' => $widgetid, 'type' => 'hidden', 'value' => $jsonstring));


        $opts=array('widgetid'=>$widgetid,'dryRun'=> $mod->can_manage(),'nexturl'=>$nexturl);
        $opts['language']=$mod->get_mod()->ttslanguage;
        $opts['region']=$region;
        $opts['token']=$token;
        $opts['parent']=$CFG->wwwroot;
        $opts['owner']=hash('md5',$USER->username);
        $opts['appid']=constants::M_COMPONENT;
        $opts['modid']= $mod->get_id();
        $opts['expiretime']=300;//max expire time is 300 seconds

        if($mod->get_mod()->transcriber == constants::TRANSCRIBER_POODLL){
            //this will force browser recognition to use Poodll (not chrome or other browser speech)
            $opts['ds_only'] = true;
        }else {
            $opts['ds_only'] = false;
        }

        $this->page->requires->js_call_amd("mod_wordcards/speechcards", 'init', array($opts));

        //are we going to force streaning transcription from AWS only if its android
        $hints = new \stdClass();
        //$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        //if(stripos($ua,'android') !== false) {
        //    $hints->streamingtranscriber = 'aws';
        //}
        $string_hints = base64_encode(json_encode($hints));

        $data = [];
        $data['cloudpoodlltoken']=$token;
        $data['language']=$mod->get_mod()->ttslanguage;
        $data['wwwroot']=$CFG->wwwroot;
        $data['region']=$region;
        $data['hints']=$string_hints;
        $data['owner']=hash('md5',$USER->username);


        //TT Recorder ---------------
        $data['waveheight']= 75;
        $data['maxtime']= 15000;
        $data['data-id']='wordcards-speechcards_pushrecorder';
        //passagehash if not empty will be region|hash eg tokyo|2353531453415134545
        //but we only send the hash up so we strip the region
        $data['passagehash']="";
        if(!empty($mod->get_mod()->passagehash)){
            $hashbits = explode('|',$mod->get_mod()->passagehash);
            if(count($hashbits)==2){
                $data['passagehash']  = $hashbits[1];
            }
        }
        switch($region) {

            case 'useast1':
                $data['asrurl'] = 'https://useast.ls.poodll.com/transcribe';
                break;
            default:
                $data['asrurl'] = 'https://' . $region . '.ls.poodll.com/transcribe';

        }


        $speechcards = $this->render_from_template('mod_wordcards/speechcards_page', $data);
        return $opts_html . $speechcards;

    }

    public function scatter_page(\mod_wordcards_module $mod, $wordpool,$currentstep) {
        list($state) = $mod->get_state();

        $nextstep = $mod->get_next_step($currentstep);
        $nexturl =  (new \moodle_url('/mod/wordcards/activity.php', ['id' => $mod->get_cmid(),'oldstep'=>$currentstep,'nextstep'=>$nextstep]))->out(true);

        //if we are in review state, we use different words and the next page is a finish page
        if($wordpool == \mod_wordcards_module::WORDPOOL_REVIEW) {
            $definitions = $mod->get_review_terms($currentstep);
        }else{
            $definitions = $mod->get_learn_terms($currentstep);
        }

        //make sure each definition has a voice
        foreach($definitions as $def){
            if($def->ttsvoice=='Auto' || $def->ttsvoice==''){
                $def->ttsvoice = utils::fetch_auto_voice($mod->get_mod()->ttslanguage);
            }
        }

        $data = [
                'canmanage' => $mod->can_manage(),
                'continue' => get_string('continue'),
                'congrats' => get_string('congrats', 'mod_wordcards'),
                'definitionsjson' => json_encode(array_values($definitions)),
                'finishscatterin' => get_string('finishscatterin', 'mod_wordcards'),
                'finishedstepmsg' => $mod->get_finishedstepmsg(),
                'modid' => $mod->get_id(),
                'isglobalcompleted' => $state == \mod_wordcards_module::STATE_END,
                'hascontinue' => $state != \mod_wordcards_module::STATE_END,
                'nexturl' => $nexturl,
                'isglobalscatter' => true
        ];

        return $this->render_from_template('mod_wordcards/scatter_page', $data);
    }


    public function navigation(\mod_wordcards_module $mod, $currentstate) {
        $tabtree = \mod_wordcards_helper::get_tabs($mod, $currentstate);
        if ($mod->can_manage() || $mod->can_viewreports()) {
            // Teachers see the tabs, as normal tabs.
            return $this->render($tabtree);
        }

        $seencurrent = false;
        $step = 1;
        $tabs = array_map(function($tab) use ($seencurrent, $currentstate, &$step, $tabtree) {
            $current = $tab->id == $currentstate;
            $seencurrent = $current || $seencurrent;
            $icon = $tab->title;
            $tab->title ='';

            return [
                'id' => $tab->id,
                'url' => $tab->link,
                'text' => $tab->text,
                'title' => '',
                'icon' => $icon,
                'current' => $tab->selected,
                'inactive' => $tab->inactive,
                'last' => $step == count($tabtree->subtree),
                'step' => $step++
            ];
        }, $tabtree->subtree);

        $data = [
            'tabs' => $tabs
        ];
        return $this->render_from_template('mod_wordcards/student_navigation', $data);
    }

    /**
     * Return HTML to display message about problem
     */
    public function show_problembox($msg) {
        $output = '';
        $output .= $this->output->box_start(constants::M_COMPONENT . '_problembox');
        $output .= $this->notification($msg, 'warning');
        $output .= $this->output->box_end();
        return $output;
    }

}
