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
 * Moodle's saylor theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_saylor
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Check the file is being called internally from within Moodle.
defined('MOODLE_INTERNAL') || die();

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param  string       $css   The CSS
 * @param  theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_saylor_process_css($css, $theme) {

    // Set the font size
    if (!empty($theme->settings->fsize)) {
        $fsize = $theme->settings->fsize;
    } else {
        $fsize = null;
    }
    $css = theme_saylor_set_fsize($css, $fsize);

    // Set the font size
    if (!empty($theme->settings->fpheight)) {
        $fpheight = $theme->settings->fpheight;
    } else {
        $fpheight = null;
    }
    $css = theme_saylor_set_fpheight($css, $fpheight);

    // Set the font size
    if (!empty($theme->settings->intheight)) {
        $intheight = $theme->settings->intheight;
    } else {
        $intheight = null;
    }
    $css = theme_saylor_set_intheight($css, $intheight);

    // Set the link color
    if (!empty($theme->settings->linkcolor)) {
        $linkcolor = $theme->settings->linkcolor;
    } else {
        $linkcolor = null;
    }
    $css = theme_saylor_set_linkcolor($css, $linkcolor);

    // Set the link hover color
    if (!empty($theme->settings->linkhover)) {
        $linkhover = $theme->settings->linkhover;
    } else {
        $linkhover = null;
    }
    $css = theme_saylor_set_linkhover($css, $linkhover);

    // Set the main color
    if (!empty($theme->settings->maincolor)) {
        $maincolor = $theme->settings->maincolor;
    } else {
        $maincolor = null;
    }
    $css = theme_saylor_set_maincolor($css, $maincolor);

     // Set the main color
    if (!empty($theme->settings->secondcolor)) {
        $secondcolor = $theme->settings->secondcolor;
    } else {
        $secondcolor = null;
    }
    $css = theme_saylor_set_secondcolor($css, $secondcolor);

    // Set the main headings color
    if (!empty($theme->settings->backcolor)) {
        $backcolor = $theme->settings->backcolor;
    } else {
        $backcolor = null;
    }
    $css = theme_saylor_set_backcolor($css, $backcolor);

    if (!empty($theme->settings->p1)) {
        $p1 = $theme->settings->p1;
    } else {
        $p1 = null;
    }
    $css = saylor_set_pone($css, $p1, $theme);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_saylor_set_customcss($css, $customcss);

    return $css;
}


/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param  string $css       The original CSS.
 * @param  string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_saylor_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

function theme_saylor_set_fsize($css, $fsize) {
    $tag = '[[setting:fsize]]';
    $replacement = $fsize;
    if (is_null($replacement)) {
        $replacement = '90';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_saylor_set_fpheight($css, $fpheight) {
    $tag = '[[setting:fpheight]]';
    $replacement = $fpheight;
    if (is_null($replacement)) {
        $replacement = '200';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_saylor_set_intheight($css, $intheight) {
    $tag = '[[setting:intheight]]';
    $replacement = $intheight;
    if (is_null($replacement)) {
        $replacement = '175';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_saylor_set_linkcolor($css, $linkcolor) {
    $tag = '[[setting:linkcolor]]';
    $replacement = $linkcolor;
    if (is_null($replacement)) {
        $replacement = '#033e55';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_saylor_set_linkhover($css, $linkhover) {
    $tag = '[[setting:linkhover]]';
    $replacement = $linkhover;
    if (is_null($replacement)) {
        $replacement = '#666666';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_saylor_set_maincolor($css, $maincolor) {
    $tag = '[[setting:maincolor]]';
    $replacement = $maincolor;
    if (is_null($replacement)) {
        $replacement = '#173140';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}


function theme_saylor_set_secondcolor($css, $secondcolor) {
    $tag = '[[setting:secondcolor]]';
    $replacement = $secondcolor;
    if (is_null($replacement)) {
        $replacement = '#9b7b2a';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_saylor_set_backcolor($css, $backcolor) {
    $tag = '[[setting:backcolor]]';
    $replacement = $backcolor;
    if (is_null($replacement)) {
        $replacement = '#eeeeee';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function saylor_set_pone($css, $p1, $theme) {
    $tag = '[[setting:bannerpic]]';
    $replacement = $p1;
    if (is_null($replacement)) {
        $replacement = $theme->pix_url('paint', 'theme');
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Deprecated: Please call theme_saylor_process_css instead.
 *
 * @deprecated since 2.5.1
 */
function saylor_process_css($css, $theme) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_saylor_process_css($css, $theme);
}

/**
 * Deprecated: Please call theme_saylor_set_customcss instead.
 *
 * @deprecated since 2.5.1
 */
function saylor_set_customcss($css, $customcss) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_saylor_set_customcss($css, $customcss);
}
