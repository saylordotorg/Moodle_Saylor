/**
 * JavaScript for form editing conditions.
 *
 * @module moodle-availability_mobileapp-form
 */
M.availability_mobileapp = M.availability_mobileapp || {};

/**
 * @class M.availability_mobileapp.form
 * @extends M.core_availability.plugin
 */
M.availability_mobileapp.form = Y.Object(M.core_availability.plugin);

M.availability_mobileapp.form.getNode = function(json) {
    // Create HTML structure.
    var strings = M.str.availability_mobileapp;
    var html = strings.title + ' <span class="availability-group">';

    html += '<label><span class="accesshide">' + strings.label_access +
            ' </span><select name="e" title="' + strings.label_access + '">' +
            '<option value="1">' + strings.requires_app + '</option>' +
            '<option value="2">' + strings.requires_notapp + '</option>' +
            '</select></label></span>';
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values.
    if (json.e !== undefined) {
        node.one('select[name=e]').set('value', '' + json.e);
    }

    // Add event handlers (first time only).
    if (!M.availability_mobileapp.form.addedEvents) {
        M.availability_mobileapp.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Whichever dropdown changed, just update the form.
            M.core_availability.form.update();
        }, '.availability_mobileapp select');
    }

    return node;
};

M.availability_mobileapp.form.fillValue = function(value, node) {
    value.e = parseInt(node.one('select[name=e]').get('value'), 10);
};

