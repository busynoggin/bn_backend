<?php

class tx_bnbackend_lib {

	/**
	 * Removes all exclude fields as defined in the TCA. This does not currently handle FlexForm excludeFields (which are rare)
	 *
	 * @return void
	 */
	public static function removeExcludeFields() {
		foreach ($GLOBALS['TCA'] as $tableName => &$tableConfiguration) {
			t3lib_div::loadTCA($tableName);
			foreach ($tableConfiguration['columns'] as $columnName => $columnConfiguration) {
				if (array_key_exists('exclude', $columnConfiguration) && $columnConfiguration['exclude']) {
					unset($GLOBALS['TCA'][$tableName]['columns'][$columnName]['exclude']);
				}
			}
		}

		// Force FlexForm fields to be allowed
		if ($GLOBALS['BE_USER']) {
			if ($GLOBALS['BE_USER']->groupData['non_exclude_fields']) {
				$GLOBALS['BE_USER']->groupData['non_exclude_fields'] .= ',pages:tx_templavoila_flex';
			} else {
				$GLOBALS['BE_USER']->groupData['non_exclude_fields'] = 'pages:tx_templavoila_flex';
			}
		}
	}

	/**
	 * Hooks into group handling to load TSConfig Static Templates.
	 *
	 * @param array $params
	 * @param t3lib_userAuthGroup $parentObject
	 * @return void
	 */
	public static function includeStaticTSConfigForGroups($params, &$parentObject) {
		$user = $parentObject->user;

		// Force FlexForm fields to be allowed
		if ($parentObject->groupData['non_exclude_fields']) {
			$parentObject->groupData['non_exclude_fields'] .= ',pages:tx_templavoila_flex';
		} else {
			$parentObject->groupData['non_exclude_fields'] = 'pages:tx_templavoila_flex';
		}

		foreach ($parentObject->includeGroupArray as $groupId) {
			$groupRow = $parentObject->userGroups[$groupId];
			if ($groupRow['tx_bnbackend_tsconfig_files']) {
				$lastTSConfig = array_pop($parentObject->TSdataArray);

				$staticTSConfigFiles = t3lib_div::trimExplode(',', $groupRow['tx_bnbackend_tsconfig_files']);
				foreach((array) $staticTSConfigFiles as $staticTSConfigFile) {
					// If we're including site config, include corresponding base config.
					if (self::isPathWithinSiteStaticTSConfigPath($staticTSConfigFile) && self::hasBaseConfiguration($staticTSConfigFile)) {
						$staticTSConfigFileFromBase = self::getBaseConfiguration($staticTSConfigFile);
						$parentObject->TSdataArray[] = '<INCLUDE_TYPOSCRIPT: source="FILE:' . $staticTSConfigFileFromBase . '">';
					}

					$parentObject->TSdataArray[] = '<INCLUDE_TYPOSCRIPT: source="FILE:' . $staticTSConfigFile . '">';
				}

				$parentObject->TSdataArray[] = $lastTSConfig;
			}
		}
	}

	/**
	 * Builds the TCA items array, populated with TSconfig-based static templates.
	 *
	 * @param array $params
	 * @param t3lib_tceforms $parentObject
	 * @return array
	 */
	public static function getStaticTSConfigItemsForGroup(&$params, &$parentObject) {
		if (!is_array($params['items'])) {
			$params['items'] = array();
		}

		$baseItems = self::getStaticTSConfigItemsFromBase();
		$siteItems = self::getStaticTSConfigItemsFromSite();
		$mergedItems = $baseItems;
		foreach ((array) $siteItems['items'] as $key => $item) {
			$mergedItems['items'][] = $item;
		}

		usort($mergedItems['items'], function($a, $b) {
			return $a[0] > $b[0];
		});

		$params['items'] = $mergedItems['items'];
	}

	/**
	 * Gets the items array from the base configuration path.
	 *
	 * @return array
	 */
	protected static function getStaticTSConfigItemsFromBase() {
		$basePath = self::getBaseStaticTSConfigPath();
		return self::getStaticTSConfigItemsFromPath($basePath);
	}

	/**
	 * Gets the items array from the site configuration path
	 *
	 * @return array
	 */
	protected static function getStaticTSConfigItemsFromSite() {
		$sitePath = self::getSiteStaticTSConfigPath();
		return self::getStaticTSConfigItemsFromPath($sitePath);
	}

