<?php

if (TYPO3_MODE === 'BE') {
	// Hook into group processing to include possible Static TSConfig
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['fetchGroups_postProcessing'][] = \BusyNoggin\BnBackend\BackendLibrary::class . '->includeStaticTSConfigForGroups';

	// Hide Allowed Exclude Fields within group record
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('TCEFORM.be_groups.non_exclude_fields.disabled = 1');


	// Process TCA from modern extensions via slot
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
	$signalSlotDispatcher->connect(
		TYPO3\CMS\Core\Utility\ExtensionManagementUtility::class,
		'tcaIsBeingBuilt',
		\BusyNoggin\BnBackend\BackendLibrary::class,
		'postProcessTCA'
	);

	// Process TCA from legacy extensions via hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = \BusyNoggin\BnBackend\RemoveExcludeFieldsHook::class;

}