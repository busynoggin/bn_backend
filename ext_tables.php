<?php

if (TYPO3_MODE === 'BE') {
	tx_bnbackend_lib::removeExcludeFields();

	// Define the TCA for the static TS Config selector
	$staticTSConfigSelector = array(
		'tx_bnbackend_tsconfig_files' => array(
			'label' => 'LLL:EXT:bn_backend/locallang_db.php:be_groups.tx_bnbackend_tsconfig_files',
			'config' => array(
				'type' => 'select',
				'size' => 5,
				'maxitems' => 100,
				'itemsProcFunc' => 'tx_bnbackend_lib->getStaticTSConfigItemsForGroup'
			)
		),
	);

	// Add the skin selector for backend users.
	t3lib_div::loadTCA('be_groups');
	t3lib_extMgm::addTCAcolumns('be_groups', $staticTSConfigSelector);
	t3lib_extMgm::addToAllTCAtypes('be_groups', 'tx_bnbackend_tsconfig_files', '', 'before:TSconfig');
}

?>