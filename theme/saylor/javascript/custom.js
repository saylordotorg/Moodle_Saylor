require(['jquery'], function ($) {
    $(document).ready(function($) {
        /**
         * Handle close functionality of user alerts.
         */
        $(".useralerts.alert a.close").unbind("click");
        $(".useralerts.alert a.close").click(function(e) {
            $(this).closest('.alert').hide();
            e.preventDefault();
        });

    });
});

// Add Copy to Clipboard Function

function copyStringToClipboard(target) {
    var str = document.getElementById(target).innerText;
    // Create new element
    var el = document.createElement('textarea');
    // Set value (string to be copied)
    el.value = str;
    // Set non-editable to avoid focus and move outside of view
    el.setAttribute('readonly', '');
    el.style = { position: 'absolute', left: '-9999px' };
    document.body.appendChild(el);
    // Select text inside element
    el.select();
    // Copy text to clipboard
    document.execCommand('copy');
    // Remove temporary element
    document.body.removeChild(el);
}