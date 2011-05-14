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
 * Testcase for the mount class
 *
 * @package TYPO3
 * @subpackage t3lib
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 */
class t3lib_vfs_MountTest extends Tx_Phpunit_TestCase {
	/**
	 * @var t3lib_vfs_Mount
	 */
	private $fixture;

	public function setUp() {
		$fixtureData = array(
			'name' => uniqid()
		);
		$this->fixture = new t3lib_vfs_Mount($fixtureData);
	}

	/**
	 * @test
	 */
	public function setMountpointThrowsException() {
		$this->setExpectedException('LogicException', 1300101066);

		$mockedMount = $this->getMock('t3lib_vfs_Mount', array(), array(), '', FALSE);

		$this->fixture->setMountpoint($mockedMount);
	}
}

?>