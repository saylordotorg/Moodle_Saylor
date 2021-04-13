<?php
/**
 * Displays information about the wordcards in the course.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'Activity completed';
$string['completedmsg'] = 'Completed message';
$string['completedmsg_help'] = 'This is the message displayed on the final screen of the activity when the student complete the last practice.';
$string['completionwhenfinish'] = 'The student has finished the activity.';
$string['congrats'] = 'Congratulations!';
$string['congratsitsover'] = '<div  style="text-align: center;">You have completed this activity. Feel free to go back and practice more!</div>';
$string['definition'] = 'Definition';
$string['definition_help'] = 'Enter definition of the term(word/phrase) here. It should be short but still tell the student what the term means.';
$string['definitions'] = 'Definitions';
$string['deleteallentries'] = 'Delete all user attempts and stats (keep the terms/definitions)';
$string['deleteterm'] = 'Delete term \'{$a}\'';
$string['delimiter'] = 'Delimiter Character';
$string['delim_tab'] = 'Tab';
$string['delim_comma'] = 'Comma';
$string['delim_pipe'] = 'Pipe';
$string['description'] = 'Description';
$string['editterm'] = 'Edit term \'{$a}\'';
$string['finishscatterin'] = '<h4 style="text-align: center;">Congratulations!</h4>';
$string['wordcards:addinstance'] = 'Add an instance';
$string['wordcards:view'] = 'View the module';
$string['wordcards:viewreports'] = 'View reports';
$string['wordcards:manageattempts'] = 'Manage Attempts';
$string['wordcards:manage'] = 'Manage';
$string['reviewactivity'] = 'Review';
$string['reviewactivityfinished'] = 'You finished the review session in {$a->seconds} seconds.';

$string['gotit'] = 'Got it';
$string['import'] = 'Import';
$string['importdata'] = 'Import Data';
$string['importresults'] = 'Successfully imported {$a->imported} rows. {$a->failed} rows failed.';
$string['introduction'] = 'Introduction';
$string['learnactivityfinished'] = 'You finished the practice session in {$a->seconds} seconds.';
$string['finishedstepmsg'] = 'Finished message';
$string['finishedstepmsg_help'] = 'This is the message displayed when you end a practice session.';
$string['step1termcount'] = 'Step 1 word set size';
$string['step2termcount'] = 'Step 2 word set size';
$string['step3termcount'] = 'Step 3 word set size';
$string['step4termcount'] = 'Step 4 word set size';
$string['step5termcount'] = 'Step 5 word set size';
$string['loading'] = 'Loading';
$string['learnactivity'] = 'New Words';
$string['markasseen'] = 'Mark as seen';
$string['modulename'] = 'Wordcards';
$string['modulename_help'] = 'The wordcards activity module enables a teacher to create custom wordcards games for encouraging students learning new words.';
$string['modulenameplural'] = 'Wordcards';
$string['mustseealltocontinue'] = 'Check all the words to continue:';
$string['name'] = 'Name';
$string['nodefinitions'] = 'No words were added yet.';
$string['noteaboutseenforteachers'] = 'Note: Teachers\' seen status are not saved.';
$string['pluginadministration'] = 'Wordcards administration';
$string['pluginname'] = 'Wordcards';
$string['reallydeleteterm'] = 'Are you sure you want to delete the term \'{$a}\'?';
$string['removeuserdata'] = 'Remove Wordcards user data';
$string['setup'] = 'Setup';
$string['managewords'] = 'Manage Words';
$string['skipreview'] = 'Hide first review session';
$string['skipreview_help'] = 'Hide the review session of this specific activity if no wordcards activities have been completed in this course.';
$string['tabdefinitions'] = 'Definitions';
$string['tabmanagewords'] = 'Words Admin';
$string['tabimport'] = 'Import';
$string['term'] = 'Term';
$string['term_help'] = 'Enter the word or phrase to be learned here.';
$string['termadded'] = 'The term \'{$a}\' has been added.';
$string['termdeleted'] = 'The term has been deleted.';
$string['termnotseen'] = 'Term not seen';
$string['termsaved'] = 'The term \'{$a}\' has been saved.';
$string['termseen'] = 'Term seen';

$string['step1practicetype'] = 'Step 1 activity';
$string['step2practicetype'] = 'Step 2 activity';
$string['step3practicetype'] = 'Step 3 activity';
$string['step4practicetype'] = 'Step 4 activity';
$string['step5practicetype'] = 'Step 5 activity';
$string['matchselect'] = 'Choose match';
$string['matchtype'] = 'Type match';
$string['dictation'] = 'Dictation';
$string['scatter'] = 'Scatter';
$string['speechcards'] = 'Speech Cards';


$string['apiuser']='Poodll API User ';
$string['apiuser_details']='The Poodll account username that authorises Poodll on this site.';
$string['apisecret']='Poodll API Secret ';
$string['apisecret_details']='The Poodll API secret. See <a href= "https://support.poodll.com/support/solutions/articles/19000083076-cloud-poodll-api-secret">here</a> for more details';
$string['useast1'] = 'US East';
$string['tokyo'] = 'Tokyo, Japan';
$string['sydney'] = 'Sydney, Australia';
$string['dublin'] = 'Dublin, Ireland';
$string['ottawa'] = 'Ottawa, Canada';
$string['frankfurt'] = 'Frankfurt, Germany';
$string['london'] = 'London, U.K';
$string['saopaulo'] = 'Sao Paulo, Brazil';
$string['mumbai'] = 'Mumbai, India';
$string['singapore'] = 'Singapore';
$string['forever']='Never expire';

$string['en-us'] = 'English (US)';
$string['en-gb'] = 'English (GB)';
$string['en-au'] = 'English (AU)';
$string['en-in'] = 'English (IN)';
$string['es-es'] = 'Spanish (ES)';
$string['es-us'] = 'Spanish (US)';
$string['fr-fr'] = 'French (FR.)';
$string['fr-ca'] = 'French (CA)';
$string['ko-kr'] = 'Korean(KR)';
$string['pt-br'] = 'Portuguese(BR)';
$string['it-it'] = 'Italian(IT)';
$string['de-de'] = 'German(DE)';
$string['hi-in'] = 'Hindi(IN)';
$string['ko-kr'] = 'Korean';
$string['ar-ae'] = 'Arabic (Gulf)';
$string['ar-sa'] = 'Arabic (Modern Standard)';
$string['zh-cn'] = 'Chinese (Mandarin-Mainland)';
$string['nl-nl'] = 'Dutch';
$string['en-ie'] = 'English (Ireland)';
$string['en-wl'] = 'English (Wales)';
$string['en-ab'] = 'English (Scotland)';
$string['fa-ir'] = 'Farsi';
$string['de-ch'] = 'German (Swiss)';
$string['he-il'] = 'Hebrew';
$string['id-id'] = 'Indonesian';
$string['ja-jp'] = 'Japanese';
$string['ms-my'] = 'Malay';
$string['pt-pt'] = 'Portuguese (PT)';
$string['ru-ru'] = 'Russian';
$string['ta-in'] = 'Tamil';
$string['te-in'] = 'Telegu';
$string['tr-tr'] = 'Turkish';

$string['awsregion']='AWS Region';
$string['region']='AWS Region';
$string['expiredays']='Days to keep file';
$string['displaysubs'] = '{$a->subscriptionname} : expires {$a->expiredate}';
$string['noapiuser'] = "No API user entered. Word Cards will not work correctly.";
$string['noapisecret'] = "No API secret entered. Word Cards will not work correctly.";
$string['credentialsinvalid'] = "The API user and secret entered could not be used to get access. Please check them.";
$string['appauthorised']= "Poodll Word Cards is authorised for this site.";
$string['appnotauthorised']= "Poodll Word Cards is NOT authorised for this site.";
$string['refreshtoken']= "Refresh license information";
$string['notokenincache']= "Refresh to see license information. Contact Poodll support if there is a problem.";
//these errors are displayed on activity page
$string['nocredentials'] = 'API user and secret not entered. Please enter them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['novalidcredentials'] = 'API user and secret were rejected and could not gain access. Please check them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['nosubscriptions'] = "There is no current subscription for this site/plugin.";

$string['transcriber'] = 'Transcriber';
$string['transcriber_details'] = 'The transcription engine to use';
$string['transcriber_none'] = 'No transcription';
$string['transcriber_amazontranscribe'] = 'Regular Transcription';
$string['transcriber_googlecloud'] = 'Fast Transcription (< 60s only)';
$string['enabletts_details'] = 'TTS is currently not implemented';
$string['ttslanguage'] = 'Target Language';
$string['ttsvoice'] = 'TTS Voice';
$string['ttsvoice_help'] = 'These are the machine voices that will read your words when users press the listen icons or do dictation activity. TTS is not used if you have uploaded an audio file for the word. The voices are limited to those for the language and dialect specified in the \'target language\' setting for the activity.';
$string['alternates'] = 'Acceptable mistranscribes';
$string['alternates_help'] = 'Enter a comma separated list of acceptable speech recognition mistranscriptions here. eg For the word \'seventy\' , \'70\' and \'seven tea\' would be ok so you might enter <i>\'70, seven tea\'</i>. Only use this if recognition is failing for the term.';

$string['audiofile'] = 'Audio file';
$string['audiofile_help'] = 'Upload an audio file illustrating the pronunciation of the word/phrase. Ths will be used in place of machine voices in dication and when students use the audio player icons for the word.';
$string['imagefile'] = 'Image file';
$string['imagefile_help'] = 'Upload an image file to be displayed on the cards.';
$string['starttest'] = 'Begin';
$string['quit'] = 'Quit';
$string['next'] = 'Next';
$string['previous'] = 'Prev';
$string['ok'] = 'OK';
$string['listen'] = 'Listen';
$string['delete'] = 'Delete';
$string['submit'] = 'Submit';
$string['flip'] = 'Flip';
$string['word'] = 'Word';
$string['meaning'] = 'Meaning';
$string['correct'] = 'Correct';
$string['backtostart'] = 'Back to Start';
$string['loading'] = 'Loading';
$string['title_matchselect'] = 'Choose the Answer';
$string['title_matchtype'] = 'Type the Answer';
$string['title_dictation'] = 'Listen and Type';
$string['title_scatter'] = 'Match the Words';
$string['title_speechcards'] = 'Say the Words';
$string['title_listenchoose'] = 'Listen and Choose';

$string['review'] = 'Review';
$string['practice'] = 'Practice';

$string['title_noactivity'] = 'None';
$string['title_matchselect_rev'] = 'Choose the Answer (Review)';
$string['title_matchtype_rev'] = 'Type the Answer (Review)';
$string['title_dictation_rev'] = 'Listen and Type (Review)';
$string['title_scatter_rev'] = 'Match the Words (Review)';
$string['title_speechcards_rev'] = 'Say the Words (Review)';
$string['title_listenchoose_rev'] = 'Listen and Choose (Review)';

$string['title_vocablist'] = 'Get Ready';
$string['instructions_matchselect'] = 'Tap the best match from the choices below for the highlighted word.';
$string['instructions_matchtype'] = 'Type the best match for the highlighted word.';
$string['instructions_dictation'] = 'Listen and type the word(s) that you hear. Tap the blue button to hear the word(s).';
$string['instructions_scatter'] = 'Match the cards with the same meaning, by tapping them,';
$string['instructions_speechcards'] = 'Tap the blue button and speak the word(s) shown on the card. Speak slowly and clearly.';
$string['instructions_vocablist'] = 'Review the words that will be used in this activity. Tap the word card or the \'Flip\' button to show the other side of the cards. When you are ready, tap \'Begin\' to test your knowledge of these words.';
$string['pushtospeak'] = 'Tap to Speak';

//Reports
$string['itemsperpage'] = "Items per Page";
$string['itemsperpage_details'] = "";
$string['tabreports'] = "Reports";
$string['reports'] = "Reports";
$string['deleteattemptconfirm'] = "Really delete this attempt?";
$string['delete'] = "Delete";
$string['attemptsreport'] = "All Attempts Report";
$string['attemptsheading'] = "All Attempts Report";
$string['basicheading'] = "Basic Report";
$string['id'] = "ID";
$string['name'] = "Name";
$string['username'] = "Username";
$string['grade'] = "Grade";
$string['grade_p'] = "Total Grade(%)";
$string['timecreated'] = "Created";
$string['deletenow'] = "Delete";

$string['returntoreports'] = "Return to Reports";
$string['exportexcel'] = "Export to Excel";
$string['nodataavailable'] = "No  data available";

$string['maxattempts'] = "Maximum Attempts";
$string['unlimited'] = "Unlimited";

//grades report
$string['grades'] = "Grades";
$string['userattemptsheading'] = "User Attempts Report";
$string['gradesheading'] = "Grades Report";
$string['gradesreport'] = "Grades Report";
$string['grade1_p'] = "Step1(%)";
$string['grade2_p'] = "Step2(%)";
$string['grade3_p'] = "Step3(%)";
$string['grade4_p'] = "Step4(%)";
$string['grade5_p'] = "Step5(%)";
$string['attempts'] = "Attempts";
$string['reportsmenutop']="Choose from the reports available below. You can export the data to CSV using the button on the lower right of the report when displayed.";
$string['try_again'] = "Try again";
$string['next_step'] = "Next";
$string['done'] = 'Next';
$string['skip'] = 'Skip';
$string['reattempt'] = 'Try Again';
$string['continue'] = 'Continue';
$string['reattempttitle'] = 'Really Try Again?';
$string['reattemptbody'] = 'If you continue your previous attempt will be replaced with this one. OK?';
$string['importinstructions']='You can import lists of words using the \'import data\' text area below. Each line should contain one term(word/phrase) and it\'s definition separated by a delimiter. Optionally specify 3rd and 4th fields for TTS voice and model sentence. You can choose a delimiter from the dropdown box below. The format of each line should be:<br> new-word | definition | TTS Voice | Model Sentence<br> Each line therefore should look something like this:<br> <i>Bonjour | Hello| Celine | Bonjour Monsieur</i>';
$string['managewordsinstructions']="Use the 'Add New' button to add new words for the activity. You can view, edit and delete previously added words from the table at the bottom of the page. Only the term and definition are required.";
$string['model_sentence'] = 'Model sentence';
$string['model_sentence_audio'] = 'Model sentence audio';
$string['model_sentence_help'] = 'Enter model sentence of the term(word/phrase) here. It should be short but still tell the student what the term means.';
$string['audioandimages'] = 'Audio and Images';
$string['addnewterm']= "Add New";
$string['enablesetuptab']="Enable setup tab";
$string['enablesetuptab_details']="Show a tab containing the activity instance settings to admins. Not super useful in most cases.";
$string['setup']="Setup";
$string['tabsetup']="Setup";
