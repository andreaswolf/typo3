# features/logout.feature
Feature: Logout
	In order to prevent others from using my account when they use my computer
	As an editor
	I need to be able to logout

	@javascript
	Scenario: Backend logout closes the backend
		Given I am logged in to the backend
		When I press "logout-submit-button"
		Then I should be on the backend login page