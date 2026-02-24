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
 * German language strings for the plugin.
 *
 * @link https://docs.moodle.org/dev/String_API Moodle docs String API
 *
 * @package    availability_ip
 * @copyright  2025 Daniel Fainberg, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['condition:description'] = 'IP-Adresse zugelassen';
$string['config:ip_option_presets'] = 'Voreingestellte IP Optionen';
$string['config:ip_option_presets:help'] = '<p>Liste voreingestellter Optionen für die IP Voraussetzung, aus denen gewählt werden kann. Schreiben Sie jede Option in eine neue Zeile.</p><p>Optionen müssen in der Form <code>IP eindeutiger_kurzname Anzeigename</code> sein, wobei <code>IP</code> eine vollständige IP-Adresse für ein einzelnes Gerät ist (z.B. <code>192.168.10.1</code>). Ebenfalls gültig als <code>IP</code> sind IP-Adressbereiche (z.B. <code>231.3.56.10-20</code>) für alle IP-Adressen im Bereich (hier 10 bis 20) oder IP-Adressen in der CIDR-Schreibweise (z.B. <code>231.54.211.0/20</code>). Mehrere <code>IPs</code> können durch Komma getrennt aufgeführt werden. <code>eindeutiger_kurzname</code> darf nur aus Kleinbuchstaben (<code>a-z</code>) und Unterstrichen (<code>_</code>) bestehen.</p><p>Beispiel:<pre>192.168.7.0/24,10.0.1.0-9 pc_pool  Lokaler PC Pool der Universität<br>111.222.33.44             admin_hq Admin HQ</pre></p><p><strong>ACHTUNG: Ein nachträgliches Entfernen von Optionen oder Ändern von Kurznamen kann bestehende Voraussetzungen zerstören!</strong></p>';
$string['description'] = 'Erlaube den Zugriff nur von bestimmten IP-Adressen.';
$string['error:bad_lines'] = 'Zeilen nicht in gültiger Form: {$a}';
$string['error:duplicate_option_id'] = 'Der Kurzname \'{$a->id}\' in Zeile {$a->line} wird weiter oben bereits verwendet.';
$string['form:custom_ip'] = 'Selbstgewählte IP-Adressen';
$string['form:custom_ip:help'] = 'Die Eingabe muss eine vollständige IP-Adresse für ein einzelnes Gerät (z.B. <code>192.168.10.1</code>), ein IP-Adressbereich (z.B. <code>231.3.56.10-20</code>) für alle IP-Adressen im Bereich (hier 10 bis 20) oder eine IP-Adresse in der CIDR-Schreibweise (z.B. <code>231.54.211.0/20</code>) sein. Sie können mehrere festlegen, indem Sie Ihre IP-Adressen/Adressbereiche durch Kommas trennen.';
$string['form:ip_options_select'] = 'Aus einem der ausgewählten IP-Adressbereiche zugreifen:';
$string['pluginname'] = 'Voraussetzung IP';
$string['title'] = 'IP';
$string['yui_error_custom_ip_invalid'] = 'Keine valide IP-Adresse eingegeben.';
$string['yui_error_no_ip_selected'] = 'Keine IP-Adressen/Adressbereiche ausgewählt.';
