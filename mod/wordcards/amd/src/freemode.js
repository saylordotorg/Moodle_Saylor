/**
 * Module to help with Free mode page.
 *
 * @package mod_wordcards
 * @author David Watson - evolutioncode.uk
 */
define(['jquery'], function($) {
    return {
        init: function () {
            $(document).ready(function() {
                $('#wordpool-selector-btn').on('click', function() {
                    const content = $('#wordpool-selector-content');
                    if (content.hasClass('show')) {
                        content.removeClass('show')
                    } else {
                        content.addClass('show')
                    }
                })
            })
        }
    }
})