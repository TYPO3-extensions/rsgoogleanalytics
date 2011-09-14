<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005-2011	 Steffen Ritter (info@rs-websystems.de)
 *
 *  All rights reserved
 *
 *  This script is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; version 2 of the License.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/*
 * Inspired by m1_google_analytics, using ga.js
 *
 * @author	Steffen Ritter
 */
class tx_rsgoogleanalytics implements t3lib_singleton {
	/**
	 * @var string
	 */
	var $trackerVar = 'pageTracker';

	/**
	 * Saves TypoScript config
	 */
	var $modConfig = array();

	/**
	@var array
	 */
	protected $commands = array();

	/**
	 * @var array
	 */
	protected $domainConfig = array();

	/**
	 * @var array
	 */
	protected $eCommerce = array('items' => array(), 'transaction' => array());

	/**
	 * constructs the system.
	 */
	public function __construct() {
		$this->modConfig = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_rsgoogleanalytics.'];

		if (t3lib_extmgm::isLoaded('naw_securedl')) {
			$this->specialFiles = 'naw';
		}
		if (t3lib_extmgm::isLoaded('dam_frontend')) {
			$this->specialFiles = 'dam_frontend';
		}
	}

	/**
	 * adds the tracking code at the end of the body tag (pi Method called from TS USER_INT). further the method
	 * adds some js code for downloads and external links if configured.
	 *
	 * @param string $content page content
	 * @param array $params Additional call parameters (unused for now)
	 * @return string Page content with google tracking code
	 */
	public function processTrackingCode($content, $params) {
			// return if the extension is not activated or no account is configured
		if (!$this->isActive()) {
			return content;
		}
			// detect how the pageTitle should be rendered
		if ($this->modConfig['registerTitle'] == 'title') {
			$pageName = '\'' . $GLOBALS['TSFE']->page['title'] . '\'';
		} else if ($this->modConfig['registerTitle'] == 'rootline') {
			$rootline = $GLOBALS['TSFE']->sys_page->getRootLine($GLOBALS['TSFE']->page['uid']);
			$pageName = '\'';
			for ($i = 0; $i < count($rootline); $i++) {
				if ($rootline[$i]['is_siteroot'] == 0) {
					$pageName .= '/' . addslashes($rootline[$i]['title']);
				}
			}
			$pageName .= '\'';
		} else {
			$pageName = NULL;
		}
		return $this->buildTrackingCode($pageName);
	}

	/**
	 * This method generates the google tracking code (JS script at the end of the body tag).
	 *
	 * @param string $pageName Name of the page to register for tracking
	 * @return string JS tracking code
	 */
	protected function buildTrackingCode($pageName = NULL) {
		$codeTemplate = file_get_contents(t3lib_div::getFileAbsFileName('EXT:rsgoogleanalytics/codeTemplate.js'));
		$marker = array(
			'ACCOUNT' => $this->modConfig['account'],
			'TRACKER_VAR' => $this->trackerVar,
			'COMMANDS' => ''
		);

		if ($pageName === NULL) {
			$this->commands[999] = $this->buildCommand('trackPageview', array());
		} else {
			$this->commands[999] = $this->buildCommand('trackPageview', array($pageName));
		}

		$this->makeDomainConfiguration();
		$this->makeSearchEngineConfiguration(); // 100
		$this->makeSpecialVars(); // 300
		$this->makeDataTracking(); // 500
		$this->makeECommerceTracking(); // 2000

		ksort($this->commands);
		$marker['COMMANDS'] = implode("\n", $this->commands);
		$code = t3lib_parsehtml::substituteMarkerArray($codeTemplate, $marker, '###|###', true, true);

		return $code;
	}

	/**
	 * Generates Commands which are needed for sub/cross-domain-tracking.
	 * linkProcessing needs this to handle the domains, which should get a "link" tracker
	 *
	 * @return void
	 */
	protected function makeDomainConfiguration() {
		if (count($this->domainConfig) == 0) {
			if ($this->modConfig['multipleDomains'] && $this->modConfig['multipleDomains'] != 'false') {
				$this->domainConfig['multiple'] = t3lib_div::trimExplode(',', $this->modConfig['multipleDomains.']['domainNames'], 1);
				$this->commands[10] = $this->buildCommand("setDomainName", array("none"));
				$this->commands[11] = $this->buildCommand("setAllowLinker", array("enable"));
				$this->commands[12] = $this->buildCommand("setAllowHash", array(false));

			} else if ($this->modConfig['trackSubDomains'] && $this->modConfig['trackSubDomains'] != 'false') {
				$this->commands[10] = $this->buildCommand("setDomainName", array("." . $this->modConfig['trackSubDomains.']['domainName']));
				$this->commands[12] = $this->buildCommand("setAllowHash", array(false));
			}
		}
	}

