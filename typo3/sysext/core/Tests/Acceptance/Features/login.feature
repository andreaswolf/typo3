# features/login.feature
Feature: Login
  In order to work in the TYPO3 backend
  As an editor
  I need to be able to login

  mink:goutte
  @useragent
  Scenario: Backend login shows warning if JavaScript is disabled
    Given I am on the backend login page
    Then I should see "Activate JavaScript"


  @javascript
  Scenario: Backend login succeeds with a valid user account
   Given there is a user with username "john" and password "password"
     And I am on the backend login page
   When I fill in the following:
     |Username|john|
     |Password|password|
   And I press "Login"
   Then I should see the backend


  @javascript
  Scenario: Backend login fails for disabled user
   Given there is a user with username "jack" and password "password"
     And the user "jack" is disabled
     And I am on the backend login page
   When I fill in the following:
     |Username|jack|
     |Password|password|
   And I press "Login"
   Then I should see "Your login attempt did not succeed"


  @javascript
  Scenario: Backend login fails with an invalid password
    Given I am on the backend login page
    When I fill in the following:
      |Username|john|
      |Password|INVALID|
    And I press "Login"
    Then I should see "Your login attempt did not succeed"



  @javascript
  Scenario: Backend login fails with an invalid user account
    Given I am on the backend login page
    When I fill in the following:
      |Username|INVALID|
      |Password|foo|
    And I press "Login"
    Then I should see "Your login attempt did not succeed"
