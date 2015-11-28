<?php
namespace TYPO3\CMS\Core\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use HeikoHardt\Behat\TYPO3Extension\Typo3;
use TYPO3\CMS\Core\Tests\Exception;


/**
 *
 */
class Typo3SetupContext extends Typo3 implements Context
{

    /**
     * Sets the stage for a test by importing stuff and adjusting the TYPO3 installation.
     *
     * TODO move the actual code here to a helper and create a second class that is run only once per Suite, so we
     * can either have a setup run once per suite or for each single scenario.
     * TODO check if it would make sense to run this per feature.
     *
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope)
    {
        $suite = $scope->getSuite();

        $parameters = $suite->getSettings()['typo3Setup'];

        if (!$parameters) {
            throw new Exception('No parameters found for setting up Behat. Please specify key "typo3Setup" with your suite.');
        }

        try {
            $coreExtensions = $parameters['coreExtensionsToLoad'];
            if (!is_array($coreExtensions)) {
                throw new Exception('Please specify key "typo3Setup.coreExtensionsToLoad" with your suite.');
            }
            // setup core extensions
            $this->setTYPO3CoreExtensionsToLoad($coreExtensions);

            // setup test extensions
            $testExtensions = $parameters['testExtensionsToLoad'];
            if (is_array($testExtensions)) {
                $this->setTYPO3TestExtensionsToLoad($testExtensions);
            }

            // extend default local configuration
            $this->setTYPO3LocalConfiguration(array('SYS' => array('encryptionKey' => 'mysecretencryptionkey')));

            // import initial db values
            $this->setTYPO3DatasetToImport($parameters['databaseFixtures']);

            // setup basic frontend page
            $this->setTYPO3FrontendRootPage(
                $parameters['rootPage']['uid'],
                (array)$parameters['rootPage']['typoScriptConstants'],
                (array)$parameters['rootPage']['typoScriptSetup']
            );

            // boot typo3
            $this->TYPO3Boot($this, $scope);

        } catch (\Exception $e) {
            // TODO sane error reporting?!?
            throw new ContextException('Kaboom' . $e->getMessage(), 0, $e);
        }
    }

}
