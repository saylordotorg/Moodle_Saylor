<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Render markup for REIN debug interface.
 *
 * @package    filter-rein
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008 onwards Remote Learner.net Inc http://www.remote-learner.net
 */

defined('MOODLE_INTERNAL') || die();

class filter_rein_renderer extends plugin_renderer_base {

    /**
     * Output the markup for the introduction to the debug interface.
     *
     * @return string HTML fragment.
     */
    public function print_debug_intro() {
        // Print page heading.
        $content = html_writer::tag('h2', get_string('debugtitle', 'filter_rein'));
        // Print page instructions.
        $content .= html_writer::tag('div', get_string('debuginstr', 'filter_rein'));

        return $content;
    }

    /**
     * Output the markup for the accordion widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_accordion($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('accordiontitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('accordiondesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('accordioninstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/accordion.png',
            'class' => 'shouldlook',
            'width' => '800',
            'height' => '320'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print Live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print Widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/accordion.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=accordion';
        $content .= html_writer::tag('a', get_string('accordionviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the tabs widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_tabs($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('tabstitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('tabsdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));

        /* Standard Tabs */
        $content .= html_writer::tag('div', get_string('tabsinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tabs.png',
            'class' => 'shouldlook',
            'width' => '833',
            'height' => '134'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tabs.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tabs';
        $content .= html_writer::tag('a', get_string('tabsviewmarkup', 'filter_rein'), $urlparams);
        /* End Standard Tabs */

        /* Vertical Tabs */
        // Print header.
        $content .= html_writer::tag('h2', get_string('tabsverticaltitle', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tabsvertical.png',
            'class' => 'shouldlook',
            'width' => '833',
            'height' => '305'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tabsvertical.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tabsvertical';
        $content .= html_writer::tag('a', get_string('tabsviewmarkup', 'filter_rein'), $urlparams);
        /* End Vertical Tabs */

        /* Top Tabs */
        // Print header.
        $content .= html_writer::tag('h2', get_string('tabstoptitle', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tabstop.png',
            'class' => 'shouldlook',
            'width' => '833',
            'height' => '306'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tabstop.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tabstop';
        $content .= html_writer::tag('a', get_string('tabsviewmarkup', 'filter_rein'), $urlparams);
        /* End Top Tabs */

        /* Arropw Tabs */
        // Print header.
        $content .= html_writer::tag('h2', get_string('tabsarrowtitle', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tabsarrows.png',
            'class' => 'shouldlook',
            'width' => '833',
            'height' => '150'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tabsarrows.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tabsarrows';
        $content .= html_writer::tag('a', get_string('tabsviewmarkup', 'filter_rein'), $urlparams);
        /* End Arrow Tabs */

        return $content;
    }

    /**
     * Output the markup for the equal columns widget.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_equal_columns($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('equalcolumnstitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('equalcolumnsdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('equalcolumnsinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/equalcolumns.png',
            'class' => 'shouldlook',
            'width' => '1000',
            'height' => '250'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/equalcolumns.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=equalcolumns';
        $content .= html_writer::tag('a', get_string('equalcolumnsviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the modal widget.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_modal($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('modaltitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('modaldesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('modalinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/modal.png',
            'class' => 'shouldlook',
            'width' => '600',
            'height' => '170'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/modal.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=modal';
        $content .= html_writer::tag('a', get_string('modalviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the equal columns widget.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_toggle($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('toggletitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('toggledesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('toggleinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/toggle.png',
            'class' => 'shouldlook',
            'width' => '666',
            'height' => '168'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/toggle.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=toggle';
        $content .= html_writer::empty_tag('br');
        $content .= html_writer::tag('a', get_string('toggleviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

   /**
     * Output the markup for the flip book widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_flipbook($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('flipbooktitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('flipbookdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('flipbookinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/flipbook.png',
            'class' => 'shouldlook',
            'width' => '848',
            'height' => '348'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print Live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print Widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/flipbook.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=flipbook';
        $content .= html_writer::tag('a', get_string('flipbookviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the flipcard widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_flipcard($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('flipcardtitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('flipcarddesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('flipcardinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/flipcard.png',
            'class' => 'shouldlook',
            'width' => '1016',
            'height' => '663'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print Live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print Widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/flipcard.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=flipcard';
        $content .= html_writer::tag('a', get_string('flipcardviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the click hotspot widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_clickhotspot($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('clickhotspottitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('clickhotspotdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('clickhotspotinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/clickhotspot.png',
            'class' => 'shouldlook',
            'width' => '600',
            'height' => '219'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/clickhotspot.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=clickhotspot';
        $content .= html_writer::tag('a', get_string('clickhotspotviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the sort multiple lists widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_sortmultiple($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('sortmultipletitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('sortmultipledesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('sortmultipleinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/multidrag.png',
            'class' => 'shouldlook',
            'width' => '491',
            'height' => '329'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $content .= html_writer::tag('h4', get_string('multipledragheader', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/sortmultidrag.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=sortmultidrag';
        $content .= html_writer::tag('a', get_string('sortmultipleviewmarkup', 'filter_rein'), $urlparams);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=sortsingledrag';
        $content .= html_writer::tag('a', get_string('sortmultiplesingledragviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the drop bubble widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_dropbubble($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('dropbubbletitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('dropbubbledesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('dropbubbleinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/dropbubble.png',
            'class' => 'shouldlook',
            'width' => '628',
            'height' => '436'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/dropbubble.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=dropbubble';
        $content .= html_writer::tag('a', get_string('dropbubbleviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the stepwise process widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_stepwise($reinoptions, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('stepwisetitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('stepwisedesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('stepwiseinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/stepwise.png',
            'class' => 'shouldlook',
            'width' => '334',
            'height' => '369'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/stepwise.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=stepwise';
        $content .= html_writer::tag('a', get_string('stepwiseviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the sequential appearance widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_sequential($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('sequentialtitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('sequentialdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('sequentialinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/sequential.png',
            'class' => 'shouldlook',
            'width' => '315',
            'height' => '194'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/sequential.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=sequential';
        $content .= html_writer::tag('a', get_string('sequentialviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the rotator widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_rotator($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('rotatortitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('rotatordesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('rotatorinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/rotator.png',
            'class' => 'shouldlook',
            'width' => '800',
            'height' => '187'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/rotator.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=rotator';
        $content .= html_writer::tag('a', get_string('rotatorviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the MarkIt widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_markit($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('markittitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('markitdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('markitinstr', 'filter_rein'), array('class' => 'instr'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/markit.png',
            'class' => 'shouldlook',
            'width' => '600',
            'height' => '462'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        // Print widget.
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/markit.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=markit';
        $content .= html_writer::tag('a', get_string('markitviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the tooltip widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_tooltip($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('tooltiptitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('tooltipdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h4', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('tooltipinstr', 'filter_rein'), array('class' => 'instr'));
        $content .= html_writer::tag('h4', get_string('widgetview', 'filter_rein'));
        // Print widget.
        // Print light style.
        $content .= html_writer::tag('h3', get_string('tooltiptitleattribute', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h4', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tooltiptitle.png',
            'class' => 'shouldlook',
            'width' => '392',
            'height' => '93'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tooltiptitle.php');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tooltiptitle';
        $content .= html_writer::tag('a', get_string('tooltiptitleattribviewmarkup', 'filter_rein'), $urlparams);

        // Print img alt tooltip.
        $content .= html_writer::tag('h3', get_string('tooltipimgalt', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h4', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tooltipimgalt.png',
            'class' => 'shouldlook',
            'width' => '603',
            'height' => '188'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h4', get_string('widgetview', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tooltipimg.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tooltipimg';
        $content .= html_writer::tag('a', get_string('tooltipdarkviewmarkup', 'filter_rein'), $urlparams);

        // Print Custom Content Tooltip
        $content .= html_writer::tag('h3', get_string('tooltipcustomcontent', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h4', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tooltipcustom.png',
            'class' => 'shouldlook',
            'width' => '618',
            'height' => '106'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h4', get_string('widgetview', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tooltipcustom.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tooltipcustom';
        $content .= html_writer::tag('a', get_string('tooltipcustomcontentmarkup', 'filter_rein'), $urlparams);

        // Print Image Map.
        $content .= html_writer::tag('h3', get_string('tooltipimgmap', 'filter_rein'));
        // Print Should Look Like header.
        $content .= html_writer::tag('h4', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Print preview image.
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/tooltipimgmap.png',
            'class' => 'shouldlook',
            'width' => '216',
            'height' => '177'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h4', get_string('widgetview', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/tooltipimgmap.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=tooltipimgmap';
        $content .= html_writer::tag('a', get_string('tooltipimgmapviewmarkup', 'filter_rein'), $urlparams);

        return $content;
    }

    /**
     * Output the markup for the overlay widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_overlay($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('overlaytitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('overlaydesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h4', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('overlayinstr', 'filter_rein'), array('class' => 'instr'));

        // Print Should Look Like header.
        $content .= html_writer::tag('h4', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Preview of links.
        $content .= html_writer::tag('p', get_string('overlaylinkdesc', 'filter_rein'), array('class' => 'shouldlook'));
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/overlaythumbnailandbutton.png',
            'class' => 'shouldlook',
            'width' => '332',
            'height' => '296'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Preview of image overlay.
        $content .= html_writer::tag('p', get_string('overlayimagedesc', 'filter_rein'), array('class' => 'shouldlook'));
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/overlayimage.png',
            'class' => 'shouldlook',
            'width' => '600',
            'height' => '402'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Preview of mixed format overlay.
        $content .= html_writer::tag('p', get_string('overlaymixeddesc', 'filter_rein'), array('class' => 'shouldlook'));
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/overlaymixed.png',
            'class' => 'shouldlook',
            'width' => '600',
            'height' => '400'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein')); // Interactive preview.
        // Print live preview.
        $content .= html_writer::tag('h4', get_string('overlayimagewiththumbnail', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/overlayimage.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= html_writer::start_tag('p');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        $content .= html_writer::end_tag('p');
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=overlayimage';
        $content .= html_writer::start_tag('p');
        $content .= html_writer::tag('a', get_string('overlayimageviewmarkup', 'filter_rein'), $urlparams);
        $content .= html_writer::end_tag('p');
        // Print live preview mixed overlay.
        $content .= html_writer::tag('h4', get_string('overlaymixedwithbutton', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/overlaymixed.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=overlaymixed';
        $content .= html_writer::start_tag('p');
        $content .= html_writer::tag('a', get_string('overlaymixedviewmarkup', 'filter_rein'), $urlparams);
        $content .= html_writer::end_tag('p');

        return $content;
    }

     /**
     * Output the markup for the swiper widget in the debug interface.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  string $imgpath Full path to directory containing image (for markup display)
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */
    public function print_debug_swiper($reinoptions, $imgpath, $urlparams) {
        global $CFG;

        // Print header.
        $content = html_writer::tag('h2', get_string('swipertitle', 'filter_rein'));
        // Print view.
        $content .= html_writer::tag('div', get_string('swiperdesc', 'filter_rein'), array('class' => 'desc'));
        // Print instructions.
        $content .= html_writer::tag('h4', get_string('widgetinstr', 'filter_rein'));
        $content .= html_writer::tag('div', get_string('swiperinstr', 'filter_rein'), array('class' => 'instr'));

        // Print Should Look Like header.
        $content .= html_writer::tag('h4', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
        // Preview of text swiper.
        $content .= html_writer::tag('p', get_string('swipertextpreview', 'filter_rein'), array('class' => 'shouldlook'));
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/swipertextpreview.png',
            'class' => 'shouldlook',
            'width' => '912',
            'height' => '348'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Preview of image swiper.
        $content .= html_writer::tag('p', get_string('swiperimgpreview', 'filter_rein'), array('class' => 'shouldlook'));
        $imgparams = array(
            'src' => $CFG->wwwroot.'/filter/rein/pix/demo/swiperimgpreview.png',
            'class' => 'shouldlook',
            'width' => '882',
            'height' => '504'
        );
        $content .= html_writer::tag('img', '', $imgparams);
        // Print live preview header.
        $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein')); // Interactive preview.
        // Print live preview.
        $content .= html_writer::tag('h4', get_string('swipertextpreview', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/swipertext.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= html_writer::start_tag('p');
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        $content .= html_writer::end_tag('p');
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=swipertext';
        $content .= html_writer::start_tag('p');
        $content .= html_writer::tag('a', get_string('swipertextviewmarkup', 'filter_rein'), $urlparams);
        $content .= html_writer::end_tag('p');
        // Print live preview mixed overlay.
        $content .= html_writer::tag('h4', get_string('swiperimageviewmarkup', 'filter_rein'));
        $getmarkup = file_get_contents(dirname(__FILE__).'/markup/swiperimage.php');
        $getmarkup = str_replace('imgpath', $imgpath, $getmarkup);
        $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions);
        // Print link to markup.
        $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=swiperimage';
        $content .= html_writer::start_tag('p');
        $content .= html_writer::tag('a', get_string('swiperimageviewmarkup', 'filter_rein'), $urlparams);
        $content .= html_writer::end_tag('p');

        return $content;
    }
}
