Feature: Register basic
  check register
  check unique register

  Scenario:
    Given I am on "form-register.php"
    And I fill in "name" with "admin"
    And I fill in "email" with "admin@agiletoolkit.org"
    And I fill in "password" with "adminPassword"
    And I fill in "password2" with "adminPassword"
    And I press button "Register"
    Then I should see "Account has been created"

  Scenario:
    Given I am on "form-register.php"
    And I fill in "name" with "admin"
    And I fill in "email" with "admin@agiletoolkit.org"
    And I fill in "password" with "adminPassword"
    And I fill in "password2" with "adminPassword"
    And I press button "Register"
    Then I should see "User with this email already exist"
