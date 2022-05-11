<?php

/**
 * REIN Libraray Javascript
 * Copyright (C) 2008 onwards Remote Learner.net Inc http://www.remote-learner.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    filter-rein
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008 onwards Remote Learner.net Inc http://www.remote-learner.net
 *
 */


/**
 * This filter includes the REIN library Javascript and CSS files if it encounters the text "rein-plugin". This text tag
 * is used in REIN HTML markup to trigger the library inclusion so it doesn't have to be included on every page.
 */
class filter_rein extends moodle_text_filter {

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {
        global $CFG, $PAGE;
        static $debugmode = null;
        static $included_rein = false;

        if ($debugmode === null) {
            $debugmode = (bool)get_config('filter_rein', 'testmode');
        }

        if (strpos($text, 'rein-plugin') !== false && $included_rein === false) {

            $PAGE->requires->strings_for_js(array(
                'markitmodalmarkuptitle',
                'markitmodalmarkupinstructions',
                'smoothlinesbuttonlabel',
                'getmarkupbuttonlabel',
                'getcheckitbuttonlabel',
                'usepenbuttonlabel',
                'usehighlightbuttonlabel',
                'undobuttonlabel',
                'clearbuttonlabel',
                'nestingerror'
            ), 'filter_rein');

            // Parameters sent to rein init.
            $reinparams = array(
                'debugmode' => $debugmode,
            );

            // Load rein JS.
            $PAGE->requires->js_call_amd('filter_rein/rein', 'reininit', array($reinparams));

            // Load Modernizr the old-fashioned way because it should be
            // in the page as soon as possible.
            $js = array( '/filter/rein/js/modernizr.js');

            $js_files = '';
            foreach ($js as $j) {
                $js_files .= '<script src="'.$CFG->wwwroot.$j.'"/></script>';
            }

            $css_files = '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/filter/rein/serve_css.php"/>';

            $text = $js_files.$css_files.$text;

            $included_rein = true;
        }

        return $text;
    }
}
