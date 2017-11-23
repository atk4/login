Feature: Login basic
    In order prevent public accessing admin system
    As a system admin
    I need to authenticate to access the interface

Scenario:
 Given I am on "login.php"
 Then I see button "Login" 
 And I dont see "You are authenticated"

Scenario:
 Given I am on "login.php"
 When I type "demo" in field "login"
 And I type "demo" in field "password"
 And I click button "Login"
 Then I see text "You are authenticated" 

Scenario:
 Given I am on "login.php"
 When I type "demo" in field "login"
 And I type "wrong" in field "password"
 And I click button "Login"
 Then I dont see text "You are authenticated" 
 And I see text "incorrect"

Scenario:
 Given I am on "login.php"
 When I type "" in field "login"
 And I type "demo" in field "password"
 And I click button "Login"
 Then I dont see text "You are authenticated" 
 And I see text "incorrect"

