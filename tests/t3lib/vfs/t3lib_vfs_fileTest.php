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
 * Testcase for the file class of the TYPO3 VFS
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_fileTest extends tx_phpunit_testcase {

	protected function prepareFixture() {
		$fixture = new t3lib_vfs_File('testfile');

		return $fixture;
	}
	/**
	 * @test
	 */
	public function openCorrectlyOpensFileInDriver() {
		$fixture = $this->prepareFixture();
		$fileMode = 'invalidMode';

		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->atLeastOnce())->method('getFileHandle')->with($this->equalTo($fixture), $this->equalTo($fileMode));

		$fixture->setStorageDriver($mockDriver);
		$fixture->open($fileMode);
	}

	/**
	 * @test
	 */
	public function isOpenReturnsCorrectValuesForClosedAndOpenFile() {
		$fixture = $this->prepareFixture();
		$fileMode = 'r';

		$mockFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockFileHandle->expects($this->any())->method('isOpen')->will($this->returnValue(TRUE));
		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->any())->method('getFileHandle')->will($this->returnValue($mockFileHandle));

		$fixture->setStorageDriver($mockDriver);

		$this->assertFalse($fixture->isOpen());
		$fixture->open($fileMode);
		$this->assertTrue($fixture->isOpen());
	}

	/**
	 * @test
	 */
	public function fileIsCorrectlyClosed() {
		$fixture = $this->prepareFixture();
		$fileMode = 'r';

		$mockFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockFileHandle->expects($this->once())->method('close');
		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->any())->method('getFileHandle')->will($this->returnValue($mockFileHandle));

		$fixture->setStorageDriver($mockDriver);

		$fixture->open($fileMode);
		$fixture->close();
		$this->assertFalse($fixture->isOpen());
	}

	/**
	 * @test
	 */
	public function readReturnsRequestedContentsFromDriver() {
		$fixture = $this->prepareFixture();
		$fileMode = 'r';
		$fileContents = 'Some random file contents.';
		$bytesToRead = 10;

		$mockFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockFileHandle->expects($this->any())->method('isOpen')->will($this->returnValue(TRUE));
		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->any())->method('getFileHandle')->will($this->returnValue($mockFileHandle));
		$mockDriver->expects($this->once())->method('readFromFile')->with($this->anything(), $this->equalTo($bytesToRead))
		    ->will($this->returnValue(substr($fileContents, 0, $bytesToRead)));

		$fixture->setStorageDriver($mockDriver);

		$fixture->open($fileMode);
		$this->assertEquals(substr($fileContents, 0, $bytesToRead), $fixture->read($bytesToRead));
	}

	/**
	 * @test
	 */
	public function readFailsIfFileIsClosed() {
		$this->setExpectedException('RuntimeException', '', 1299863431);

		$fixture = $this->prepareFixture();

		$mockFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->any())->method('getFileHandle')->will($this->returnValue($mockFileHandle));

		$fixture->setStorageDriver($mockDriver);

		$fixture->read(1);
	}

	/**
	 * @test
	 */
	public function writePassesContentsToDriver() {
		$fixture = $this->prepareFixture();
		$fileMode = 'r+';
		$fileContents = 'Some random file contents.';

		$mockFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockFileHandle->expects($this->any())->method('isOpen')->will($this->returnValue(TRUE));
		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->any())->method('getFileHandle')->will($this->returnValue($mockFileHandle));
		$mockDriver->expects($this->once())->method('writeToFile')->with($this->anything(), $this->equalTo($fileContents))
		    ->will($this->returnValue(TRUE));

		$fixture->setStorageDriver($mockDriver);

		$fixture->open($fileMode);
		$this->assertTrue($fixture->write($fileContents));
	}

	/**
	 * @test
	 */
	public function writeFailsIfFileIsClosed() {
		$this->setExpectedException('RuntimeException', '', 1299863432);

		$fixture = $this->prepareFixture();

		$mockFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockDriver = $this->getMockForAbstractClass('t3lib_vfs_driver_Abstract');
		$mockDriver->expects($this->any())->method('getFileHandle')->will($this->returnValue($mockFileHandle));

		$fixture->setStorageDriver($mockDriver);

		$fixture->write('asdf');
	}
}

?>