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
 * Definition of the {@see condition_test} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpIllegalPsrClassPathInspection, PhpUnhandledExceptionInspection}
 */

namespace availability_ip;

use advanced_testcase;
use core\exception\coding_exception;
use core_availability\mock_info;
use dml_exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for the {@see condition} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(condition::class)]
final class condition_test extends advanced_testcase {
    /**
     * Ensures that relevant files are loaded.
     */
    #[\Override]
    public static function setupBeforeClass(): void {
        global $CFG;
        parent::setupBeforeClass();
        require_once("$CFG->dirroot/availability/tests/fixtures/mock_info.php");
    }

    /**
     * Tests the {@see condition::__construct} method.
     *
     * @param array $presets Admin options presets to set at the beginning.
     * @param array $structure Constructor argument for {@see condition}.
     * @param array|string $expected An array representing the expected properties of the initialized {@see condition} instance or
     *                               the class name of the expected error. If the array contains the `customips` key, the instance
     *                               is expected to have the same values in its `customips` property. If the array contains the
     *                               `options` key, its value must be an array of associative arrays with the keys `id`, `ip`,
     *                               and `name` that the instances `options` property will be checked against.
     * @param bool $debugging If `true`, a debugging call will be expected.
     * @throws coding_exception
     * @throws dml_exception
     */
    #[DataProvider('provider_test___construct')]
    public function test___construct(array $presets, array $structure, array|string $expected, bool $debugging = false): void {
        $this->resetAfterTest();
        set_config(
            name: 'ip_option_presets',
            value: implode("\n", $presets),
            plugin: 'availability_ip',
        );
        if (is_string($expected)) {
            $this->expectException($expected);
            new condition((object) $structure);
            return;
        }
        $condition = new condition((object) $structure);
        if (array_key_exists('customips', $expected)) {
            self::assertSame($expected['customips'], $condition->customips);
        }
        $optionids = array_column($condition->options, 'id');
        $optionips = array_column($condition->options, 'ips');
        $optionnames = array_column($condition->options, 'name');
        if (array_key_exists('options', $expected)) {
            foreach ($expected['options'] as $option) {
                self::assertContains($option['id'], $optionids);
                self::assertContains($option['ips'], $optionips);
                self::assertContains($option['name'], $optionnames);
            }
        }
        if ($debugging) {
            self::assertDebuggingCalled();
        }
    }

