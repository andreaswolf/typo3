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
 * Testcase for the VFS repository class
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_repositoryTest extends Tx_Phpunit_TestCase {

	/**
	 * @var t3lib_vfs_Repository
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new t3lib_vfs_Repository();
	}

	/**
	 * @test
	 */
	public function persistNodeUsesCorrectTablesForObjects() {
		$changedProperties = array('test' => 'test2');
		$fileMock = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);
		$fileMock->expects($this->any())->method('isNew')->will($this->returnValue(FALSE));
		$fileMock->expects($this->any())->method('getChangedProperties')->will($this->returnValue($changedProperties));
		$folderMock = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$folderMock->expects($this->any())->method('isNew')->will($this->returnValue(FALSE));
		$folderMock->expects($this->any())->method('getChangedProperties')->will($this->returnValue($changedProperties));

		$dbMock = $this->getMock('t3lib_DB', array('exec_UPDATEquery'), array(), '', FALSE);
		$dbMock->expects($this->at(0))->method('exec_UPDATEquery')->with('sys_file');
		$dbMock->expects($this->at(1))->method('exec_UPDATEquery')->with('sys_folder');
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($fileMock);
		$this->fixture->persistNodeToDatabase($folderMock);
	}

	/**
	 * @test
	 */
	public function persistNodeUsesCorrectRecordIdentity() {
		$uid = rand(1, 100);
		$changedProperties = array('test' => 'test2');
		$fileMock = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);
		$fileMock->expects($this->any())->method('getValue')->with($this->equalTo('uid'))->will($this->returnValue($uid));
		$fileMock->expects($this->any())->method('isNew')->will($this->returnValue(FALSE));
		$fileMock->expects($this->any())->method('getChangedProperties')->will($this->returnValue($changedProperties));

		$dbMock = $this->getMock('t3lib_DB', array(), array(), '', FALSE);
		$dbMock->expects($this->once())->method('exec_UPDATEquery')->with('sys_file', $this->stringContains((string)$uid));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($fileMock);
	}

	/**
	 * @test
	 */
	public function persistNodeUpdatesFields() {
		$changedProperties = array('test' => 'test2');
		$fileMock = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);
		$fileMock->expects($this->any())->method('getChangedProperties')->will($this->returnValue($changedProperties));
		$fileMock->expects($this->any())->method('isNew')->will($this->returnValue(FALSE));

		$dbMock = $this->getMock('t3lib_DB', array(), array(), '', FALSE);
		$dbMock->expects($this->once())->method('exec_UPDATEquery')->with($this->anything(), $this->anything(),
		  $this->logicalAnd($this->contains($changedProperties['test']), $this->arrayHasKey('test')));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($fileMock);
	}

	/**
	 * @test
	 */
	public function persistNodeRemovesImmutablePropertiesFromUpdateFields() {
		$changedProperties = array(
			'test' => uniqid(),
			'uid' => uniqid(),
			'crdate' => uniqid(),
			'cruser_id' => uniqid()
		);

		$fileMock = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);
		$fileMock->expects($this->any())->method('getChangedProperties')->will($this->returnValue($changedProperties));
		$fileMock->expects($this->any())->method('isNew')->will($this->returnValue(FALSE));

		$dbMock = $this->getMock('t3lib_DB', array('exec_UPDATEquery'), array(), '', FALSE);
		$dbMock->expects($this->at(0))->method('exec_UPDATEquery')->will($this->returnCallback(array($this, 'persistNodeRemovesImmutablePropertiesFromUpdateFields_callback')));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($fileMock);
	}

	public function persistNodeRemovesImmutablePropertiesFromUpdateFields_callback($table, $where, $updateFields) {
		$this->assertArrayNotHasKey('uid', $updateFields);
		$this->assertArrayNotHasKey('crdate', $updateFields);
		$this->assertArrayNotHasKey('cruser_id', $updateFields);
	}

	/**
	 * @test
	 */
	public function persistNodeCreatesNewRecordForNewNodes() {
		$properties = array(
			'test' => uniqid()
		);
		$fileMock = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);
		$fileMock->expects($this->any())->method('getProperties')->will($this->returnValue($properties));
		$fileMock->expects($this->any())->method('isNew')->will($this->returnValue(TRUE));
		$fileMock->expects($this->any())->method('getParent')->will($this->returnValue($this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE)));

		$dbMock = $this->getMock('t3lib_DB', array('exec_INSERTquery'), array(), '', FALSE);
		$dbMock->expects($this->at(0))->method('exec_INSERTquery')->with($this->anything(), $this->contains($properties['test']));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($fileMock);
	}

	/**
	 * @test
	 * @depends persistNodeCreatesNewRecordForNewNodes
	 */
	public function persistNodeSetsUidOfNewRecordAfterCreatingDatabaseRecord() {
		$properties = array(
			'test' => uniqid()
		);
		$uid = uniqid();
		$fileMock = $this->getMock('t3lib_vfs_File', array(), array(), '', FALSE);
		$fileMock->expects($this->any())->method('getProperties')->will($this->returnValue($properties));
		$fileMock->expects($this->any())->method('isNew')->will($this->returnValue(TRUE));
		$fileMock->expects($this->once())->method('setUid')->with($this->equalTo($uid));
		$fileMock->expects($this->any())->method('getParent')
		  ->will($this->returnValue($this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE)));

		$dbMock = $this->getMock('t3lib_DB', array('exec_INSERTquery', 'sql_insert_id'), array(), '', FALSE);
		$dbMock->expects($this->at(0))->method('exec_INSERTquery')->with($this->anything(), $this->contains($properties['test']));
		$dbMock->expects($this->once())->method('sql_insert_id')->will($this->returnValue($uid));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($fileMock);
	}

	/**
	 * @test
	 */
	public function persistNodeUpdatesTimestampForNewAndExistingRecords() {
		$mockedExistingNode = $this->getMock('t3lib_vfs_File', array('getChangedProperties', 'isNew'));
		$mockedNewNode = $this->getMock('t3lib_vfs_File', array('getProperties', 'isNew', 'getParent'));

		$mockedExistingNode->expects($this->any())->method('getChangedProperties')->will($this->returnValue(array('foo' => 'bar', 'tstamp' => 10)));
		$mockedExistingNode->expects($this->any())->method('isNew')->will($this->returnValue(FALSE));
		$mockedNewNode->expects($this->any())->method('getProperties')->will($this->returnValue(array('foo' => 'bar')));
		$mockedNewNode->expects($this->any())->method('isNew')->will($this->returnValue(TRUE));
		$mockedNewNode->expects($this->any())->method('getParent')
		  ->will($this->returnValue($this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE)));

		$dbMock = $this->getMock('t3lib_DB', array('exec_INSERTquery', 'exec_UPDATEquery'), array(), '', FALSE);
		$dbMock->expects($this->any())->method('exec_INSERTquery')->with($this->anything(), $this->arrayHasKey('tstamp'))->will($this->returnValue(1));
		$dbMock->expects($this->once())->method('exec_UPDATEquery')->with($this->anything(), $this->anything(), $this->arrayHasKey('tstamp'));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($mockedExistingNode);
		$this->fixture->persistNodeToDatabase($mockedNewNode);
	}

	/**
	 * @test
	 */
	public function persistNodePersistsNewParentsBeforePersistingChildren() {
		$folderProperties = array('this is' => 'a folder');
		$fileProperties = array('this is' => 'a file');

		$mockedFileNode = $this->getMock('t3lib_vfs_File', array());
		$mockedParent = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedGrandparent = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);

		$mockedFileNode->expects($this->atLeastOnce())->method('getProperties')->will($this->returnValue($fileProperties));
		$mockedFileNode->expects($this->any())->method('isNew')->will($this->returnValue(TRUE));
		$mockedFileNode->expects($this->any())->method('getParent')->will($this->returnValue($mockedParent));
		$mockedParent->expects($this->atLeastOnce())->method('getProperties')->will($this->returnValue($folderProperties));
		$mockedParent->expects($this->any())->method('isNew')->will($this->returnValue(TRUE));
		$mockedParent->expects($this->once())->method('getParent')
		  ->will($this->returnValue($mockedGrandparent));

		$dbMock = $this->getMock('t3lib_DB', array('exec_INSERTquery'), array(), '', FALSE);
		$dbMock->expects($this->at(0))->method('exec_INSERTquery')->with($this->anything(), $this->contains('a folder'));
		$dbMock->expects($this->at(1))->method('exec_INSERTquery')->with($this->anything(), $this->contains('a file'));
		$GLOBALS['TYPO3_DB'] = $dbMock;

		$this->fixture->persistNodeToDatabase($mockedFileNode);
	}

	/**
	 * @test
	 */
	public function getFolderNodeTraversesPathFromRootnode() {
		$pathParts = array(
			'subfolder',
			'anothersubfolder'
		);

		$mockedFolder1 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedFolder2 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedFolder2->expects($this->once())->method('getSubfolder')->with($pathParts[1])->will($this->returnValue($mockedFolder1));
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		$mockedRootNode->expects($this->once())->method('getSubfolder')->with($pathParts[0])->will($this->returnValue($mockedFolder2));
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);

		$folder = $this->fixture->getFolderNode(implode('/', $pathParts));
		$this->assertEquals($mockedFolder1, $folder);
	}

	/**
	 * @test
	 */
	public function getFolderNodeIgnoresLeadingAndTrailingSlash() {
		$pathParts = array(
			'subfolder'
		);

		$mockedFolder = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		$mockedRootNode->expects($this->once())->method('getSubfolder')->with($pathParts[0])->will($this->returnValue($mockedFolder));
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);

		$folder = $this->fixture->getFolderNode('/' . implode('/', $pathParts) . '/');
		$this->assertEquals($mockedFolder, $folder);
	}

	/**
	 * @test
	 */
	public function getFolderNodeIgnoresEmptyPathParts() {
		$path = '/subfolder//anothersubfolder/';

		$mockedFolder1 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedFolder2 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE);
		$mockedFolder2->expects($this->once())->method('getSubfolder')->with('anothersubfolder')->will($this->returnValue($mockedFolder1));
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		$mockedRootNode->expects($this->once())->method('getSubfolder')->with('subfolder')->will($this->returnValue($mockedFolder2));
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);

		$folder = $this->fixture->getFolderNode($path);
		$this->assertEquals($mockedFolder1, $folder);
	}

	/**
	 * @test
	 */
	public function getNearestIndexedNodeReturnsIndexedNodeAndMissingParts() {
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);
		$mockedFolder1 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE); // foo/

		$mockedRootNode->expects($this->once())->method('getSubfolder')->will($this->returnValue($mockedFolder1));
		$mockedFolder1->expects($this->once())->method('getSubfolder')->will($this->throwException(new RuntimeException()));

			// we only have the node "foo", "bar" does not exist (and thus also not "baz")
		list($folder, $notIndexedParts) = $this->fixture->getNearestIndexedNode('foo/bar/baz');

		$this->assertSame($mockedFolder1, $folder);
		$this->assertEquals(array('bar', 'baz'), $notIndexedParts);
	}

	/**
	 * @test
	 */
	public function getNearestIndexedNodeReturnsEmptyMissingNodesArrayIfQueriedNodeWasIndexed() {
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);
		$mockedFolder1 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE); // foo/
		$mockedFolder2 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE); // foo/bar/

		$mockedRootNode->expects($this->once())->method('getSubfolder')->will($this->returnValue($mockedFolder1));
		$mockedFolder1->expects($this->once())->method('getSubfolder')->will($this->returnValue($mockedFolder2));

		list($folder, $notIndexedParts) = $this->fixture->getNearestIndexedNode('foo/bar/');

		$this->assertSame($mockedFolder2, $folder);
		$this->assertEquals(array(), $notIndexedParts);
	}

	/**
	 * @test
	 */
	public function getNearestIndexedNodeDetectsFileInPath() {
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);
		$mockedFolder1 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE); // foo/
		$mockedFolder2 = $this->getMock('t3lib_vfs_Folder', array(), array(), '', FALSE); // foo/bar/

		$mockedRootNode->expects($this->once())->method('getSubfolder')->will($this->returnValue($mockedFolder1));
		$mockedFolder1->expects($this->once())->method('getSubfolder')->will($this->returnValue($mockedFolder2));
		$mockedFolder2->expects($this->never())->method('getSubfolder');
		$mockedFolder2->expects($this->once())->method('getFile')->with($this->equalTo('file.baz'));

		list($folder, $notIndexedParts) = $this->fixture->getNearestIndexedNode('foo/bar/file.baz');
	}

	/**
	 * @test
	 */
	public function getNearestIndexedNodeReturnsNoNodeIfNoPathPartIsIndexed() {
		$mockedRootNode = $this->getMock('t3lib_vfs_RootNode');
		t3lib_div::setSingletonInstance('t3lib_vfs_RootNode', $mockedRootNode);

		$mockedRootNode->expects($this->once())->method('getSubfolder')->will($this->throwException(new RuntimeException()));

		list($folder) = $this->fixture->getNearestIndexedNode('some/randome/path/');
		$this->assertNull($folder);
	}
}

?>