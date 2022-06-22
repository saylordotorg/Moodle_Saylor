define(['jquery','core/log'], function($,log) {
    "use strict"; // jshint ;_;

/*
This file contains class and ID definitions.
 */

    log.debug('solo definitions: initialising');

    return{
        component: 'mod_solo',
        C_AUDIOPLAYER: 'vs_audioplayer',
        C_CURRENTFORMAT: 'vs_currentformat',
        C_KEYFORMAT: 'vs_keyformat',
        C_ATTRFORMAT: 'vs_attrformat',
        C_FILENAMETEXT: 'vs_filenametext',
        C_UPDATECONTROL: 'filename',
        C_STREAMINGCONTROL: 'streamingtranscript',
        topicscontainer: 'topicscontainer',
        topiccheckbox: 'topicscheckbox',
        C_BUTTONAPPLY: 'poodllconvedit_edapply',
        C_BUTTONDELETE: 'poodllconvedit_eddelete',
        C_BUTTONMOVEUP: 'poodllconvedit_edmoveup',
        C_BUTTONMOVEDOWN: 'poodllconvedit_edmovedown',
        C_BUTTONCANCEL: 'poodllconvedit_edcancel',
        C_EDITFIELD: 'poodllconvedit_edpart',
        C_TARGETWORDSDISPLAY: 'mod_solo_targetwordsdisplay',
        //hidden player
        hiddenplayer: 'mod_solo_hidden_player',
        hiddenplayerbutton: 'mod_solo_hidden_player_button',
        hiddenplayerbuttonactive: 'mod_solo_hidden_player_button_active',
        hiddenplayerbuttonpaused: 'mod_solo_hidden_player_button_paused',
        hiddenplayerbuttonplaying: 'mod_solo_hidden_player_button_playing',
        transcriber_amazonstreaming: 4,
        smallreportplaceholdertext: 'mod_solo_placeholdertext',
        smallreportplaceholderspinner: 'mod_solo_placeholderspinner',
        cloudpoodllurl: 'https://cloud.poodll.com',
        //cloudpoodllurl: 'http://localhost/moodle',
        grammarsuggestionscont: 'mod_solo_corrections_cont',
        checkgrammarbutton: 'mod_solo_checkgrammarbutton'

    };//end of return value
});