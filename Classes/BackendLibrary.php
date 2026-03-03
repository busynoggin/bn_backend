<?php

namespace BusyNoggin\BnBackend;

class BackendLibrary
{
	public function postProcessTCA($tca)
    {
		$tca = self::removeExcludeFields($tca);
		$tca = self::removeInlineFileUpload($tca);

		return $tca;
	}

	/**
	 * Removes all exclude fields as defined in the TCA.
     * This does not currently handle FlexForm excludeFields (which are rare).
	 */
	private function removeExcludeFields(array $tca): array
    {
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
	 * Removes the inline file upload capability
	 */
	private function removeInlineFileUpload(array $tca): array
    {
		$tca['sys_file_reference']['columns']['uid_local']['config']['appearance']['fileUploadAllowed'] = 0;

		return $tca;
	}

	/**
	 * Call this method to add an entry in the userTSconfig list found in be_groups
	 *
	 * @param string $extKey The extension key
	 * @param string $file The path and title where the UserTSconfig file is located
	 * @param string $title The title in the selector box
	 */
	static public function registerUserTSConfigFile(string $extKey, string $file, string $title):void
    {
		if ($extKey && $file && is_array($GLOBALS['TCA']['be_groups']['columns'])) {
			$value = str_replace(',',  '', 'EXT:' . $extKey . '/' . $file);
			$itemArray = ['label' => trim($title . ' (' . $extKey . ')'), 'value' => $value];
			$GLOBALS['TCA']['be_groups']['columns']['tx_bnbackend_tsconfig_files']['config']['items'][] = $itemArray;
		}
	}
}
