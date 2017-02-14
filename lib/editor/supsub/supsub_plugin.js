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
 * TinyMCE plugin that:
 *     1. disables the enter key, and
 *     2. makes up and down arrow do superscript and subscript.
 *
 * @package    editor_supsub
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


(function() {
    // Load plugin specific language pack
    tinymce.create('tinymce.plugins.SupSubPlugin', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         */
        init : function(ed) {
            ed.onKeyDown.add(function(ed, e) {
                switch (e.keyCode) {

                case 13: // Enter
                    tinymce.dom.Event.cancel(e);
                    break;

                case 38: // Up arrow
                    if (ed.queryCommandState('superscript')) {
                        // Already superscript. Do nothing.
                        /*jshint noempty:false */
                    } else if (ed.queryCommandState('subscript')) {
                        // Is subscript. Do subscript again to turn it off.
                        ed.execCommand('subscript');
                    } else if (ed.schema.elements.sup) {
                        // Normal text, and superscript is allowed, so do it.
                        ed.execCommand('superscript');
                    }
                    tinymce.dom.Event.cancel(e);
                    break;

                case 40: // Down arrow
                    if (ed.queryCommandState('superscript')) {
                        // Is superscript. Do superscript again to turn it off.
                        ed.execCommand('superscript');
                    } else if (ed.queryCommandState('subscript')) {
                        // Already subscript. Do nothing.
                    } else if (ed.schema.elements.sub) {
                        // Normal text, and subscript is allowed, so do it.
                        ed.execCommand('subscript');
                    }
                    tinymce.dom.Event.cancel(e);
                    break;
                }
            });
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname  : 'Superscrip-subscript editor helper',
                author    : 'Tim Hunt, The Open University',
                authorurl : 'http://www.open.ac.uk/',
                infourl   : 'http://www.open.ac.uk/',
                version   : '1.0'
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('supsub', tinymce.plugins.SupSubPlugin);
})();
