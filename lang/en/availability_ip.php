<?php
// This file is part of availability_ip for Moodle.
//
// availability_ip for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// availability_ip for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with availability_ip for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for the plugin.
 *
 * @link https://docs.moodle.org/dev/String_API Moodle docs String API
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['condition_description'] = 'IP address allowed';
$string['custom_ip'] = 'Custom IP addresses';
$string['custom_ip_help'] = 'Input must either be a full IP address (such as <code>192.168.10.1</code>) which matches a single host; or CIDR notation (such as <code>231.54.211.0/20</code>); or a range of IP addresses (such as <code>231.3.56.10-20</code>) where the range applies to the last part of the address. You can specify more than one by comma-separating your IP addresses/ranges.';
$string['description'] = 'Allow only students with certain IP addresses.';
$string['error_custom_ip'] = 'Invalid IP address entered.';
$string['error_select_ip'] = 'No IP address/range selected.';
$string['ip_option_presets'] = 'Preconfigured IP options';
$string['ip_option_presets_help'] = '<p>List of preconfigured options for the IP address/range availability condition that can be selected. Put every entry on one line.</p><p>Entries must be in the format <code>IPs unique_shortname Displayname</code>, where <code>IPs</code> is either a full IP address (such as <code>192.168.10.1</code>) which matches a single host; or CIDR notation (such as <code>231.54.211.0/20</code>); or a range of IP addresses (such as <code>231.3.56.10-20</code>) where the range applies to the last part of the address. You can set multiple <code>IPs</code> by separating them with commas. <code>unique_shortname</code> may only consist of lower-case letters (<code>a-z</code>) and underscores (<code>_</code>).</p><p>Example:<pre>192.168.7.0/24,10.0.1.0-9 pc_pool  Local University PC Pool<br>111.222.33.44             admin_hq Admin HQ</pre></p><p><strong>CAUTION: Deleting options or changing their short names later can break existing access restrictions!</strong></p>';
$string['ip_options_select'] = 'Access from any of the selected IP address ranges:';
$string['pluginname'] = 'Restriction by IP';
$string['settings_error_bad_lines'] = 'Lines not in a valid format: {$a}';
$string['settings_error_duplicate_option_id'] = 'The shortname \'{$a->id}\' in line {$a->line} was already used above.';
$string['title'] = 'IP';
