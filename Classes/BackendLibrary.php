<?php

namespace BusyNoggin\BnBackend;

class BackendLibrary {

	public function postProcessTCA($tca) {
		$tca = $this->removeExcludeFields($tca);
		$tca = $this->removeInlineFileUpload($tca);

		return array($tca);
	}

	/**
	 * Removes all exclude fields as defined in the TCA. This does not currently handle FlexForm excludeFields (which are rare)
	 *
	 * @param array
	 * @return void
	 */
	public static function removeExcludeFields(array $tca) {
		foreach ($tca as $tableName => &$tableConfiguration) {
			if (is_array($tableConfiguration['columns'])) {
				foreach ($tableConfiguration['columns'] as $columnName => $columnConfiguration) {
					if (array_key_exists('exclude', $columnConfiguration) && $columnConfiguration['exclude']) {
						unset($tca[$tableName]['columns'][$columnName]['exclude']);
					}
				}
			}
		}

		return $tca;
	}

	/**
	 * Removes the inline file upload capabilityl
	 *
	 * @param array
	 * @return void
	 */
	public static function removeInlineFileUpload(array $tca) {
		$tca['sys_file_reference']['columns']['uid_local']['config']['appearance']['fileUploadAllowed'] = 0;

		return $tca;
	}

	/**
	 * Call this method to add an entry in the userTSconfig list found in be_groups
	 *
	 * @param string $extKey The extension key
	 * @param string $file The path and title where the UserTSconfig file is located
	 * @param string $title The title in the selector box
	 * @return void
	 */
	static public function registerUserTSConfigFile($extKey, $file, $title) {
		if ($extKey && $file && is_array($GLOBALS['TCA']['be_groups']['columns'])) {
			$value = str_replace(',',  '', 'EXT:' . $extKey . '/' . $file);
			$itemArray = array(trim($title . ' (' . $extKey . ')'), $value);
			$GLOBALS['TCA']['be_groups']['columns']['tx_bnbackend_tsconfig_files']['config']['items'][] = $itemArray;
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

		foreach ($parentObject->includeGroupArray as $groupId) {
			$groupRow = $parentObject->userGroups[$groupId];
			if ($groupRow['tx_bnbackend_tsconfig_files']) {
				$lastTSConfig = array_pop($parentObject->TSdataArray);

				$staticTSConfigFiles = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $groupRow['tx_bnbackend_tsconfig_files']);
				foreach((array) $staticTSConfigFiles as $staticTSConfigFile) {
					$parentObject->TSdataArray[] = '<INCLUDE_TYPOSCRIPT: source="FILE:' . $staticTSConfigFile . '">';
				}

				$parentObject->TSdataArray[] = $lastTSConfig;
			}
		}
	}

}