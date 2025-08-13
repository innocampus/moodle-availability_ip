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
 * Definition of the {@see admin_ip_option_test} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpIllegalPsrClassPathInspection}
 */

namespace availability_ip;

use advanced_testcase;
use core\exception\coding_exception;

/**
 * Unit tests for the {@see admin_ip_option} class.
 *
 * @coversDefaultClass \availability_ip\admin_ip_option
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_ip_option_test extends advanced_testcase {

    /**
     * @covers ::__construct
     * @dataProvider test___construct_provider
     * @param string[] $ips IP addresses/ranges.
     * @param string $id Option identifier.
     * @param string $name Human-readable name for the option.
     * @param string|null $error Error class name, if such an error is to be expected; `null` (default) otherwise.
     * @throws coding_exception
     */
    public function test___construct(
        array $ips,
        string $id,
        string $name,
        string|null $error = null,
    ): void {
        if (is_null($error)) {
            $option = new admin_ip_option($ips, $id, $name);
            self::assertSame($ips, $option->ips);
            self::assertSame($id, $option->id);
            self::assertSame($name, $option->name);
        } else {
            $this->expectException($error);
            new admin_ip_option($ips, $id, $name);
        }
    }

    /**
     * Data provider for the {@see test___construct} method.
     *
     * @return array Inputs for the test method.
     */
    public static function test___construct_provider(): array {
        return [
            'Single IPv4 address' => [
                'ips' => ['127.0.0.1'],
                'id' => 'foo',
                'name' => 'Bar Baz',
            ],
            'IPv4 address range in CIDR notation' => [
                'ips' => ['10.10.10.0/24'],
                'id' => 'foo',
                'name' => 'Bar Baz',
            ],
            'IPv4 address range with hyphen in last octet' => [
                'ips' => ['192.168.0.100-200'],
                'id' => 'foo',
                'name' => 'Bar Baz',
            ],
            'IP address 0.0.0.0' => [
                'ips' => ['0.0.0.0'],
                'id' => 'foo',
                'name' => 'Bar Baz',
            ],
            'Multiple valid addresses/ranges' => [
                'ips' => ['127.0.0.1', '10.10.10.0/24', '192.168.0.100-200'],
                'id' => 'foo',
                'name' => 'Bar Baz',
            ],
            'Invalid IPv4 address' => [
                'ips' => ['1.20.30.400'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'error' => coding_exception::class,
            ],
            'Incomplete IPv4 address' => [
                'ips' => ['10.10.10.'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'error' => coding_exception::class,
            ],
            'Invalid CIDR length greater than 32' => [
                'ips' => ['10.10.10.0/33'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'error' => coding_exception::class,
            ],
            'Invalid last octet range greater than 255' => [
                'ips' => ['192.168.0.100-256'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'error' => coding_exception::class,
            ],
            'Invalid last octet empty range' => [
                'ips' => ['192.168.0.100-90'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'error' => coding_exception::class,
            ],
        ];
    }

    /**
     * @covers ::parse
     * @param string $line Input for the method.
     * @param array|null $expected Expected properties on the returned object or `null` if `null` is expected to be returned.
     * @dataProvider test_parse_provider
     */
    public function test_parse(string $line, array|null $expected): void {
        $option = admin_ip_option::parse($line);
        if (is_null($expected)) {
            self::assertNull($option);
        } else {
            self::assertInstanceOf(admin_ip_option::class, $option);
            self::assertSame($expected, (array) $option);
        }
    }

    /**
     * Data provider for the {@see test_parse} method.
     *
     * @return array Inputs for the test method.
     */
    public static function test_parse_provider(): array {
        return [
            'Single IPv4 address' => [
                'line' => '127.0.0.1 foo Bar Baz',
                'expected' => [
                    'ips' => ['127.0.0.1'],
                    'id' => 'foo',
                    'name' => 'Bar Baz',
                ],
            ],
            'Single IPv4 address and lots of whitespace' => [
                'line' => '   127.0.0.1    foo    Bar Baz     ',
                'expected' => [
                    'ips' => ['127.0.0.1'],
                    'id' => 'foo',
                    'name' => 'Bar Baz',
                ],
            ],
            'IPv4 address range in CIDR notation' => [
                'line' => '10.10.10.0/24 foo Bar Baz',
                'expected' => [
                    'ips' => ['10.10.10.0/24'],
                    'id' => 'foo',
                    'name' => 'Bar Baz',
                ],
            ],
            'IPv4 address range with hyphen in last octet' => [
                'line' => '192.168.0.100-200 foo Bar Baz',
                'expected' => [
                    'ips' => ['192.168.0.100-200'],
                    'id' => 'foo',
                    'name' => 'Bar Baz',
                ],
            ],
            'Multiple valid IPv4 addresses/ranges' => [
                'line' => '127.0.0.1,10.10.10.0/24,192.168.0.100-200 foo Bar Baz',
                'expected' => [
                    'ips' => ['127.0.0.1', '10.10.10.0/24', '192.168.0.100-200'],
                    'id' => 'foo',
                    'name' => 'Bar Baz',
                ],
            ],
            'Invalid IPv4 address' => [
                'line' => '1.20.30.400 foo Bar Baz',
                'expected' => null,
            ],
            'Missing IPv4 address' => [
                'line' => ' foo Bar Baz',
                'expected' => null,
            ],
        ];
    }
}
