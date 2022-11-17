<?php
// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();
$THEME->name = 'trending';
$THEME->sheets = [];
$THEME->layouts = [
// Most backwards compatible layout without the blocks - this is the layout used by default.
'base' => array(
'file' => 'columns.php',
'regions' => array(),
),
// Standard layout with blocks, this is recommended for most pages with general information.
'standard' => array(
'file' => 'columns.php',
'regions' => array('side-pre', 'side-post'),
'defaultregion' => 'side-pre',
),
// Main course page.
'course' => array(
'file' => 'columns.php',
'regions' => array('side-pre', 'side-post'),
'defaultregion' => 'side-pre',
'options' => array('langmenu' => true),
),
'coursecategory' => array(
'file' => 'columns.php',
'regions' => array('side-pre'),
'defaultregion' => 'side-pre',
),
// Part of course, typical for modules - default page layout if $cm specified in require_login().
'incourse' => array(
'file' => 'columns.php',
'regions' => array('side-pre'),
'defaultregion' => 'side-pre',
),
// The site home page.
'frontpage' => array(
'file' => 'frontpage.php',
'regions' => array('side-pre', 'side-post'),
'defaultregion' => 'side-pre',
'options' => array('nofullheader' => true),
),
// Server administration scripts.
'admin' => array(
'file' => 'columns.php',
'regions' => array('side-pre'),
'defaultregion' => 'side-pre',
),
// My dashboard page.
'mydashboard' => array(
'file' => 'columns.php',
'regions' => array('side-pre', 'side-post'),
'defaultregion' => 'side-pre',
'options' => array('nonavbar' => true, 'langmenu' => true, 'nocontextheader' => true),
),
// My courses page.
'mycourses' => array(
'file' => 'columns.php',
'regions' => ['side-pre', 'side-post'],
'defaultregion' => 'side-pre',
),

// My public page.
'mypublic' => array(
'file' => 'columns.php',
'regions' => array('side-pre'),
'defaultregion' => 'side-pre',
),
'login' => array(
'file' => 'login.php',
'regions' => array(),
'options' => array('nofooter' => true, 'langmenu' => true),
),
// Pages that appear in pop-up windows - no navigation, no blocks, no header.
'popup' => array(
'file' => 'contentonly.php',
'regions' => array(),
'options' => array('nofooter' => true, 'nonavbar' => true),
),
// No blocks and minimal footer - used for legacy frame layouts only!
'frametop' => array(
'file' => 'contentonly.php',
'regions' => array(),
'options' => array('nofooter' => true, 'nocoursefooter' => true),
),
// Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible.
'embedded' => array(
'file' => 'embedded.php',
'regions' => array()
),
// Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
// This must not have any blocks, links, or API calls that would lead to database or cache interaction.
// Please be extremely careful if you are modifying this layout.
'maintenance' => array(
'file' => 'maintenance.php',
'regions' => array(),
),
// Should display the content and basic headers only.
'print' => array(
'file' => 'contentonly.php',
'regions' => array(),
'options' => array('nofooter' => true, 'nonavbar' => false),
),
// The pagelayout used when a redirection is occuring.
'redirect' => array(
'file' => 'embedded.php',
'regions' => array(),
),
// The pagelayout used for reports.
'report' => array(
'file' => 'columns.php',
'regions' => array('side-pre'),
'defaultregion' => 'side-pre',
),
// The pagelayout used for safebrowser and securewindow.
'secure' => array(
'file' => 'secure.php',
'regions' => array('side-pre'),
'defaultregion' => 'side-pre'
)
];
$THEME->editor_sheets = [];
$THEME->parents = ['boost','classic'];
$THEME->enable_dock = false;
$THEME->extrascsscallback = 'theme_trending_get_extra_scss';
$THEME->prescsscallback = 'theme_trending_get_pre_scss';
$THEME->precompiledcsscallback = 'theme_trending_get_precompiled_css';
$THEME->yuicssmodules = array();
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->scss = function($theme) {
return theme_trending_get_main_scss_content($theme);
};
$THEME->sheets_footer = array('custom');
$THEME->usefallback = true;
