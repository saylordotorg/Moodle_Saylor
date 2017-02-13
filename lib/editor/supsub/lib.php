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
 * Superscript/subscript editor. A very cut-down version of TinyMCE that just
 * allows <sup> and <sub> tags.
 *
 * Relies of the full tinymce editor plugin being installed.
 *
 * @package    editor_supsub
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/editor/tinymce/lib.php');


/**
 * Superscript/subscript editor. A very cut-down version of TinyMCE that just
 * allows <sup> and <sub> tags.
 *
 * Relies of the full tinymce editor plugin being installed.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class supsub_texteditor extends texteditor {
    /** @var string active version of tinyMCE used - directory name */
    public $version = null; // Set below to match core tinymce.

    public function __construct() {
        // Hack to avoid having to update this plugin everytime core updates TinyMCE.
        $tinymce = new tinymce_texteditor();
        $this->version = $tinymce->version;
    }

    public function supported_by_browser() {
        if (core_useragent::check_browser_version('MSIE', 6)) {
            return true;
        }
        if (core_useragent::check_browser_version('Gecko', 20030516)) {
            return true;
        }
        if (core_useragent::check_browser_version('Safari', 412)) {
            return true;
        }
        if (core_useragent::check_browser_version('Chrome', 6)) {
            return true;
        }
        if (core_useragent::check_browser_version('Opera', 9)) {
            return true;
        }

        return false;
    }

    public function get_supported_formats() {
        return array(FORMAT_HTML => FORMAT_HTML);
    }

    public function get_preferred_format() {
        return FORMAT_HTML;
    }

    public function supports_repositories() {
        return false;
    }

    public function head_setup() {
    }

    public function use_editor($elementid, array $options=null, $fpoptions=null) {
        global $PAGE, $CFG;
        if (debugging('', DEBUG_DEVELOPER)) {
            $PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/lib/editor/tinymce/tiny_mce/' .
                    $this->version . '/tiny_mce_src.js'));
        } else {
            $PAGE->requires->js(new moodle_url($CFG->httpswwwroot . '/lib/editor/tinymce/tiny_mce/' .
                    $this->version . '/tiny_mce.js'));
        }
        $PAGE->requires->js_init_call('M.editor_supsub.init_editor',
                array($elementid, $this->get_init_params($elementid, $options)), true);
    }

    protected function get_init_params($elementid, array $options = null) {
        global $CFG, $PAGE, $OUTPUT;

        $directionality = get_string('thisdirection', 'langconfig');
        $lang           = current_language();
        $contentcss     = $PAGE->theme->editor_css_url()->out(false);

        $langrev = -1;
        if (!empty($CFG->cachejs)) {
            $langrev = get_string_manager()->get_revision();
        }

        $params = array(
            'apply_source_formatting' => true,
            'content_css' => $contentcss . ',' . $CFG->httpswwwroot .
                    '/lib/editor/supsub/extra.css',
            'directionality' => $directionality,
            'document_base_url' => $CFG->httpswwwroot,
            'elements' => $elementid,
            'entity_encoding' => 'raw',
            'forced_root_block' => false,
            'force_br_newlines' => true,
            'force_p_newlines' => false,
            'height' => 30,
            'init_instance_callback' => 'M.editor_supsub.init_instance_callback',
            'langrev' => $langrev,
            'language' => $lang,
            'language_load' => false, // We load all lang strings directly from Moodle.
            'min_height' => 30,
            'mode' => 'exact',
            'moodle_plugin_base' => "$CFG->httpswwwroot/lib/editor/tinymce/plugins/",
            'nowrap' => true,
            'paste_auto_cleanup_on_paste' => true,
            'plugins' => '-supsub,paste',
            'relative_urls' => false,
            'remove_script_host' => false,
            'skin' => "o2k7",
            'skin_variant' => "silver",
            'theme' => "advanced",
            'theme_advanced_layout_manager' => 'SimpleLayout',
            'theme_advanced_toolbar_align' => 'left',
            'theme_advanced_buttons1' => 'sup,sub',
            'theme_advanced_buttons2' => '',
            'theme_advanced_buttons3' => '',
            'theme_advanced_resize_horizontal' => false,
            'theme_advanced_resizing' => false,
            'theme_advanced_resizing_min_height' => 30,
            'theme_advanced_toolbar_location' => 'top',
            'theme_advanced_statusbar_location' => 'none',
            'valid_elements' => '-sup,-sub',
            'valid_children' => 'body[sup|sub|#text],sup[#text],sub[#text]',
        );

        if (empty($options['supsub'])) {
            $options['supsub'] = 'both';
        }

        switch ($options['supsub']) {
            case 'both':
                // Do nothing, the $params above are for this case.
                break;

            case 'sup':
                $params['theme_advanced_buttons1'] = 'sup';
                $params['valid_elements'] = '-sup';
                $params['valid_children'] = 'body[sup|#text],sup[#text]';
                break;

            case 'sub':
                $params['theme_advanced_buttons1'] = 'sub';
                $params['valid_elements'] = '-sub';
                $params['valid_children'] = 'body[sub|#text],sub[#text]';
                break;

            default:
                throw new coding_exception("Invalid value '" .$options['supsub'] .
                        "' for option 'supsub'. Must be one of 'both', 'sup' or 'sub'.");
        }

        return $params;
    }
}
