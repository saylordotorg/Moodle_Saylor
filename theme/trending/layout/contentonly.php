<?php
require_once(dirname(__FILE__).'/header.php');
require_once(dirname(__FILE__).'/customscriptloadrenderer.php');
require_once(dirname(__FILE__).'/footerrenderer.php');
$bodyattributes = $OUTPUT->body_attributes([]);
$templatecontext = [
'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
'output' => $OUTPUT,
'bodyattributes' => $bodyattributes,
'customscriptloadernoamd'=> $customscriptloadernoamd,
'footer'=> $footer
];
echo $OUTPUT->render_from_template('theme_trending/contentonly', $templatecontext);

