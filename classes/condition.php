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
 * Definition of the {@see \availability_ip\condition} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_ip;

use core\exception\coding_exception;
use core\lang_string;
use core_availability\condition as abstract_condition;
use core_availability\info;
use dml_exception;
use stdClass;

/**
 * Availability condition class.
 *
 * Depends on an array of IDs that represent valid IP range presets defined by admins in the `ip_option_presets` setting.
 * These IDs must be passed to the constructor via an `ids` property on the `$structure` argument.
 * IDs that do not match admin presets are ignored.
 *
 * For {@see is_available} to be `true` for a given user, the user's IP address has to fall within at least one of those IP ranges.
 * **CAUTION**: If no (valid) IP address/range is provided, {@see is_available} will always be `false` for positive conditions and
 * always `true` for inverted conditions!
 *
 * @see https://moodledev.io/docs/4.5/apis/plugintypes/availability#classesconditionphp
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends abstract_condition {

    /** @var admin_ip_option[] $options Chosen options for the availability condition */
    public readonly array $options;

    /**
     * Sets the relevant properties based off of the provided JSON structure object.
     *
     * @param stdClass $structure Extracted from JSON data stored in the database as part of the tree structure of conditions
     *                            relating to an activity. Must have an `ids` property set with values that correspond to valid IP
     *                            option presets from the `ip_option_presets` configuration setting.
     * @throws coding_exception The `ids` property is missing or not an array or contains invalid values.
     * @throws dml_exception The admin settings for the plugin are not available.
     */
    public function __construct(stdClass $structure) {
        if (!isset($structure->ids)) {
            throw new coding_exception("The `ids` value is missing from the structure");
        }
        if (!is_array($structure->ids)) {
            throw new coding_exception("The `ids` value is not an array");
        }
        $optionpresets = admin_setting_ip_options::get_parsed('availability_ip', 'ip_option_presets');
        $options = [];
        foreach ($structure->ids as $id) {
            if (!is_string($id)) {
                throw new coding_exception("The ID '$id' is not a string");
            }
            if (!array_key_exists($id, $optionpresets)) {
                debugging("The ID '$id' is not a valid IP option");
                continue;
            }
            $options[] = $optionpresets[$id];
        }
        $this->options = $options;
    }

    public function is_available($not, info $info, $grabthelot, $userid): bool {
        $clientip = getremoteaddr();
        // If the client IP matches at least one of the IP addresses/ranges, then the availability condition is satisfied.
        // When the condition is inverted (`$not === true`), at least one match means it is not satisfied.
        foreach ($this->options as $option) {
            if (address_in_subnet($clientip, $option->ip)) {
                return !$not;
            }
        }
        return $not;
    }

    public function get_description($full, $not, info $info): lang_string {
        return new lang_string('condition_description', 'availability_ip');
    }

    protected function get_debug_string(): string {
        return json_encode($this->save());
    }

    public function save(): stdClass {
        return (object) ['type' => 'ip', 'ids' => array_column($this->options, 'id')];
    }
}
