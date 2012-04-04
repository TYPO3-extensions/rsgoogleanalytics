<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

	// Define the path to the static TS files
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Google Analytics (Legacy Setup)');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/general/', 'Google Analytics (General Setup)');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/traditional/', 'Google Analytics (Traditional Method)');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/asynchronous/', 'Google Analytics (Asynchronous Method)');
?>
