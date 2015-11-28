Feature: Login/Logout

	@javascript
	Scenario: Login
		Given I am on the homepage
		When I fill in "user" with "john.doe"
		And I fill in "pass" with "foo"
		And I press "Login"
		Then I wait for "logged in" to appear

	@javascript
	Scenario: Failing login with wrong password
		Given I am on the homepage
		When I fill in "user" with "john.doe"
		And I fill in "pass" with "bar"
		And I press "Login"
		Then I wait for "Login failure" to appear

	@javascript
	Scenario: Failing login for nonexisting user
		Given I am on the homepage
		When I fill in "user" with "jane.doe"
		And I fill in "pass" with "foo"
		And I press "Login"
		Then I wait for "Login failure" to appear
