<?php
namespace TYPO3\CMS\Core\Tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\MinkExtension\Context\MinkContext;


class FeatureContext extends MinkContext implements Context
{

    /**
     * Periodically checks if the given text is visible on the page; the check is repeated until either
     * the text appears or the maximum waiting time (5 seconds currently) has passed.
     *
     * @When /^(?:|I )wait for "(.*)" to appear$/
     */
    public function waitFor($text)
    {
        for ($i = 0; $i < 100; ++$i) {
            // TODO make sure we always have the current page text here
            $pageText = $this->getSession()->getPage()->getContent();
            $text = $this->fixStepArgument($text);

            if (strpos($pageText, $text) !== FALSE) {
                return;
            }

            // wait 50ms
            usleep(50000);
        }
        throw new ResponseTextException('Text "' . $text . '" did not appear on page in time.', $this->getSession()->getDriver());
    }

    /**
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $scope)
    {
        if (99 === $scope->getTestResult()->getResultCode()) {
            $this->takeScreenshot();
        }
    }

    private function takeScreenshot()
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof Selenium2Driver) {
            return;
        }
        $baseUrl = $this->getMinkParameter('base_url');
        $fileName = date('y-m-d') . '-' . uniqid() . '.png';
        $filePath = PATH_site . '/typo3temp/';

        $this->saveScreenshot($fileName, $filePath);
        print 'Screenshot at: ' . $baseUrl . 'tmp/' . $fileName;
    }

}
