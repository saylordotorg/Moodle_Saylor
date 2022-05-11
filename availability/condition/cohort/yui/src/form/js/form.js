/**
 * JavaScript for form editing cohort conditions.
 *
 * @module moodle-availability_cohort-form
 */
M.availability_cohort = M.availability_cohort || {}; // eslint-disable-line camelcase

/**
 * @class M.availability_cohort.form
 * @extends M.core_availability.plugin
 */
M.availability_cohort.form = Y.Object(M.core_availability.plugin);

/**
 * Cohorts available for selection (alphabetical order).
 *
 * @property cohorts
 * @type Array
 */
M.availability_cohort.form.cohorts = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} cohorts Array of objects containing cohortid => name
 */
M.availability_cohort.form.initInner = function(cohorts) {
    this.cohorts = cohorts;
};

M.availability_cohort.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<label><span class="pr-3">' + M.util.get_string('title', 'availability_cohort') + '</span> ' +
            '<span class="availability-cohort">' +
            '<select name="id" class="custom-select">' +
            '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>' +
            '<option value="any">' + M.util.get_string('anycohort', 'availability_cohort') + '</option>';
    for (var i = 0; i < this.cohorts.length; i++) {
        var cohort = this.cohorts[i];
        // String has already been escaped using format_string.
        html += '<option value="' + cohort.id + '">' + cohort.name + '</option>';
    }
    html += '</select></span></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
    if (json.creating === undefined) {
        if (json.id !== undefined &&
                node.one('select[name=id] > option[value=' + json.id + ']')) {
            node.one('select[name=id]').set('value', '' + json.id);
        } else if (json.id === undefined) {
            node.one('select[name=id]').set('value', 'any');
        }
    }

    // Add event handlers (first time only).
    if (!M.availability_cohort.form.addedEvents) {
        M.availability_cohort.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_cohort select');
    }

    return node;
};

M.availability_cohort.form.fillValue = function(value, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        value.id = 'choose';
    } else if (selected !== 'any') {
        value.id = parseInt(selected, 10);
    }
};

M.availability_cohort.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check cohort item id.
    if (value.id && value.id === 'choose') {
        errors.push('availability_cohort:error_selectcohort');
    }
};
