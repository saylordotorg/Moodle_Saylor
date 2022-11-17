<?php
   defined('MOODLE_INTERNAL') || die();
   ob_start(); 
   require_once('customscriptloadernoamd.php');
   $customscriptloadernoamd = ob_get_clean();
?>