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
 * Definition of the {@see frontend_test} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpIllegalPsrClassPathInspection}
 */

namespace availability_ip;

use advanced_testcase;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for the {@see frontend} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(frontend::class)]
class frontend_test extends advanced_testcase {

    /**
     * @param string|null $config Value for the `ip_option_presets` config to set.
     * @param array[] $expected IP options (as associative arrays) expected as the first and only element in the returned array.
     * {@noinspection PhpUndefinedMethodInspection}
     */
    #[DataProvider('test_get_javascript_init_params_provider')]
    public function test_get_javascript_init_params(string|null $config, array $expected): void {
        if (!is_null($config)) {
            $this->resetAfterTest();
            set_config('ip_option_presets', $config, 'availability_ip');
        }
        // We need nothing from the parent implementation/constructor and no internal state for our method.
        $frontend = $this->getMockBuilder(frontend::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        // Simple closure binding workaround to test the protected method.
        $closure = function(): array { return $this->get_javascript_init_params(new stdClass()); };
        $output = $closure->call($frontend);
        self::assertCount(1, $output);
        $options = array_map(fn (admin_ip_option $option): array => (array) $option, $output[0]);
        self::assertEquals($expected, $options);
    }

    /**
     * Data provider for the {@see test_get_javascript_init_params} method.
     *
     * @return array[] Inputs for the test method.
     */
    public static function test_get_javascript_init_params_provider(): array {
        return [
            'No preset options' => [
                'config' => null,
                'expected' => [],
            ],
            'A few preset options' => [
                'config' => "1.0.0.0 foo Foo\n1.1.1.1 bar Bar",
                'expected' => [
                    ['ips' => ['1.0.0.0'], 'id' => 'foo', 'name' => 'Foo'],
                    ['ips' => ['1.1.1.1'], 'id' => 'bar', 'name' => 'Bar'],
                ],
            ],
        ];
    }

    /**
     * {@noinspection PhpUndefinedMethodInspection}
     */
    public function test_get_javascript_strings(): void {
        // We need nothing from the parent implementation/constructor and no internal state for our method.
        $frontend = $this->getMockBuilder(frontend::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        // Simple closure binding workaround to test the protected method.
        $closure = function(): array { return $this->get_javascript_strings(); };
        $output = $closure->call($frontend);
        $expected = [
            'custom_ip',
            'custom_ip_help',
            'error_custom_ip',
            'error_select_ip',
            'ip_options_select',
        ];
        self::assertSame($expected, $output);
    }
}
