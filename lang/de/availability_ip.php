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
 * German language strings for the plugin.
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['condition_description'] = 'IP-Adresse zugelassen';
$string['custom_ip'] = 'Selbstgewählte IP-Adresse';
$string['custom_ip_help'] = 'Die Eingabe muss eine vollständige IP-Adresse für ein einzelnes Gerät (z.B. <code>192.168.10.1</code>), ein IP-Adressbereich (z.B. <code>231.3.56.10-20</code>) für alle IP-Adressen im Bereich (hier 10 bis 20) oder eine IP-Adresse in der CIDR-Schreibweise (z.B. <code>231.54.211.0/20</code>) sein.';
$string['description'] = 'Erlaube den Zugriff nur von bestimmten IP-Adressen.';
$string['error_custom_ip'] = 'Keine valide IP-Adresse eingegeben.';
$string['error_select_ip'] = 'Keine IP-Adressen/Adressbereiche ausgewählt.';
$string['ip_option_presets'] = 'Voreingestellte IP Optionen';
$string['ip_option_presets_help'] = '<p>Liste voreingestellter Optionen für die IP Voraussetzung, aus denen gewählt werden kann. Schreiben Sie jede Option in eine neue Zeile.</p><p>Optionen müssen in der Form <code>IP eindeutiger_kurzname Anzeigename</code> sein, wobei <code>IP</code> eine vollständige IP-Adresse für ein einzelnes Gerät ist (z.B. <code>192.168.10.1</code>). Ebenfalls gültig als <code>IP</code> sind IP-Adressbereiche (z.B. <code>231.3.56.10-20</code>) für alle IP-Adressen im Bereich (hier 10 bis 20) oder IP-Adressen in der CIDR-Schreibweise (z.B. <code>231.54.211.0/20</code>). <code>eindeutiger_kurzname</code> darf nur aus Kleinbuchstaben (<code>a-z</code>) und Unterstrichen (<code>_</code>) bestehen.</p><p>Beispiel:<pre>192.168.7.0/24  pc_pool  Lokaler PC Pool der Universität<br>111.222.333.444 admin_hq Admin HQ</pre></p><p><strong>ACHTUNG: Ein nachträgliches Entfernen von Optionen oder Ändern von Kurznamen kann bestehende Voraussetzungen zerstören!</strong></p>';
$string['ip_options_select'] = 'Aus einem der ausgewählten IP-Adressbereiche zugreifen:';
$string['pluginname'] = 'Voraussetzung IP';
$string['settings_error_bad_lines'] = 'Zeilen nicht in gültiger Form: {$a}';
$string['settings_error_duplicate_option_id'] = 'Der Kurzname \'{$a->id}\' in Zeile {$a->line} wird weiter oben bereits verwendet.';
$string['title'] = 'IP';
