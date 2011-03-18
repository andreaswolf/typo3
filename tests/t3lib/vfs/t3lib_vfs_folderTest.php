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
 * Testcase for the folder abstraction class
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_folderTest extends tx_phpunit_testcase {
	/**
	 * @var t3lib_vfs_Folder
	 */
	private $fixture;

	private $fixtureData = array();

	public function setUp() {
		$this->fixtureData = array(
			'name' => uniqid(),
			'uid' => uniqid(),
			'driver' => uniqid()
		);
		$this->fixture = new t3lib_vfs_Folder($this->fixtureData);
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Folder::getValue
	 */
	public function getValueReturnsDataInjectedViaConstructor() {
		foreach ($this->fixtureData as $key => $value) {
			$this->assertEquals($value, $this->fixture->getValue($key));
		}
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Folder::isMountpoint
	 */
	public function isMountpointReturnsFalse() {
		$this->assertFalse($this->fixture->isMountpoint());
	}

	/**
	 * @test
	 */
	public function createSubfolderCallsDriverWithCorrectArguments() {
		$this->markTestSkipped('This test requires functionality in PHPUnit which is currently not available (mocking concrete methods in abstract classes); a patch for this is pending, see https://github.com/sebastianbergmann/phpunit-mock-objects/issues#issue/49');
		$basePath = 'someFolder/someSubfolder';
		$folderName = uniqid();
		$path = $basePath . '/' . $folderName;

		$this->fixture = $this->getMock('t3lib_vfs_Folder', array('getPathInMountpoint'), array($this->fixtureData));
		$this->fixture->expects($this->any())->method('getPathInMountpoint')->will($this->returnValue($basePath));

		$mockedDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract', array(), '', TRUE, TRUE, TRUE, array('hasCapability', 'createFolder'));
		$mockedDriver->expects($this->once())->method('createFolder')->with($this->equalTo($path))->will($this->returnValue(TRUE));
		$mockedDriver->expects($this->any())->method('hasCapability')->with($this->anything())->will($this->returnValue(TRUE));
		$mockedMount = $this->getMock('t3lib_vfs_Mount', array(), array(), '', FALSE);
		$mockedMount->expects($this->any())->method('getStorageDriver')->will($this->returnValue($mockedDriver));
		$this->fixture->setMountpoint($mockedMount);

		$this->fixture->createSubfolder($folderName);
	}

	/**
	 * @test
	 */
	public function createSubfolderFailsIfDriverDoesntSupportFolders() {
		$this->markTestSkipped('This test requires functionality in PHPUnit which is currently not available (mocking concrete methods in abstract classes); a patch for this is pending, see https://github.com/sebastianbergmann/phpunit-mock-objects/issues#issue/49');
		$this->setExpectedException('RuntimeException', 1300287831);

		$mockedDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		// TODO: mock behaviour of hasCapabiltiy() as soon as PHPUnit supports mocking concrete methods in abstract classes
		$mockedMount = $this->getMock('t3lib_vfs_Mount', array(), array(), '', FALSE);
		$mockedMount->expects($this->any())->method('getStorageDriver')->will($this->returnValue($mockedDriver));
		$this->fixture->setMountpoint($mockedMount);

		$this->fixture->createSubfolder(uniqid());
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfolderQueriesDatabaseWithCorrectArguments() {
			// mock a folder instance for the getSubfolder() method to return, so we don't have to fake a folder array here
		$mockedSubfolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		t3lib_div::addInstance('t3lib_vfs_Folder', $mockedSubfolder);

		$mockedFactory = $this->getMock('t3lib_vfs_Factory', array(), array(), '', FALSE);
		t3lib_div::setSingletonInstance('t3lib_vfs_Factory', $mockedFactory);

		//$this->markTestIncomplete();
		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->once())->method('fetch')->will($this->returnValue(array()));
		$mockedStatement->expects($this->any())->method('rowCount')->will($this->returnValue(1));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$subfolderName = uniqid();
		$mockedStatement->expects($this->once())->method('execute')->with($this->equalTo(array('pid' => $this->fixtureData['uid'], 'name' => $subfolderName)));

		$this->fixture->getSubfolder($subfolderName);
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfolderUsesCorrectProtocolOnPreparedStatement() {
			// mock a folder instance for the getSubfolder() method to return, so we don't have to fake a folder array here
		$mockedSubfolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		t3lib_div::addInstance('t3lib_vfs_Folder', $mockedSubfolder);

		$mockedFactory = $this->getMock('t3lib_vfs_Factory', array(), array(), '', FALSE);
		t3lib_div::setSingletonInstance('t3lib_vfs_Factory', $mockedFactory);

		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');

		$mockedStatement->expects($this->once())->method('execute');
		$mockedStatement->expects($this->once())->method('fetch')->will($this->returnValue(array()));
		$mockedStatement->expects($this->once())->method('free');
		$mockedStatement->expects($this->any())->method('rowCount')->will($this->returnValue(1));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getSubfolder(uniqid());
	}
}
