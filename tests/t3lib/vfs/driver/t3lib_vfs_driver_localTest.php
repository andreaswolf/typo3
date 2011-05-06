<?php

/***************************************************************
 * Copyright notice
 *
 * (c) 2011 Andreas Wolf (andreas.wolf@ikt-werk.de)
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
 * Testcase for the local filesystem driver of FAL/VFS
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_driver_localTest extends tx_phpunit_testcase {

	/**
	 * @var t3lib_vfs_driver_Local
	 */
	private $fixture;

	private $basedir = 'basedir';

	public function setUp() {
		vfsStream::setup($this->basedir);

		$driverConfiguration = array(
			'basePath' => vfsStream::url(vfsStreamWrapper::getRoot()->getName())
		);

		$this->fixture = new t3lib_vfs_driver_Local($driverConfiguration);
	}

	public function tearDown() {
		$this->fixture = NULL;
	}

	/**
	 * @param  $path
	 * @return vfsStreamFile
	 */
	protected function createEmptyFile($path) {
		// TODO create directories if they don't exist
		return vfsStream::newFile($path);
	}

	/**
	 * @param  $path
	 * @param  $dirname
	 * @return void
	 */
	protected function createFixtureDir($path, $dirname) {
	}

	/**
	 * @test
	 */
	public function getAbsoluteBasePathReturnsCorrectPathForFile() {
			// please note: mountFolder is only part of the virtual path (.../mountFolder/subFolder/fileName),
			// not of the physical path, which we handle here
		$mockedMount = $this->getMock('t3lib_vfs_Mount', array('getName', 'isMountpoint'), array(), '', FALSE);
		$mockedMount->expects($this->any())->method('getName')->will($this->returnValue('mountFolder'));
		$mockedMount->expects($this->any())->method('isMountpoint')->will($this->returnValue(TRUE));
		$mockedSubFolder = $this->getMock('t3lib_vfs_Folder', array('getName', 'getParent'), array(), '', FALSE);
		$mockedSubFolder->expects($this->any())->method('getName')->will($this->returnValue('subFolder'));
		$mockedFile = $this->getMock('t3lib_vfs_File', array('getName', 'getParent'), array(), '', FALSE);
		$mockedFile->expects($this->any())->method('getName')->will($this->returnValue('fileName'));

		$mockedFile->expects($this->any())->method('getParent')->will($this->returnValue($mockedSubFolder));
		$mockedSubFolder->expects($this->any())->method('getParent')->will($this->returnValue($mockedMount));

		$path = $this->fixture->getAbsolutePath($mockedFile);
		$expectedPath = vfsStream::url($this->basedir) . '/subFolder/fileName';

		$this->assertEquals($expectedPath, $path);
	}

	/**
	 * @test
	 */
	public function instantiatingDriverFailsIfBasePathDoesNotExist() {
		$this->setExpectedException('RuntimeException', '', 1299233097);

		$driverConfiguration = array(
			'basePath' => vfsStream::url('doesntexist/')
		);

		$this->assertFalse(file_exists($driverConfiguration['basePath']));
		new t3lib_vfs_driver_Local($driverConfiguration);
	}

	/**
	 * @test
	 */
	public function basePathIsNormalizedWithTrailingSlash() {
		$driverConfiguration = array(
			'basePath' => vfsStream::url(vfsStreamWrapper::getRoot()->getName())
		);

		$driver = new t3lib_vfs_driver_Local($driverConfiguration);
		$this->assertEquals('/', substr($driver->getAbsoluteBasePath(), -1));
	}

	/**
	 * @test
	 */
	public function noSecondSlashIsAddedIfBasePathAlreadyHasTrailingSlash() {
		$driverConfiguration = array(
			'basePath' => vfsStream::url(vfsStreamWrapper::getRoot()->getName() . '/')
		);

		$driver = new t3lib_vfs_driver_Local($driverConfiguration);
		$this->assertNotEquals('/', substr($driver->getAbsoluteBasePath(), -2, 1));
	}

	/**
	 * @test
	 */
	public function createFileReturnsFileObject() {
		$fileObject = $this->fixture->createFile('testFile');

		$this->assertInstanceOf('t3lib_vfs_File', $fileObject);
	}

	/**
	 * @test
	 */
	public function createFileCreatesPhysicalFile() {
		$this->fixture->createFile('testFile');

		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('testFile'));
		$this->assertTrue(is_file(vfsStream::url($this->basedir . '/testFile')), 'Created item is not a file');
	}

	/**
	 * @test
	 */
	public function createFileWorksForSubdirectories() {
		$dir = vfsStream::newDirectory('testDir');
		vfsStreamWrapper::getRoot()->addChild($dir);

		$fileObject = $this->fixture->createFile('testDir/testFile');
		$this->assertInstanceOf('t3lib_vfs_File', $fileObject);
		$this->assertTrue($dir->hasChild('testFile'));
	}

	/**
	 * @test
	 */
	public function createFileFailsIfFileExists() {
		$this->setExpectedException('RuntimeException', '', 1299761887);

		vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile('testFile'));

		$this->fixture->createFile('testFile');
	}

	/**
	 * @test
	 */
	public function createFileFailsIfFolderDoesntExist() {
		$this->setExpectedException('RuntimeException', '', 1299761888);

		$this->fixture->createFile('testDir/testFile');
	}

	/**
	 * @test
	 */
	public function createFileFailsIfFolderIsAFile() {
		$this->setExpectedException('RuntimeException', '', 1299761889);

		vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile('testDir'));
		$this->fixture->createFile('testDir/testFile');
	}

	/**
	 * @test
	 */
	public function createFolderReturnsFolderObject() {
		$folderObject = $this->fixture->createFolder('testFolder');

		$this->assertInstanceOf('t3lib_vfs_Folder', $folderObject);
	}

	/**
	 * @test
	 */
	public function createFolderCreatesPhysicalFolder() {
		$this->fixture->createFolder('testFolder');

		$this->assertTrue(vfsStreamWrapper::getRoot()->hasChild('testFolder'));
		$this->assertTrue(is_dir(vfsStream::url($this->basedir . '/testFolder')));
	}

	/**
	 * @test
	 */
	public function createFolderFailsIfFolderExists() {
		$this->setExpectedException('RuntimeException', '', 1299761890);

		vfsStreamWrapper::getRoot()->addChild(vfsStream::newDirectory('testDir'));
		$this->fixture->createFolder('testDir');
	}

	/**
	 * @test
	 *
	 * @covers t3lib_vfs_driver_Local::getFileHandle
	 */
	public function getFileHandleReturnsHandleObject() {
		$fileName = 'testFile';

		vfsStreamWrapper::getRoot()->addChild(vfsStream::newFile($fileName));
		$folderMock = $this->getMock('t3lib_vfs_Folder', array('isMountpoint', 'getName'), array(), '', FALSE);
		$folderMock->expects($this->any())->method('isMountpoint')->will($this->returnValue(TRUE));
		$folderMock->expects($this->any())->method('getName')->will($this->returnValue(vfsStreamWrapper::getRoot()->getName()));
		//$folderMock->expects($this->any())->method('getPath')->will($this->returnValue(TRUE));
		$fileMock = $this->getMock('t3lib_vfs_File', array('getParent', 'getName'), array(), '', FALSE);
		$fileMock->expects($this->any())->method('getParent')->will($this->returnValue($folderMock));
		$fileMock->expects($this->any())->method('getName')->will($this->returnValue($fileName));

		$handleObject = $this->fixture->getFileHandle($fileMock, 'r');

		$this->assertInstanceOf('t3lib_vfs_FileHandle', $handleObject);
	}

	/**
	 * @test
	 */
	public function seekMovesCursorToAndReturnsCorrectPosition() {
		vfsStream::newFile('testFile')->at(vfsStreamWrapper::getRoot())->withContent('These are the contents of some file. Lorem ipsum dolor sit amet...');

		$fileUrl = vfsStream::url($this->basedir . '/testFile');
		$fileResource = fopen($fileUrl, 'r');

		$mockedFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockedFileHandle->expects($this->any())->method('getResource')->will($this->returnValue($fileResource));

		$this->fixture->seek($mockedFileHandle, 10);
		$this->assertEquals(10, $this->fixture->seek($mockedFileHandle));
		$this->assertEquals(ftell($fileResource), $this->fixture->seek($mockedFileHandle));

			// test if cursor is moved forward correctly
		$this->fixture->seek($mockedFileHandle, 10);
		$this->fixture->seek($mockedFileHandle, 5, t3lib_VFS::SEEK_MODE_CUR);
		$this->assertEquals(15, $this->fixture->seek($mockedFileHandle), 'Seek mode "current" does not set cursor to correct position');

			// test if cursor is moved to EOF correctly
		$this->fixture->seek($mockedFileHandle, 0, t3lib_VFS::SEEK_MODE_END);
		$this->assertEquals(filesize($fileUrl), $this->fixture->seek($mockedFileHandle), 'Seek mode "end" does not set cursor to correct position');
	}

	/**
	 * @test
	 */
	public function readFromFileReturnsPartsOfFileCorrectly() {
		$fileContents = 'These are the contents of some file. Lorem ipsum dolor sit amet...';
		vfsStream::newFile('testFile')->at(vfsStreamWrapper::getRoot())->withContent($fileContents);

		$fileUrl = vfsStream::url($this->basedir . '/testFile');
		$fileResource = fopen($fileUrl, 'r');

		$mockedFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockedFileHandle->expects($this->any())->method('getResource')->will($this->returnValue($fileResource));

		$this->assertEquals(substr($fileContents, 0, 20), $this->fixture->readFromFile($mockedFileHandle, 20));
	}

	/**
	 * @test
	 */
	public function writeToFilePutsContentsToFile() {
		$fileContents = 'These are the contents of some file. Lorem ipsum dolor sit amet...';
		vfsStream::newFile('testFile')->at(vfsStreamWrapper::getRoot());

		$fileUrl = vfsStream::url($this->basedir . '/testFile');
		$fileResource = fopen($fileUrl, 'r+');

		$mockedFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockedFileHandle->expects($this->any())->method('getResource')->will($this->returnValue($fileResource));
		$mockedFileHandle->expects($this->any())->method('hasCapability')->with($this->equalTo(t3lib_vfs_FileHandle::CAP_WRITABLE))->will($this->returnValue(TRUE));

		$this->fixture->writeToFile($mockedFileHandle, $fileContents);
		$this->assertEquals($fileContents, file_get_contents($fileUrl));
	}

	/**
	 * @test
	 */
	public function writeToFileFailsIfFileIsOpenedReadOnly() {
		$this->setExpectedException('RuntimeException', '', 1299851832);

		vfsStream::newFile('testFile')->at(vfsStreamWrapper::getRoot());

		$fileUrl = vfsStream::url($this->basedir . '/testFile');
		$fileResource = fopen($fileUrl, 'r');

		$mockedFileHandle = $this->getMock('t3lib_vfs_FileHandle', array(), array(), '', FALSE);
		$mockedFileHandle->expects($this->any())->method('getResource')->will($this->returnValue($fileResource));
		$mockedFileHandle->expects($this->any())->method('hasCapability')->with($this->equalTo(t3lib_vfs_FileHandle::CAP_WRITABLE))->will($this->returnValue(FALSE));

		$this->fixture->writeToFile($mockedFileHandle, 'Random contents');
	}

	/**
	 * @test
	 */
	public function nodeExistsReturnsCorrectValuesForFiles() {
		vfsStream::newFile('existingFile')->at(vfsStreamWrapper::getRoot());

		$this->assertTrue($this->fixture->nodeExists('existingFile'));
		$this->assertFalse($this->fixture->nodeExists('nonexistingFile'));
	}

	/**
	 * @test
	 */
	public function nodeExistsReturnsCorrectValuesForFolders() {
		vfsStream::newDirectory('existingFolder')->at(vfsStreamWrapper::getRoot());

		$this->assertTrue($this->fixture->nodeExists('existingFolder'));
		$this->assertFalse($this->fixture->nodeExists('nonexistingFolder'));
	}
}

?>