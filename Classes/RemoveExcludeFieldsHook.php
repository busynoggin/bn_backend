<?php
namespace BusyNoggin\BnBackend;

class RemoveExcludeFieldsHook implements \TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface {

	/**
	 * Removes excludeFields after extTables processing.
	 *
	 * @return void
	 */
	public function processData() {
		$GLOBALS['TCA'] = \BusyNoggin\BnBackend\BackendLibrary::removeExcludeFields($GLOBALS['TCA']);
	}

}