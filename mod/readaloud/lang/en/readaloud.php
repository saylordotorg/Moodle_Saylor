<?php

/**
 * English strings for readaloud
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_readaloud
 * @copyright  2015 Justin Hunt (poodllsupport@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Poodll ReadAloud';
$string['modulenameplural'] = 'Poodll ReadAlouds';
$string['modulename_help'] =
        'ReadAloud is an activity designed to assist teachers in evaluating their students reading fluency. Students read a passage, set by the teacher, into a microphone. Later the teacher can mark words as incorrect and get the student WCPM(Words Correct Per Minute) scores.';
$string['readaloudfieldset'] = 'Custom example fieldset';
$string['readaloudname'] = 'Poodll ReadAloud';
$string['readaloudname_help'] =
        'This is the content of the help tooltip associated with the readaloudname field. Markdown syntax is supported.';
$string['readaloud'] = 'readaloud';
$string['activitylink'] = 'Link to next activity';
$string['activitylink_help'] =
        'To provide a link after the attempt to another activity in the course, select the activity from the dropdown list.';
$string['activitylinkname'] = 'Continue to next activity: {$a}';
$string['pluginadministration'] = 'ReadAloud Administration';
$string['pluginname'] = 'Poodll ReadAloud';
$string['someadminsetting'] = 'Some Admin Setting';
$string['someadminsetting_details'] = 'More info about Some Admin Setting';
$string['someinstancesetting'] = 'Some Instance Setting';
$string['someinstancesetting_details'] = 'More info about Some Instance Setting';
$string['readaloudsettings'] = 'readaloud settings';
$string['readaloud:addinstance'] = 'Add a new Read Aloud';
$string['readaloud:view'] = 'View Read Aloud';
$string['readaloud:view'] = 'Preview Read Aloud';
$string['readaloud:itemview'] = 'View items';
$string['readaloud:itemedit'] = 'Edit items';
$string['readaloud:tts'] = 'Can use Text To Speech(tts)';
$string['readaloud:manageattempts'] = 'Can manage Read Aloud attempts';
$string['readaloud:manage'] = 'Can manage Read Aloud instances';
$string['readaloud:preview'] = 'Can preview Read Aloud activities';
$string['readaloud:submit'] = 'Can submit Read Aloud attempts';
$string['readaloud:viewreports'] = 'Can view Read Aloud grades and reports';
$string['privacy:metadata'] = 'The Poodll Read Aloud plugin does store personal data.';

$string['id'] = 'ID';
$string['name'] = 'Name';
$string['timecreated'] = 'Time Created';
$string['basicheading'] = 'Basic Report';
$string['attemptsheading'] = 'Attempts Report';
$string['attemptsbyuserheading'] = 'User Attempts Report';
$string['attemptssummaryheading'] = 'Attempts Summary Report';
$string['gradingheading'] = 'Grading latest attempts for each user.';
$string['machinegradingheading'] = 'Machine evaluated latest attempt for each user.';
$string['gradingbyuserheading'] = 'Grading all attempts for: {$a}';
$string['machinegradingbyuserheading'] = 'Machine evaluated attempts for: {$a}';
$string['totalattempts'] = 'Attempts';
$string['overview'] = 'Overview';
$string['overview_help'] = 'Overview Help';
$string['view'] = 'View';
$string['preview'] = 'Preview';
$string['viewreports'] = 'View Reports';
$string['reports'] = 'Reports';
$string['viewgrading'] = 'View Grading';
$string['grading'] = 'Grading';
$string['gradenow'] = 'Grade Now';
$string['cannotgradenow'] = ' - ';
$string['gradenowtitle'] = 'Grading: {$a}';
$string['showingattempt'] = 'Showing attempt for: {$a}';
$string['showingmachinegradedattempt'] = 'Machine evaluated attempt for: {$a}';
$string['basicreport'] = 'Basic Report';
$string['returntoreports'] = 'Return to Reports';
$string['returntogradinghome'] = 'Return to Grading Top';
$string['returntomachinegradinghome'] = 'Return to Machine Evaluations Top';
$string['exportexcel'] = 'Export to CSV';
$string['mingradedetails'] = 'The minimum Read Aloud grade(%) required to "complete" this activity.';
$string['mingrade'] = 'Minimum Grade';
$string['deletealluserdata'] = 'Delete all user data';
$string['maxattempts'] = 'Max. Attempts';
$string['unlimited'] = 'unlimited';
$string['gradeoptions'] = 'Grade Options';
$string['gradeoptions_help'] =
        'When there are multiple attempts by a user on a reading, this setting determines which attempt to use when grading';
$string['gradenone'] = 'No grade';
$string['gradelowest'] = 'lowest scoring attempt';
$string['gradehighest'] = 'highest scoring attempt';
$string['gradelatest'] = 'score of latest attempt';
$string['gradeaverage'] = 'average score of all attempts';
$string['defaultsettings'] = 'Default Settings';
$string['exceededattempts'] = 'You have completed the maximum {$a} attempts.';
$string['readaloudtask'] = 'Read Aloud Task';
$string['passagelabel'] = 'Reading Passage';
$string['welcomelabel'] = 'Default instructions';
$string['welcomelabel_details'] = 'The default instructions. Can be edited when creating a new Read Aloud activity.';
$string['feedbacklabel'] = 'Default Feedback';
$string['feedbacklabel_details'] = 'The default text to show in the feedback field when creating a new Read Aloud activity.';
$string['welcomelabel'] = 'Pre-attempt instructions';
$string['feedbacklabel'] = 'Post-attempt instructions';
$string['alternatives'] = 'Alternatives';
$string['alternatives_descr'] =
        'Specify matching options for specific passage words. 1 word set per line. e.g their|there|they\'re See <a href="https://support.poodll.com/support/solutions/articles/19000096937-tuning-your-read-aloud-activity">docs</a> for more details.';

$string['defaultwelcome'] =
        'In this activity you should read a passage out loud. You may be required to test your microphone first. You should see the audio recorder below. After you have started recording the reading passage will appear. Read the passage aloud as clearly as you can.';
$string['defaultfeedback'] = 'Thanks for reading.';
$string['timelimit'] = 'Time Limit';
$string['gotnosound'] = 'We could not hear you. Please check the permissions and settings for microphone and try again.';
$string['done'] = 'Done';
$string['processing'] = 'Processing';
$string['feedbackheader'] = 'Finished';
$string['beginreading'] = 'Begin Reading';
$string['errorheader'] = 'Error';
$string['uploadconverterror'] =
        'An error occured while posting your file to the server. Your submission has NOT been received. Please refresh the page and try again.';
$string['attemptsreport'] = 'Attempts Report';
$string['attemptssummaryreport'] = 'Attempts Summary Report';
$string['myattemptssummary'] = 'Attempts Summary ({$a} attempts)';
$string['summaryexplainer'] = 'The table below shows your average and your highest scores for this activity.';
$string['averages'] = 'Average';
$string['highest'] = 'Highest';
$string['submitted'] = 'submitted';
$string['id'] = 'ID';
$string['username'] = 'User';
$string['audiofile'] = 'Audio';
$string['wpm'] = 'WPM';
$string['timecreated'] = 'Time Created';
$string['nodataavailable'] = 'No Data Available Yet';
$string['saveandnext'] = 'Save .... and next';
$string['reattempt'] = 'Try Again';
$string['notgradedyet'] = 'Your submission has been received, but has not been graded yet. It might take a few minutes.';
$string['evaluatedmessage'] = 'Your latest attempt has been received and the evaluation is shown below.';
$string['enabletts'] = 'Enable TTS(experimental)';
$string['enabletts_details'] = 'TTS is currently not implemented';
//we hijacked this setting for both TTS STT .... bad ... but they are always the same aren't they?
$string['ttslanguage'] = 'Passage Language';
$string['ttslanguage_details'] = 'This value is used for speech recognition and text to speech.';
$string['deleteattemptconfirm'] = "Are you sure that you want to delete this attempt?";
$string['deletenow'] = '';
$string['allowearlyexit'] = 'Can exit early';
$string['allowearlyexit_details'] =
        'If checked students can finish before the time limit, by pressing a finish button. The WPM is calculated using their recording time.';
$string['allowearlyexit_defaultdetails'] =
        'Sets the default setting for allow_early_exit. Can be overriden at the activity level. If true, allow_early_exit means that students can finish before the time limit, by pressing a finish button. The WPM is calculated using their recording time.';
$string['itemsperpage'] = 'Items per page';
$string['itemsperpage_details'] = 'This sets the number of rows to be shown on reports or lists of attempts.';
$string['accuracy'] = 'Accuracy';
$string['accuracy_p'] = 'Acc(%)';
$string['av_accuracy_p'] = 'Av. Acc(%)';
$string['h_accuracy_p'] = 'Max Acc(%)';
$string['mistakes'] = 'Mistakes';
$string['grade'] = 'Grade';
$string['grade_p'] = 'Grade(%)';
$string['av_grade_p'] = 'Av. Grade(%)';
$string['h_grade_p'] = 'Max Grade(%)';
$string['av_wpm'] = 'Av. WPM';
$string['h_wpm'] = 'Max WPM';
$string['targetwpm'] = 'Target WPM';
$string['targetwpm_details'] =
        'The default target WPM. A students grade is calculated for the gradebook using this value as the maximum score. If their WPM score is equal to, or greater than the target WPM, they will score 100%. The target WPM can also be set at the activity instance level. ';
$string['targetwpm_help'] =
        'The target WPM score. A students grade is calculated for the gradebook using this value as the maximum score. If their WPM score is equal to, or greater than the target WPM, they will score 100%.';
$string['passage_editor'] = 'Reading Passage';
$string['passage_editor_help'] = "The passage that will be shown to the student to read. Numbers should be written as words, or a wildcard entry added to alternatives. eg 1986|* . Plain text with no formatting is safer.";
$string['passage_help'] = "The passage that will be shown to the student to read. Numbers should be written as words, or a wildcard entry added to alternatives. eg 1986|* . Plain text with no formatting is safer.";
$string['timelimit_help'] = "Sets a time limit on the reading. Reading time is used in the WPM calculation. Consider also checking - Allow Early Exit";
$string['ttslanguage_help'] = "This value is used for speech recognition and text to speech.";
$string['ttsvoice_help'] = "The machine voice used to read the passage aloud. You should select a voice that matches the language famly of the passage language. Use the model audio tab to record or upload an alternative model audio.";
$string['ttsspeed_help'] = "The machine voice reading speed. Slow or Extra Slow are good for learners, but can distort the audio.";
$string['alternatives_help'] = "Specify matching options for specific passage words. 1 word set per line. e.g their|there|they're See <a href=\"https://support.poodll.com/support/solutions/articles/19000096937-tuning-your-read-aloud-activity\">docs</a> for more details.";

$string['accadjust'] = 'Fixed adjustment.';
$string['accadjust_details'] =
        'This is the number of reading errors to compensate WPM scores for. If WPM adjust is set to "Fixed" then this value will be used to compensate WPM acores. This is a method of mitigating for machine transcription mistakes.';
$string['accadjust_help'] =
        'This rate should correspond as closely as possible to the estimated machine transcription mistake average for a passage.';

$string['accadjustmethod'] = 'WPM Adjust(AI)';
$string['accadjustmethod_details'] =
        'Adjust the WPM score by ignoring, or discounting some, reading errors found by AI. The default \'No adjustment\' subtracts all reading errors from final WPM score. ';
$string['accadjustmethod_help'] =
        'For WPM adjustment we can: never adjust, adjust by a fixed amount, or ignore errors when calculating WPM';
$string['accmethod_none'] = 'No adjustment';
$string['accmethod_auto'] = 'Auto audjustment';
$string['accmethod_fixed'] = 'Adjust by fixed amount';
$string['accmethod_noerrors'] = 'Ignore all errors';

$string['apiuser'] = 'Poodll API User ';
$string['apiuser_details'] = 'The Poodll account username that authorises Poodll on this site.';
$string['apisecret'] = 'Poodll API Secret ';
$string['apisecret_details'] =
        'The Poodll API secret. See <a href= "https://support.poodll.com/support/solutions/articles/19000083076-cloud-poodll-api-secret">here</a> for more details';
$string['enableai'] = 'Enable AI';
$string['enableai_details'] = 'Read Aloud can evaluate results from a student attempt using AI. Check to enable.';

$string['useast1'] = 'US East';
$string['tokyo'] = 'Tokyo, Japan';
$string['sydney'] = 'Sydney, Australia';
$string['dublin'] = 'Dublin, Ireland';
$string['capetown'] = 'Capetown, South Africa';
$string['bahrain'] = 'Bahrain';
$string['ottawa'] = 'Ottawa, Canada';
$string['frankfurt'] = 'Frankfurt, Germany';
$string['london'] = 'London, U.K';
$string['saopaulo'] = 'Sao Paulo, Brazil';
$string['singapore'] = 'Singapore';
$string['mumbai'] = 'Mumbai, India';
$string['forever'] = 'Never expire';

$string['en-us'] = 'English (US)';
$string['es-us'] = 'Spanish (US)';
$string['en-au'] = 'English (Aus.)';
$string['en-gb'] = 'English (GB)';
$string['fr-ca'] = 'French (Can.)';
$string['fr-fr'] = 'French (FR)';
$string['it-it'] = 'Italian (IT)';
$string['pt-br'] = 'Portuguese (BR)';
$string['en-in'] = 'English (IN)';
$string['es-es'] = 'Spanish (ES)';
$string['fr-fr'] = 'French (FR)';
$string['de-de'] = 'German (DE)';
$string['da-dk'] = 'Danish (DK)';
$string['hi-in'] = 'Hindi';
$string['ko-kr'] = 'Korean';
$string['ar-ae'] = 'Arabic (Gulf)';
$string['ar-sa'] = 'Arabic (Modern Standard)';
$string['zh-cn'] = 'Chinese (Mandarin-Mainland)';
$string['nl-nl'] = 'Dutch';
$string['en-ie'] = 'English (Ireland)';
$string['en-wl'] = 'English (Wales)';
$string['en-ab'] = 'English (Scotland)';
$string['en-nz'] = 'English (New Zealand)';
$string['en-za'] = 'English (South Africa)';
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

$string['awsregion'] = 'AWS Region';
$string['region'] = 'AWS Region';
$string['awsregion_details']='Choose the region closest to you. Your data will stay within that region. Capetown region only supports English and German.';
$string['expiredays'] = 'Days to keep file';
$string['aigradenow'] = 'AI Grade';

$string['machinegrading'] = 'Machine Evaluations';
$string['viewmachinegrading'] = 'Machine Evaluation';
$string['review'] = 'Review';
$string['regrade'] = 'Regrade';

$string['dospotcheck'] = "Spot Check";
$string['spotcheckbutton'] = "Spot Check Mode";
$string['gradingbutton'] = "Grading Mode";
$string['transcriptcheckbutton'] = "Transcript Check Mode";
$string['doaigrade'] = "AI Grade";
$string['doclear'] = "Clear all markers";

$string['gradethisattempt'] = "Grade this attempt";
$string['rawwpm'] = "WPM";
$string['rawaccuracy_p'] = 'Acc(%)';
$string['rawgrade_p'] = 'Grade(%)';
$string['adjustedwpm'] = "Adj. WPM";
$string['adjustedaccuracy_p'] = 'Adj. Acc(%)';
$string['adjustedgrade_p'] = 'Adj. Grade(%)';

$string['evaluationview'] = "Evaluation display";
$string['evaluationview_details'] = "What to show students after they have attempted and received an evaluation";
$string['humanpostattempt'] = "Evaluation display (human)";
$string['humanpostattempt_details'] = "What to show students after they have attempted and received a human evaluation";
$string['machinepostattempt'] = "Evaluation display (machine)";
$string['machinepostattempt_details'] = "What to show students after they have attempted and received a machine evaluation";
$string['postattempt_none'] = "Show the passage. Don't show evaluation or errors.";
$string['postattempt_eval'] = "Show the passage, and evaluation(WPM,Acc,Grade)";
$string['postattempt_evalerrorsnograde'] = "Show the passage, evaluation(WPM, Acc) and errors";
$string['postattempt_evalerrors'] = "Show the passage, evaluation(WPM,Acc,Grade) and errors";


$string['attemptsperpage'] = "Attempts to show per page: ";
$string['backtotop'] = "Check for Results";
$string['transcript'] = "Transcript";
$string['quickgrade'] = "Quick Grade";
$string['ok'] = "OK";
$string['ng'] = "Not OK";
$string['notok'] = "Not OK";
$string['machinegrademethod'] = "Human/Machine Grading";
$string['machinegrademethod_details'] = "Use machine evaluations or human evaluations as grades in grade book.";
$string['machinegrademethod_help'] = "Use machine evaluations or human evaluations as grades in grade book.";
$string['machinegradenone'] = "Never use machine eval. for grade";
$string['machinegradehybrid'] = "Use human or machine eval. for grade";
$string['machinegrademachineonly'] = "Always use machine eval. grade";
$string['gradesadmin'] = "Alternatives Admin";
$string['viewgradesadmin'] = 'Grades Admin';
$string['machineregradeall'] = 'Save and re-evaluate all attempts';
$string['pushmachinegrades'] = 'Push machine evaluations to gradebook';
$string['currenterrorestimate'] = 'Current error estimate: {$a}';
$string['gradesadmintitle'] = 'Alternatives Administration';
$string['gradesadmininstructions'] =
        'On this page you can edit the alternatives for the passage while viewing a summary of the mistranscriptions. When you save, all the attempts will be re-evaluated and the adjusted grades to the gradebook.';

$string['noattemptsregrade'] = 'No attempts to regrade';
$string['machineregraded'] = 'Successfully regraded {$a->done} attempts. Skipped {$a->skipped} attempts.';
$string['machinegradespushed'] = 'Successfully pushed grades to gradebook';

$string['notimelimit'] = 'No time limit';
$string['xsecs'] = '{$a} seconds';
$string['onemin'] = '1 minute';
$string['xmins'] = '{$a} minutes';
$string['oneminxsecs'] = '1 minutes {$a} seconds';
$string['xminsecs'] = '{$a->minutes} minutes {$a->seconds} seconds';

$string['postattemptheader'] = 'Post attempt options';
$string['recordingaiheader'] = 'Recording and AI options';

$string['grader'] = 'Graded by';
$string['grader_ai'] = 'AI';
$string['grader_human'] = 'Human';
$string['grader_ungraded'] = 'Ungraded';

$string['displaysubs'] = '{$a->subscriptionname} : expires {$a->expiredate}';
$string['noapiuser'] = "No API user entered. Read Aloud will not work correctly.";
$string['noapisecret'] = "No API secret entered. Read Aloud will not work correctly.";
$string['credentialsinvalid'] = "The API user and secret entered could not be used to get access. Please check them.";
$string['appauthorised'] = "Poodll Read Aloud is authorised for this site.";
$string['appnotauthorised'] = "Poodll Read Aloud is NOT authorised for this site.";
$string['refreshtoken'] = "Refresh license information";
$string['notokenincache'] = "Refresh to see license information. Contact Poodll support if there is a problem.";
//these errors are displayed on activity page
$string['nocredentials'] = 'API user and secret not entered. Please enter them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['novalidcredentials'] = 'API user and secret were rejected and could not gain access. Please check them on <a href="{$a}">the settings page.</a> You can get them from <a href="https://poodll.com/member">Poodll.com.</a>';
$string['nosubscriptions'] = "There is no current subscription for this site/plugin.";

$string['privacy:metadata:attemptid'] = 'The unique identifier of a users Read aloud attempt.';
$string['privacy:metadata:readaloudid'] = 'The unique identifier of a Read Aloud activity instance.';
$string['privacy:metadata:userid'] = 'The user id for the Read Aloud attempt';
$string['privacy:metadata:filename'] = 'File urls of submitted recordings.';
$string['privacy:metadata:wpm'] = 'The Words Per Minute score for the attempt';
$string['privacy:metadata:accuracy'] = 'The accuracy score for the attempt';
$string['privacy:metadata:sessionscore'] = 'The session score for the attempt';
$string['privacy:metadata:sessiontime'] = 'The session time(recording time) for the attempt';
$string['privacy:metadata:sessionerrors'] = 'The reading errors for the attempt';
$string['privacy:metadata:sessionendword'] = 'The position of last word for the attempt';
$string['privacy:metadata:errorcount'] = 'The reading error count for the attempt';
$string['privacy:metadata:timemodified'] = 'The last time attempt was modified for the attempt';
$string['privacy:metadata:attempttable'] = 'Stores the scores and other user data associated with a read aloud attempt.';
$string['privacy:metadata:aitable'] =
        'Stores the scores and other user data associated with a read aloud attempt as evaluated by machine.';
$string['privacy:metadata:transcriptpurpose'] = 'The recording short transcripts.';
$string['privacy:metadata:fulltranscriptpurpose'] = 'The full transcripts of recordings.';
$string['privacy:metadata:cloudpoodllcom:userid'] =
        'The ReadAloud plugin includes the moodle userid in the urls of recordings and transcripts';
$string['privacy:metadata:cloudpoodllcom'] = 'The ReadAloud plugin stores recordings in AWS S3 buckets via cloud.poodll.com.';

$string['mistranscriptions_summary'] = 'Summary of mistranscriptions.';
$string['nomistranscriptions'] = 'No mistranscriptions.';
$string['passageindex'] = 'Passage Index';
$string['passageword'] = 'Passage Word';
$string['mistranscriptions'] = 'Mistranscriptions';
$string['mistrans_count'] = 'Count';
$string['total_mistranscriptions'] = 'Total mistranscriptions: {$a}';

$string['previewreading'] = 'Listen';
$string['startreading'] = 'Read';
$string['startshadowreading'] = 'Shadow Practice';
$string['landrreading'] = 'Practice';


$string['transcriber'] = 'Transcriber';
$string['transcriber_details'] = 'The transcription engine to use. AWS works best with ReadAloud.';
$string['transcriber_none'] = 'No transcription';
$string['transcriber_amazontranscribe'] = 'Regular Transcription(AWS): recommended';
$string['transcriber_googlecloud'] = 'Quick Transcription(Google)(audio length < 60s only)';

$string['stricttranscribe'] = 'Strict Transcription';
$string['stricttranscribemode_details'] = 'By default ReadAloud transcribes generously in English and German. Set to strict to get more speech mistakes for full passage readings in those languages.';

$string['submitrawaudio'] = 'Submit uncompressed audio';
$string['submitrawaudio_details'] = 'Submitting uncompressed audio may increase transcription accuracy, but at the expense of upload speed and reliability.';

$string['sessionscoremethod'] = 'Grade Calculation';
$string['sessionscoremethod_details'] = 'How the value(%) for gradebook is calculated.';
$string['sessionscoremethod_help'] = 'The value(%) for gradebook is calculated as a percentage, either WPM / Target_WPM (normal) or (WPM - Errors)/ Target_WPM (strict)';
$string['sessionscorenormal'] = 'Normal: Total correct words per min / Target_WPM';
$string['sessionscorestrict'] = 'Strict: (Total correct words - errors) per min /Target WPM';
$string['modelaudio'] = 'Model Audio';
$string['ttsvoice'] = 'TTS Voice';
$string['enablepreview'] = 'Enable Listen mode';
$string['enablepreview_details'] = 'Listen mode shows the reading and model audio to student before the activity commences.';
$string['enableshadow'] = 'Enable Practice mode (Shadowing)';
$string['enableshadow_details'] = 'Enables shadowing mode. This plays the model audio as students are read the entire passage aloud. Students will need headphones for this.';
$string['enablelandr'] = 'Enable Practice mode (Listen and Repeat)';
$string['enablelandr_details'] = 'Enables listen and repeat mode. Line by line, the student listens and reads alternately.';
$string['savemodelaudio'] = 'Save Recording';
$string['uploadmodelaudio'] = 'Upload Audio File';
$string['modelaudioclear'] = 'Clear Audio';
$string['modelaudio_recordinstructions'] = 'Record audio here to be used as the model audio. You can optionally choose to upload audio by pressing the upload audio button. There will be a delay of a few minutes before break point text and audio are automatically synced';
$string['modelaudio_playerinstructions'] = 'The current model audio can be played using the player below.';
$string['modelaudio_breaksinstructions'] = 'Tap words in the passage below to add a break at that point in the audio playback in preview and practive modes. The system will automatically sync the audio and the text. Check <i>manual break timing</i> to set tapped breaks to current location of playing audio.';
$string['modelaudio_recordtitle'] = 'Record Model Audio';
$string['modelaudio_playertitle'] = 'Play Model Audio';
$string['modelaudio_breakstitle'] = 'Mark-up Model Audio';
$string['viewmodeltranscript'] = 'View Model Transcript';

$string['ttsspeed'] = 'TTS Speed';
$string['mediumspeed'] = 'Medium';
$string['slowspeed'] = 'Slow';
$string['extraslowspeed'] = 'Extra Slow';


$string['welcomemenu'] = 'Choose from the options below.';
$string['returnmenu'] = 'Return to Menu';
$string['attemptno'] = 'Attempt {$a}';
$string['progresschart'] = 'Progress Chart';
$string['chartexplainer'] = 'The chart below shows your progress over time in reading this passage.';

$string['previewhelp'] = "Listen to a speaker read the passage aloud. You do not need to read aloud.";
$string['normalhelp'] = "Read the passage aloud. Speak at a speed that is natural for you.";
$string['shadowhelp'] = "Read the passage aloud, along with the teacher. You should wear headphones.";
$string['landrhelp'] = "Listen to the speaker. Repeat after each sentence and check your pronunciation.";
$string['playbutton'] = "Play";
$string['stopbutton'] = "Stop";

$string['returntomenu']="Return to Menu";
$string['fullreport'] = "View Full Report";
$string['nocourseid'] = 'You must specify a course_module ID or an instance ID. Probably your session expired.';

$string['secs_till_check']='Checking for results in: ';
$string['checking']=' ... checking ... ';

$string['recorder']='Audio recorder type';
$string['recorder_help']='Choose the audio recorder type that best suits your students and situation.';
$string['defaultrecorder']='Default recorder';
$string['defaultrecorder_details']='Choose the default recorder to be shown to students. ';
$string['rec_readaloud']='Mic-test then start';
$string['rec_once']='Just start';
$string['rec_upload']='Upload (for devs/admins)';

$string['transcriber_warning']='You have selected instant transcription. Note that this will <strong>only work if passage language and region are correct</strong>.';

$string['close']='Close';
$string['modelaudiowarning']="<span style='color: red'>Model audio not marked up.</span>";
$string['modelaudiobreaksclear']=' Clear model audio markup';
$string['savemodelaudiomarkup']=' Save model audio markup';
$string['enablesetuptab']="Enable setup tab";
$string['enablesetuptab_details']="Show a tab containing the activity instance settings to admins. Not super useful in most cases.";
$string['setup']="Setup";
$string['failedttsmarkup']='Unable to mark up speech..';
$string['manualbreaktiming']=' Manual break timing';

$string['nopassage']="No Reading Passage";
$string['addpassage']="Setup Activity";
$string['waitforpassage']="There is no reading passage set yet for this activity. You will not be able to do the activity until your teacher adds one";
$string['letsaddpassage']="There is no reading passage set yet for this activity. Lets add one.";

$string['readaloud:itemview'] = 'View questions';
$string['readaloud:itemedit'] = 'Edit questions';

//rsquestions
$string['durationgradesettings'] = 'Grade Settings ';
$string['durationboundary']='{$a}: Completion time less than (seconds)';
$string['boundarygrade']='{$a}: points ';
$string['numeric']='Must be numeric ';
$string['iteminuse']= 'This item is part of users attempt history. It cannot be deleted.';
$string['moveitemup']='Up';
$string['moveitemdown']='Down';

//questions
$string['rsquestions'] ='Questions';
$string['managersquestions'] ='Manage Questions';
$string['correctanswer'] ='Correct answer';
$string['whatdonow'] = 'What would you like to do?';
$string['addnewitem'] = 'Add a New question';
$string['addingitem'] = 'Adding a New question';
$string['editingitem'] = 'Editing a question';
$string['addtextpromptshortitem']='Add item';
$string['createaitem'] = 'Create a question';
$string['edit'] = 'Edit';
$string['item'] = 'Item';
$string['itemtitle'] = 'Question Title';
$string['itemcontents'] = 'Question Description';
$string['answer'] = 'Answer';
$string['saveitem'] = 'Save item';
$string['audioitemfile'] = 'item Audio(MP3)';
$string['itemname'] = 'Question Name';
$string['itemorder'] = 'Item Order';
$string['correct'] = 'Correct';
$string['itemtype'] = 'Item Type';
$string['actions'] = 'Actions';
$string['edititem'] = 'Edit item';
$string['previewitem'] = 'Preview item';
$string['deleteitem'] = 'Delete item';
$string['confirmitemdelete'] = 'Are you sure you want to <i>DELETE</i> item? : {$a}';
$string['confirmitemdeletetitle'] = 'Really Delete item?';
$string['noitems'] = 'This quiz contains no questions';
$string['itemdetails'] = 'item Details: {$a}';
$string['itemsummary'] = 'item Summary: {$a}';
$string['iscorrectlabel'] = 'Correct/Incorrect';
$string['textchoice'] = 'Text Area Choice';
$string['textboxchoice'] = 'Text Box Choice';
$string['audioresponse'] = 'Audio response';
$string['correcttranslationtitle'] = 'Correct Translation';
$string['shuffleanswers'] = 'Shuffle Answers';
$string['shufflequestions'] = 'Shuffle Questions';
$string['correct'] = 'Correct';
$string['avgcorrect'] = 'Av. Correct';
$string['avgtotaltime'] = 'Av. Duration';
$string['nodataavailable'] = 'No data available';
$string['quiz'] = 'Quiz';
$string['waiting']='-- waiting --';
