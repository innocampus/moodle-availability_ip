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
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;
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
     * Casts a row in a table of IP options to an {@see admin_ip_option} instance.
     *
     * @param string[] $row Associative array with the keys `ips`, `id`, and `name`.
     *                      The `ips` may include special IP codes (e.g. {@see self::CODE_BEHAT_USER}).
     *                      The values will be validated by the {@see admin_ip_option} constructor.
     * @return admin_ip_option Object with the properties from the table row.
     * @throws coding_exception Row was missing required keys, an IP value was invalid, or the session IP could not be determined.
     *
     * {@noinspection PhpUnused}
     */
    #[Transform('row:ips,id,name')]
    public function cast_ip_option_row(array $row): admin_ip_option {
        return new admin_ip_option(
            ips: array_map(
                [$this, 'replace_special_ip_value'],
                array_filter(explode(',', $row['ips'])),
            ),
            id: $row['id'],
            name: $row['name'],
        );
    }

    /**
     * Sets the `ip_option_presets` config value to what was specified in an IP options table.
     *
     * The table must contain the columns `ips`, `id`, and `name`. Special IP codes (e.g. {@see self::CODE_BEHAT_USER}) can be
     * passed in the `ips` column.
     *
     * This method depends on the {@see cast_ip_option_row} transformation.
     *
     * @param admin_ip_option[] $options IP options as defined in the table.
     *
     * {@noinspection PhpUnused}
     */
    #[Given('the following IP option presets exist:')]
    public function the_following_ip_option_presets_exist(array $options): void {
        set_config('ip_option_presets', implode("\n", $options), 'availability_ip');
    }

    /**
     * Sets the custom IP value in the availability form.
     *
     * @param string $value Either a valid IP address/range or one of the special IP codes (e.g. {@see self::CODE_BEHAT_USER}).
     * @throws coding_exception
     *
     * {@noinspection PhpUnused}
     */
    #[Given('I set the custom IP field to :value')]
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
     * Gets the actual IP address of the user in the behat session (note `$USER` does not correspond to the behat session's user).
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

    /**
     * Asserts that a checkbox with a specific label is visible.
     *
     * @param string $label Exact contents of the expected label element.
     * @param string|null $inputname Value of the `for` attribute the label must have. Ignored if `null` (default).
     * @throws Exception
     *
     * {@noinspection PhpUnused}
     */
    #[Then('I should see a checkbox labeled :label')]
    #[Then('I should see a checkbox labeled :label for the input :inputname')]
    public function i_should_see_a_checkbox_labeled(string $label, string|null $inputname = null): void {
        $labelpredicate = "text()='$label'";
        if (!is_null($inputname)) {
            $labelpredicate .= " and @for='$inputname'";
        }
        $xpath = "//input[@type='checkbox']/following-sibling::label[$labelpredicate]";
        $this->execute(
            'behat_general::should_be_visible',
            [$xpath, 'xpath_element'],
        );
    }

    /**
     * Asserts that an alert of a certain type containing the specified text is displayed.
     *
     * @param string $text Text expected to be present in the alert.
     * @param string|null $type Either `success`, `warning`, or `danger` indicating the expected alert class.
     *                          Ignored if `null` (default).
     * @throws Exception
     *
     * {@noinspection PhpUnused}
     */
    #[Then('/^I should get an alert with "(?P<text>(?:[^"]|\\")*)"$/')]
    #[Then('/^I should get a (?P<type>success|warning|danger) alert with "(?P<text>(?:[^"]|\\")*)"$/')]
    public function i_should_get_a_alert_with(string $text, string|null $type = null): void {
        $locator = '.alert';
        if (!is_null($type)) {
            $locator .= match ($type) {
                'success' => '.alert-success',
                'warning' => '.alert-warning',
                'danger' => '.alert-danger',
                default => throw new coding_exception("Invalid alert type: $type"),
            };
        }
        $this->execute(
            'behat_general::assert_element_contains_text',
            [$text, $locator, 'css_element'],
        );
    }

    /**
     * Asserts that a warning badge containing the specified text is displayed.
     *
     * @param string $text Text expected to be present in the warning badge element.
     * @throws Exception
     *
     * {@noinspection PhpUnused}
     */
    #[Then('I should see a warning badge with :text')]
    public function i_should_see_a_warning_badge_with(string $text): void {
        $this->execute(
            'behat_general::assert_element_contains_text',
            [$text, '.badge.bg-warning', 'css_element'],
        );
    }
}
