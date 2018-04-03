<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2018040200;
$plugin->requires = 2017111300; // Moodle 3.4+
$plugin->release = '3.4+ (Build: 2018040200)';
$plugin->component = 'block_checklist';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = array('mod_checklist' => 2010041800); // Must have checklist activity module installed.
