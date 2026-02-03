YUI.add('moodle-availability_ip-form', function (Y, NAME) {

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
};

/**
 * Gets a YUI node representing the controls for this plugin on the form.
 *
 * @method getNode
 * @return {Y.Node} YUI node
 */
M.availability_ip.form.getNode = function(json) {
    // TODO: Unfortunately, due to the synchronous way this is implemented in the `core_availability` YUI module,
    //       it is impossible to use the asynchronous `core/templates` utilities without ugly hacks,
    //       so we are stuck with wonderful string concatenation for now.
    var html = '<span class="pe-3">' + M.util.get_string('ip_options_select', 'availability_ip') + '</span> ' +
               '<span class="availability-group availability_ip-options">';
    var initialValues = json.ids !== undefined ? json.ids : [];
    this.ipoptions.forEach(function(option) {
        var checkedAttr = initialValues.includes(option.id) ? ' checked' : '';
        var ips = option.ips.join(', ');
        html += '<div class="form-check">' +
                '<label class="form-check-label">' +
                '<input class="form-check-input" type="checkbox" value="" name="' + option.id + '"' + checkedAttr + '>' +
                option.name + ' <small class="text-muted">(' + ips + ')</small>' +
                '</div>';
    });
    var customValue = '';
    var customChecked = '';
    var customHidden = ' hidden';
    if (json.custom !== undefined && json.custom.length > 0) {
        customValue = json.custom.join(', ');
        customChecked = ' checked';
        customHidden = '';
    }
    html += '<div class="form-check">' +
            '<label class="form-check-label">' +
            '<input class="form-check-input" type="checkbox" value="" name="-custom-check-"' + customChecked + '>' +
            M.util.get_string('custom_ip', 'availability_ip') +
            '</label>' +
            '</div>';
    html += '<div class="availability_ip-custom-container" class="mt-2"' + customHidden + '>' +
            '<input name="-custom-" ' +
                   'type="text" ' +
                   'value="' + customValue + '" ' +
                   'class="form-control">' +
            '<div class="form-text text-muted">' +
            M.util.get_string('custom_ip_help', 'availability_ip') +
            '</div>' +
            '</div>';
    html += '</span></label>';
    var node = Y.Node.create('<span class="d-flex flex-wrap align-items-center">' + html + '</span>');
    var customContainerNode = node.one('div.availability_ip-custom-container');
    // Add event handlers for when a checkbox is ticked (`change`) or custom input changes (`input`).
    if (!M.availability_ip.form.addedEvents) {
        M.availability_ip.form.addedEvents = true;
        var container = Y.one('.availability-field');
        container.delegate(
            ['change', 'input'],
            function(event) {
                // Update the form fields.
                M.core_availability.form.update();
                // Show/hide custom IP input field.
                if (event.type === 'change' && event.target.get('name') === '-custom-check-') {
                    if (event.target.get('checked')) {
                        customContainerNode.show();
                    } else {
                        customContainerNode.hide();
                    }
                }
            },
            '.availability_ip input'
        );
    }
    return node;
};

/**
 * Fills in the value from this plugin's controls into a value object,
 * which will later be converted to JSON and stored in the form field.
 *
 * Sets the `ids` property to the names of the checked options
 * and the `custom` property to an array of values taken from the custom IP input.
 *
 * @method fillValue
 * @param {Object} value Value object (to be written to)
 * @param {Y.Node} node YUI node (same one returned from getNode)
 */
M.availability_ip.form.fillValue = function(value, node) {
    // Collect values from all selected checkboxes (excluding the "custom" checkbox).
    value.ids = [];
    node.one('span.availability_ip-options').all('input').each(function(input) {
        var name = input.get('name');
        if (name !== '-custom-check-' && input.get('checked')) {
            value.ids.push(name);
        }
    });
    // If the custom checkbox is selected, get custom IP addresses/ranges (comma-separated) from the input field.
    // If it is not selected, ignore the text input.
    if (node.one('input[name="-custom-check-"]').get('checked')) {
        var customInputText = node.one('span.availability_ip-options input[name="-custom-"]').get('value').trim();
        // Split into an array and filter out empty strings.
        value.custom = customInputText.split(/\s*,\s*/).filter(Boolean);
    } else {
        value.custom = [];
    }
};

/**
 * Fills in any errors from this plugin's controls. If there are any errors, push them into the supplied array.
 *
 * Errors are Moodle language strings in format `component:string`, e.g. `availability_ip:error_select_ip`.
 *
 * This method pushes an error if
 * - no preset was selected and no custom IP address/range was entered or
 * - custom values were entered, but at least one of them does not represent a valid IP address/range.
 *
 * @method fillErrors
 * @param {Array} errors Array of errors (push new errors here)
 * @param {Y.Node} node YUI node (same one returned from getNode)
 */
M.availability_ip.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);
    if (value.custom.length > 0) {
        var regex = this.ipregex;
        value.custom.every(function(ip) {
            // Ensure each custom value matches our regular expression.
            // If range notation is used, ensure the end of the range is greater than or equal to the start of the range.
            var matches = regex.exec(ip);
            if (matches === null || matches.length === 5 && parseInt(matches[2]) > parseInt(matches[4])) {
                errors.push('availability_ip:error_custom_ip');
                return false; // Do not bother checking any following IPs if an error is encountered.
            }
            return true;
        });
    } else if (value.ids.length === 0) {
        // Neither a custom input was provided nor a checkbox selected.
        errors.push('availability_ip:error_select_ip');
    }
    // TODO: Unfortunately the Moodle code handling the errors array is poorly implemented.
    //       If there was one error previously and there is a _different_ one now, the old one is still displayed.
    //       Whether this is worth fixing depends on how long YUI is expected to be kept on life support...
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
