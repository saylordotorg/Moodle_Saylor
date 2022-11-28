<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace mod_readaloud;

defined('MOODLE_INTERNAL') || die();

class constants {
    //component name, db tables, things that define app
    const M_COMPONENT = 'mod_readaloud';
    const M_FILEAREA_SUBMISSIONS = 'submission';
    const M_TABLE = 'readaloud';
    const M_USERTABLE = 'readaloud_attempt';
    const M_AITABLE = 'readaloud_ai_result';
    const M_QTABLE = 'readaloud_rsquestions';
    const M_MODNAME = 'readaloud';
    const M_URL = '/mod/readaloud';

    const M_PLUGINSETTINGS = '/admin/settings.php?section=modsettingreadaloud';

    const M_NEURALVOICES = array("Amy","Emma","Brian","Olivia","Aria","Ayanda","Ivy","Joanna","Kendra","Kimberly",
            "Salli","Joey","Justin","Kevin","Matthew","Lupe", "Gabrielle", "Vicki", "Seoyeon","Takumi","lucia",
        "Lea","Bianca","Laura","Kajal","Suvi","Liam","Daniel","Hannah","Camila");

    //classes for use in CSS
    const M_CLASS = 'mod_readaloud';

    //Guided transcription uses the passage or a combination of passages (corpus)
    const GUIDEDTRANS_PASSAGE = 0;
    const GUIDEDTRANS_CORPUS = 1;
    //corpus (combination of packages) covers the whole site or just the course
    const CORPUSRANGE_SITE = 0;
    const CORPUSRANGE_COURSE = 1;
    //when pushing a setting, apply it activity, course or site wide
    const APPLY_ACTIVITY = 0;
    const APPLY_COURSE = 1;
    const APPLY_SITE = 2;



    //audio recorders
    const REC_READALOUD = 'readaloud';
    const REC_ONCE = 'once';
    const REC_UPLOAD = 'upload';

    //Constants for RS Questions
    const NONE=0;
    const MAXANSWERS=4;
    const TEXTQUESTION = 'itemtext';
    const TEXTANSWER = 'customtext';
    const TEXTQUESTION_FILEAREA = 'itemarea';
    const TEXTANSWER_FILEAREA ='answerarea';
    const TEXTPROMPT_FILEAREA = 'textitem';
    const TYPE_TEXTPROMPT_LONG = 4;
    const TYPE_TEXTPROMPT_SHORT = 5;
    const TYPE_TEXTPROMPT_AUDIO = 6;
    const TYPE_INSTRUCTIONS = 7;
    const TEXTCHOICE = 'textchoice';
    const TEXTBOXCHOICE = 'textboxchoice';
    const CORRECTANSWER = 'correctanswer';



    //grading options
    const M_GRADEHIGHEST = 0;
    const M_GRADELOWEST = 1;
    const M_GRADELATEST = 2;
    const M_GRADEAVERAGE = 3;
    const M_GRADENONE = 4;
    //accuracy adjustment method options
    const ACCMETHOD_NONE = 0;
    const ACCMETHOD_AUTO = 1;
    const ACCMETHOD_FIXED = 2;
    const ACCMETHOD_NOERRORS = 3;
    //what to display to user when reviewing activity options
    const POSTATTEMPT_NONE = 0;
    const POSTATTEMPT_EVAL = 1;
    const POSTATTEMPT_EVALERRORS = 2;
    const POSTATTEMPT_EVALERRORSNOGRADE = 3;
    //more review mode options
    const REVIEWMODE_NONE = 0;
    const REVIEWMODE_MACHINE = 1;
    const REVIEWMODE_HUMAN = 2;
    const REVIEWMODE_SCORESONLY = 3;
    //to use or not use machine grades
    const MACHINEGRADE_NONE = 0;
    const MACHINEGRADE_HYBRID = 1;
    const MACHINEGRADE_MACHINEONLY = 2;

    //Session Score
    const SESSIONSCORE_NORMAL = 0; //Normal = WPM / Targetwpm * 100
    const SESSIONSCORE_STRICT = 1; //Strict = (WPM - Errors) / Targetwpm * 100

    //TTS Speed
    const TTSSPEED_MEDIUM = 0;
    const TTSSPEED_SLOW = 1;
    const TTSSPEED_XSLOW = 2;