    /**
     * Data provider for the {@see test___construct} method.
     *
     * @return array[] Inputs for the test method.
     *
     * @phpcs:disable moodle.Strings.ForbiddenStrings
     */
    public static function provider_test___construct(): array {
        return [
            'No `ids` and no `custom`' => [
                'presets' => [],
                'structure' => ['spam' => 'eggs'],
                'expected' => coding_exception::class,
            ],
            'Not an array in `ids`' => [
                'presets' => [],
                'structure' => ['ids' => 'beans'],
                'expected' => coding_exception::class,
            ],
            'Invalid IP in `custom`' => [
                'presets' => [],
                'structure' => ['custom' => ['1.2.3.400']],
                'expected' => coding_exception::class,
            ],
            'Non-string ID in `ids`' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                ],
                'structure' => ['ids' => ['foo', 3.14]],
                'expected' => coding_exception::class,
            ],
            'No option preset matches one of the IDs in `ids`' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                ],
                'structure' => ['ids' => ['foo', 'bar']],
                'expected' => ['customips' => []],
                'debugging' => true,
            ],
            'Only a `custom` IP' => [
                'presets' => [],
                'structure' => ['custom' => ['192.168.0.1']],
                'expected' => ['customips' => ['192.168.0.1']],
            ],
            'Valid `ids` and valid `custom`' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                    '10.0.0.1  bar Bar',
                ],
                'structure' => [
                    'ids' => ['foo', 'bar'],
                    'custom' => ['192.168.0.1'],
                ],
                'expected' => [
                    'options' => [
                        ['ips' => ['127.0.0.1'], 'id' => 'foo', 'name' => 'Foo'],
                        ['ips' => ['10.0.0.1'], 'id' => 'bar', 'name' => 'Bar'],
                    ],
                    'customips' => ['192.168.0.1'],
                ],
            ],
        ];
    }

    /**
     * Tests the {@see condition::is_available} method.
     *
     * @param array $presets Admin options presets to set at the beginning.
     * @param array $structure Constructor argument for {@see condition}.
     * @param bool $expected Whether we expect the test module to be available to the user.
     * @throws coding_exception
     * @throws dml_exception
     */
    #[DataProvider('provider_test_is_available')]
    public function test_is_available(array $presets, array $structure, bool $expected): void {
        $this->resetAfterTest();
        set_config(
            name: 'ip_option_presets',
            value: implode("\n", $presets),
            plugin: 'availability_ip',
        );
        $condition = new condition((object) $structure);
        $user = $this->getDataGenerator()->create_user();
        $info = new mock_info();
        self::assertTrue($condition->is_available(not: !$expected, info: $info, grabthelot: false, userid: $user->id));
        self::assertFalse($condition->is_available(not: $expected, info: $info, grabthelot: false, userid: $user->id));
    }

    /**
     * Data provider for the {@see test_is_available} method.
     *
     * @return array[] Inputs for the test method.
     */
    public static function provider_test_is_available(): array {
        return [
            'IP neither matches an admin option preset nor the custom range' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                    '10.0.0.1  bar Bar',
                ],
                'structure' => [
                    'ids' => ['foo', 'bar'],
                    'custom' => ['192.168.0.1'],
                ],
                'expected' => false,
            ],
            'IP matches an admin option exactly' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                    '10.0.0.1  bar Bar',
                    condition::PHPUNIT_CLIENT_IP . ' testing Testing',
                ],
                'structure' => [
                    'ids' => ['foo', 'bar', 'testing'],
                    'custom' => ['192.168.0.1'],
                ],
                'expected' => true,
            ],
            'IP falls in an admin option range' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                    '10.0.0.1  bar Bar',
                    condition::PHPUNIT_CLIENT_IP . '-255 testing Testing',
                ],
                'structure' => [
                    'ids' => ['foo', 'bar', 'testing'],
                    'custom' => ['192.168.0.1'],
                ],
                'expected' => true,
            ],
            'IP matches custom setting exactly' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                    '10.0.0.1  bar Bar',
                ],
                'structure' => [
                    'ids' => ['foo', 'bar'],
                    'custom' => [condition::PHPUNIT_CLIENT_IP],
                ],
                'expected' => true,
            ],
        ];
    }

    public function test_get_description(): void {
        $condition = new condition((object) ['ids' => []]);
        $info = new mock_info();
        $expected = get_string('condition_description', 'availability_ip');
        self::assertEquals($expected, $condition->get_description(full: false, not: false, info: $info));
        self::assertEquals($expected, $condition->get_description(full: false, not: true, info: $info));
        self::assertEquals($expected, $condition->get_description(full: true, not: false, info: $info));
        self::assertEquals($expected, $condition->get_description(full: true, not: true, info: $info));
    }

    /**
     * {@noinspection PhpUndefinedMethodInspection}
     */
    public function test_get_debug_string(): void {
        // We expect our implementation to simply JSON encode the return value of the `save` method.
        // Therefore, we simply mock the `save` method and check if it is called.
        $condition = $this->getMockBuilder(condition::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();
        $saveobject = (object) ['type' => 'ip', 'foo' => 'bar'];
        $condition->expects($this->once())
            ->method('save')
            ->willReturn($saveobject)
            ->with();
        // Simple closure binding workaround to test the protected method.
        $closure = function (): string {
            return $this->get_debug_string();
        };
        $output = $closure->call($condition);
        self::assertSame(json_encode($saveobject), $output);
    }

    /**
     * Tests the {@see condition::save} method.
     *
     * @param array $presets Admin options presets to set at the beginning.
     * @param array $structure Constructor argument for {@see condition}.
     * @param array $expected Expected properties on the returned object.
     * @throws coding_exception
     * @throws dml_exception
     */
    #[DataProvider('provider_test_save')]
    public function test_save(array $presets, array $structure, array $expected): void {
        $this->resetAfterTest();
        set_config(
            name: 'ip_option_presets',
            value: implode("\n", $presets),
            plugin: 'availability_ip',
        );
        $condition = new condition((object) $structure);
        self::assertEquals((object) $expected, $condition->save());
    }

    /**
     * Data provider for the {@see test_save} method.
     *
     * @return array[] Inputs for the test method.
     */
    public static function provider_test_save(): array {
        return [
            'Just a custom IP' => [
                'presets' => [],
                'structure' => [
                    'custom' => ['192.168.0.1'],
                ],
                'expected' => ['type' => 'ip', 'custom' => ['192.168.0.1']],
            ],
            'Just a preset ID' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                ],
                'structure' => [
                    'ids' => ['foo'],
                ],
                'expected' => ['type' => 'ip', 'ids' => ['foo']],
            ],
            'Both a preset ID and a custom IP' => [
                'presets' => [
                    '127.0.0.1 foo Foo',
                ],
                'structure' => [
                    'ids' => ['foo'],
                    'custom' => ['192.168.0.1'],
                ],
                'expected' => ['type' => 'ip', 'ids' => ['foo'], 'custom' => ['192.168.0.1']],
            ],
        ];
    }
}
