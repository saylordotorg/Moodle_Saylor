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
 * Helper javascript functions for the superscript/subscript editor.
 *
 * @package    editor_supsub
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.editor_supsub = M.editor_supsub || {};
M.editor_supsub.initialised = false;

M.editor_supsub.init_editor = function(Y, editorid, options) {
    if (!M.editor_supsub.initialised) {
        // Load all language strings for all plugins - we do not use standard TinyMCE lang pack loading!
        tinymce.ScriptLoader.add(M.cfg.wwwroot + '/lib/editor/tinymce/all_strings.php?elanguage=' + options.language + '&rev=' + options.langrev);

        M.editor_supsub.initialised = true;
    }

    tinymce.PluginManager.load('supsub', M.cfg.wwwroot + '/lib/editor/supsub/supsub_plugin.js');
    if (tinymce.isGecko) {
        options.theme_advanced_resizing_min_height += 10;
        options.height += 10;
    }
    tinymce.init(options);
};

M.editor_supsub.init_instance_callback = function(inst) {
    if (!tinymce.isGecko) {
        document.getElementById(inst.editorId + '_ifr').scrolling = 'No';
    }
};
