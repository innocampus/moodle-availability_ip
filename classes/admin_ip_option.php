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
 * Definition of the {@see admin_ip_option} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_ip;

use core\exception\coding_exception;
use core\ip_utils;

/**
 * Encapsulates an IP option preset by administrators via {@see admin_setting_ip_options}.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class admin_ip_option {

    /**
     * @param string[] $ips IP addresses/ranges.
     * @param string $id Option identifier.
     * @param string $name Human-readable name for the option.
     * @throws coding_exception One of the provided `$ìps` is not a valid IP address/range.
     */
    public function __construct(
        public readonly array $ips,
        public readonly string $id,
        public readonly string $name,
    ) {
        foreach ($this->ips as $ip) {
            if (!ip_utils::is_ipv4_address($ip) && !ip_utils::is_ipv4_range($ip)) {
                throw new coding_exception("Not a valid IP address/range: $ip");
            }
        }
    }

    /**
     * Parses an IP option string into an object.
     *
     * @param string $line String in the form `<IPs> <unique_id> <Arbitrary display name>`.
     *                     See the `ip_option_presets_help` language string for details.
     * @return self|null New instance, if successful. `null` otherwise.
     */
    public static function parse(string $line): self|null {
        if (!preg_match('/^(\S+)\s+([a-z_]+)\s+(.+)$/', trim($line), $matches)) {
            return null;
        }
        [, $ips, $id, $name] = $matches;
        try {
            return new self(
                ips: array_values(array_filter(explode(',', $ips))),
                id: $id,
                name: $name,
            );
        } catch (coding_exception) {
            return null;
        }
    }

    /**
     * Returns an IP option string from the object.
     *
     * Inverse to {@see parse}.
     *
     * @return string String in the form `<IPs> <unique_id> <Arbitrary display name>`.
     */
    public function __toString(): string {
        return implode(' ', [implode(',', $this->ips), $this->id, $this->name]);
    }
}
