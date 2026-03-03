<?php

namespace BusyNoggin\BnBackend\EventListener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\Event\AfterGroupsResolvedEvent;

class AfterGroupsResolvedEventListener
{
    #[AsEventListener]
    public function handleEvent(AfterGroupsResolvedEvent $event): void
    {
        $groups = $event->getGroups();

        foreach ($groups as &$groupRow) {
            if (!empty($groupRow['tx_bnbackend_tsconfig_files'])) {
                $staticTSConfigFiles = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $groupRow['tx_bnbackend_tsconfig_files']);

                foreach ($staticTSConfigFiles as $staticTSConfigFile) {
                    $groupRow['TSconfig'] .= PHP_EOL . '@import "' . $staticTSConfigFile . '"';
                }

            }
        }

        $event->setGroups($groups);
    }
}
