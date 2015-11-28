Feature: Redirect after login

	@javascript
	Scenario: Login and redirect to another page specified in user record
		Given I am on "/?id=3"
		When I fill in "user" with "john.doe.userredirect"
		And I fill in "pass" with "foo"
		And I press "Login"
		Then I wait for "User redirect target" to appear

	@javascript
	Scenario: Login and redirect to another page specified in group record
		Given I am on "/?id=3"
		When I fill in "user" with "john.doe.groupredirect"
		And I fill in "pass" with "foo"
		And I press "Login"
		Then I wait for "Group redirect target" to appear
