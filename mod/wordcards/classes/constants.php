<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace mod_wordcards;

defined('MOODLE_INTERNAL') || die();

class constants
{
//component name, db tables, things that define app
const M_COMPONENT='mod_wordcards';
const M_MODNAME='wordcards';
const M_URL='/mod/wordcards';
const M_CLASS='mod_wordcards';
const M_TABLE='wordcards';
const M_ATTEMPTSTABLE='wordcards_progress';
const M_PLUGINSETTINGS ='/admin/settings.php?section=modsettingwordcards';

//  const CLOUDPOODLL = 'http://localhost/moodle';
const CLOUDPOODLL = 'https://cloud.poodll.com';

const M_FRONTFACEFLIP_DEF = 0;
const M_FRONTFACEFLIP_TERM = 1;


//grading options
const M_GRADEHIGHEST= 0;
const M_GRADELOWEST= 1;
const M_GRADELATEST= 2; // we only use this one currently
const M_GRADEAVERAGE= 3;
const M_GRADENONE= 4;

//languages
const M_LANG_ENUS = 'en-US';
const M_LANG_ENGB = 'en-GB';
const M_LANG_ENAU = 'en-AU';
const M_LANG_ESUS = 'es-US';
const M_LANG_FRCA = 'fr-CA';
const M_LANG_FRFR = 'fr-FR';
const M_LANG_ITIT = 'it-IT';
const M_LANG_PTBR = 'pt-BR';
const M_LANG_KOKR = 'ko-KR';
const M_LANG_DEDE = 'de-DE';
const M_LANG_HIIN = 'hi-IN';
const M_LANG_ENIN = 'en-IN';
const M_LANG_ESES = 'es-ES';

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

const TRANSCRIBER_NONE = 0;
const TRANSCRIBER_AUTO = 1;
const TRANSCRIBER_POODLL = 2;


}