<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for the plugin.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['description'] = 'Allow only students with certain IP addresses.';
$string['error_select_ip'] = 'No IP address/range selected.';
$string['ip_option_presets'] = 'Preconfigured IP options';
$string['ip_option_presets_help'] = '<p>List of preconfigured options for the IP address/range availability condition that can be selected. Put every entry on one line.</p><p>Entries must be in the format <code>IP unique_shortname Displayname</code>, where <code>IP</code> is either a full IP address (such as <code>192.168.10.1</code>) which matches a single host; or CIDR notation (such as <code>231.54.211.0/20</code>); or a range of IP addresses (such as <code>231.3.56.10-20</code>) where the range applies to the last part of the address. <code>unique_shortname</code> may only consist of lower-case letters (<code>a-z</code>) and underscores (<code>_</code>).</p><p>Example:<pre>192.168.7.0/24  pc_pool  Local University PC Pool<br>111.222.333.444 admin_hq Admin HQ</pre></p><p><strong>CAUTION: Deleting options or changing their short names later can break existing access restrictions!</strong></p>';
$string['pluginname'] = 'Restriction by IP';
$string['title'] = 'IP';
