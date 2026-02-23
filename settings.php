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
 * Definition of plugin-specific admin settings.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpUndefinedVariableInspection}
 */

use availability_ip\admin_setting_ip_options;
use core\lang_string;

defined('MOODLE_INTERNAL') || die;

global $ADMIN, $PAGE, $OUTPUT;

if ($hassiteconfig && $ADMIN->fulltree) {
    $settings->add(
        new admin_setting_ip_options(
            name: 'availability_ip/ip_option_presets',
            visiblename: new lang_string('ip_option_presets', 'availability_ip'),
            description: new lang_string('ip_option_presets_help', 'availability_ip'),
            defaultsetting: '',
        )
    );
}
