<?php
   defined('MOODLE_INTERNAL') || die();
   ob_start(); 
   require_once('register.php');
   $reg = ob_get_clean();
?>