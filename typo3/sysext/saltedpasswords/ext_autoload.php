<?php
// DO NOT CHANGE THIS FILE! It is automatically generated by extdeveval::buildAutoloadRegistry.
// This file was generated on 2009-09-16 14:24

$extensionPath = t3lib_extMgm::extPath('saltedpasswords');
return array(
	'tx_saltedpasswords_div' => $extensionPath . 'classes/class.tx_saltedpasswords_div.php',
	'tx_saltedpasswords_emconfhelper' => $extensionPath . 'classes/class.tx_saltedpasswords_emconfhelper.php',
	'tx_saltedpasswords_abstract_salts' => $extensionPath . 'classes/salts/class.tx_saltedpasswords_abstract_salts.php',
	'tx_saltedpasswords_salts_blowfish' => $extensionPath . 'classes/salts/class.tx_saltedpasswords_salts_blowfish.php',
	'tx_saltedpasswords_salts_factory' => $extensionPath . 'classes/salts/class.tx_saltedpasswords_salts_factory.php',
	'tx_saltedpasswords_salts_md5' => $extensionPath . 'classes/salts/class.tx_saltedpasswords_salts_md5.php',
	'tx_saltedpasswords_salts_phpass' => $extensionPath . 'classes/salts/class.tx_saltedpasswords_salts_phpass.php',
	'tx_saltedpasswords_salts' => $extensionPath . 'classes/salts/interfaces/interface.tx_saltedpasswords_salts.php',
	'tx_saltedpasswords_eval' => $extensionPath . 'classes/eval/class.tx_saltedpasswords_eval.php',
	'tx_saltedpasswords_eval_be' => $extensionPath . 'classes/eval/class.tx_saltedpasswords_eval_be.php',
	'tx_saltedpasswords_eval_fe' => $extensionPath . 'classes/eval/class.tx_saltedpasswords_eval_fe.php',
	'tx_saltedpasswords_sv1' => $extensionPath . 'sv1/class.tx_saltedpasswords_sv1.php',
	'tx_saltedpasswords_div_testcase' => $extensionPath . 'tests/tx_saltedpasswords_div_testcase.php',
	'tx_saltedpasswords_salts_blowfish_testcase' => $extensionPath . 'tests/tx_saltedpasswords_salts_blowfish_testcase.php',
	'tx_saltedpasswords_salts_factory_testcase' => $extensionPath . 'tests/tx_saltedpasswords_salts_factory_testcase.php',
	'tx_saltedpasswords_salts_md5_testcase' => $extensionPath . 'tests/tx_saltedpasswords_salts_md5_testcase.php',
	'tx_saltedpasswords_salts_phpass_testcase' => $extensionPath . 'tests/tx_saltedpasswords_salts_phpass_testcase.php',
);
?>