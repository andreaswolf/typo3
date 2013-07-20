<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Steffen Müller <typo3@t3node.com>
 *  (c) 2013 Andreas Wolf <andreas.wolf@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\BehatContext,
	Behat\Behat\Context\Step,
	Behat\Behat\Exception\PendingException,
	Behat\Behat\Event\ScenarioEvent;

use Behat\Gherkin\Node\PyStringNode,
	Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;

// Bootstrap a TYPO3 environment to be able to manipulate the database
require_once __DIR__ . '/TYPO3Bootstrap.php';


/**
 * Features context.
 */
class FeatureContext extends MinkContext {

	/**
	 * URL paths
	 *
	 * @var array
	 */
	public $paths = array();

	/**
	 * @var \TYPO3\CMS\Core\Tests\Utility\TestRecordUtility
	 */
	protected $helper;

	/**
	 * Initializes context.
	 * Every scenario gets it's own context object.
	 *
	 * @param array $parameters context parameters from behat.yml
	 */
	public function __construct(array $parameters) {
		if ($parameters['paths']) {
			foreach ($parameters['paths'] as $key => $path) {
				$this->paths[$key] = $path;
			}
		}
		$this->helper = new \TYPO3\CMS\Core\Tests\Utility\TestRecordUtility();
	}

	/**
	 * Fakes the browser user agent.
	 *
	 * This is needed when a scenario for the TYPO3 Backend is executed using goutte driver.
	 * The TYPO3 Backend throws a RuntimeException with the default goutte user agent.
	 *
	 * @BeforeScenario @useragent
	 */
	public function fakeUserAgent() {
		$this->getSession()->setRequestHeader('User-Agent', 'Mozilla');
	}

	/**
	 * Ensures that we see the backend login form of TYPO3.
	 *
	 * @Given /^I am on the backend login page$/
	 */
	public function ensureCurrentPageIsBackendLoginPage() {
		return new Step\Given('I am on "' . $this->paths['backendLogin'] . '"');
	}

	/**
	 * Makes sure the given user exists.
	 *
	 * @Given /^there is a user with username "([^"]*)" and password "([^"]*)"$/
	 */
	public function ensureUserExists($username, $password) {
		$this->helper->ensureBackendUserExists(
			$username,
			$password,
			TRUE
		);
	}

	/**
	 * Makes sure the given user is disabled and thus cannot use the backend.
	 *
	 * @Given /^the user "([^"]*)" is disabled$/
	 */
	public function ensureUserIsDisabled($username) {
		$this->helper->ensureRecordPresent(
			'be_users',
			array('username' => $username),
			array('disable' => 1)
		);
	}

	/**
	 * Performs a backend login with a test user.
	 *
	 * @Given /^I am logged in to the backend$/
	 */
	public function ensureBackendLogin() {
		return array(
			new Step\Given('I am on the backend login page'),
			new Step\Given('there is a user with username "foo" and password "baz"'),
			new Step\When('I fill in the following:', new Behat\Gherkin\Node\TableNode(<<<TEXT
			      |Username|foo|
			      |Password|baz|
TEXT
			)),
			new Step\When('I press "Login"'),
			new Step\Then('I should see the backend')
		);
	}

	/**
	 * Ensures that we are logged in to the TYPO3 backend.
	 *
	 * @Then /^I should see the backend$/
	 */
	public function iShouldSeeTheBackend() {
		return array(
			new Step\Then('I should be on "' . $this->paths['backend'] . '"')
		);
	}

	/**
	 * @Then /^I should be on the backend login page$/
	 */
	public function iShouldBeOnTheBackendLoginPage() {
		return array(
			new Step\Then('I am on "' . $this->paths['backendLogin'] . '"')
		);
	}



	/**
	 * Take screenshot when step fails. Works only with Selenium2Driver.
	 * Screenshot is saved at [Date]/[Feature]/[Scenario]/[Step].jpg
	 *
	 * @TODO Add a check if WebDriver Session fully works.
	 *
	 * @AfterStep @javascript
	 */
	public function takeScreenshotAfterFailedStep(Behat\Behat\Event\StepEvent $event) {
		if ($event->getResult() === Behat\Behat\Event\StepEvent::FAILED) {
			$driver = $this->getSession()->getDriver();
			if ($driver instanceof Behat\Mink\Driver\Selenium2Driver) {
				$step = $event->getStep();
				$path = array(
					'date' => date("Ymd-Hi"),
					'feature' => $step->getParent()->getFeature()->getTitle(),
					'scenario' => $step->getParent()->getTitle(),
					'step' => $step->getType() . ' ' . $step->getText()
				);
				$path = preg_replace('/[^\-\.\w]/', '_', $path);
				$filename = '/tmp/behat-screenshots/' .  implode('/', $path) . '.jpg';

				// Create directories if needed
				if (!@is_dir(dirname($filename))) {
					@mkdir(dirname($filename), 0775, TRUE);
				}

				file_put_contents($filename, $driver->getScreenshot());
				#echo sprintf('[NOTICE] A screenshot was saved to %s %s', $filename, PHP_EOL);
			}
		}
	}
}
