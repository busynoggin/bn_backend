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
					unset($TCA[$tableName]['columns'][$columnName]['exclude']);
				}
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
		$groupId = end($parentObject->includeGroupArray);
		$groupRow = $parentObject->userGroups[$groupId];
		if ($groupRow['tx_bnbackend_tsconfig_files']) {
			$lastTSConfig = array_pop($parentObject->TSdataArray);

			$staticTSConfigFiles = t3lib_div::trimExplode(',', $groupRow['tx_bnbackend_tsconfig_files']);
			foreach($staticTSConfigFiles as $staticTSConfigFile) {
				$parentObject->TSdataArray[] = '<INCLUDE_TYPOSCRIPT: source="FILE:' . $staticTSConfigFile . '">';
			}

			$parentObject->TSdataArray[] = $lastTSConfig;
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
		$relativeConfigurationPath = self::getStaticTSConfigPath();
		$absoluteConfigurationPath = PATH_site . '/' . $relativeConfigurationPath;

		$configurations = t3lib_div::getFilesInDir($absoluteConfigurationPath, 'ts');
		foreach ($configurations as $configurationName) {
			$itemArray = self::addStaticTSConfigFromPath($relativeConfigurationPath, $configurationName);
			if ($itemArray) {
				$params['items'][] = $itemArray;
			}
		}

		return $params['items'];
	}

	/**
	 * Creates an item array from the given path and filename
	 *
	 * @param string $path
	 * @param string $filename
	 * @return array
	 */
	public static function addStaticTSConfigFromPath($path, $filename) {
			$info = pathinfo($filename);
			$name = basename($filename,'.'.$info['extension']);
			$name = preg_replace('/(?<=\\w)(?=[A-Z])/', ' $1', $name);
			$name = trim($name);

			$itemArray = array('Busy Noggin: ' . $name, $path . $filename);
			return $itemArray;
	}

	/**
	 * Gets the static TSConfig path
	 *
	 * @return string
	 */
	public static function getStaticTSConfigPath() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bn_backend']);
		return $extConf['tx_bnbackend_tsconfig_file_path'];
	}
}

class user_bnbackend_lib extends tx_bnbackend_lib {}

?>