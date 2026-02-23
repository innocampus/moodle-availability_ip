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
 * Contains the function for migrating the plugin from one version to a newer version.
 *
 * @link https://moodledev.io/docs/guides/upgrade#dbupgradephp Moodle docs upgrade.php
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpUnused}
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/upgradelib.php');

/**
 * Performs the steps required to bring the plugin to the correct state for the current version.
 *
 * @param int $oldversion the version we are upgrading from.
 * @return true The update concluded successfully.
 * @throws moodle_exception The update failed.
 */
function xmldb_availability_ip_upgrade(int $oldversion = 0): true {
    if ($oldversion < 2025081900) {
        try {
            replace_custom_single_ips_with_arrays();
        } catch (Exception $e) {
            throw new upgrade_exception('availability_ip', 2025081900, $e->getMessage());
        }
        upgrade_plugin_savepoint(true, 2025081900, 'availability', 'ip');
    }
    return true;
}
