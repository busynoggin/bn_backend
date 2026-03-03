<?php

namespace BusyNoggin\BnBackend\EventListener;

use BusyNoggin\BnBackend\BackendLibrary;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;

class BootCompletedEventListener
{
    #[AsEventListener]
    public function handleEvent(BootCompletedEvent $event): void
    {
        $GLOBALS['TCA'] = (new BackendLibrary())->postProcessTCA($GLOBALS['TCA']);
    }
}
