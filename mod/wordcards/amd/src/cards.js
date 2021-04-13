/**
 * Cards module.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

// TODO Handle window resizing/rotating?
// TODO Test Edge

define([
    'jquery',
    'core/ajax',
], function($, Ajax) {

    var PAGEOFFSET = 60;
    var CARDMARGIN = 4;

    /**
     * Randomize array element order in-place.
     * Using Durstenfeld shuffle algorithm.
     * @see http://stackoverflow.com/questions/2450954/how-to-randomize-shuffle-a-javascript-array
     */
    function shuffleArray(array) {
        for (var i = array.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = array[i];
            array[i] = array[j];
            array[j] = temp;
        }
        return array;
    }

    var Cards = function(selector, terms) {
        this._container = $(selector);
        this._terms = terms;
        this._selected = null;
    };
    Cards.prototype._container = null;
    Cards.prototype._dryRun = false;
    Cards.prototype._resizeTimeout = null;
    Cards.prototype._selected = null;
    Cards.prototype._terms = null;

    Cards.prototype.init = function() {
        var pool = [];

        this._terms.forEach(function(item) {
            pool.push(this._makeCard(item.id, item.term));
            pool.push(this._makeCard(item.id, item.definition));
        }.bind(this));

        shuffleArray(pool);
        pool.forEach(function(item) {
            // item.hide();
            this._container.append(item);
        }.bind(this));
        this._arrangePlayground();
        pool.forEach(function(item) {
            item.show();
        });

        // Event listeners.
        this._container.on('click', '.wordcard', this._handlePick.bind(this));
        $(window).on('resize', function() {
            if (this._resizeTimeout) {
                clearTimeout(this._resizeTimeout);
            }
            this._resizeTimeout = setTimeout(this._arrangePlayground.bind(this), 200);
        }.bind(this));
    };

    Cards.prototype._arrangePlayground = function() {
        var width = this._container.width(),
            height = $(window).height(),
            perRow = 3,
            cardCount = this._terms.length * 2,
            cardWidth = null,
            cardHeight = null,
            row = 0,
            col = 0,
            lineHeight,
            lineHeightValue,
            lineHeightUnit,
            suggestedHeight;

        if (cardCount % 2 < cardCount % 3) {
            perRow = 2;
        }
        lineHeight = this._container.find('.wordcard').first().css('lineHeight');
        lineHeightValue = parseInt(lineHeight.replace(/([^0-9]+)/, ''));
        lineHeightUnit = lineHeight.replace(/([0-9]+)/, '');
        suggestedHeight = 60;
        if (lineHeightUnit === 'px') {
            suggestedHeight = !lineHeightValue ? suggestedHeight : ((lineHeightValue + 1) * 3);
        }
        suggestedHeight = 999;

        cardWidth = Math.floor(width / perRow);
        cardHeight = Math.min(Math.round((height - PAGEOFFSET) / Math.ceil(cardCount / perRow)), suggestedHeight);

        this._container.find('.wordcard').each(function(index, item) {
            $(item).css({
                top: row * cardHeight,
                left: col * cardWidth,
                width: (col == perRow - 1) ? cardWidth : cardWidth - CARDMARGIN,
                height: cardHeight - CARDMARGIN
            });
            col++;
            if (col >= perRow) {
                col = 0;
                row++;
            }
        });

        this._container.find('.wordcard-content').css('maxHeight', cardHeight - CARDMARGIN);
        this._container.css({height: row * cardHeight});
        this._adjustCardContent();
    };

    Cards.prototype._adjustCardContent = function() {
        this._container.find('.wordcard-content').each(function(index, el) {
            var node = $(el),
                over,
                txt,
                loops = 0;

            node.text(node.data('text'));
            while (el.scrollHeight > el.offsetHeight && el.scrollHeight > 0) {
                txt = node.text();
                over = Math.max(0.1, (el.offsetHeight / el.scrollHeight) - 0.05);
                txt = txt.substr(0, Math.round(txt.length * over)).trim();
                txt += '…';
                node.text(txt);

                // Fail safe, because sometimes it loops forever...
                if (loops++ > 4) {
                    break;
                }
            }
        });
    };

    Cards.prototype._checkComplete = function() {
        if (this._container.find('.wordcard.found').length == this._terms.length * 2) {
            this._trigger('complete');
        }
    };

    Cards.prototype._handlePick = function(e) {
        e.preventDefault();
        var card = $(e.currentTarget);

        // It's already invisible.
        if (card.hasClass('found')) {
            return;
        }

        // It's the first out of the two picks.
        if (!this._selected) {
            this._selected = card;
            card.addClass('selected');
            return;
        }

        // We've clicked the selected card.
        if (this._selected.is(card)) {
            return;
        }

        // It's a match!
        if (card.data('id') == this._selected.data('id')) {
            this._selected
                .addClass('found')
                .animate({'opacity': 0});
            card.addClass('found')
                .animate({'opacity': 0});

            this._reportSuccess(this._selected.data('id'));
            this._checkComplete();

        // It's not a match...
        } else {
            var original = this._selected;
            original.addClass('mismatch');
            card.addClass('mismatch');

            this._reportFailure(this._selected.data('id'), card.data('id'));

            setTimeout(function() {
                original.removeClass('mismatch');
                card.removeClass('mismatch');
            }, 600);
        }

        // Reset the selection.
        this._selected.removeClass('selected');
        this._selected = null;
    };

    Cards.prototype._makeCard = function(id, text) {
        var container = $('<div class="wordcard">'),
            wrapper = $('<div class="wordcard-wrapper">'),
            content = $('<div class="wordcard-content">');

        content.text(text);
        content.data('text', text);
        wrapper.append(content);
        container.append(wrapper);
        container.data('id', id);

        return container;
    };

    Cards.prototype.on = function(action, cb) {
        this._container.on(action, cb);
    };

    Cards.prototype._reportFailure = function(term1id, term2id) {
        if (this._dryRun) {
            return;
        }

        Ajax.call([{
            methodname: 'mod_wordcards_report_failed_association',
            args: {
                term1id: term1id,
                term2id: term2id
            }
        }]);
    };

    Cards.prototype._reportSuccess = function(termid) {
        if (this._dryRun) {
            return;
        }

        Ajax.call([{
            methodname: 'mod_wordcards_report_successful_association',
            args: {
                termid: termid
            }
        }]);
    };

    Cards.prototype.setDryRun = function(value) {
        this._dryRun = value;
    };

    Cards.prototype._trigger = function(action) {
        this._container.trigger(action);
    };

    return Cards;

});