	/**
	 * Gets the items array from the specified path
	 *
	 * @param string $path
	 * @return array
	 */
	protected static function getStaticTSConfigItemsFromPath($path) {
		$configurationKey = 'Default';
		$pathToTSConfigFiles = $path . $configurationKey . '/Configuration/UserTSConfig/';
		$configurations = t3lib_div::getFilesInDir(PATH_site . $pathToTSConfigFiles, 'ts');
		foreach ((array) $configurations as $configurationFilename) {
			$itemArray = self::addStaticTSConfigFromPath($pathToTSConfigFiles, $configurationFilename, $configurationKey);
			if ($itemArray) {
				$params['items'][] = $itemArray;
			}
		}

		// Loop over all the folders within the configuration, typically extension-based names
		$pathToExtensions = $path . 'Extensions/';
		$configurationFolders = t3lib_div::get_dirs(PATH_site . $pathToExtensions);
		foreach ((array) $configurationFolders as $configurationFolderName) {
			// Within a folder, look for /UserTSConfig/*.ts and add it as a static template.
			$pathToTSConfigFiles = $pathToExtensions . $configurationFolderName . '/Configuration/UserTSConfig/';
			$configurations = t3lib_div::getFilesInDir(PATH_site . $pathToTSConfigFiles, 'ts');
			foreach ((array) $configurations as $configurationFilename) {
				$itemArray = self::addStaticTSConfigFromPath($pathToTSConfigFiles, $configurationFilename, $configurationFolderName);
				if ($itemArray) {
					$params['items'][] = $itemArray;
				}
			}
		}

		return $params;
	}

	/**
	 * Creates an item array from the given path and filename
	 *
	 * @param string $path
	 * @param string $filename
	 * @param string $configurationKey
	 * @return array
	 */
	protected static function addStaticTSConfigFromPath($path, $filename, $configurationKey) {
			$info = pathinfo($filename);
			$name = basename($filename,'.'.$info['extension']);
			$name = $configurationKey . '/' . trim($name);

			if (self::isPathWithinSiteStaticTSConfigPath($path)) {
				$name .= ' (Site)';
			} elseif (self::isPathWithinBaseStaticTSConfigPath($path)) {
				$name .= ' (Base)';
			}

			$itemArray = array($name, $path . $filename);
			return $itemArray;
	}

	/**
	 * Gets the static TSConfig path
	 *
	 * @return string
	 */
	protected static function getSiteStaticTSConfigPath() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bn_backend']);
		return $extConf['siteConfigurationPath'];
	}

	/**
	 * Checks if the given path is within the site static TSConfig path
	 *
	 * @param string $path
	 * @return boolean
	 */
	protected static function isPathWithinSiteStaticTSConfigPath($path) {
		$siteStaticTSConfigPath = self::getSiteStaticTSConfigPath();
		return (strstr($path, $siteStaticTSConfigPath) !== FALSE);
	}

	/**
	 * Gets the static TSConfig path
	 *
	 * @return string
	 */
	protected static function getBaseStaticTSConfigPath() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bn_backend']);
		return $extConf['baseConfigurationPath'];
	}

	/**
	 * Checks if the given path is within the base static TSConfig path
	 *
	 * @param string $path
	 * @return boolean
	 */
	protected static function isPathWithinBaseStaticTSConfigPath($path) {
		$baseStaticTSConfigPath = self::getBaseStaticTSConfigPath();
		return (strstr($path, $baseStaticTSConfigPath) !== FALSE);
	}

	/**
	 * Checks if the given path has a corresponding base configuration.
	 *
	 * @param string $staticTSConfigFile
	 * @return boolean
	 */
	protected static function hasBaseConfiguration($staticTSConfigFile) {
		return @is_file(PATH_site . self::getBaseConfiguration($staticTSConfigFile));
	}

	/**
	 * Gets the corresponding base configuration.
	 *
	 * @param string $staticTSConfigFile
	 * @return boolean
	 */
	protected static function getBaseConfiguration($staticTSConfigFile) {
		$siteStaticTSConfigPath = self::getSiteStaticTSConfigPath();
		$baseStaticTSConfigPath = self::getBaseStaticTSConfigPath();

		$baseFile = str_replace($siteStaticTSConfigPath, $baseStaticTSConfigPath, $staticTSConfigFile);
		return $baseFile;
	}
}

?>