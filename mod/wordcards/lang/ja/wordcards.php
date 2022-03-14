<?php
/**
 * Displays information about the wordcards in the course.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

defined('MOODLE_INTERNAL') || die();

$string['activitycompleted'] = 'アクティビティ完了';
$string['completedmsg'] = '完成メッセージ';
$string['completedmsg_help'] = 'アクティビティが完了してから表示するメッセージです。';
$string['completionwhenfinish'] = '生徒は終わったら、アクティビティを完了する';
$string['congrats'] = 'おめでとうございます!';
$string['congratsitsover'] = '<div  style="text-align: center;">完了しました. 自由に「新用語練習」と「復習」をもう一度練習できます!</div>';
$string['definition'] = '意味';
$string['definitions'] = '意味';
$string['deleteterm'] = ' 新出用語の削除 \'{$a}\'';
$string['delimiter'] = '区切りの文字';
$string['delim_tab'] = 'タブ';
$string['delim_comma'] = 'カンマ';
$string['delim_pipe'] = 'パイプ';
$string['description'] = '説明';
$string['editterm'] = '新出用語の編集 \'{$a}\'';
$string['finishscatterin'] = '<div style="text-align: center;">おめでとうございます! <br/> 完成時間は [[time]]</div>';
$string['wordcards:addinstance'] = 'インスタンスの追加';
$string['wordcards:view'] = 'モジュールの表示';
$string['reviewactivity'] = '復習';
$string['reviewactivityfinished'] = '復習は {$a->seconds} 秒で完成しました。';
$string['step2termcount'] = '復習用語の数';
$string['gotit'] = 'OK!';
$string['import'] = 'インポート';
$string['importdata'] = 'インポート・データ';
$string['importresults'] = ' {$a->imported} 行のインポートは完成しました. {$a->failed} のインポートは失敗しました。';
$string['learnactivityfinished'] = '新出用語の練習は{$a->seconds} 秒で完成しました。';
$string['finishedstepmsg'] = '練習完成のメッセージ';
$string['finishedstepmsg_help'] = '新出用語練習か復習が完成後、表示するメッセージです。[[time]]の記号で完成する時間を表示できます。';
$string['step1termcount'] = '新出用語の数';
$string['loading'] = 'ロード中';
$string['learnactivity'] = '新出用語の練習';
$string['markasseen'] = '「チェック」をする';
$string['modulename'] = 'Wordcards';
$string['modulename_help'] = 'Wordcardsは単語・用語を暗記するアクティビティです。すでに習った単語の復習活動がふくめています。';
$string['modulenameplural'] = 'Wordcards';
$string['name'] = 'Name';
$string['nodefinitions'] = '用語はまだ入れていません。';
$string['noteaboutseenforteachers'] = //'注意: 生徒の進捗のみは記録されます.';
$string['pluginadministration'] = 'Wordcards 管理';
$string['pluginname'] = 'Wordcards';
$string['reallydeleteterm'] = '本当のこの用語を削除しますか？： \'{$a}\'?';
$string['setup'] = '設定';
$string['managewords'] = '用語の追加・編集';
$string['skipreview'] = '最初のコース練習を隠す';
$string['skipreview_help'] = '生徒はまだ、習った単語は「０」の場合、コース練習のタブを非表示する';
$string['tabdefinitions'] = '新出用語';
$string['tabglobal'] = '復習';
$string['tablocal'] = '新出用語練習';
$string['tabmanagewords'] = '用語の追加・編集';
$string['tabimport'] = 'インポート';
$string['term'] = '新出用語';
$string['termadded'] = '新出用語 \'{$a}\' は追加されました。';
$string['termdeleted'] = '新出用語は削除されました。';
$string['termnotseen'] = '新出用語はまだ「チェック」されていません。';
$string['termsaved'] = '新出用語は \'{$a}\' 保村されました。';
$string['termseen'] = '新出用語は「チェック」';
$string['model_sentence'] = 'モデル文';
$string['model_sentence_audio'] = 'モデルセンテンスオーディオ';
$string['model_sentence_help'] = '用語（単語/フレーズ）のモデル文をここに入力します。短くする必要がありますが、それでもその用語の意味を生徒に伝えます。';