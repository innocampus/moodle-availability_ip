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
 * Definition of the {@see upgradelib_test} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpIllegalPsrClassPathInspection}
 */

namespace availability_ip;

use advanced_testcase;
use availability_date\condition as date_condition;
use core_availability\tree;
use dml_exception;
use JsonException;

/**
 * Unit tests for the `upgradelib.php` functions.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upgradelib_test extends advanced_testcase {
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        require_once(__DIR__ . '/../db/upgradelib.php');
    }

    /**
     *
     * @covers ::replace_custom_single_ips_with_arrays
     * @covers ::replace_custom_single_ip_with_array
     * @dataProvider test_replace_custom_single_ips_with_arrays_provider
     * @param string $module Type of module to create (e.g. `page` or `forum`).
     * @param array $initial Conditions to set for the module initially.
     * @param array $expected Conditions expected to be set after the replacement function was called.
     * @throws dml_exception
     * @throws JsonException
     */
    public function test_replace_custom_single_ips_with_arrays(string $module, array $initial, array $expected): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module($module, ['course' => $course]);
        if (!empty($initial)) {
            $DB->set_field(
                table: 'course_modules',
                newfield: 'availability',
                newvalue: json_encode(tree::get_root_json($initial)),
                conditions: ['id' => $module->cmid],
            );
        }
        replace_custom_single_ips_with_arrays();
        $cm = $DB->get_record('course_modules', ['id' => $module->cmid]);
        if (!empty($expected)) {
            self::assertSame(
                json_encode(tree::get_root_json($expected)),
                $cm->availability,
            );
        } else {
            self::assertNull($cm->availability);
        }
    }

    /**
     * Data provider for the {@see test_replace_custom_single_ips_with_arrays} method.
     *
     * @return array[] Inputs for the test method.
     */
    public static function test_replace_custom_single_ips_with_arrays_provider(): array {
        $unrelatedcondition = date_condition::get_json(date_condition::DIRECTION_FROM, time());
        return [
            'Two custom IP conditions and an unrelated one' => [
                'module' => 'page',
                'initial' => [
                    $unrelatedcondition,
                    ['type' => 'ip', 'ids' => [], 'custom' => '127.0.0.1'],
                    ['type' => 'ip', 'ids' => [], 'custom' => '192.168.0.1'],
                ],
                'expected' => [
                    $unrelatedcondition,
                    ['type' => 'ip', 'ids' => [], 'custom' => ['127.0.0.1']],
                    ['type' => 'ip', 'ids' => [], 'custom' => ['192.168.0.1']],
                ],
            ],
            'Empty custom string IP condition and an unrelated one' => [
                'module' => 'forum',
                'initial' => [
                    $unrelatedcondition,
                    ['type' => 'ip', 'ids' => [], 'custom' => ''],
                ],
                'expected' => [
                    $unrelatedcondition,
                    ['type' => 'ip', 'ids' => [], 'custom' => []],
                ]
            ],
            'Unrelated availability condition' => [
                'module' => 'url',
                'initial' => [$unrelatedcondition],
                'expected' => [$unrelatedcondition],
            ],
            'No availability condition' => [
                'module' => 'page',
                'initial' => [],
                'expected' => [],
            ],
        ];
    }
}
