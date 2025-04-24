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
};

/**
 * Gets a YUI node representing the controls for this plugin on the form.
 *
 * @method getNode
 * @return {Y.Node} YUI node
 */
M.availability_ip.form.getNode = function(json) {
    // TODO: See if we can use a template instead.
    // TODO: Make this a multi-/checkbox-select.
    var html = '<label><span class="pe-3">' + M.util.get_string('title', 'availability_ip') + '</span> ' +
        '<span class="availability-group">' +
        '<select name="ip" class="custom-select">' +
        '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    this.ipoptions.forEach(function(option) {
        html += '<option value="' + option.id + '">' + option.name + '</option>';
    });
    html += '</select></span></label>';
    var node = Y.Node.create('<span class="d-flex flex-wrap align-items-center">' + html + '</span>');
    // Set the initial value if specified.
    if (json.ids !== undefined) {
        var initialvalue = json.ids[0];
        // If the initial value is invalid (i.e. no such option exists), select the `choose` option.
        if (!node.one('select[name=ip] > option[value=' + initialvalue + ']')) {
            initialvalue = 'choose';
        }
        node.one('select[name=ip]').set('value', initialvalue);
    }
    // Add event handlers.
    if (!M.availability_ip.form.addedEvents) {
        M.availability_ip.form.addedEvents = true;
        var container = Y.one('.availability-field');
        container.delegate('change', function() {
            // Update the form fields.
            M.core_availability.form.update();
        }, '.availability_ip select');
    }
    return node;
};

/**
 * Fills in the value from this plugin's controls into a value object,
 * which will later be converted to JSON and stored in the form field.
 *
 * Sets the `id` property to the value of the selected option.
 *
 * @method fillValue
 * @param {Object} value Value object (to be written to)
 * @param {Y.Node} node YUI node (same one returned from getNode)
 */
M.availability_ip.form.fillValue = function(value, node) {
    value.ids = [node.one('select[name=ip]').get('value')];
};

/**
 * Fills in any errors from this plugin's controls. If there are any errors, push them into the supplied array.
 *
 * Errors are Moodle language strings in format `component:string`, e.g. `availability_ip:error_select_ip`.
 *
 * This method currently only checks whether anything other than the default `choose` option was actually selected.
 *
 * @method fillErrors
 * @param {Array} errors Array of errors (push new errors here)
 * @param {Y.Node} node YUI node (same one returned from getNode)
 */
M.availability_ip.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    if (value.ids[0] === 'choose') {
        errors.push('availability_ip:error_select_ip');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
