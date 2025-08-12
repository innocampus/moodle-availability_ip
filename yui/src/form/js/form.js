/**
 * YUI code for extending the availability form.
 *
 * @see https://moodledev.io/docs/4.5/apis/plugintypes/availability#yuisrcjsformjs
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Identifier availability_ip has to be used, but does not follow Moodle's naming convention.
/* eslint-disable camelcase */

/**
 * JavaScript for form editing profile conditions.
 *
 * @module moodle-availability_ip-form
 */
M.availability_ip = M.availability_ip || {};

/**
 * Extension of the core availability form plugin.
 *
 * @class M.availability_ip.form
 * @extends M.core_availability.plugin
 */
M.availability_ip.form = Y.Object(M.core_availability.plugin);

/**
 * Initializes this plugin.
 *
 * This method receives the array elements returned by the `get_javascript_init_params` method of the `frontend` class as arguments.
 *
 * @method initInner
 */
M.availability_ip.form.initInner = function(ipoptions) {
    this.ipoptions = ipoptions;
    // Construct IP address validation RegEx for the custom IP address/range input.
    // We do not capture `0.0.0.0` or `a.b.c.d/0`; using that would render the entire access restriction moot.
    var firstOctet = /([1-9]|[1-9]\d|1\d\d|2(?:[0-4]\d|5[0-5]))/;
    var otherOctets = /(?:\.(\d|[1-9]\d|1\d\d|2(?:[0-4]\d|5[0-5]))){3}/;
    var cidrLengthOrRangeEnd = /(?:\/([1-9]|[12]\d|3[0-2])|-(25[0-5]|2[0-4]\d|[1-9]\d?\d?|0))?/;
    this.ipregex = new RegExp('^' + firstOctet.source + otherOctets.source + cidrLengthOrRangeEnd.source + '$');
    // TODO: Behat tests incoming...
};

/**
 * Gets a YUI node representing the controls for this plugin on the form.
 *
 * @method getNode
 * @return {Y.Node} YUI node
 */
M.availability_ip.form.getNode = function(json) {
    // TODO: See if we can use a template instead.
    var html = '<label><span class="pe-3">' + M.util.get_string('ip_options_select', 'availability_ip') + '</span> ' +
               '<span class="availability-group" id="availability_ip-options">';
    var initialValues = json.ids !== undefined ? json.ids : [];
    this.ipoptions.forEach(function(option) {
        var checkedAttr = initialValues.includes(option.id) ? ' checked' : '';
        html += '<div class="form-check">' +
                '<input class="form-check-input" type="checkbox" value="" id="' + option.id + '"' + checkedAttr + '>' +
                '<label class="form-check-label" for="' + option.id + '">' + option.name + '</label>' +
                '</div>';
    });
    var customValue = json.custom !== undefined ? json.custom : '';
    html += '<div class="mt-2">' +
            '<label class="form-label" for="-custom-">' + M.util.get_string('custom_ip', 'availability_ip') + '</label>' +
            '<input id="-custom-" ' +
                   'type="text" ' +
                   'value="' + customValue + '" ' +
                   'class="form-control" ' +
                   'aria-describedby="custom-help">' +
            '<div class="form-text text-muted" id="custom-help">' +
            M.util.get_string('custom_ip_help', 'availability_ip') +
            '</div>' +
            '</div>';
    html += '</span></label>';
    var node = Y.Node.create('<span class="d-flex flex-wrap align-items-center">' + html + '</span>');
    // Add event handlers for when a checkbox is ticked (`change`) or custom input changes (`input`).
    if (!M.availability_ip.form.addedEvents) {
        M.availability_ip.form.addedEvents = true;
        var container = Y.one('.availability-field');
        container.delegate(['change', 'input'], function() {
            // Update the form fields.
            M.core_availability.form.update();
        }, '.availability_ip input');
    }
    return node;
};

/**
 * Fills in the value from this plugin's controls into a value object,
 * which will later be converted to JSON and stored in the form field.
 *
 * Sets the `ids` property to the ids of the checked options
 * and the `custom` property to the value in the custom IP input.
 *
 * @method fillValue
 * @param {Object} value Value object (to be written to)
 * @param {Y.Node} node YUI node (same one returned from getNode)
 */
M.availability_ip.form.fillValue = function(value, node) {
    // Collect values from all selected checkboxes.
    value.ids = [];
    node.one('span[id=availability_ip-options]').all('input').each(function(input) {
        if (input.get('checked')) {
            value.ids.push(input.get('id'));
        }
    });
    // Get custom input value.
    value.custom = node.one('span[id=availability_ip-options] input[id=-custom-]').get('value').trim();
};

/**
 * Fills in any errors from this plugin's controls. If there are any errors, push them into the supplied array.
 *
 * Errors are Moodle language strings in format `component:string`, e.g. `availability_ip:error_select_ip`.
 *
 * This method pushes an error if
 * - no preset was selected and no custom IP address/range was entered or
 * - a custom value was entered, but it does not represent a valid IP address/range.
 *
 * @method fillErrors
 * @param {Array} errors Array of errors (push new errors here)
 * @param {Y.Node} node YUI node (same one returned from getNode)
 */
M.availability_ip.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);
    if (value.custom !== '') {
        // Ensure the entered value matches our regular expression.
        // If range notation is used, ensure the end of the range is greater than or equal to the start of the range.
        var matches = this.ipregex.exec(value.custom);
        if (matches === null || matches.length === 5 && parseInt(matches[2]) > parseInt(matches[4])) {
            errors.push('availability_ip:error_custom_ip');
        }
    } else if (value.ids.length === 0) {
        // Neither a custom input was provided, nor a checkbox selected.
        errors.push('availability_ip:error_select_ip');
    }
};