    //CSS ids/classes
    const M_RECORD_BUTTON = 'mod_readaloud_record_button';
    const M_START_BUTTON = 'mod_readaloud_start_button';
    const M_UPDATE_CONTROL = 'mod_readaloud_update_control';
    const M_DRAFT_CONTROL = 'mod_readaloud_draft_control';
    const M_PROGRESS_CONTAINER = 'mod_readaloud_progress_cont';
    const M_HIDER = 'mod_readaloud_hider';
    const M_STOP_BUTTON = 'mod_readaloud_stop_button';
    const M_WHERETONEXT_CONTAINER = 'mod_readaloud_wheretonext_cont';
    const M_RECORD_BUTTON_CONTAINER = 'mod_readaloud_record_button_cont';
    const M_START_BUTTON_CONTAINER = 'mod_readaloud_start_button_cont';
    const M_STOP_BUTTON_CONTAINER = 'mod_readaloud_stop_button_cont';
    const M_RECORDERID = 'therecorderid';
    const M_RECORDING_CONTAINER = 'mod_readaloud_recording_cont';
    const M_RECORDER_CONTAINER = 'mod_readaloud_recorder_cont';
    const M_DUMMY_RECORDER = 'mod_readaloud_dummy_recorder';
    const M_RECORDER_INSTRUCTIONS_RIGHT = 'mod_readaloud_recorder_instr_right';
    const M_RECORDER_INSTRUCTIONS_LEFT = 'mod_readaloud_recorder_instr_left';
    const M_INSTRUCTIONS_CONTAINER = 'mod_readaloud_instructions_cont';
    const M_INSTRUCTIONS = 'mod_readaloud_instructions';
    const M_ACTIVITYINSTRUCTIONS_CONTAINER = 'mod_readaloud_activityinstructions_const';
    const M_MENUINSTRUCTIONS_CONTAINER = 'mod_readaloud_menuinstructions_const';
    const M_MENUBUTTONS_CONTAINER = 'mod_readaloud_menubuttons_cont';
    const M_PREVIEWINSTRUCTIONS_CONTAINER = 'mod_readaloud_previewinstructions_cont';
    const M_PREVIEWINSTRUCTIONS = 'mod_readaloud_previewinstructions';
    const M_LANDRINSTRUCTIONS_CONTAINER = 'mod_readaloud_landrinstructions_cont';
    const M_LANDRINSTRUCTIONS = 'mod_readaloud_landrinstructions';
    const M_SMALLREPORT_CONTAINER = 'mod_readaloud_smallreport_cont';
    const M_INTRO_CONTAINER = 'mod_intro_box';


    const M_PASSAGE_CONTAINER = 'mod_readaloud_passage_cont';
    const M_POSTATTEMPT = 'mod_readaloud_postattempt';
    const M_FEEDBACK_CONTAINER = 'mod_readaloud_feedback_cont';
    const M_ERROR_CONTAINER = 'mod_readaloud_error_cont';
    const M_GRADING_ERROR_CONTAINER = 'mod_readaloud_grading_error_cont';
    const M_GRADING_ERROR_IMG = 'mod_readaloud_grading_error_img';
    const M_GRADING_ERROR_SCORE = 'mod_readaloud_grading_error_score';
    const M_GRADING_WPM_CONTAINER = 'mod_readaloud_grading_wpm_cont';
    const M_GRADING_WPM_IMG = 'mod_readaloud_grading_wpm_img';
    const M_GRADING_WPM_SCORE = 'mod_readaloud_grading_wpm_score';
    const M_GRADING_ACCURACY_CONTAINER = 'mod_readaloud_grading_accuracy_cont';
    const M_GRADING_ACCURACY_IMG = 'mod_readaloud_grading_accuracy_img';
    const M_GRADING_ACCURACY_SCORE = 'mod_readaloud_grading_accuracy_score';
    const M_GRADING_SESSION_SCORE = 'mod_readaloud_grading_session_score';
    const M_GRADING_SESSIONSCORE_CONTAINER = 'mod_readaloud_grading_sessionscore_cont';
    const M_GRADING_SCORE = 'mod_readaloud_grading_score';
    const M_GRADING_PLAYER_CONTAINER = 'mod_readaloud_grading_player_cont';
    const M_GRADING_PLAYER = 'mod_readaloud_grading_player';
    const M_GRADING_ACTION_CONTAINER = 'mod_readaloud_grading_action_cont';
    const M_GRADING_FORM_SESSIONTIME = 'mod_readaloud_grading_form_sessiontime';
    const M_GRADING_FORM_SESSIONSCORE = 'mod_readaloud_grading_form_sessionscore';
    const M_GRADING_FORM_WPM = 'mod_readaloud_grading_form_wpm';
    const M_GRADING_FORM_ACCURACY = 'mod_readaloud_grading_form_accuracy';
    const M_GRADING_FORM_SESSIONENDWORD = 'mod_readaloud_grading_form_sessionendword';
    const M_GRADING_FORM_SESSIONERRORS = 'mod_readaloud_grading_form_sessionerrors';
    const M_ADMINTAB_CONTAINER = 'mod_readaloud_admintab_cont';
    const M_HIDDEN_PLAYER = 'mod_readaloud_hidden_player';
    const M_HIDDEN_PLAYER_BUTTON = 'mod_readaloud_hidden_player_button';
    const M_HIDDEN_PLAYER_BUTTON_ACTIVE = 'mod_readaloud_hidden_player_button_active';
    const M_HIDDEN_PLAYER_BUTTON_PAUSED = 'mod_readaloud_hidden_player_button_paused';
    const M_HIDDEN_PLAYER_BUTTON_PLAYING = 'mod_readaloud_hidden_player_button_playing';
    const M_EVALUATED_MESSAGE = 'mod_readaloud_evaluated_message';
    const M_MODELAUDIO_FORM_URLFIELD = 'mod_readaloud_modelaudio_form_urlfield';
    const M_MODELAUDIO_FORM_BREAKSFIELD = 'mod_readaloud_modelaudio_form_breaksfield';
    const M_MODELAUDIO_PLAYER = 'mod_readaloud_modelaudio_player';
    const M_VIEWMODELTRANSCRIPT = 'mod_readaloud_modeltranscript_button';
    const M_MODELTRANSCRIPT = 'mod_readaloud_modeltranscript';
    const M_CLASS_PASSAGEWORD = 'mod_readaloud_grading_passageword';
    const M_CLASS_PASSAGESPACE = 'mod_readaloud_grading_passagespace';
    const M_CLASS_PASSAGEGRADINGCONT = 'mod_readaloud_grading_passagecont';

