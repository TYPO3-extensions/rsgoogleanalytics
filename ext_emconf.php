<?php

########################################################################
# Extension Manager/Repository config file for ext "rsgoogleanalytics".
#
# Auto generated 24-06-2012 12:38
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Google Analytics',
	'description' => 'Supports full options of Google Analytics with new ga.js (within Domains, FilesLinks, External URLs e.g.)',
	'category' => 'RS WebSystems',
	'shy' => 0,
	'version' => '3.0.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Steffen Ritter',
	'author_email' => 'info@rs-websystems.de',
	'author_company' => 'RS WebSystems',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:9:"ChangeLog";s:4:"564c";s:30:"class.tx_rsgoogleanalytics.php";s:4:"a675";s:12:"ext_icon.gif";s:4:"18fd";s:17:"ext_localconf.php";s:4:"0064";s:14:"ext_tables.php";s:4:"0d4f";s:13:"locallang.xml";s:4:"9db3";s:14:"doc/manual.pdf";s:4:"1b06";s:14:"doc/manual.sxw";s:4:"3b26";s:33:"res/templates/codeAsynchronous.js";s:4:"e6eb";s:32:"res/templates/codeTraditional.js";s:4:"127a";s:16:"static/setup.txt";s:4:"5a95";s:29:"static/asynchronous/setup.txt";s:4:"e921";s:28:"static/general/constants.txt";s:4:"d62e";s:24:"static/general/setup.txt";s:4:"a3aa";s:28:"static/traditional/setup.txt";s:4:"f2dd";}',
	'suggests' => array(
	),
);

?>