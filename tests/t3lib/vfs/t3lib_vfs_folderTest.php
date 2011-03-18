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
	 * NOTE: All tests on methods getSubfolder() and getSubfolders() have to run in their own processes because of a
	 *       PHPUnit misbehaviour with backups of static class attributes. If ran within the same process, all tests for
	 *       each method except the first will fail.
	 */

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfolderThrowsExceptionIfNoSubfoldersAreFound() {
		$this->setExpectedException('RuntimeException', 1300481287);

		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->any())->method('rowCount')->will($this->returnValue(0));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getSubfolder('asdf');
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfolderQueriesDatabaseWithCorrectArguments() {
			// just expect this exception because we don't return any folder rows and thus will have an exception
		$this->setExpectedException('RuntimeException', 1300481287);

		//$this->markTestIncomplete();
		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
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
		$mockedFactory = $this->getMock('t3lib_vfs_Factory', array(), array(), '', FALSE);
		t3lib_div::setSingletonInstance('t3lib_vfs_Factory', $mockedFactory);

		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');

		$mockedStatement->expects($this->once())->method('execute');
		$mockedStatement->expects($this->once())->method('fetch')->will($this->returnValue(array('uid' => 1)));
		$mockedStatement->expects($this->once())->method('free');
		$mockedStatement->expects($this->any())->method('rowCount')->will($this->returnValue(1));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getSubfolder(uniqid());
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfoldersReturnsEmptyArrayIfNoSubfoldersAreFound() {
		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->any())->method('rowCount')->will($this->returnValue(0));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$folders = $this->fixture->getSubfolders();
		$this->assertEmpty($folders);
		$this->assertInternalType('array', $folders);
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfoldersQueriesDatabaseWithCorrectArguments() {
		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->once())->method('execute')->with($this->equalTo(array('pid' => $this->fixtureData['uid'])));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getSubfolders();
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getSubfoldersCreatesObjectsForAllReturnedRows() {
		$folderData1 = array(
			'uid' => uniqid()
		);
		$folderData2 = array(
			'uid' => uniqid()
		);

		$mockedFactory = $this->getMock('t3lib_vfs_Factory', array(), array(), '', FALSE);
		$mockedFactory->expects($this->at(0))->method('getFolderObjectFromData')
		  ->with($this->equalTo($folderData1));
		$mockedFactory->expects($this->at(1))->method('getFolderObjectFromData')
		  ->with($this->equalTo($folderData2));
		t3lib_div::setSingletonInstance('t3lib_vfs_Factory', $mockedFactory);

		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->once())->method('execute')->with($this->equalTo(array('pid' => $this->fixtureData['uid'])));
		$mockedStatement->expects($this->once())->method('rowCount')->will($this->returnValue(2));
		$mockedStatement->expects($this->any())->method('fetch')
		  ->will($this->onConsecutiveCalls($this->returnValue($folderData1), $this->returnValue($folderData2),
		    $this->returnValue(NULL)));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getSubfolders();
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getFilesReturnsEmptyArrayIfNoFilesAreFound() {
		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->any())->method('rowCount')->will($this->returnValue(0));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$files = $this->fixture->getFiles();
		$this->assertEmpty($files);
		$this->assertInternalType('array', $files);
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getFilesQueriesDatabaseWithCorrectArguments() {
		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->once())->method('execute')->with($this->equalTo(array('pid' => $this->fixtureData['uid'])));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getFiles();
	}

	/**
	 * @test
	 * @runInSeparateProcess
	 */
	public function getFilesCreatesObjectsForAllReturnedRows() {
		$this->markTestIncomplete('Can\'t complete this method because the methods in Factory are not finished yet.');
		$fileData1 = array(
			'uid' => uniqid()
		);
		$fileData2 = array(
			'uid' => uniqid()
		);

		$mockedFactory = $this->getMock('t3lib_vfs_Factory', array(), array(), '', FALSE);
		$mockedFactory->expects($this->at(0))->method('getFolderObjectFromData')
		  ->with($this->equalTo($fileData1));
		$mockedFactory->expects($this->at(1))->method('getFolderObjectFromData')
		  ->with($this->equalTo($fileData2));
		t3lib_div::setSingletonInstance('t3lib_vfs_Factory', $mockedFactory);

		$mockedStatement = $this->getMock('t3lib_db_PreparedStatement');
		$mockedStatement->expects($this->once())->method('execute')->with($this->equalTo(array('pid' => $this->fixtureData['uid'])));
		$mockedStatement->expects($this->once())->method('rowCount')->will($this->returnValue(2));
		$mockedStatement->expects($this->any())->method('fetch')
		  ->will($this->onConsecutiveCalls($this->returnValue($fileData1), $this->returnValue($fileData2),
		    $this->returnValue(NULL)));
		t3lib_div::addInstance('t3lib_db_PreparedStatement', $mockedStatement);

		$this->fixture->getFiles();
	}
}
