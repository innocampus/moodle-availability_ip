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
 * Utility functions used in the upgrade process.
 *
 * @see https://moodledev.io/docs/5.1/guides/upgrade
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Finds every course module with an IP availability condition and ensures its `custom` property is an array.
 *
 * @throws dml_exception An issue with the DB queries/transaction.
 * @throws JsonException Something went wrong re-encoding the availability object for one of the records.
 */
function replace_custom_single_ips_with_arrays(): void {
    global $DB;
    $recordset = $DB->get_recordset_select(
        table: 'course_modules',
        select: $DB->sql_like('availability', ':iptype'),
        params: ['iptype' => '%"type":"ip"%'],
        fields: 'id, availability',
    );
    $transaction = $DB->start_delegated_transaction();
    try {
        foreach ($recordset as $record) {
            if (replace_custom_single_ip_with_array($record)) {
                $DB->update_record('course_modules', $record);
            }
        }
        $transaction->allow_commit();
    // @codeCoverageIgnoreStart
    } catch (dml_exception | JsonException $e) {
        if (!empty($transaction) && !$transaction->is_disposed()) {
            $transaction->rollback($e);
        }
        throw $e;
    }
    // @codeCoverageIgnoreEnd
    $recordset->close();
}

/**
 * Replaces a string in the `custom` property of an IP availability condition with an array for a given course module record.
 *
 * @param stdClass $record DB record representing a course module; must have the `availability` property.
 * @return bool Whether the `availability` property was modified.
 * @throws JsonException Something went wrong re-encoding the availability object.
 */
function replace_custom_single_ip_with_array(stdClass $record): bool {
    $availability = json_decode($record->availability);
    if (is_null($availability)) {
        // Should not happen, but just in case.
        return false; // @codeCoverageIgnore
    }
    $conditions = $availability->c ?? null;
    if (!is_array($conditions)) {
        // Should not happen, but just in case.
        return false; // @codeCoverageIgnore
    }
    $replaced = false;
    foreach ($conditions as $condition) {
        if (($condition->type ?? null) === 'ip' && is_string($condition->custom ?? null)) {
            $condition->custom = $condition->custom === '' ? [] : [$condition->custom];
            $replaced = true;
        }
    }
    if ($replaced) {
        $record->availability = json_encode($availability, JSON_THROW_ON_ERROR);
    }
    return $replaced;
}
