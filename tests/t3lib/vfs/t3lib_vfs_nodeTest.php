<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011 Andreas Wolf <andreas.wolf@ikt-werk.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


require_once 'vfsStream/vfsStream.php';

/**
 * Testcase for the abstract basic node class of
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_nodeTest extends tx_phpunit_testcase {

	/**
	 * @var t3lib_vfs_Node
	 */
	private $fixture;

	private $fixtureConstructorData;

	public function setUp() {
		$this->fixtureConstructorData = array(
		array(
			'propA' => uniqid(),
			'propB' => uniqid(),
			'name' => uniqid()
		)
	);
		$this->fixture = $this->getMockForAbstractClass('t3lib_vfs_Node', $this->fixtureConstructorData);
	}

	/**
	 * @test
	 */
	public function constructorSavesProperties() {
		$this->assertEquals($this->fixtureConstructorData[0]['propA'], $this->fixture->getValue('propA'));
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Node::isMountpoint
	 * @covers t3lib_vfs_Node::isRootNode
	 */
	public function nodeIsNeitherMountpointNorRootNode() {
		$this->assertFalse($this->fixture->isMountpoint());
		$this->assertFalse($this->fixture->isRootNode());
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Node::getName
	 */
	public function nodeNameIsSetFromConstructorArguments() {
		$this->assertEquals($this->fixtureConstructorData[0]['name'], $this->fixture->getName());
	}

	/**
	 * @test
	 */
	public function parentMayBeSetAndRetrieved() {
		$mockedParent = $this->getMockForAbstractClass('t3lib_vfs_Node', array(), '', FALSE);

		$this->fixture->setParent($mockedParent);
		$this->assertSame($mockedParent, $this->fixture->getParent());
	}

	/**
	 * @test
	 */
	public function setValueChangesPropertyValues() {
		$newValue = uniqid();

		$this->fixture->setValue('propA', $newValue);

		$this->assertEquals($newValue, $this->fixture->getValue('propA'));
	}

	/**
	 * @test
	 */
	public function setValueFailsIfPropertyDoesntExist() {
		$this->setExpectedException('InvalidArgumentException', 1300127094);

		$this->fixture->setValue(uniqid(), uniqid());
	}

	/**
	 * @test
	 */
	public function setValueRegistersPropertyAsChanged() {
		$this->fixture->setValue('propA', uniqid());

		$changedProperties = $this->fixture->getChangedProperties();

		$this->assertArrayHasKey('propA', $changedProperties);
	}

	/**
	 * @test
	 */
	public function setValueKeepsOldValueInChangedPropertiesArray() {
		$oldValue = $this->fixture->getValue('propA');
		$this->fixture->setValue('propA', uniqid());

		$changedProperties = $this->fixture->getChangedProperties();
		$this->assertEquals($oldValue, $changedProperties['propA']);
	}

	/**
	 * @test
	 */
	public function setValueDoesntOverwriteOldValueIfItHasBeenChangedBefore() {
		$oldValue = $this->fixture->getValue('propA');
		$this->fixture->setValue('propA', uniqid());
		$this->fixture->setValue('propA', uniqid());

		$changedProperties = $this->fixture->getChangedProperties();
		$this->assertEquals($oldValue, $changedProperties['propA']);
	}

	/**
	 * @test
	 */
	public function changedPropertiesMayBeReset() {
		$this->fixture->setValue('propA', uniqid());

		$this->assertNotEmpty($this->fixture->getChangedProperties());

		$this->fixture->resetChangedProperties();

		$this->assertEmpty($this->fixture->getChangedProperties());
	}

	/**
	 * @test
	 */
	public function getChangedPropertiesReturnsAllChangedProperties() {
		$this->fixture->setValue('propA', uniqid());
		$this->fixture->setValue('propB', uniqid());

		$changedProperties = $this->fixture->getChangedProperties();

		$this->assertArrayHasKey('propA', $changedProperties);
		$this->assertArrayHasKey('propB', $changedProperties);
	}

	/**
	 * @test
	 */
	public function getChangedPropertyNamesReturnsNames() {
		$this->fixture->setValue('propA', uniqid());
		$this->fixture->setValue('propB', uniqid());

		$changedPropertyNames = $this->fixture->getChangedPropertyNames();

		$this->assertContains('propA', $changedPropertyNames);
		$this->assertContains('propB', $changedPropertyNames);
	}

	/**
	 * @test
	 */
	public function getPropertiesReturnsAllProperties() {
		$this->assertEquals($this->fixtureConstructorData[0], $this->fixture->getProperties());
	}

	/**
	 * @test
	 */
	public function setMountpointSetsMountpoint() {
		$mockedMount = $this->getMock('t3lib_vfs_Mount', array(), array(), '', FALSE);

		$this->fixture->setMountpoint($mockedMount);

		$this->assertEquals($mockedMount, $this->fixture->getMountpoint());
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Node::getPathInMountpoint
	 */
	public function getPathInMountpointReturnsCorrectPath() {
			// please note: the name of the mountpoint is not included in the path used here
		$pathParts = array(
			uniqid(), // Mount
			uniqid(), // Folder 1
			uniqid(), // Folder 2
			uniqid(), // File
		);

		$mockedMount = $this->getMock('t3lib_vfs_Mount', array('getName', 'isMountpoint'), array(), '', FALSE);
		$mockedMount->expects($this->any())->method('getName')->will($this->returnValue($pathParts[0]));
		$mockedMount->expects($this->any())->method('isMountpoint')->will($this->returnValue(TRUE));
		$mockedSubFolder = $this->getMock('t3lib_vfs_Node', array('getName', 'getParent'), array(), '', FALSE);
		$mockedSubFolder->expects($this->any())->method('getName')->will($this->returnValue($pathParts[1]));
		$mockedSubSubFolder = $this->getMock('t3lib_vfs_Node', array('getName', 'getParent'), array(), '', FALSE);
		$mockedSubSubFolder->expects($this->any())->method('getName')->will($this->returnValue($pathParts[2]));
		$mockedNode = $this->getMock('t3lib_vfs_Node', array('getName', 'getParent'), array(), '', FALSE);
		$mockedNode->expects($this->any())->method('getName')->will($this->returnValue($pathParts[3]));

		$mockedNode->expects($this->any())->method('getParent')->will($this->returnValue($mockedSubSubFolder));
		$mockedSubSubFolder->expects($this->any())->method('getParent')->will($this->returnValue($mockedSubFolder));
		$mockedSubFolder->expects($this->any())->method('getParent')->will($this->returnValue($mockedMount));

		$this->assertEquals(implode('/', array_slice($pathParts, 1)), $mockedNode->getPathInMountpoint(TRUE), 'Incorrect path with current node returned by getPathInMountpoint');
		$this->assertEquals(implode('/', array_slice($pathParts, 1, 2)), $mockedNode->getPathInMountpoint(FALSE), 'Incorrect path without current node returned by getPathInMountpoint');
	}
}

?>