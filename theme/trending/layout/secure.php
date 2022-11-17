<?php
require_once(dirname(__FILE__).'/header.php');
require_once(dirname(__FILE__).'/customscriptloadrenderer.php');
require_once(dirname(__FILE__).'/footerrenderer.php');
$bodyattributes = $OUTPUT->body_attributes();
$blockspre = $OUTPUT->blocks('side-pre');
$blockspost = $OUTPUT->blocks('side-post');
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$templatecontext = [
'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
'output' => $OUTPUT,
'sidepreblocks' => $blockspre,
'sidepostblocks' => $blockspost,
'haspreblocks' => $hassidepre,
'haspostblocks' => $hassidepost,
'bodyattributes' => $bodyattributes,
// Global
'favicon' => $favicon,
'logourl' => $logourl,
'internalbannertagline' => $internalbannertagline,
// Footer Section
'footerlogo' => $footerlogo,
'hasfacebook' => $hasfacebook,
'hastwitter' => $hastwitter,
'hasgoogleplus' => $hasgoogleplus,
'haspinterest' => $haspinterest,
'hasinstagram' => $hasinstagram,
'hasyoutube' => $hasyoutube,
'hasflickr' => $hasflickr,
'haswhatsapp' => $haswhatsapp,
'hasskype' => $hasskype,
'hasgithub' => $hasgithub,
'hascopyright' => $hascopyright,
'privacypolicy' => $privacypolicy,
'privacypolicyurl' => $privacypolicyurl,
'backtotop' => $backtotop,
'googleplayurl' => $googleplayurl,
'copyrightY' => $copyrightY,
'customscriptloadernoamd'=> $customscriptloadernoamd,
'footer'=> $footer
];
echo $OUTPUT->render_from_template('theme_trending/secure', $templatecontext);
