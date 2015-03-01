<?php

if (TYPO3_MODE === 'BE') {
	\BusyNoggin\BnBackend\BackendLibrary::removeExcludeFields();
	\BusyNoggin\BnBackend\BackendLibrary::removeInlineFileUpload();
}
