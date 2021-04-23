Feature: Login basic
    In order prevent public accessing admin system
    As a system admin
    I need to authenticate to access the interface

Scenario:
 Given I am on "form-login.php"
 Then I see button "Sign in" 
 And I should not see "Currently logged in"

Scenario:
 Given I am on "form-login.php"
 And I fill in "email" with "admin"
 And I fill in "password" with "admin"
 And I press button "Sign in"
 Then I should see "Currently logged in"

Scenario:
 Given I am on "form-login.php"
 When I fill in "email" with "admin"
 And I fill in "password" with "wrong"
 And I press button "Sign in"
 Then I should see "incorrect"
 And I should not see "Currently logged in"

Scenario:
 Given I am on "form-login.php"
 When I fill in "email" with ""
 And I fill in "password" with "admin"
 And I press button "Sign in"
 Then I should see "Must not be empty"
 And I should not see "Currently logged in"
