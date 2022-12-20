<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace mod_solo;

defined('MOODLE_INTERNAL') || die();

class constants
{
//component name, db tables, things that define app
const M_COMPONENT='mod_solo';
const M_TABLE='solo';
const M_ATTEMPTSTABLE='solo_attempts';
const M_STATSTABLE='solo_attemptstats';
const M_TOPIC_TABLE='solo_topics';
const M_SELECTEDTOPIC_TABLE='solo_selectedtopics';
const M_AITABLE='solo_ai_result';
const M_MODNAME='solo';
const M_URL='/mod/solo';
const M_CLASS='mod_solo';
const M_INTRO_CONTAINER = 'mod_intro_box';
const M_CLASS_ITEMTABLE='mod_solo_attempttable';
const M_CLASS_TOPICSCONTAINER ='topicscontainer';
const M_CLASS_TOPICSCHECKBOX = 'topicscheckbox';
const M_PLUGINSETTINGS ='/admin/settings.php?section=modsettingsolo';

const M_USE_DATATABLES=true;
const M_STEP_NONE=0;
const M_STEP_PREPARE=1;
const M_STEP_RECORD=2;
const M_STEP_TRANSCRIBE=3;
const M_STEP_MODEL=4;

//sequence of steps (P.repare, R.ecord, T.ranscribe, M.odel)
const M_SEQ_PRTM=0;
const M_SEQ_PRM=1;
const M_SEQ_PTRM=2;
const M_SEQ_PRMT=3;

//AI Transcript constants
const M_AI_PARENTFIELDNAME = 'solo';
const M_TARGET_CONVLENGTHFIELDNAME = 'convlength';
const ACCMETHOD_NONE =0;
const ACCMETHOD_AUTO =1;
const ACCMETHOD_FIXED =2;
const ACCMETHOD_NOERRORS =3;


const M_RECORDERID='solo_audio_recorder';
const M_WIDGETID='solo_audio_recorder_9999';

//grading options
const M_GRADEHIGHEST= 0;
const M_GRADELOWEST= 1;
const M_GRADELATEST= 2; // we only use this one currently
const M_GRADEAVERAGE= 3;
const M_GRADENONE= 4;

//recorder options
const REC_AUDIO = 'audio';
const REC_VIDEO = 'video';

const SKIN_PLAIN = 'standard';
const SKIN_BMR = 'bmr';
const SKIN_123 = 'onetwothree';
const SKIN_FRESH = 'fresh';
const SKIN_ONCE = 'once';
const SKIN_UPLOAD = 'upload';

//Constants for Attempt Steps
const STEP_NONE=0;
const STEP_PREPARE= 1;
const STEP_MEDIARECORDING= 2;
const STEP_SELFTRANSCRIBE= 3;
const STEP_MODEL= 4;
//later we need to make this variable
const STEP_FINAL= 4;

//constants for relevance
const RELEVANCE_NONE = 0;
const RELEVANCE_BROAD = 50;
const RELEVANCE_QUITE = 70;
const RELEVANCE_VERY = 80;
const RELEVANCE_EXTREME = 90;

//constants for suggest grade
const SUGGEST_GRADE_NONE = 0;
const SUGGEST_GRADE_USE = 1;

const CLASS_AUDIOREC_IFRAME ='solo_audiorec_iframe';
const CLASS_VIDEOREC_IFRAME ='solo_videorec_iframe';

const TEXTDESCR = 'itemtext';
const TEXTDESCR_FILEAREA = 'itemarea';

const M_FILEAREA_SUBMISSIONS='submission';
const M_FILEAREA_TOPICMEDIA='topicmedia';
const M_FILEAREA_MODELMEDIA='modelmedia';

const AUDIOPROMPT_FILEAREA = 'audioitem';
const AUDIOANSWER_FILEAREA = 'audioanswer';
const PICTUREPROMPT_FILEAREA = 'pictureitem';
const TEXTPROMPT_FILEAREA = 'textitem';
const TEXTANSWER_FILEAREA ='answerarea';
const PASSAGEPICTURE_FILEAREA = 'passagepicture';

//CSS DEFS
CONST C_AUDIOPLAYER = 'vs_audioplayer';
CONST C_CURRENTFORMAT= 'vs_currentformat';
CONST C_LANGSELECT = 'vs_langselect';
CONST C_VOICESELECT = 'vs_voiceselect';
CONST C_PLAYBUTTON = 'vs_playbutton';
CONST C_FILENAMETEXT = 'vs_filenametext';
CONST C_TARGETWORDSDISPLAY = 'mod_solo_targetwordsdisplay';

const RECORDINGURLFIELD='filename';
const STREAMINGTRANSCRIPTFIELD='streamingtranscript';
const RECORDERORPLAYERFIELD='recorderorplayer';

const TRANSCRIBER_NONE = 0;
const TRANSCRIBER_OPEN = 1;


const M_TOPICLEVEL_CUSTOM =1;
const M_TOPICLEVEL_COURSE =0;

const DEF_CONVLENGTH=2;
const M_C_TRANSCRIPTDISPLAY='mod_solo_transcriptdisplay';
const M_C_TRANSCRIPTEDITOR='mod_solo_transcripteditor';
const M_C_CONVERSATION='mod_solo_conversation';

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
const M_LANG_FILPH = 'fil-PH';
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

const M_LANG_NBNO ='nb-NO';
const M_LANG_PLPL ='pl-PL';
const M_LANG_RORO ='ro-RO';
const M_LANG_SVSE ='sv-SE';

const M_LANG_UKUA ='uk-UA';
const M_LANG_EUES ='eu-ES';
const M_LANG_FIFI ='fi-FI';
const M_LANG_HUHU ='hu-HU';

const M_HIDDEN_PLAYER = 'mod_solo_hidden_player';
const M_HIDDEN_PLAYER_BUTTON = 'mod_solo_hidden_player_button';
const M_HIDDEN_PLAYER_BUTTON_ACTIVE = 'mod_solo_hidden_player_button_active';
const M_HIDDEN_PLAYER_BUTTON_PAUSED = 'mod_solo_hidden_player_button_paused';
const M_HIDDEN_PLAYER_BUTTON_PLAYING = 'mod_solo_hidden_player_button_playing';

const M_NEURALVOICES = array("Amy","Emma","Brian","Olivia","Aria","Ayanda","Ivy","Joanna","Kendra","Kimberly",
        "Salli","Joey","Justin","Kevin","Matthew","Camila","Lupe", "Gabrielle", "Vicki", "Seoyeon","Takumi","lucia");


}