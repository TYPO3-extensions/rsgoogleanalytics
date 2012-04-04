<?php

########################################################################
# Extension Manager/Repository config file for ext "rsgoogleanalytics".
#
# Auto generated 19-03-2011 11:45
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
	'version' => '2.0.0-dev',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
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
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.4.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:6:{s:30:"class.tx_rsgoogleanalytics.php";s:4:"8eb8";s:15:"codeTemplate.js";s:4:"127a";s:12:"ext_icon.gif";s:4:"18fd";s:17:"ext_localconf.php";s:4:"0064";s:14:"ext_tables.php";s:4:"db57";s:16:"static/setup.txt";s:4:"a793";}',
	'suggests' => array(
	),
);

?>