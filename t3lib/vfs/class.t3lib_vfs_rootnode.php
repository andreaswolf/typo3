<?php

class t3lib_vfs_RootNode extends t3lib_vfs_Mount implements t3lib_Singleton {
	public function __construct() {
		$this->uid = '0';
		$this->mountpoint = $this;
	}

	public function getParent() {
		return NULL;
	}
}

?>