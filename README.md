# IP availability condition - `moodle-availability_ip`

Availability plugin for [Moodle][moodle home] making it possible to [restrict access][moodle docs restrict access] to activities and sections by IP address.

## Installation

The minimum supported Moodle version is [**5.0**][moodle docs release 5.0] (build 2025041400).
There are no additional dependencies.

You install `availability_ip` just like any other Moodle plugin.
Starting with Moodle [**5.1**][moodle docs release 5.1], it belongs in the `public/availability/condition/ip` directory.
(For Moodle 5.0 it goes into `availability/condition/ip`.)

For example, using `git` from the root directory of your Moodle 5.1+ installation:

```shell
git clone \
    https://github.com/innocampus/moodle-availability_ip.git \
    public/availability/condition/ip
```

For other options and general plugin installation instructions, see the [official Moodle documentation][moodle docs plugin install].

## Usage

### Admin settings

The condition type is [enabled like any other][moodle docs restrict access settings] kind of access restriction in Moodle under _Site administration_ > _Plugins_ > _Availability restrictions_ > _Manage restrictions_.

All `availability_ip` specific settings are available at `admin/settings.php?section=availabilitysettingip` or by navigating to _Site administration_ > _Plugins_ > _Availability restrictions_ > _Restriction by IP_.

Admins can predefine certain IP addresses or ranges that teachers can choose from when setting up availability conditions for activities and sections.
This is done through the `availability_ip | ip_option_presets` setting.
Each line in the text field represents one IP address/range option.

**TODO: Screenshot**

Entries must be in the format `IPs unique_shortname Displayname`, where `IPs` is either a full IP address (such as `192.168.10.1`) which matches a single host; or CIDR notation (such as `231.54.211.0/20`); or a range of IP addresses (such as `231.3.56.10-20`) where the range applies to the last part of the address.
Multiple IPs can be set by separating them with commas.
`unique_shortname` may only consist of lower-case letters (`a-z`) and underscores (`_`).

### Activity/section settings

Teachers and other users with the necessary permissions will find the condition type under the name _IP_.
[As always in Moodle][moodle docs restrict access activity], it can be added via the _Add restriction_ button in the _Restrict access_ section of the activity/section settings.

In addition to relying on the IP address/range options that admins pre-defined (see above), teachers can also enter custom IP addresses/ranges by selecting the _Custom IP addresses_ checkbox.

**TODO: Screenshot**

If a student tries to view an activity/section restricted by this condition, access will be granted only if the associated client's IP address/range matches **at least one** of the options that were selected.

Of course, the _IP_ condition can also be inverted and/or combined with more conditions as always.

## Copyright

© 2025 Daniel Fainberg, TU Berlin

`availability_ip` for Moodle is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

`availability_ip` for Moodle is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with `availability_ip` for Moodle. If not, see <https://www.gnu.org/licenses/>.

---

**Code, tests, and documentation written by and for humans.** 🚫🤖

[moodle docs plugin install]: https://docs.moodle.org/en/Installing_plugins#Installing_a_plugin
[moodle docs release 5.0]: https://moodledev.io/general/releases/5.0
[moodle docs release 5.1]: https://moodledev.io/general/releases/5.1
[moodle docs restrict access]: https://docs.moodle.org/en/Restrict_access
[moodle docs restrict access activity]: https://docs.moodle.org/en/Restrict_access_settings#Restricting_activity_access
[moodle docs restrict access settings]: https://docs.moodle.org/en/Restrict_access_settings
[moodle home]: https://moodle.com
