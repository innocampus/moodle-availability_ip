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
 * Definition of the {@see behat_availability_ip} class.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * {@noinspection PhpIllegalPsrClassPathInspection}
 */

use availability_ip\admin_ip_option;
use Behat\Gherkin\Node\TableNode;
use core\session\manager;

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Behat steps definitions.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_availability_ip extends behat_base {
    /** @var string Special IP code to replace with the IP address of the current Behat session user. */
    const CODE_BEHAT_USER = '<behat_user>';

    /** @var string Special IP code to replace with an IP address range that covers the current Behat session user. */
    const CODE_BEHAT_USER_RANGE = '<behat_user_range>';

    /** @var string Special IP code to replace with an IP address in CIDR notation that covers the current Behat session user. */
    const CODE_BEHAT_USER_CIDR = '<behat_user_cidr>';

    /** @var string Special IP code to replace with an IP address that is not the one of the current Behat session user. */
    const CODE_NOT_BEHAT_USER = '<not_behat_user>';

    /**
     * Sets the `ip_option_presets` config value to the provided options.
     *
     * @Given /^the following IP option presets exist:$/
     * @param TableNode $data Table with the columns `ips`, `id`, and `name`.
     *                        These will be validated by the {@see admin_ip_option} constructor.
     *                        Special IP codes (e.g. {@see self::CODE_BEHAT_USER}) can be passed in the `ips` column as well.
     * @throws coding_exception The table was missing the required columns, or the session IP could not be determined.
     *
     * {@noinspection PhpUnused}
     */
    public function the_following_ip_option_presets_exist(TableNode $data): void {
        $options = [];
        foreach ($data->getColumnsHash() as $item) {
            if ($diff = array_diff_key(array_flip(['ips', 'id', 'name']), $item)) {
                throw new coding_exception("Missing columns: " . implode(', ', $diff));
            }
            $options[] = new admin_ip_option(
                ips: array_map(
                    [$this, 'replace_special_ip_value'],
                    array_filter(explode(',', $item['ips'])),
                ),
                id: $item['id'],
                name: $item['name'],
            );
        }
        set_config('ip_option_presets', implode("\n", $options), 'availability_ip');
    }

    /**
     * Sets the custom IP value in the availability form.
     *
     * @Given /^I set the custom IP field to "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @param string $value Either a valid IP address/range or one of the special IP codes (e.g. {@see self::CODE_BEHAT_USER}).
     * @throws coding_exception
     *
     * {@noinspection PhpUnused}
     */
    public function i_set_the_custom_ip_field_to(string $value): void {
        $field = behat_field_manager::get_form_field_from_label('-custom-', $this);
        $field->set_value($this->replace_special_ip_value($value));
    }

    /**
     * Replaces a given special IP code (e.g. {@see self::CODE_BEHAT_USER}) with the corresponding valid IP address/range.
     *
     * @param string $ip
     * @return string IP address/range or special value.
     * @throws coding_exception Either the session cookie could not be retrieved, or the session entry did not have a `lastip`.
     */
    private function replace_special_ip_value(string $ip): string {
        return match (strtolower($ip)) {
            self::CODE_BEHAT_USER => $this->get_session_ip(),
            self::CODE_BEHAT_USER_RANGE => preg_replace(
                pattern: '/\.\d+$/',
                replacement: '.0-255',
                subject: $this->get_session_ip(),
            ),
            self::CODE_BEHAT_USER_CIDR => preg_replace(
                pattern: '/\.\d+$/',
                replacement: '.0/24',
                subject: $this->get_session_ip(),
            ),
            self::CODE_NOT_BEHAT_USER => preg_replace_callback(
                pattern: '/(\d+)\.(\d+)$/',
                callback: fn (array $matches): string => ($matches[1] === '0' ? '1' : '0') . ".$matches[2]",
                subject: $this->get_session_ip(),
            ),
            default => $ip,
        };
    }

    /**
     * Get the actual IP address of the user in the behat session (note `$USER` does not correspond to the behat session's user).
     *
     * @return string IP address as saved in the `lastip` field of the record in the `session` table.
     * @throws coding_exception Either the session cookie could not be retrieved, or the session entry did not have a `lastip`.
     */
    private function get_session_ip(): string {
        $sid = $this->getSession()->getCookie('MoodleSession');
        if (empty($sid)) {
            throw new coding_exception('Failed to get Moodle session cookie');
        }
        $session = manager::get_session_by_sid($sid);
        if (empty($session->lastip)) {
            throw new coding_exception("Failed to get last IP from session id: $sid");
        }
        return $session->lastip;
    }
}
