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
 *  the Freef Software Foundation; either version 2 of the License, or
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


/**
 * Testcase for the VFS indexer
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_vfs_IndexerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var t3lib_vfs_Indexer
	 */
	private $fixture;

	/**
	 * @var t3lib_vfs_Repository
	 */
	private $mockedRepository;

	/**
	 * @var t3lib_vfs_Factory
	 */
	private $mockedFactory;

	public function setUp() {
		$this->mockedRepository = $this->getMock('t3lib_vfs_Repository');
		$this->mockedFactory = $this->getMock('t3lib_vfs_Factory');
		t3lib_div::setSingletonInstance('t3lib_vfs_Repository', $this->mockedRepository);
		$this->fixture = new t3lib_vfs_Indexer();
		$this->fixture->setFactory($this->mockedFactory);
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Indexer::indexNodeAtPath
	 */
	public function indexNodeReturnsNodeForRightmostPathPartIfItIsAlreadyIndexed() {
		$path = 'some/random/arbitrary/path/with/a/file/at/the/end.jpg';
		$mockedFile = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);

		$this->mockedRepository->expects($this->once())->method('getNearestIndexedNode')->with($this->equalTo($path))
		  ->will($this->returnValue(array($mockedFile, array())));

		$this->assertSame($mockedFile, $this->fixture->indexNodeAtPath($path));
	}

	/**
	 * @test
	 * @covers t3lib_vfs_Indexer::indexNodeAtPath
	 */
	public function indexNodeAtPathIndexesParentFoldersIfNotIndexed() {
		$pathParts = array(
			'some/arbitrary/path',
			'notIndexedFolder',
			'notIndexedFile.jpg'
		);
		$completePath = implode('/', $pathParts);
		$mockedIndexedFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), uniqid('folder_'), FALSE);
		$mockedNewFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), uniqid('folder_'), FALSE);
		$mockedNewFile = $this->getMock('t3lib_vfs_File', array(), array(), uniqid('file_'), FALSE);
		$mockedMount = $this->getMock('t3lib_vfs_Mount', array(), array(), uniqid('mount_'), FALSE);
		$mockedStorage = $this->getMock('t3lib_vfs_driver_Local', array(), array(), uniqid('storage_'), FALSE);

		$mockedIndexedFolder->expects($this->any())->method('getMountpoint')->will($this->returnValue($mockedMount));
		$mockedMount->expects($this->any())->method('getStorageDriver')->will($this->returnValue($mockedStorage));
		$mockedStorage->expects($this->at(0))->method('getNodeType')->will($this->returnValue('dir'));
		$mockedStorage->expects($this->at(1))->method('getNodeType')->will($this->returnValue('file'));

		/** @var $fixture t3lib_vfs_Indexer */
		$fixture = $this->getMock('t3lib_vfs_Indexer', array('indexFolder', 'indexFile', 'gatherFileInformation'));
		$fixture->expects($this->at(0))->method('indexFolder')->with($this->equalTo($mockedIndexedFolder), $pathParts[1])
		  ->will($this->returnValue($mockedNewFolder));
		$fixture->expects($this->at(1))->method('indexFile')->with($this->equalTo($mockedNewFolder), $pathParts[2])
		  ->will($this->returnValue($mockedNewFile));
		$this->mockedRepository->expects($this->once())->method('getNearestIndexedNode')->with($this->equalTo($completePath))
		  ->will($this->returnValue(array($mockedIndexedFolder, array($pathParts[1], $pathParts[2]))));

		$fixture->indexNodeAtPath($completePath);
	}

	/**
	 * @test
	 */
	public function indexNodeAtPathFailsIfNoNodeInPathIsIndexed() {
		$this->setExpectedException('RuntimeException', '', 1305193038);

		$path = 'some/path/file.jpg';

		$this->mockedRepository->expects($this->once())->method('getNearestIndexedNode')->with($this->equalTo($path))
		  ->will($this->returnValue(array(NULL, array('some', 'path', 'file.jpg'))));

		$this->fixture->indexNodeAtPath($path);
	}

	/**
	 * @test
	 */
	public function indexFileSetsParentFolderOfNewFile() {
		$mockedFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedFile = $this->getMock('t3lib_vfs_File');
		$mockedFile->expects($this->once())->method('setParent')->with($this->equalTo($mockedFolder));
		t3lib_div::addInstance('t3lib_vfs_File', $mockedFile);
		$mockedRepository = $this->getMock('t3lib_vfs_Repository');
		$mockedRepository->expects($this->once())->method('persistNodeToDatabase')->with($mockedFile);

		$fixture = $this->getMock('t3lib_vfs_Indexer', array('gatherFileInformation'));
		$fixture->setRepository($mockedRepository);
		$fixture->indexFile($mockedFolder, 'path.jpg');
	}

	/**
	 * @test
	 */
	public function indexFileCallsRepositoryToPersistNode() {
		$mockedFile = $this->getMock('t3lib_vfs_File');
		t3lib_div::addInstance('t3lib_vfs_File', $mockedFile);
		$mockedFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedRepository = $this->getMock('t3lib_vfs_Repository');
		$mockedRepository->expects($this->once())->method('persistNodeToDatabase')->with($mockedFile);

		$fixture = $this->getMock('t3lib_vfs_Indexer', array('gatherFileInformation'));
		$fixture->setRepository($mockedRepository);
		$fixture->indexFile($mockedFolder, 'path.jpg');
	}

	/**
	 * @test
	 */
	public function storageDriverIsCalledForGatheringFileInformation() {
		$mockedFile = $this->getMock('t3lib_vfs_File');
		t3lib_div::addInstance('t3lib_vfs_File', $mockedFile);
		$mockedStorageDriver = $this->getMockForAbstractClass('t3lib_vfs_Driver_Abstract', array(), '', FALSE);
		$mockedFolder = $this->getMock('t3lib_vfs_Mount', array(), array(), '', FALSE);

		$mockedFolder->expects($this->any())->method('getStorageDriver')->will($this->returnValue($mockedStorageDriver));
		$mockedFolder->expects($this->any())->method('getMountpoint')->will($this->returnValue($mockedFolder));
		$mockedFile->expects($this->any())->method('getParent')->will($this->returnValue($mockedFolder));
		$mockedStorageDriver->expects($this->once())->method('stat')->with($this->equalTo($mockedFile));

		$this->fixture->indexFile($mockedFolder, 'path.jpg');
	}

	/**
	 * @test
	 */
	public function indexFileSetsGatheredFileInformationInFileRecord() {
		$mockedFile = $this->getMock('t3lib_vfs_File');
		$fileInfo = array(
			'sha1' => sha1(uniqid()),
			'tstamp' => time()
		);
		$mockedFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedRepository = $this->getMock('t3lib_vfs_Repository');
		$mockedFile->expects($this->at(1))->method('setValue')->with('sha1', $fileInfo['sha1']);
		$mockedFile->expects($this->at(2))->method('setValue')->with('tstamp', $fileInfo['tstamp']);
		t3lib_div::addInstance('t3lib_vfs_File', $mockedFile);

		$fixture = $this->getMock('t3lib_vfs_Indexer', array('gatherFileInformation'));
		$fixture->expects($this->any())->method('gatherFileInformation')->will($this->returnValue($fileInfo));
		$fixture->setRepository($mockedRepository);
		$fixture->indexFile($mockedFolder, 'path.jpg');
	}

	/**
	 * @test
	 */
	public function indexFolderSetsParentFolder() {
		$mockedNewFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedParentFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);

		$mockedNewFolder->expects($this->once())->method('setParent')->with($this->equalTo($mockedParentFolder));
		t3lib_div::addInstance('t3lib_vfs_Folder', $mockedNewFolder);

		$this->fixture->indexFolder($mockedParentFolder, 'some/random/folder/');
	}
}

?>