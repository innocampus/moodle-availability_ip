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
 * Utility functions used in the upgrade process.
 *
 * @link https://moodledev.io/docs/guides/upgrade Moodle docs upgrade
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Finds every course module and course section with an IP availability condition and ensures its `custom` property is an array.
 *
 * @throws dml_exception An issue with the DB queries/transaction.
 * @throws JsonException Something went wrong re-encoding the availability object for one of the records.
 */
function replace_custom_single_ips_with_arrays(): void {
    global $DB;
    $transaction = $DB->start_delegated_transaction();
    try {
        foreach (['course_modules', 'course_sections'] as $table) {
            $recordset = $DB->get_recordset_select(
                table: $table,
                select: $DB->sql_like('availability', ':iptype'),
                params: ['iptype' => '%"type":"ip"%'],
                fields: 'id, availability',
            );
            try {
                foreach ($recordset as $record) {
                    if (replace_custom_single_ip_with_array($record)) {
                        $DB->update_record($table, $record);
                    }
                }
            } finally {
                $recordset->close();
            }
        }
        $transaction->allow_commit();
        // @codeCoverageIgnoreStart
    } catch (dml_exception | JsonException $e) {
        if (!$transaction->is_disposed()) {
            $transaction->rollback($e);
        }
        throw $e;
        // @codeCoverageIgnoreEnd
    }
}

/**
 * Replaces strings in the `custom` property of every IP availability condition of a course module/section record.
 *
 * @param stdClass $record DB record representing a course module or course section; must have the `availability` property.
 * @return bool Whether the `availability` property was modified.
 * @throws JsonException Something went wrong re-encoding the availability object.
 */
function replace_custom_single_ip_with_array(stdClass $record): bool {
    $availability = json_decode($record->availability);
    if (!is_object($availability)) {
        // Should not happen, but just in case.
        return false; // @codeCoverageIgnore
    }
    if (!replace_custom_single_ips_in_tree($availability)) {
        return false;
    }
    $record->availability = json_encode($availability, JSON_THROW_ON_ERROR);
    return true;
}

/**
 * Recursively replaces strings in the `custom` property of every IP condition in an availability tree.
 *
 * Conditions can be nested inside arbitrarily deep subtrees (restriction sets), so recursion is necessary.
 *
 * @param stdClass $tree Decoded availability tree; its children are expected in the `c` property.
 * @return bool Whether any condition in the tree was modified.
 */
function replace_custom_single_ips_in_tree(stdClass $tree): bool {
    $children = $tree->c ?? null;
    if (!is_array($children)) {
        // Should not happen, but just in case.
        return false; // @codeCoverageIgnore
    }
    $replaced = false;
    foreach ($children as $child) {
        if (!is_object($child)) {
            // Should not happen, but just in case.
            continue; // @codeCoverageIgnore
        }
        if (!isset($child->type)) {
            // Not a condition; must be a subtree.
            $replaced = replace_custom_single_ips_in_tree($child) || $replaced;
        } else if ($child->type === 'ip' && is_string($child->custom ?? null)) {
            $child->custom = $child->custom === '' ? [] : [$child->custom];
            $replaced = true;
        }
    }
    return $replaced;
}
