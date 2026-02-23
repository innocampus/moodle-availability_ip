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
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for the {@see admin_ip_option} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(admin_ip_option::class)]
class admin_ip_option_test extends advanced_testcase {
    /**
     * Tests the {@see admin_ip_option::__construct} and {@see admin_ip_option::__toString} methods.
     * 
     * @param string[] $ips IP addresses/ranges.
     * @param string $id Option identifier.
     * @param string $name Human-readable name for the option.
     * @param string|Exception $expected Expected string representation of the constructed instance or expected exception object.
     * @throws coding_exception
     */
    #[DataProvider('test___construct_and___toString_provider')]
    public function test___construct_and___toString(
        array $ips,
        string $id,
        string $name,
        string|Exception $expected,
    ): void {
        if (is_string($expected)) {
            $option = new admin_ip_option($ips, $id, $name);
            self::assertSame($ips, $option->ips);
            self::assertSame($id, $option->id);
            self::assertSame($name, $option->name);
            self::assertSame($expected, (string) $option);
        } else {
            $this->expectExceptionObject($expected);
            new admin_ip_option($ips, $id, $name);
        }
    }

    /**
     * Data provider for the {@see test___construct_and___toString} method.
     *
     * @return array Inputs for the test method.
     */
    public static function test___construct_and___toString_provider(): array {
        return [
            'Single IPv4 address' => [
                'ips' => ['127.0.0.1'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => '127.0.0.1 foo Bar Baz',
            ],
            'IPv4 address range in CIDR notation' => [
                'ips' => ['10.10.10.0/24'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => '10.10.10.0/24 foo Bar Baz',
            ],
            'IPv4 address range with hyphen in last octet' => [
                'ips' => ['192.168.0.100-200'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => '192.168.0.100-200 foo Bar Baz',
            ],
            'IP address 0.0.0.0' => [
                'ips' => ['0.0.0.0'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => '0.0.0.0 foo Bar Baz',
            ],
            'Multiple valid addresses/ranges' => [
                'ips' => ['127.0.0.1', '10.10.10.0/24', '192.168.0.100-200'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => '127.0.0.1,10.10.10.0/24,192.168.0.100-200 foo Bar Baz',
            ],
            'Invalid IPv4 address' => [
                'ips' => ['1.20.30.400'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => new coding_exception('Not a valid IP address/range: 1.20.30.400'),
            ],
            'Incomplete IPv4 address' => [
                'ips' => ['10.10.10.'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => new coding_exception('Not a valid IP address/range: 10.10.10.'),
            ],
            'Invalid CIDR length greater than 32' => [
                'ips' => ['10.10.10.0/33'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => new coding_exception('Not a valid IP address/range: 10.10.10.0/33'),
            ],
            'Invalid last octet range greater than 255' => [
                'ips' => ['192.168.0.100-256'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => new coding_exception('Not a valid IP address/range: 192.168.0.100-256'),
            ],
            'Invalid last octet empty range' => [
                'ips' => ['192.168.0.100-90'],
                'id' => 'foo',
                'name' => 'Bar Baz',
                'expected' => new coding_exception('Not a valid IP address/range: 192.168.0.100-90'),
            ],
        ];
    }

    /**
     * Test the {@see admin_ip_option::parse} method.
     * 
     * @param string $line Input for the method.
     * @param array|null $expected Expected properties on the returned object or `null` if `null` is expected to be returned.
     */
    #[DataProvider('test_parse_provider')]
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
