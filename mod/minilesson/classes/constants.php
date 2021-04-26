<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace mod_minilesson;

defined('MOODLE_INTERNAL') || die();

class constants
{
//component name, db tables, things that define app
const M_COMPONENT='mod_minilesson';
const M_FILEAREA_SUBMISSIONS='submission';
const M_TABLE='minilesson';
const M_ATTEMPTSTABLE='minilesson_attempt';
const M_AITABLE='minilesson_ai_result';
const M_QTABLE='minilesson_rsquestions';
const M_MODNAME='minilesson';
const M_URL='/mod/minilesson';
const M_PATH='/mod/minilesson';
const M_CLASS='mod_minilesson';
const M_PLUGINSETTINGS ='/admin/settings.php?section=modsettingminilesson';
const M_STATE_COMPLETE=1;
const M_STATE_INCOMPLETE=0;

const M_NOITEMS_CONT= 'mod_minilesson_noitems_cont';
const M_ITEMS_CONT= 'mod_minilesson_items_cont';
const M_ITEMS_TABLE= 'mod_minilesson_qpanel';


//grading options
const M_GRADEHIGHEST= 0;
const M_GRADELOWEST= 1;
const M_GRADELATEST= 2;
const M_GRADEAVERAGE= 3;
const M_GRADENONE= 4;
//accuracy adjustment method options
const ACCMETHOD_NONE =0;
const ACCMETHOD_AUTO =1;
const ACCMETHOD_FIXED =2;
const ACCMETHOD_NOERRORS =3;
//what to display to user when reviewing activity options
const POSTATTEMPT_NONE=0;
const POSTATTEMPT_EVAL=1;
const POSTATTEMPT_EVALERRORS=2;


//Constants for RS Questions
const NONE=0;
const TYPE_TEXTPROMPT_LONG = 'multichoicelong';

const TYPE_MULTIAUDIO = 'multiaudio';
const TYPE_MULTICHOICE = 'multichoice';
const TYPE_PAGE = 'page';
const TYPE_DICTATIONCHAT = 'dictationchat';
const TYPE_DICTATION = 'dictation';
const TYPE_SPEECHCARDS = 'speechcards';
const TYPE_LISTENREPEAT = 'listenrepeat';
const TYPE_TEACHERTOOLS = 'teachertools';
const TYPE_SHORTANSWER = 'shortanswer';

const AUDIOFNAME = 'itemaudiofname';
const AUDIOPROMPT = 'audioitem';
const AUDIOANSWER = 'audioanswer';
const AUDIOMODEL = 'audiomodel';
const CORRECTANSWER = 'correctanswer';
const AUDIOPROMPT_FILEAREA = 'audioitem';
const TEXTPROMPT_FILEAREA = 'textitem';
const TEXTQUESTION = 'itemtext';
const TEXTQUESTION_FORMAT = 'itemtextformat';
const TTSQUESTION = 'itemtts';
const TTSQUESTIONVOICE = 'itemttsvoice';
const TTSQUESTIONOPTION = 'itemttsoption';
const MEDIAQUESTION = 'itemmedia';
const QUESTIONTEXTAREA = 'itemtextarea';
const MEDIAIFRAME = 'customdata5';
const TEXTANSWER = 'customtext';
const CUSTOMDATA = 'customdata';
const CUSTOMINT = 'customint';
const POLLYVOICE = 'customtext5';
const POLLYOPTION = 'customint4';
const TEXTQUESTION_FILEAREA = 'itemarea';
const TEXTANSWER_FILEAREA ='customtextfilearea';
const PASSAGEPICTURE='passagepicture';
const PASSAGEPICTURE_FILEAREA = 'passagepicture';
const MAXANSWERS=4;
const MAXCUSTOMTEXT=5;
const MAXCUSTOMDATA=5;
const MAXCUSTOMINT=5;

const SHOWTEXTPROMPT = 'customint1';
const TEXTPROMPT_WORDS = 1;
const TEXTPROMPT_DOTS = 0;

const LISTENORREAD = 'customint2';
const LISTENORREAD_READ = 0;
const LISTENORREAD_LISTEN = 1;


const TTS_NORMAL = 0;
const TTS_SLOW = 1;
const TTS_VERYSLOW = 2;
const TTS_SSML = 3;

//CSS ids/classes
const M_RECORD_BUTTON='mod_minilesson_record_button';
const M_START_BUTTON='mod_minilesson_start_button';
const M_READING_AUDIO_URL='mod_minilesson_readingaudiourl';
const M_DRAFT_CONTROL='mod_minilesson_draft_control';
const M_PROGRESS_CONTAINER='mod_minilesson_progress_cont';
const M_HIDER='mod_minilesson_hider';
const M_STOP_BUTTON='mod_minilesson_stop_button';
const M_WHERETONEXT_CONTAINER='mod_minilesson_wheretonext_cont';
const M_RECORD_BUTTON_CONTAINER='mod_minilesson_record_button_cont';
const M_START_BUTTON_CONTAINER='mod_minilesson_start_button_cont';
const M_STOP_BUTTON_CONTAINER='mod_minilesson_stop_button_cont';
const M_RECORDERID='therecorderid';
const M_RECORDING_CONTAINER='mod_minilesson_recording_cont';
const M_RECORDER_CONTAINER='mod_minilesson_recorder_cont';
const M_DUMMY_RECORDER='mod_minilesson_dummy_recorder';
const M_RECORDER_INSTRUCTIONS_RIGHT='mod_minilesson_recorder_instr_right';
const M_RECORDER_INSTRUCTIONS_LEFT='mod_minilesson_recorder_instr_left';
const M_INSTRUCTIONS_CONTAINER='mod_minilesson_instructions_cont';
const M_PASSAGE_CONTAINER='mod_minilesson_passage_cont';
const M_MSV_MODE = 'mod_minilesson_msvmode';
const M_QUICK_MODE = 'mod_minilesson_spotcheckmode';
const M_GRADING_MODE = 'mod_minilesson_gradingmode';
const M_QUIZ_CONTAINER='mod_minilesson_quiz_cont';
const M_POSTATTEMPT= 'mod_minilesson_postattempt';
const M_FEEDBACK_CONTAINER='mod_minilesson_feedback_cont';
const M_ERROR_CONTAINER='mod_minilesson_error_cont';
const M_GRADING_ERROR_CONTAINER='mod_minilesson_grading_error_cont';
const M_GRADING_ERROR_IMG='mod_minilesson_grading_error_img';
const M_GRADING_ERROR_SCORE='mod_minilesson_grading_error_score';

const M_GRADING_QUIZ_CONTAINER='mod_minilesson_grading_quiz_cont';
const M_TWOCOL_CONTAINER='mod_minilesson_twocol_cont';
const M_TWOCOL_QUIZ_CONTAINER='mod_minilesson_twocol_quiz_cont';
const M_TWOCOL_PLAYER_CONTAINER='mod_minilesson_twocol_player_cont';
const M_TWOCOL_PLAYER='mod_minilesson_twocol_player';
const M_TWOCOL_LEFTCOL='mod_minilesson_leftcol';
const M_TWOCOL_RIGHTCOL='mod_minilesson_rightcol';
const M_GRADING_QUIZ_SCORE='mod_minilesson_grading_quiz_score';
const M_GRADING_ACCURACY_CONTAINER='mod_minilesson_grading_accuracy_cont';
const M_GRADING_ACCURACY_IMG='mod_minilesson_grading_accuracy_img';
const M_GRADING_ACCURACY_SCORE='mod_minilesson_grading_accuracy_score';
const M_GRADING_SESSION_SCORE='mod_minilesson_grading_session_score';
const M_GRADING_SESSIONSCORE_CONTAINER='mod_minilesson_grading_sessionscore_cont';
const M_GRADING_ERRORRATE_SCORE='mod_minilesson_grading_errorrate_score';
const M_GRADING_ERRORRATE_CONTAINER='mod_minilesson_grading_errorrate_cont';
const M_GRADING_SCRATE_SCORE='mod_minilesson_grading_scrate_score';
const M_GRADING_SCRATE_CONTAINER='mod_minilesson_grading_scrate_cont';
const M_GRADING_SCORE='mod_minilesson_grading_score';
const M_GRADING_PLAYER_CONTAINER='mod_minilesson_grading_player_cont';
const M_GRADING_PLAYER='mod_minilesson_grading_player';
const M_GRADING_ACTION_CONTAINER='mod_minilesson_grading_action_cont';
const M_GRADING_FORM_SESSIONTIME='mod_minilesson_grading_form_sessiontime';
const M_GRADING_FORM_SESSIONSCORE='mod_minilesson_grading_form_sessionscore';
const M_GRADING_FORM_SESSIONENDWORD='mod_minilesson_grading_form_sessionendword';
const M_GRADING_FORM_SESSIONERRORS='mod_minilesson_grading_form_sessionerrors';
const M_GRADING_FORM_NOTES='mod_minilesson_grading_form_notes';
const M_HIDDEN_PLAYER='mod_minilesson_hidden_player';
const M_HIDDEN_PLAYER_BUTTON='mod_minilesson_hidden_player_button';
const M_HIDDEN_PLAYER_BUTTON_ACTIVE='mod_minilesson_hidden_player_button_active';
const M_HIDDEN_PLAYER_BUTTON_PAUSED='mod_minilesson_hidden_player_button_paused';
const M_HIDDEN_PLAYER_BUTTON_PLAYING='mod_minilesson_hidden_player_button_playing';
const M_EVALUATED_MESSAGE='mod_minilesson_evaluated_message';
const M_QR_PLAYER='mod_minilesson_qr_player';
const M_LINK_BOX='mod_minilesson_link_box';
const M_LINK_BOX_TITLE='mod_minilesson_link_box_title';
const M_NOITEMS_MSG='mod_minilesson_noitems_msg';


