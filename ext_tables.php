<?php

if (TYPO3_MODE === 'BE') {
	\BusyNoggin\BnBackend\BackendLibrary::removeExcludeFields();
	\BusyNoggin\BnBackend\BackendLibrary::removeInlineFileUpload();

	// Define the TCA for the static TS Config selector
	$staticTSConfigSelector = array(
		'tx_bnbackend_tsconfig_files' => array(
			'label' => 'LLL:EXT:bn_backend/locallang_db.php:be_groups.tx_bnbackend_tsconfig_files',
			'config' => array(
				'type' => 'select',
				'size' => 5,
				'maxitems' => 100,
				'itemsProcFunc' => 'BusyNoggin\\BnBackend\\BackendLibrary->getStaticTSConfigItemsForGroup'
			)
		),
	);

	if (version_compare(TYPO3_branch, '6.1', '<')) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('be_groups');
	}
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $staticTSConfigSelector);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'tx_bnbackend_tsconfig_files', '', 'before:TSconfig');
}

?>