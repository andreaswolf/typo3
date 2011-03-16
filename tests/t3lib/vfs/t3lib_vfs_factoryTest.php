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
 * Testcase for the factory of VFS
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_factoryTest extends tx_phpunit_testcase {

	/**
	 * @var t3lib_vfs_Factory
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new t3lib_vfs_Factory();
	}

	/**
	 * @test
	 */
	public function getFolderObjectReturnsRootNodeForUidZero() {
		$object = $this->fixture->getFolderObject(0);

		$this->assertInstanceOf('t3lib_vfs_RootNode', $object);
	}

	/**
	 * @test
	 */
	public function getFolderObjectReturnsSameObjectForSameUid() {
		$folderObject1 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$folderObject2 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);

		$this->fixture = $this->getMock('t3lib_vfs_Factory', array('createFolderObject'));
		$this->fixture->expects($this->any())->method('createFolderObject')
		    ->will($this->onConsecutiveCalls($folderObject1, $folderObject2));

		$builtObj1 = $this->fixture->getFolderObject(1);
		$builtObj2 = $this->fixture->getFolderObject(2);
		$builtObj3 = $this->fixture->getFolderObject(1);

		$this->assertInternalType('object', $builtObj1);
		$this->assertNotSame($builtObj1, $builtObj2);
		$this->assertSame($builtObj1, $builtObj3);
	}

	public function getFolderObjectThrowsExceptionForInvalidUids_dataProvider() {
		return array(
			array(1),
			array('asdf'),
			array(-2)
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider getFolderObjectThrowsExceptionForInvalidUids_dataProvider
	 */
	public function getFolderObjectThrowsExceptionForInvalidUids($uid) {
		if (is_int($uid)) $this->markTestSkipped('Data Provider has a bug with integer values.');
		$this->setExpectedException('InvalidArgumentException', '', 1299957013);

		$this->fixture->getFolderObject($uid);
	}

	/**
	 * @test
	 */
	public function createFolderObjectReturnsMountObjectForMountpoint() {
		$this->fixture = $this->getMock('t3lib_vfs_Factory', array('getFolderObject'));
		$this->fixture->expects($this->once())->method('getFolderObject')->will($this->returnValue($this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE)));

		$mockedFolderData = array(
			'uid' => 1,
			'pid' => 0,
			'driver' => 'mockedDriverClass'
		);

		$driverMock = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract', array(), 'mockedDriverClass');
		t3lib_div::addInstance('mockedDriverClass', $driverMock);
		$mockedMountObject = $this->getMock('t3lib_vfs_Mount', NULL, array(), '', FALSE);
		t3lib_div::addInstance('t3lib_vfs_Mount', $mockedMountObject);

		$this->assertSame($mockedMountObject, $this->fixture->createFolderObject($mockedFolderData));
	}

	/**
	 * @test
	 */
	public function createFolderObjectReturnsFolderObjectForNormalFolder() {
		$mockedMountObject = $this->getMock('t3lib_vfs_Mount', NULL, array(), '', FALSE);

		$mockedParentFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedParentFolder->expects($this->any())->method('getMountpoint')->will($this->returnValue($mockedMountObject));

		$this->fixture = $this->getMock('t3lib_vfs_Factory', array('getFolderObject'));
		$this->fixture->expects($this->once())->method('getFolderObject')->will($this->returnValue($mockedParentFolder));
		$mockedFolderData = array(
			'uid' => 1,
			'pid' => 0,
		);

		$folderObject = $this->fixture->createFolderObject($mockedFolderData);

		$this->assertInstanceOf('t3lib_vfs_Folder', $folderObject);
	}
}