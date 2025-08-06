@availability @availability_ip @javascript
Feature: Setting availability conditions for a course module.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | summary | enablecompletion |
      | Course 1 | C1        | topics | Foo bar | 1                |
    And the following "users" exist:
      | username | email                |
      | teacher1 | teacher1@example.com |
      | student1 | student1@example.com |
      | student2 | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name  |
      | page     | C1     | P1    |
    # See `behat_availability_ip` class for the meaning of the special IP values.
    And the following IP option presets exist:
      | ips                 | id        | name             |
      | 127.0.0.1           | localhost | Local machine    |
      | <behat_user>        | me        | Current IP       |
      | <behat_user_range>  | me_range  | Current IP range |
      | <behat_user_cidr>   | me_cidr   | Current IP CIDR  |
      | <not_behat_user>    | not_me    | Different IP     |

  Scenario: Seeing the expected IP option presets.
    # Open the IP availability restriction form.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "IP" "button" should exist in the "Add restriction..." "dialogue"
    When I click on "IP" "button"
    # Check that all option presets defined above are visible.
    Then I should see a warning badge with "No IP address/range selected."
    And I should see a checkbox labeled "Local machine"
    And I should see a checkbox labeled "Current IP"
    And I should see a checkbox labeled "Current IP range"
    And I should see a checkbox labeled "Current IP CIDR"
    And I should see a checkbox labeled "Different IP"

  Scenario: Entering custom IP values.
    # This is not done as a scenario outline because running one scenario per input would take way too long.
    # Since input validation is done in Javascript immediately, simply trying one input after another is much more efficient.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "IP" "button" should exist in the "Add restriction..." "dialogue"
    When I click on "IP" "button"
    Then I should see a warning badge with "No IP address/range selected."
    When I click on "localhost" "checkbox"
    Then I should not see "No IP address/range selected."

    # These should all be invalid inputs.
    # Arbitrary string.
    When I set the custom IP field to "foo"
    Then I should see a warning badge with "Invalid IP address entered."
    # Missing last octet.
    When I set the custom IP field to "127.0.0"
    Then I should see a warning badge with "Invalid IP address entered."
    # Last number greater than 255.
    When I set the custom IP field to "127.0.0.256"
    Then I should see a warning badge with "Invalid IP address entered."
    # Number with leading zero.
    When I set the custom IP field to "127.0.0.01"
    Then I should see a warning badge with "Invalid IP address entered."
    # CIDR suffix is 0.
    When I set the custom IP field to "1.2.3.4/0"
    Then I should see a warning badge with "Invalid IP address entered."
    # CIDR suffix is greater than 32.
    When I set the custom IP field to "1.2.3.4/33"
    Then I should see a warning badge with "Invalid IP address entered."
    # Last octet range is empty (i.e. start greater than end).
    When I set the custom IP field to "1.2.3.40-39"
    Then I should see a warning badge with "Invalid IP address entered."
    # First octet is 0.
    When I set the custom IP field to "0.1.2.3"
    Then I should see a warning badge with "Invalid IP address entered."
    # All zeros.
    When I set the custom IP field to "0.0.0.0"
    Then I should see a warning badge with "Invalid IP address entered."
    # One of the entered IPs is invalid.
    When I set the custom IP field to "127.0.0.1, 127.0.0.256, 127.0.0.2"
    Then I should see a warning badge with "Invalid IP address entered."

    # These should all be valid inputs.
    When I set the custom IP field to "1.0.0.0"
    Then I should not see "Invalid IP address entered."
    When I set the custom IP field to "255.255.255.255"
    Then I should not see "Invalid IP address entered."
    When I set the custom IP field to "192.168.0.100-200"
    Then I should not see "Invalid IP address entered."
    When I set the custom IP field to "192.168.0.100-100"
    Then I should not see "Invalid IP address entered."
    When I set the custom IP field to "10.0.0.1/32"
    Then I should not see "Invalid IP address entered."
    When I set the custom IP field to "3.2.1.0/24"
    Then I should not see "Invalid IP address entered."
    When I set the custom IP field to "1.0.0.0, 192.168.0.100-200, 3.2.1.0/24"
    Then I should not see "Invalid IP address entered."

  Scenario: Student IP does not match any of the selected IP options for an activity.
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "Not available unless: IP address allowed"
    # Set the availability condition.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "IP" "button"
    Then I should see a warning badge with "No IP address/range selected."
    When I click on "not_me" "checkbox"
    Then I should not see "No IP address/range selected."
    And I click on "Save and return to course" "button"
    # Log in as student. Module should be unavailable.
    Given I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "Not available unless: IP address allowed"

  Scenario Outline: Student IP matches one of the selected IP options for an activity.
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "Not available unless: IP address allowed"
    # Set the availability condition.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "IP" "button"
    Then I should see a warning badge with "No IP address/range selected."
    When I click on "<id>" "checkbox"
    And I click on "not_me" "checkbox"
    Then I should not see "No IP address/range selected."
    And I click on "Save and return to course" "button"
    # Log in as student. Module should be available.
    Given I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "Not available unless: IP address allowed"
    When I am on the "P1" "activity" page
    Then I should see "Test page content"

    Examples:
      | id       |
      | me       |
      | me_range |
      | me_cidr  |

  Scenario Outline: Student IP matches custom IP condition set on an activity.
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "Not available unless: IP address allowed"
    # Set the availability condition.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "IP" "button"
    Then I should see a warning badge with "No IP address/range selected."
    When I click on "not_me" "checkbox"
    And I set the custom IP field to "<ip>"
    Then I should not see "No IP address/range selected."
    And I should not see "Invalid IP address entered."
    Then I click on "Save and return to course" "button"
    # Log in as student. Module should be available.
    Given I am on the "Course 1" "course" page logged in as "student1"
    Then I <should_or_should_not> see "Not available unless: IP address allowed"

    # See `behat_availability_ip` class for the meaning of the special IP values.
    Examples:
      | ip                             | should_or_should_not |
      | <behat_user>                   | should not           |
      | <behat_user_range>             | should not           |
      | <behat_user_cidr>              | should not           |
      | <not_behat_user>               | should               |
      | <behat_user>, <not_behat_user> | should not           |

  Scenario: IP is combined with another availability condition.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    # First add an IP restriction.
    And I click on "Add restriction..." "button"
    And I click on "IP" "button"
    Then I should see a warning badge with "No IP address/range selected."
    When I click on "me" "checkbox"
    Then I should not see "No IP address/range selected."
    # Next add an Email address restriction.
    And I click on "Add restriction..." "button"
    # Fun fact: Because the previous steps leave an invisible zombie restriction dialogue in the DOM,
    # the following step will fail, without the ` in the "Add restriction..." "dialogue"` at the end.
    And I click on "User profile" "button" in the "Add restriction..." "dialogue"
    And I set the field "User profile field" to "Email address"
    And I set the field "Value to compare against" to "student2@example.com"
    And I click on "Save and return to course" "button"
    # Log in as student1. Module should be unavailable.
    Given I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "Not available"
    # Log in as student2. Now it should be available.
    Given I am on the "Course 1" "course" page logged in as "student2"
    Then I should not see "Not available"
    When I am on the "P1" "activity" page
    Then I should see "Test page content"
