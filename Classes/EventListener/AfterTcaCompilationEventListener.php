<?php

namespace BusyNoggin\BnBackend\EventListener;

use BusyNoggin\BnBackend\BackendLibrary;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

class AfterTcaCompilationEventListener
{
    #[AsEventListener]
    public function handleEvent(AfterTcaCompilationEvent $event): void
    {
        $event->setTca((new BackendLibrary())->postProcessTCA($event->getTca()));
    }
}