	/**
	 * Generates Commands for tracking custom variables
	 *
	 * @return void
	 */
	protected function makeSpecialVars() {
		$x = 300;
		$cObj = t3lib_div::makeInstance('tslib_cObj');

			// render CustomVars
		for ($i = 1; $i <= 5; $i++) {
			if (is_array($this->modConfig['customVars.'][$i . '.'])) {
				$data = $cObj->stdWrap('', $this->modConfig['customVars.'][$i . '.']);
				if (trim($data)) {
					$this->commands[$x] = $this->buildCommand(
						'setCustomVar',
						array(
							$i, $this->modConfig['customVars.'][$i . '.']['name'],
							$data, $this->modConfig['customVars.'][$i . '.']['scope']
						)
					);
				}
				$x++;
			}
		}

		// render customSegment
		$currentValue = explode('.', $_COOKIE['__utmv']);
		$currentValue = $currentValue[1];
		$shouldBe = $cObj->stdWrap('', $this->modConfig['visitorSegment.']);
		if ($currentValue != $shouldBe && trim($shouldBe) !== '') {
			$this->commands[$x] = $this->buildCommand('setVar', array($shouldBe));
		}
	}

	/**
	 * Generates Commands for e-commerce tracking
	 * @return
	 */
	protected function makeECommerceTracking() {
		if (!$this->modConfig['eCommerce.']['enableTracking']) return;
		$i = 2000; // Should be after trackPageView()
		foreach ($this->eCommerce['transaction'] AS $trans) {
			$this->commands[$i] = $this->buildCommand('addTrans', $trans);
			$i++;
		}
		foreach ($this->eCommerce['items'] AS $item) {
			$this->commands[$i] = $this->buildCommand('addItem', $item);
			$i++;
		}

		if (count($this->eCommerce['transaction']) > 0 || count($this->eCommerce['items']) > 0) {
			$this->commands[$i] = $this->buildCommand('trackTrans', array());
		}
	}

	/**
	 * Generates Commands related to search engine configuration
	 * @return void
	 */
	protected function makeSearchEngineConfiguration() {
			// Set keywords which should marked as redirect
		if ($this->modConfig['redirectKeywords']) {
			$keywords = t3lib_div::trimExplode(',', $this->modConfig['redirectKeywords'], 1);
			$i = 100;
			foreach ($keywords AS $val) {
				$this->commands[$i] = $this->buildCommand('addIgnoredOrganic', array($val));
				$i++;
			}
		}
			// which referrers should be handled as "own domain"
		if ($this->modConfig['redirectReferer']) {
			$domains = t3lib_div::trimExplode(',', $this->modConfig['redirectReferer'], 1);
			foreach ($domains AS $val) {
				$this->commands[$i] = $this->buildCommand('addIgnoredRef', array($val));
				$i++;
			}
		}
	}

	/**
	 * Generates Commands for data tracking
	 *
	 * @return void
	 */
	protected function makeDataTracking() {
		if ($this->modConfig['disableDataTracking.']['browserInfo']) {
			$this->commands[500] = $this->buildCommand('setClientInfo', array(false));
		}
		if ($this->modConfig['disableDataTracking.']['flashTest']) {
			$this->commands[501] = $this->buildCommand('setDetectFlash', array(false));
		}
		if ($this->modConfig['disableDataTracking.']['pageTitle']) {
			$this->commands[502] = $this->buildCommand('setDetectTitle', array(false));
		}
		if ($this->modConfig['disableDataTracking.']['anonymizeIp']) {
			$this->commands[503] = $this->buildCommand('anonymizeIp', array());
		}
	}

	/**
	 * Assembles a single tracker command
	 *
	 * @param string $command The name of the command
	 * @param array $parameter The list of call parameters
	 * @return string The assemble JavaScript command
	 */
	protected function buildCommand($command, array $parameter) {
		return "\t" . $this->trackerVar . '._' . $command . '(' . implode(',', $this->wrapJSParams($parameter)) . ');';
	}

	/**
	 * Wraps and escapes a list of parameters for proper usage in JavaScript
	 *
	 * @param array $parameter List of parameters to handle
	 * @return array The wrapped and escaped parameters
	 */
	protected function wrapJSParams(array $parameter) {
		for ($i = 0; $i < count($parameter); $i++) {
			if (!is_bool($parameter[$i]) && !is_numeric($parameter[$i])) {
				$parameter[$i] = '"' . str_replace('"', '\"', $parameter[$i]) . '"';
			} else if (is_bool($parameter[$i])) {
				$parameter[$i] = ($parameter[$i] ? 'true' : 'false');
			}
		}
		return $parameter;
	}

	/**
	 * This method checks whether the URL is in the list to track
	 *
	 * @param string $url filename (with directories from site root) which is linked
	 * @return boolean True if filename is in locations, false if not
	 */
	protected function checkURL($url) {
		$locations = t3lib_div::trimExplode(',', $this->modConfig['trackExternals.']['domainList'], 1);
		foreach ($locations as $location) {
			if (strpos($url, $location) !== false) return true;
		}
		return false;
	}