    //languages
    const M_LANG_ENUS = 'en-US';
    const M_LANG_ENGB = 'en-GB';
    const M_LANG_ENAU = 'en-AU';
    const M_LANG_ENPH = 'en-PH';
    const M_LANG_ENNZ = 'en-NZ';
    const M_LANG_ENZA = 'en-ZA';
    const M_LANG_ENIN = 'en-IN';
    const M_LANG_ESUS = 'es-US';
    const M_LANG_ESES = 'es-ES';
    const M_LANG_FRCA = 'fr-CA';
    const M_LANG_FRFR = 'fr-FR';
    const M_LANG_DEDE = 'de-DE';
    const M_LANG_DEAT ='de-AT';
    const M_LANG_ITIT = 'it-IT';
    const M_LANG_PTBR = 'pt-BR';

    const M_LANG_DADK = 'da-DK';
    const M_LANG_FILPH = 'fil-PH';

    const M_LANG_KOKR = 'ko-KR';
    const M_LANG_HIIN = 'hi-IN';
    const M_LANG_ARAE ='ar-AE';
    const M_LANG_ARSA ='ar-SA';
    const M_LANG_ZHCN ='zh-CN';
    const M_LANG_NLNL ='nl-NL';
    const M_LANG_NLBE ='nl-BE';
    const M_LANG_ENIE ='en-IE';
    const M_LANG_ENWL ='en-WL';
    const M_LANG_ENAB ='en-AB';
    const M_LANG_FAIR ='fa-IR';
    const M_LANG_DECH ='de-CH';
    const M_LANG_HEIL ='he-IL';
    const M_LANG_IDID ='id-ID';
    const M_LANG_JAJP ='ja-JP';
    const M_LANG_MSMY ='ms-MY';
    const M_LANG_PTPT ='pt-PT';
    const M_LANG_RURU ='ru-RU';
    const M_LANG_TAIN ='ta-IN';
    const M_LANG_TEIN ='te-IN';
    const M_LANG_TRTR ='tr-TR';

    const M_LANG_NBNO ='nb-NO';
    const M_LANG_PLPL ='pl-PL';
    const M_LANG_RORO ='ro-RO';
    const M_LANG_SVSE ='sv-SE';
    const M_LANG_UKUA ='uk-UA';
    const M_LANG_EUES ='eu-ES';
    const M_LANG_FIFI ='fi-FI';
    const M_LANG_HUHU ='hu-HU';

    const TTS_NONE='ttsnone';

    const TRANSCRIBER_GUIDED = 0;
    const TRANSCRIBER_STRICT = 1;

    //no longer used
    const TRANSCRIBER_NONE = 0; //defunct
    const TRANSCRIBER_AMAZONSTREAMING =4; //defunct

    const M_STARTPREVIEW= 'mod_readaloud_button_startpreview';
    const M_STARTLANDR= 'mod_readaloud_button_startlandr';
    const M_STARTSHADOW= 'mod_readaloud_button_startshadow';
    const M_STARTNOSHADOW= 'mod_readaloud_button_startnoshadow';
    const M_RETURNMENU= 'mod_readaloud_button_returnmenu';
    const M_STOPANDPLAY= 'mod_readaloud_button_stopandplay';
    const M_BACKTOTOP= 'mod_readaloud_button_backtotop';
    const M_STOP_BTN = 'mod_readaloud_button_stop';
    const M_PLAY_BTN = 'mod_readaloud_button_play';

    const M_PUSH_NONE =0;
    const M_PUSH_PASSAGE =1;
    const M_PUSH_ALTERNATIVES =2;
    const M_PUSH_QUESTIONS =3;
    const M_PUSH_TARGETWPM =4;
    const M_PUSH_TTSMODELAUDIO = 5;
    const M_PUSH_TIMELIMIT = 6;
    const M_PUSH_MODES = 7;
    const M_PUSH_GRADESETTINGS = 8;

    const M_USE_DATATABLES=true;

    const M_STANDARD_FONTS = ["Arial", "Arial Black", "Verdana", "Tahoma", "Trebuchet MS", "Impact",
        "Times New Roman", "Didot", "Georgia", "American Typewriter", "Andalé Mono", "Courier",
        "Lucida Console", "Monaco", "Bradley Hand", "Brush Script MT", "Luminari", "Comic Sans MS"];

    const M_GOOGLE_FONTS = ["Andika"];
  
}