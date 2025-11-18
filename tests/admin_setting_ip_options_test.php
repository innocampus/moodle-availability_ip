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
 * Definition of the {@see admin_setting_ip_options_test} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpIllegalPsrClassPathInspection}
 */

namespace availability_ip;

use advanced_testcase;
use admin_setting_configtextarea;
use core\exception\coding_exception;
use core\param;
use dml_exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for the {@see admin_setting_ip_options} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(admin_setting_ip_options::class)]
class admin_setting_ip_options_test extends advanced_testcase {

    /**
     * Ensures that relevant files are loaded.
     */
    public static function setupBeforeClass(): void {
        global $CFG;
        parent::setupBeforeClass();
        require_once("$CFG->libdir/adminlib.php");
    }

    public function test_output_html(): void {
        $this->resetAfterTest();
        $options = new admin_setting_ip_options(
            name: 'does_not_matter',
            visiblename: 'foo',
            description: 'bar',
            defaultsetting: '',
        );
        $textarea = new admin_setting_configtextarea(
            name: 'does_not_matter',
            visiblename: 'foo',
            description: 'bar',
            defaultsetting: '',
        );
        $style = '<style>' . "\n";
        $style .= "textarea[name=\"{$options->get_full_name()}\"] ";
        $style .= '{ font-family: ' . admin_setting_ip_options::FONT_FAMILY . '; }' . "\n";
        $style .= '</style>' . "\n";
        self::assertSame(
            $style . $textarea->output_html(''),
            $options->output_html(''),
        );
    }

    /**
     * @param string $data Input for the method.
     * @param array|string $expected Any array if the input data is expected to pass validation; expected error string otherwise.
     *                               (The array type is just because we are re-using the data provider.)
     * @throws coding_exception
     */
    #[DataProvider('test_parse_ip_options_provider')]
    public function test_validate(string $data, array|string $expected = []): void {
        $options = new admin_setting_ip_options(
            name: 'does_not_matter',
            visiblename: 'foo',
            description: 'bar',
            defaultsetting: '',
        );
        if (is_array($expected)) {
            self::assertTrue($options->validate($data));
        } else {
            self::assertSame($expected, $options->validate($data));
        }
    }

    /**
     * Tests only that an error string returned from the inherited `validate` method is propagated.
     *
     * @throws coding_exception
     */
    public function test_validate_parent(): void {
        $options = new admin_setting_ip_options(
            name: 'does_not_matter',
            visiblename: 'foo',
            description: 'bar',
            defaultsetting: '',
            paramtype: param::RAW_TRIMMED->value,
        );
        self::assertTrue($options->validate(''));
        self::assertSame(
            get_string('validateerror', 'admin'),
            $options->validate(' '),
        );
    }

    /**
     * @param string $data Input for the method.
     * @param array|string $expected Resulting admin options (as associative arrays) if the input data is expected to be valid;
     *                               expected error string otherwise.
     * @throws coding_exception
     */
    #[DataProvider('test_parse_ip_options_provider')]
    public function test_parse_ip_options(string $data, array|string $expected = []): void {
        $output = admin_setting_ip_options::parse_ip_options($data);
        if (is_string($expected)) {
            self::assertSame($expected, $output);
            return;
        }
        self::assertIsArray($output);
        self::assertSameSize($expected, $output);
        foreach ($expected as $id => $option) {
            self::assertArrayHasKey($id, $output);
            self::assertEquals($option, (array) $output[$id]);
        }
    }

    /**
     * @param string $data Input for the method.
     * @param array|string $expected Resulting admin options (as associative arrays) if the input data is expected to be valid;
     *                               any string otherwise. (The string type is just because we are re-using the data provider.)
     * @throws coding_exception
     * @throws dml_exception
     */
    #[DataProvider('test_parse_ip_options_provider')]
    public function test_get_parsed(string $data, array|string $expected): void {
        $this->resetAfterTest();
        set_config('foo', $data, 'availability_ip');
        $output = admin_setting_ip_options::get_parsed('availability_ip', 'foo');
        if (is_string($expected)) {
            self::assertSame([], $output);
            return;
        }
        self::assertIsArray($output);
        self::assertSameSize($expected, $output);
        foreach ($expected as $id => $option) {
            self::assertArrayHasKey($id, $output);
            self::assertEquals($option, (array) $output[$id]);
        }
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_get_parsed_no_config(): void {
        $output = admin_setting_ip_options::get_parsed('availability_ip', 'foo');
        self::assertSame([], $output);
    }

    /**
     * Data provider for the {@see test_parse_ip_options} method.
     *
     * @return array[] Inputs for the test method.
     * @throws coding_exception
     */
    public static function test_parse_ip_options_provider(): array {
        return [
            'Empty string' => [
                'data' => '',
                'expected' => [],
            ],
            'Whitespace only' => [
                'data' => '      ',
                'expected' => [],
            ],
            'Single valid line' => [
                'data' => '127.0.0.1,10.10.10.0/24,192.168.0.100-200 localhost Local machine',
                'expected' => [
                    'localhost' => [
                        'ips' => ['127.0.0.1', '10.10.10.0/24', '192.168.0.100-200'],
                        'id' => 'localhost',
                        'name' => 'Local machine',
                    ],
                ],
            ],
            'Multiple valid lines with arbitrary whitespace in between' => [
                'data' => "\n
                127.0.0.1      localhost       Local machine        \n\n\n
                172.18.0.0/16  docker_network  Moodle Docker network\n\n
                ",
                'expected' => [
                    'localhost' => [
                        'ips' => ['127.0.0.1'],
                        'id' => 'localhost',
                        'name' => 'Local machine',
                    ],
                    'docker_network' => [
                        'ips' => ['172.18.0.0/16'],
                        'id' => 'docker_network',
                        'name' => 'Moodle Docker network',
                    ],
                ],
            ],
            'Bad line' => [
                'data' => 'foo',
                'expected' => get_string(
                    identifier: 'settings_error_bad_lines',
                    component: 'availability_ip',
                    a: '"foo"',
                ),
            ],
            'Duplicate IDs' => [
                'data' => "1.0.0.1 foo Foo \n 1.1.1.1 foo Bar",
                'expected' => get_string(
                    identifier: 'settings_error_duplicate_option_id',
                    component: 'availability_ip',
                    a: ['id'  => 'foo', 'line' => 2],
                ),
            ],
        ];
    }
}
