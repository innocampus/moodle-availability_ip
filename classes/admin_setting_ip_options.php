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
 * Definition of the {@see admin_setting_ip_options} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_ip;

use admin_setting_configtextarea;
use core\exception\coding_exception;
use dml_exception;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once("$CFG->libdir/adminlib.php");
// @codeCoverageIgnoreEnd

/**
 * Text area with validation for IP options.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_ip_options extends admin_setting_configtextarea {
    /** @var string Font family to use in the text area. */
    const FONT_FAMILY = 'var(--bs-font-monospace)';

    #[\Override]
    public function output_html($data, $query = ''): string {
        $style = '<style>' . "\n";
        $style .= "textarea[name=\"{$this->get_full_name()}\"] ";
        $style .= '{ font-family: ' . self::FONT_FAMILY . '; }' . "\n";
        $style .= '</style>' . "\n";
        return $style . parent::output_html($data, $query);
    }

    /**
     * Validates contents of the text field before storage.
     *
     * On top of standard validation, the text muss pass {@see parse_ip_options} without errors.
     *
     * @param string $data Text entered by the admin
     * @return true|string `true` if validation was successful, error string otherwise
     * @throws coding_exception
     */
    #[\Override]
    public function validate($data): bool|string {
        if (true !== $result = parent::validate($data)) {
            return $result;
        }
        $parsed = self::parse_ip_options($data);
        return is_string($parsed) ? $parsed : true;
    }

    /**
     * Parses IP options text into an associative array of objects.
     *
     * @param string $data Text with each line either empty or in the form `<IPs> <unique_id> <Arbitrary display name>`.
     *                     See the `ip_option_presets_help` language string for details.
     * @return admin_ip_option[]|string If successful, returns an associative array, where the keys are IDs of the option presets,
     *                                  and the values are instances of {@see admin_ip_option}. Error string otherwise.
     * @throws coding_exception
     */
    public static function parse_ip_options(string $data): array|string {
        $options = [];
        $badlines = [];
        foreach (explode("\n", $data) as $idx => $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            if ($option = admin_ip_option::parse($line)) {
                if (array_key_exists($option->id, $options)) {
                    return get_string(
                        identifier: 'settings_error_duplicate_option_id',
                        component: 'availability_ip',
                        a: ['id'  => $option->id, 'line' => $idx + 1],
                    );
                }
                $options[$option->id] = $option;
            } else {
                $badlines[] = $line;
            }
        }
        if ($badlines) {
            return get_string(
                identifier: 'settings_error_bad_lines',
                component: 'availability_ip',
                a: '"' . implode('", "', $badlines) . '"',
            );
        }
        return $options;
    }

    /**
     * Returns the current value of the config setting as an associative array.
     *
     * @param string $plugin Full component name.
     * @param string $name Name of the config setting.
     * @return admin_ip_option[] Associative array, where the keys are the IDs of the option presets, and the values are instances
     *                           of {@see admin_ip_option}.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_parsed(string $plugin, string $name): array {
        if (!is_string($setting = get_config($plugin, $name))) {
            return [];
        }
        $parsed = self::parse_ip_options($setting);
        return is_string($parsed) ? [] : $parsed;
    }
}
