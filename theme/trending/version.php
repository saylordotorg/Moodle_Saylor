<?php
// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();
$plugin->version = 2019030100;
$plugin->requires = 2019030100;
$plugin->component = 'theme_trending';
$plugin->dependencies = array('theme_boost' => 2019022600,'theme_classic' => 2019022600,);