    //languages
const M_LANG_ENUS = 'en-US';
const M_LANG_ENGB = 'en-GB';
const M_LANG_ENAU = 'en-AU';
const M_LANG_ENIN = 'en-IN';
const M_LANG_ESUS = 'es-US';
const M_LANG_ESES = 'es-ES';
const M_LANG_FRCA = 'fr-CA';
const M_LANG_FRFR = 'fr-FR';
const M_LANG_DEDE = 'de-DE';
const M_LANG_ITIT = 'it-IT';
const M_LANG_PTBR = 'pt-BR';

const M_LANG_DADK = 'da-DK';

const M_LANG_KOKR = 'ko-KR';
const M_LANG_HIIN = 'hi-IN';
const M_LANG_ARAE ='ar-AE';
const M_LANG_ARSA ='ar-SA';
const M_LANG_ZHCN ='zh-CN';
const M_LANG_NLNL ='nl-NL';
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

const M_PROMPT_SEPARATE=0;
const M_PROMPT_RICHTEXT=1;

const TRANSCRIBER_NONE = 0;
const TRANSCRIBER_AMAZONTRANSCRIBE = 1;
const TRANSCRIBER_GOOGLECLOUDSPEECH = 2;
const TRANSCRIBER_GOOGLECHROME = 3;


const M_PUSH_NONE =0;
const M_PUSH_PASSAGE =1;
const M_PUSH_ALTERNATIVES =2;
const M_PUSH_QUESTIONS =3;
const M_PUSH_LEVEL =4;
  
const M_QUIZ_FINISHED = "mod_minilesson_quiz_finished";
const M_QUIZ_REATTEMPT = "mod_minilesson_quiz_reattempt";

}