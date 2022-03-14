/**
 * Module to watch my words buttons for clicks and report to back end.
 *
 * @package mod_wordcards
 * @author David Watson - evolutioncode.uk
 */
define(['jquery', 'core/ajax', 'core/str'], function($, ajax, str) {
    const SELECTOR = {
        DATA_SET: '*[data-action="wordcards-set-my-words"]',
        MY_WORDS_DIV: '#my-words-ids',
        MY_WORDS_ACTION_BTN_ID: '.wordcards-mywords-action-',
        WORDPOOL_COUNTS: '.wordpool-count'
    }

    const CLASS = {
        DISABLED: 'disabled',
        BTN_NOT_IN_MY_WORDS: 'btn-outline-primary',
        BTN_IN_MY_WORDS: 'btn-primary'
    }

    const EVENT = {
        CLICK: 'click'
    }

    const DATA = {
        TERM_ID: 'data-termid',
        VALUE: 'data-value',
        ADD: 'data-add',
        REMOVE: 'data-remove',
        MY_WORDS_IDS: 'data-my-words-term-ids'
    }
    var stringStore = {};

    const initStrings = function (callback) {
        str.get_strings([
            {key: "addtomywords", component: "mod_wordcards"},
            {key: "removefrommywords", component: "mod_wordcards"},
        ]).done(function (strings) {
            stringStore = strings;
            if (typeof callback == 'function') {
                callback();
            }
        });
    }

    const initButtonListeners = function() {
        $(SELECTOR.DATA_SET).on(EVENT.CLICK, function(e) {
            // There are two buttons for each term (one in grid and one in flashcards).
            const currTar = $(e.currentTarget);
            const buttons = $(SELECTOR.MY_WORDS_ACTION_BTN_ID + currTar.attr(DATA.TERM_ID));
            const termId = buttons.attr(DATA.TERM_ID);
            if (!currTar.hasClass(CLASS.DISABLED)) {
                e.preventDefault();
                buttons.addClass(CLASS.DISABLED)
                // Hide wordpool counts in drop down as may become incorrect here.
                $(SELECTOR.WORDPOOL_COUNTS).fadeOut();
                const newStatus = buttons.hasClass(CLASS.BTN_IN_MY_WORDS) ? 0 : 1;
                ajax.call([{
                    methodname: 'mod_wordcards_set_my_words',
                    args: {
                        termid: termId,
                        newstatus: newStatus
                    }
                }])[0].done(function(response) {
                    if (response.success) {
                        buttons.removeClass(CLASS.DISABLED);
                        if (response.newStatus) {
                            buttons.addClass(CLASS.BTN_IN_MY_WORDS);
                            buttons.removeClass(CLASS.BTN_NOT_IN_MY_WORDS);
                            buttons.attr('title', stringStore[1]);
                        } else {
                            buttons.removeClass(CLASS.BTN_IN_MY_WORDS);
                            buttons.addClass(CLASS.BTN_NOT_IN_MY_WORDS);
                            buttons.attr('title', stringStore[0]);
                        }
                    }
                }).fail(function() {
                    buttons.removeClass(CLASS.DISABLED);
                })
            }
        })
    }

    /**
     * Existing button statuses are on the main freemode template markup, so we need to grab them and render them,
     */
    const applyButtonStatuses = function() {
        const myWordsDiv = $(SELECTOR.MY_WORDS_DIV);
        if (myWordsDiv) {
            const ids = JSON.parse(myWordsDiv.attr(DATA.MY_WORDS_IDS));
            ids.forEach((id) => {
                const btn = $(SELECTOR.MY_WORDS_ACTION_BTN_ID + id);
                if (!btn.hasClass(CLASS.BTN_IN_MY_WORDS)) {
                    btn
                        .addClass(CLASS.BTN_IN_MY_WORDS)
                        .removeClass(CLASS.BTN_NOT_IN_MY_WORDS)
                        .attr('title', stringStore[1]);
                }
            });
            return true;
        }
    }
    return {
        init: function () {
            $(document).ready(function() {
                initStrings();
                initButtonListeners();
            })
        },
        initFromFeedbackPage: function () {
            initStrings(applyButtonStatuses);
            initButtonListeners();
        }
    }
});