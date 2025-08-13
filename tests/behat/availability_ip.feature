@availability @availability_ip @javascript
Feature: Setting availability conditions for a course module.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
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

  Scenario: Seeing the expected OP option presets.
    # Open the IP availability restriction form.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "IP" "button" should exist in the "Add restriction..." "dialogue"
    When I click on "IP" "button"
    # Check that all option presets defined above are visible.
    Then I should see "No IP address/range selected." in the ".bg-warning" "css_element"
    And I should see "Local machine" in the "//label[@for='localhost']" "xpath_element"
    And I should see "Current IP" in the "//label[@for='me']" "xpath_element"
    And I should see "Current IP range" in the "//label[@for='me_range']" "xpath_element"
    And I should see "Current IP CIDR" in the "//label[@for='me_cidr']" "xpath_element"
    And I should see "Different IP" in the "//label[@for='not_me']" "xpath_element"

  Scenario: Entering custom IP values.
    # This is not done as a scenario outline because running one scenario per input would take way too long.
    # Since input validation is done in Javascript immediately, simply trying one input after another is much more efficient.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "IP" "button" should exist in the "Add restriction..." "dialogue"
    When I click on "IP" "button"
    Then I should see "No IP address/range selected." in the ".bg-warning" "css_element"
    When I click on "localhost" "checkbox"
    Then I should not see "No IP address/range selected."

    # These should all be invalid inputs.
    # Arbitrary string.
    When I set the custom IP field to "foo"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # Missing last octet.
    When I set the custom IP field to "127.0.0"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # Last number greater than 255.
    When I set the custom IP field to "127.0.0.256"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # Number with leading zero.
    When I set the custom IP field to "127.0.0.01"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # CIDR suffix is 0.
    When I set the custom IP field to "1.2.3.4/0"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # CIDR suffix is greater than 32.
    When I set the custom IP field to "1.2.3.4/33"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # Last octet range is empty (i.e. start greater than end).
    When I set the custom IP field to "1.2.3.40-39"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # First octet is 0.
    When I set the custom IP field to "0.1.2.3"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"
    # All zeros.
    When I set the custom IP field to "0.0.0.0"
    Then I should see "Invalid IP address entered." in the ".bg-warning" "css_element"

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

  Scenario: Student IP does not match any of the selected IP options for an activity.
    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "Not available unless: IP address allowed"
    # Set the availability condition.
    Given I am on the "P1" "page activity editing" page logged in as "teacher1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "IP" "button"
    Then I should see "No IP address/range selected." in the ".bg-warning" "css_element"
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
    Then I should see "No IP address/range selected." in the ".bg-warning" "css_element"
    When I click on "<id>" "checkbox"
    And I click on "not_me" "checkbox"
    Then I should not see "No IP address/range selected."
    And I click on "Save and return to course" "button"
    # Log in as student. Module should be available.
    Given I am on the "Course 1" "course" page logged in as "student1"
    Then I should not see "Not available unless: IP address allowed"
    When I follow "P1"
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
    Then I should see "No IP address/range selected." in the ".bg-warning" "css_element"
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
      | ip                  | should_or_should_not |
      | <behat_user>        | should not           |
      | <behat_user_range>  | should not           |
      | <behat_user_cidr>   | should not           |
      | <not_behat_user>    | should               |
