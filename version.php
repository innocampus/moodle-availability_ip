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
 * Version and requirements information for the plugin.
 *
 * @link https://moodledev.io/docs/apis/commonfiles/version.php Moodle docs version.php
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpUndefinedVariableInspection}
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'availability_ip';
$plugin->version = 2026022400;
$plugin->requires = 2025041400;  // Moodle 5.0.0+.
$plugin->supported = [500, 501];
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.0.0';
