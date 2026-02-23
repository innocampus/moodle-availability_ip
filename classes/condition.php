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
 * Definition of the {@see \availability_ip\condition} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_ip;

use core\exception\coding_exception;
use core\ip_utils;
use core_availability\condition as abstract_condition;
use core_availability\info;
use dml_exception;
use stdClass;

/**
 * Availability condition class.
 *
 * Depends on an array of IDs that represent valid IP range presets defined by admins in the `ip_option_presets` setting and/or
 * custom IP addresses/ranges. These must be passed to the constructor via the `ids` and `custom` properties respectively on the
 * `$structure` argument. IDs that do not match admin presets are ignored.
 *
 * For {@see is_available} to be `true` for a given user, the user's IP address has to fall within at least one of those IP ranges.
 * **CAUTION**: If no (valid) IP address/range is provided, {@see is_available} will always be `false` for positive conditions and
 * always `true` for inverted conditions!
 *
 * @link https://moodledev.io/docs/apis/plugintypes/availability#classesconditionphp Moodle availability condition docs
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends abstract_condition {
    /** @var string Fake client IP address used for unit tests. */
    const PHPUNIT_CLIENT_IP = '255.255.255.254';

    /** @var admin_ip_option[] $options Chosen options for the availability condition */
    public readonly array $options;

    /** @var string[] $customips Custom IP addresses/ranges defined for the availability condition */
    public readonly array $customips;

    /**
     * Sets the relevant properties based off of the provided JSON structure object.
     *
     * @param stdClass $structure Extracted from JSON data stored in the database as part of the tree structure of conditions
     *                            relating to an activity. Requires either an `ids` property or a `custom` property (or both).
     *                            The former must be an array of values that correspond to valid IP option presets from the
     *                            `ip_option_presets` configuration setting; the latter must be a valid IP address/range.
     * @throws coding_exception The `$structure` neither has an `ids` nor a `custom` property.
     *                          Or the `ids` property is not an array or contains non-string values.
     *                          Or the `custom` property is not an array or contains an invalid IP address/range.
     * @throws dml_exception The admin settings for the plugin are not available.
     */
    public function __construct(stdClass $structure) {
        if (!isset($structure->ids) && !isset($structure->custom)) {
            throw new coding_exception("Both 'ids' and 'custom' properties are missing from the structure.");
        }
        $ids = $structure->ids ?? [];
        if (!is_array($ids)) {
            throw new coding_exception("The 'ids' property is not an array");
        }
        $this->customips = $structure->custom ?? [];
        foreach ($this->customips as $custom) {
            if (!ip_utils::is_ipv4_address($custom) && !ip_utils::is_ipv4_range($custom)) {
                throw new coding_exception("Not a valid custom IP address/range: $custom");
            }
        }
        if (count($ids) === 0) {
            $this->options = [];
            return;
        }
        $optionpresets = admin_setting_ip_options::get_parsed('availability_ip', 'ip_option_presets');
        $options = [];
        foreach ($ids as $id) {
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

    #[\Override]
    public function is_available($not, info $info, $grabthelot, $userid): bool {
        $clientip = PHPUNIT_TEST ? self::PHPUNIT_CLIENT_IP : getremoteaddr();
        // If the client IP matches at least one of the IP addresses/ranges, then the availability condition is satisfied.
        // When the condition is inverted (`$not === true`), at least one match means it is not satisfied.
        foreach ($this->options as $option) {
            if (address_in_subnet($clientip, implode(',', $option->ips))) {
                return !$not;
            }
        }
        foreach ($this->customips as $custom) {
            if (address_in_subnet($clientip, $custom)) {
                return !$not;
            }
        }
        return $not;
    }

    /**
     * {@inheritDoc}
     *
     * @throws coding_exception
     */
    #[\Override]
    public function get_description($full, $not, info $info): string {
        return get_string('condition_description', 'availability_ip');
    }

    #[\Override]
    protected function get_debug_string(): string {
        return json_encode($this->save());
    }

    #[\Override]
    public function save(): stdClass {
        $structure = ['type' => 'ip'];
        if (count($this->options) > 0) {
            $structure['ids'] = array_column($this->options, 'id');
        }
        if (count($this->customips) > 0) {
            $structure['custom'] = $this->customips;
        }
        return (object) $structure;
    }
}
