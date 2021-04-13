define(['jquery', 'core/log'], function ($, log) {
    "use strict"; // jshint ;_;

    /*
    This file contains class and ID definitions.
     */

    log.debug('Readaloud definitions: initialising');

    return {
        component: 'mod_readaloud',
        componentpath: 'mod/readaloud',
        //hidden player
        hiddenplayer: 'mod_readaloud_hidden_player',
        hiddenplayerbutton: 'mod_readaloud_hidden_player_button',
        hiddenplayerbuttonactive: 'mod_readaloud_hidden_player_button_active',
        hiddenplayerbuttonpaused: 'mod_readaloud_hidden_player_button_paused',
        hiddenplayerbuttonplaying: 'mod_readaloud_hidden_player_button_playing',

        //popover
        okbuttonclass: 'mod_readaloud_quickgrade_ok',
        ngbuttonclass: 'mod_readaloud_quickgrade_ng',
        quickgradecontainerclass: 'mod_readaloud_quickgrade_cont',

        //grade now
        //passagecontainer: 'mod_readaloud_grading_passagecont',
        passagecontainer: 'mod_readaloud_passage_cont',
        audioplayerclass: 'mod_readaloud_grading_player',
        wordplayerclass: 'mod_readaloud_hidden_player',
        wordclass: 'mod_readaloud_grading_passageword',
        spaceclass: 'mod_readaloud_grading_passagespace',
        badwordclass: 'mod_readaloud_grading_badword',
        endspaceclass: 'mod_readaloud_grading_endspace',
        unreadwordclass: 'mod_readaloud_grading_unreadword',
        unreadspaceclass: 'mod_readaloud_grading_unreadspace',
        wpmscoreid: 'mod_readaloud_grading_wpm_score',
        accuracyscoreid: 'mod_readaloud_grading_accuracy_score',
        sessionscoreid: 'mod_readaloud_grading_session_score',
        errorscoreid: 'mod_readaloud_grading_error_score',
        formelementwpmscore: 'mod_readaloud_grading_form_wpm',
        formelementaccuracy: 'mod_readaloud_grading_form_accuracy',
        formelementsessionscore: 'mod_readaloud_grading_form_sessionscore',
        formelementendword: 'mod_readaloud_grading_form_sessionendword',
        formelementtime: 'mod_readaloud_grading_form_sessiontime',
        formelementerrors: 'mod_readaloud_grading_form_sessionerrors',
        modebutton: 'mod_readaloud_modebutton',

        //running records features
        maybeselfcorrectclass: 'mod_readaloud_grading_maybeselfcorrect',
        selfcorrectclass: 'mod_readaloud_grading_selfcorrect',
        structuralclass: 'mod_readaloud_grading_structural',
        meaningclass: 'mod_readaloud_grading_meaning',
        visualclass: 'mod_readaloud_grading_visual',
        notesclass: 'mod_readaloud_grading_notes',

        //activity
        passagefinished: 'mod_readaloud_passage_finished',

        spotcheckbutton: 'mod_readaloud_spotcheckbutton',
        transcriptcheckbutton: 'mod_readaloud_transcriptcheckbutton',
        gradingbutton: 'mod_readaloud_gradingbutton',
        clearbutton: 'mod_readaloud_clearbutton',
        spotcheckmode: 'mod_readaloud_spotcheckmode',
        aiunmatched: 'mod_readaloud_aiunmatched',

        menuinstructions: 'mod_readaloud_menuinstructions_const',
        activityinstructions: 'mod_readaloud_activityinstructions_const',
        menubuttons: 'mod_readaloud_menubuttons_cont',

        //model audio
        modelaudioplayerclass: 'mod_readaloud_modelaudio_player',
        modelaudiobreaksfield: 'mod_readaloud_modelaudio_form_breaksfield',
        modelaudiourlfield: 'mod_readaloud_modelaudio_form_urlfield',
        activesentence: 'mod_readaloud_modelaudio_activesentence',
        modeltranscriptbutton: 'mod_readaloud_modeltranscript_button',
        modeltranscript: 'mod_readaloud_modeltranscript',
        transcriber_amazonstreaming: 4,

        //small report
        smallreportcontainer: 'mod_readaloud_smallreport_cont',
        smallreportrating: 'mod_readaloud_smallreport_rating',
        smallreportplayer: 'mod_readaloud_smallreport_player',
        smallreportstatus: 'mod_readaloud_smallreport_status',
        smallreportheading: 'mod_readaloud_smallreport_heading',
        fullreportbutton: 'mod_readaloud_smallreport_fullreportbutton',

        //listen and repeat container
        landrcontainer: 'mod_readaloud_landr_container'

    };//end of return value
});