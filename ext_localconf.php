<?php

if (TYPO3_MODE === 'BE') {
	// Hook into group processing to include possible Static TSConfig
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['fetchGroups_postProcessing'][] = 'BusyNoggin\\BnBackend\\BackendLibrary->includeStaticTSConfigForGroups';

	// Hide Allowed Exclude Fields within group record
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('TCEFORM.be_groups.non_exclude_fields.disabled = 1');
}

?>