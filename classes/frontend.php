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
 * Definition of the {@see \availability_ip\frontend} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_ip;

use cm_info;
use core\exception\coding_exception;
use core_availability\frontend as abstract_frontend;
use dml_exception;
use section_info;
use stdClass;

/**
 * Class for front-end (editing form) functionality.
 *
 * @see https://moodledev.io/docs/4.5/apis/plugintypes/availability#classesfrontendphp
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends abstract_frontend {

    /**
     * Returns an array of arguments to be passed to the plugin's JavaScript `initInner` function.
     *
     * The only element/argument in that array will be an array of preset IP options for constructing the select field.
     * Each option will be represented by an associative array with the keys `id` and `name`.
     *
     * @param stdClass $course Course object
     * @param cm_info|null $cm Course-module currently being edited (`null` if none)
     * @param section_info|null $section Section currently being edited (`null` if none)
     * @return array Array of elements to be passed to the JavaScript function as arguments
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function get_javascript_init_params($course, cm_info|null $cm = null, section_info|null $section = null): array {
        $optionpresets = admin_setting_ip_options::get_parsed('availability_ip', 'ip_option_presets');
        // Do not show the actual IP ranges to the client.
        return [array_values($optionpresets)];
    }

    protected function get_javascript_strings(): array {
        return [
            'custom_ip',
            'custom_ip_help',
            'error_custom_ip',
            'error_select_ip',
            'ip_options_select',
        ];
    }
}
