<?php
defined('TYPO3_MODE') or die();

// Define the TCA for the static TS Config selector
$staticTSConfigSelector = array(
	'tx_bnbackend_tsconfig_files' => array(
		'label' => 'LLL:EXT:bn_backend/locallang_db.xlf:be_groups.tx_bnbackend_tsconfig_files',
		'config' => array(
			'type' => 'select',
			'size' => 5,
			'maxitems' => 100,
			'itemsProcFunc' => 'BusyNoggin\\BnBackend\\BackendLibrary->getStaticTSConfigItemsForGroup'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $staticTSConfigSelector);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'tx_bnbackend_tsconfig_files', '', 'before:TSconfig');