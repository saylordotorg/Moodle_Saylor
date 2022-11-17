<?php
/**
 * @package		Mb2 Shortcodes
 * @author		Mariusz Boloz (http://mb2extensions.com)
 * @copyright	Copyright (C) 2017 Mariusz Boloz (http://mb2extensions.com). All rights reserved
 * @license		Commercial (http://codecanyon.net/licenses)
**/


defined( 'MOODLE_INTERNAL' ) || die();

global $CFG;
require_once ( dirname( __FILE__ ) . '/lib/shortcodes.php' );
$themename = $CFG->theme;

if ( get_config( 'filter_mb2shortcodes', 'themename' ) )
{
	$themename = get_config( 'filter_mb2shortcodes', 'themename' );
}

$themeconfig = theme_config::load( $themename );
$shortcodesDir = '';
$current_theme =  $CFG->dirroot . '/theme/' . $themename . '/shortcodes/';

if ( is_dir( $current_theme ) )
{
	$shortcodesDir = $current_theme;
}

$filter = 'php';

if ( is_dir( $shortcodesDir ) )
{
	$dirContents = scandir( $shortcodesDir );

	foreach ( $dirContents as $file )
	{
		$fileType = pathinfo( $file, PATHINFO_EXTENSION );

		if ( $fileType === $filter )
		{
			require_once ( $shortcodesDir . basename( $file ) );
		}
	}
}

class filter_mb2shortcodes extends moodle_text_filter
{

	public function filter( $text, array $options = array() )
	{
		global $PAGE, $DB;
		$output = '';

		$array2 = array(
			'GENERIC0' => 'GENERICO'
		);

		$array1 = array (
			// Before and after shortcode tag shortcode
            '<p>[' 			=> '[',
			'<p> [' 		=> '[',
		 	']</p>' 		=> ']',
		 	'] </p>' 		=> ']',
			']<br></p>' 	=> ']',
			']</p><br>' 	=> ']',
			'] </p><br>' 	=> ']',
			']</p> <br>' 	=> ']',
			'] </p> <br>' 	=> ']',
			'] <br></p>' 	=> ']',
			']<br> </p>' 	=> ']',
			'] <br> </p>' 	=> ']',
            ']<br>' 		=> ']',
			'] <br>' 		=> ']',
			'"&nbsp;'		=> '" ',

			// Additional filter
			'<p></p>' 		=> '',
			'<p> </p>' 		=> '',
			'<p><br>' 		=> '<p>',
			'<p> <br>' 		=> '<p>',
			'<br></p>'		=> '</p>',
			'<br> </p>' 	=> '</p>'
        );

		$array = $array1;

		// Generico filter for content build by page builder
		$genericosql = 'SELECT * FROM {filter_active} WHERE ' . $DB->sql_like( 'filter', '?' ) . ' AND active = ?';

		if ( $DB->record_exists_sql( $genericosql, array( 'generico', 1 ) ) && ! preg_match( '@mb2builder@', $PAGE->pagetype ) )
		{
			$array = array_merge( $array1, $array2 );
		}

		$textFixed = strtr( $text, $array );
		return mb2_do_shortcode( $textFixed );

    }

}