	/**
	 * This method checks whether the given file is in the paths to track
	 *
	 * @param string $file Filename (with directories from site root) which is linked
	 * @return boolean True if filename is in locations, false if not
	 */
	protected function checkFilePath($file) {
		$locations = t3lib_div::trimExplode(',', $this->modConfig['trackDownloads.']['folderList']);
		foreach ($locations as $location) {
			if (strpos($file, $location) !== false) return true;
		}
		return false;
	}

	/**
	 * This method checks whether the given file if of a type to track
	 *
	 * @param string $file Filename (with directories from site root) which is linked
	 * @return boolean True if filename is in list, false if not
	 */
	protected function checkFileType($file) {
		$pathParts = pathinfo($file);
		return t3lib_div::inList($this->modConfig['trackDownloads.']['fileTypes'], $pathParts['extension']);
	}

	/**
	 * Checks whether filePath and Type is in allowed range
	 *
	 * @param string $file filename (with directories from site root) which is linked
	 * @return boolean True if filename is in locations and file type should be tracked, false if not
	 */
	protected function checkFile(&$file) {
		return $this->checkFilePath($file) && $this->checkFileType($file);

	}

	/**
	 * Hooks into TYPOLink generation
	 * Classic userFunc hook called in tslib/tslib_content.php
	 * Used to add Google Analytics tracking code to hyperlinks
	 *
	 * @param array $params TypoLink configuration
	 * @param tslib_cObj $reference Back-reference to the calling object
	 * @return void
	 */
	function linkPostProcess(&$params, $reference) {
		if (!$this->isActive()) return;
		$this->makeDomainConfiguration();

		$function = FALSE;
		switch ($params['finalTagParts']['TYPE']) {
			case 'page':
				// do nothing on normal links
				break;
			case 'url' :
				$url = $params['finalTagParts']['url'];
				if ( /*checkInMultiple($url)*/
					0) {
					$function = $this->buildCommand('link', array($url)) . 'return false;';
				} elseif ($this->modConfig['trackExternals'] && ($this->checkURL($url) || $this->modConfig['trackExternals'] == '!ALL')) {
					$function = $this->buildCommand('trackEvent', array('Leaving Site', 'External URL', $url));
				}
				break;
			case 'file':
				if ($this->modConfig['trackDownloads']) {
					$fileName = $params['finalTagParts']['url'];
					$file = t3lib_div::getFileAbsFileName($fileName);
					$fileInfo = pathinfo($file);
					// TODO: provide hook where downloader extension can register there transformation function

					if ($this->checkFile($fileName) || $this->modConfig['trackDownloads'] == '!ALL') {
						$function = $this->buildCommand('trackEvent', array('Download', $fileInfo['extension'], $fileName));
					}
				}
				break;
		}
		if (!stripos('onclick', $params['finalTagParts']['aTagParams']) && $function !== FALSE) {
			$function = str_replace('"', '\'', trim($function));
			$params['finalTagParts']['aTagParams'] .= ' onclick="' . $function . '"';
			$params['finalTag'] = str_replace('>', ' onclick="' . $function . '">', $params['finalTag']);
		}
	}

	/**
	 * adds an single Item to an eCommerce Transaction to be tracked
	 *
	 * @param string $orderId
	 * @param string $sku
	 * @param string $name
	 * @param string $category
	 * @param string $price
	 * @param string $quantity
	 * @return void
	 */
	public function addCommerceItem($orderId, $sku, $name, $category, $price, $quantity) {
		if (!$this->isActive() || !$this->modConfig['eCommerce.']['enableTracking']) return;

		if (isset($this->eCommerce['transaction'][$orderId])) {
			$this->eCommerce['items'][] = array(0 => $orderId, 1 => $sku, 2 => $name, 3 => $category, 4 => $price, 5 => $quantity);
		}
	}

	/**
	 * adds an e-commerce transaction to be tracked
	 *
	 * @param string $orderId
	 * @param string $storeName
	 * @param string $total
	 * @param string $tax
	 * @param string $shipping
	 * @param string $city
	 * @param string $state
	 * @param string $country
	 * @return void
	 */
	public function addCommerceTransaction($orderId, $storeName, $total, $tax, $shipping, $city, $state, $country) {
		if (!$this->isActive() || !$this->modConfig['eCommerce.']['enableTracking']) return;

		$this->eCommerce['transaction'][$orderId] = array(0 => $orderId, 1 => $storeName, 2 => $total, 3 => $tax, 4 => $shipping, 5 => $city, 6 => $state, 7 => $country);
	}

	/**
	 * Checks whether the plugin is active
	 *
	 * @return bool
	 */
	protected function isActive() {
		return intval($this->modConfig['active']) == 1 && trim($this->modConfig['account']) != '';
	}
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rsgoogleanalytics/class.tx_rsgoogleanalytics.php"]) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rsgoogleanalytics/class.tx_rsgoogleanalytics.php"]);
}

?>