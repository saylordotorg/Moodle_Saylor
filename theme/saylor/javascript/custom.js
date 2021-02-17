jQuery(document).ready(function($) {
    /**
     * Handle close functionality of user alerts.
     */
    $(".useralerts.alert a.close").unbind("click");
    $(".useralerts.alert a.close").click(function(e) {
        $(this).closest('.alert').hide();
        e.preventDefault();
    });

});
