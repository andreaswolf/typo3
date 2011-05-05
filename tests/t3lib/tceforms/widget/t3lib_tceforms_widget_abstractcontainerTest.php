<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Andreas Wolf <andreas.wolf@ikt-werk.de>
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


require_once 'vfsStream/vfsStream.php';

/**
 * Testcase for the abstract container widget
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_TCEforms_Widget_AbstractContainerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var t3lib_TCEforms_Widget_AbstractContainer
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = $this->getMockForAbstractClass('t3lib_TCEforms_Widget_AbstractContainer', array(), '', FALSE);
	}

	/**
	 * @test
	 */
	public function childWidgetsMayBeAddedAndRetrieved() {
		$mockedWidget = $this->getMock('t3lib_TCEforms_Widget');
		$mockedWidget2 = $this->getMock('t3lib_TCEforms_Widget');

		$this->fixture->addChildWidget($mockedWidget);
		$this->fixture->addChildWidget($mockedWidget2);

		$this->assertContains($mockedWidget, $this->fixture->getChildWidgets());
		$this->assertContains($mockedWidget2, $this->fixture->getChildWidgets());
	}

	/**
	 * @test
	 */
	public function addChildWidgetsAddsAllGivenWidgets() {
		$mockedWidgets = array(
			$this->getMock('t3lib_TCEforms_Widget'),
			$this->getMock('t3lib_TCEforms_Widget')
		);

		$this->fixture->addChildWidgets($mockedWidgets);

		$this->assertContains($mockedWidgets[0], $this->fixture->getChildWidgets());
		$this->assertContains($mockedWidgets[1], $this->fixture->getChildWidgets());
	}

	/**
	 * @test
	 * @covers t3lib_TCEforms_Widget_AbstractContainer::getChildWidgetCount
	 */
	public function getChildWidgetCountReturnsCorrectValues() {
		$this->assertEquals(0, $this->fixture->getChildWidgetCount());
		$this->fixture->addChildWidget($this->getMock('t3lib_TCEforms_Widget'));
		$this->assertEquals(1, $this->fixture->getChildWidgetCount());
		$this->fixture->addChildWidget($this->getMock('t3lib_TCEforms_Widget'));
		$this->assertEquals(2, $this->fixture->getChildWidgetCount());
	}

	/**
	 * @test
	 * @covers t3lib_TCEforms_Widget_AbstractContainer::hasChildWidgets
	 */
	public function hasChildWidgetsReturnsFalseIfNoChildWidgetsAreSet() {
		$this->assertFalse($this->fixture->hasChildWidgets());
	}

	/**
	 * @test
	 * @covers t3lib_TCEforms_Widget_AbstractContainer::hasChildWidgets
	 */
	public function hasChildWidgetsReturnsTrueIfChildWidgetsAreSet() {
		$this->fixture->addChildWidget($this->getMock('t3lib_TCEforms_Widget'));
		$this->assertTrue($this->fixture->hasChildWidgets());
	}

	/**
	 * @test
	 */
	public function abstractContainerCallsSetParentMethodForNewChildWidgets() {
		$childWidget = $this->getMock('t3lib_TCEforms_Widget');
		$childWidget->expects($this->once())->method('setParentWidget')->with($this->equalTo($this->fixture));

		$this->fixture->addChildWidget($childWidget);
	}

	/**
	 * @test
	 */
	public function childWidgetsAreAlsoCloned() {
		$childWidget = $this->getMock('t3lib_TCEforms_Widget');
		$childWidget->expects($this->once())->method('setParentWidget')->with($this->equalTo($this->fixture));

		$this->fixture->addChildWidget($childWidget);
		$fixtureChildWidgets = $this->fixture->getChildWidgets();

		$clonedFixture = clone $this->fixture;
		$clonedFixtureChildWidgets = $clonedFixture->getChildWidgets();

		$this->assertSame($childWidget, $fixtureChildWidgets[0]);
		$this->assertNotSame($fixtureChildWidgets[0], $clonedFixtureChildWidgets[0]);
	}

	/**
	 * @test
	 */
	public function replaceChildWidgetRemovesOldWidget() {
		$childWidget = $this->getMock('t3lib_TCEforms_Widget');
		$this->fixture->addChildWidget($childWidget);

		$newChildWidget = $this->getMock('t3lib_TCEforms_Widget');
		$this->fixture->replaceChildWidget($childWidget, $newChildWidget);

		$childWidgets = $this->fixture->getChildWidgets();
		$this->assertNotContains($childWidget, $childWidgets);
	}

	/**
	 * @test
	 */
	public function replaceChildWidgetInsertsOldWidgetAtSamePosition() {
		$mockedChildWidgets = array(
			$this->getMock('t3lib_TCEforms_Widget', array(), array(), uniqid('t3lib_TCEforms_MockedWidgetOne')),
			$this->getMock('t3lib_TCEforms_Widget', array(), array(), uniqid('t3lib_TCEforms_MockedWidgetTwo')),
			$this->getMock('t3lib_TCEforms_Widget', array(), array(), uniqid('t3lib_TCEforms_MockedWidgetThree'))
		);
		$this->fixture->addChildWidgets($mockedChildWidgets);

		$newChildWidget = $this->getMock('t3lib_TCEforms_Widget', array(), array(), uniqid('t3lib_TCEforms_MockedWidgetFour'));
		$this->fixture->replaceChildWidget($mockedChildWidgets[1], $newChildWidget);

		$childWidgets = $this->fixture->getChildWidgets();
		$this->assertEquals(array($mockedChildWidgets[0], $newChildWidget, $mockedChildWidgets[2]), $childWidgets);
	}

	protected function setupStreamFile($basedir, $filename, $contents) {
		vfsStream::setup($basedir);
		$templateFile = vfsStream::newFile($filename)->setContent($contents);
		vfsStreamWrapper::getRoot()->addChild($templateFile);
	}

	/**
	 * @test
	 */
	public function renderContainerReturnsContentsFromTemplateFile() {
		$baseDir = 'basedir';
		$templateFileName = 'template.example';
		$templateContent = 'exampleTemplateContent ' . uniqid();
		$this->setupStreamFile($baseDir, $templateFileName, $templateContent);
		$templateFilePath = vfsStream::url("$baseDir/$templateFileName");
		$mockedRenderer = $this->getMock('t3lib_TCEforms_Renderer');

		$renderedContents = $this->fixture->renderContainer($mockedRenderer, $templateFilePath, '');

		$this->assertContains($templateContent, (string)$renderedContents);
	}

	/**
	 * @test
	 */
	public function phpContentFromTemplatesGetsExecuted() {
		$baseDir = 'basedir';
		$templateFileName = 'template.example';
		$templateTextContent = 'exampleTemplateContent ' . uniqid();
		$templateCode = '<?php echo "' . $templateTextContent . '"; ?>';
		$this->setupStreamFile($baseDir, $templateFileName, $templateCode);
		$templateFilePath = vfsStream::url("$baseDir/$templateFileName");
		$mockedRenderer = $this->getMock('t3lib_TCEforms_Renderer');

		$renderedContents = $this->fixture->renderContainer($mockedRenderer, $templateFilePath, '');

		$this->assertEquals($templateTextContent, (string)$renderedContents);
	}
}

?>