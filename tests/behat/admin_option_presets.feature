@availability @availability_ip @javascript
Feature: Configuring IP option presets.

  Scenario: Setting two valid IP options.

    Given I am logged in as "admin"
    And I navigate to "Plugins > Restriction by IP" in site administration
    Then I should see "availability_ip | ip_option_presets"
    And the field "Preconfigured IP options" matches value ""

    When I set the field "Preconfigured IP options" to multiline:
      """
      127.0.0.1      localhost       Local machine

      172.18.0.0/16  docker_network  Moodle Docker network
      """
    And I click on "Save changes" "button"
    Then I should get a success alert with "Changes saved"
    And the field "Preconfigured IP options" matches multiline:
      """
      127.0.0.1      localhost       Local machine

      172.18.0.0/16  docker_network  Moodle Docker network
      """

  Scenario: Using invalid option syntax.

    Given I am logged in as "admin"
    And I navigate to "Plugins > Restriction by IP" in site administration
    Then I should see "availability_ip | ip_option_presets"
    And the field "Preconfigured IP options" matches value ""

    When I set the field "Preconfigured IP options" to multiline:
      """
      foo 127.0.0.1 bar
      spam eggs
      """
    And I click on "Save changes" "button"
    Then I should get a danger alert with "Some settings were not changed due to an error."
    And I should see "Lines not in a valid format: \"foo 127.0.0.1 bar\", \"spam eggs\""
    When I navigate to "Plugins > Restriction by IP" in site administration
    Then the field "Preconfigured IP options" matches value ""

  Scenario: Not using valid IPs.

    Given I am logged in as "admin"
    And I navigate to "Plugins > Restriction by IP" in site administration
    Then I should see "availability_ip | ip_option_presets"
    And the field "Preconfigured IP options" matches value ""

    When I set the field "Preconfigured IP options" to multiline:
      """
      1.2.3. foo bar
      1.2.3 abc def
      1.2.3.400 spam eggs
      255.255.255.254-255 correct This is fine
      """
    And I click on "Save changes" "button"
    Then I should get a danger alert with "Some settings were not changed due to an error."
    And I should see "Lines not in a valid format: \"1.2.3. foo bar\", \"1.2.3 abc def\", \"1.2.3.400 spam eggs\""
    When I navigate to "Plugins > Restriction by IP" in site administration
    Then the field "Preconfigured IP options" matches value ""

  Scenario: Repeating the same ID in multiple options.

    Given I am logged in as "admin"
    And I navigate to "Plugins > Restriction by IP" in site administration
    Then I should see "availability_ip | ip_option_presets"
    And the field "Preconfigured IP options" matches value ""

    When I set the field "Preconfigured IP options" to multiline:
      """
      127.0.0.1      foo  Local machine
      172.18.0.0/16  foo  Moodle Docker network
      1.2.3.4        bar  Baz
      """
    And I click on "Save changes" "button"
    Then I should get a danger alert with "Some settings were not changed due to an error."
    And I should see "The shortname 'foo' in line 2 was already used above."
    When I navigate to "Plugins > Restriction by IP" in site administration
    Then the field "Preconfigured IP options" matches value ""
