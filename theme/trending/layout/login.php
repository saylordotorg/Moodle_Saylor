<?php
   defined('MOODLE_INTERNAL') || die();
   require_once(dirname(__FILE__).'/header.php');
    require_once(dirname(__FILE__).'/customscriptloadrenderer.php');
   require_once(dirname(__FILE__).'/reg.php');
   
   $bodyattributes = $OUTPUT->body_attributes();
   
   $templatecontext = [
   'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
   'output' => $OUTPUT,
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
    'reg' => $reg
   ];
   
   echo $OUTPUT->render_from_template('theme_trending/login', $templatecontext);
   ?>
<script type="text/javascript" src="<?php echo $CFG->wwwroot ?>/theme/trending/javascript/jquery.min.js"></script>

