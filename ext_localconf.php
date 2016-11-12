<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'CPSIT.' . $_EXTKEY,
    'Pi1',
    [
        'Reservation' => 'new, show, create, edit, checkout, confirm, delete, newParticipant, createParticipant, removeParticipant,
		newBillingAddress,createBillingAddress,editBillingAddress,removeBillingAddress,update,error',
        'Participant' => 'edit,update,error',
        'Contact' => 'new,edit,create,update,error'
    ]
    ,
    // non-cacheable actions
    [
        'Reservation' => 'new, show, create, edit, checkout, confirm, delete, newParticipant, createParticipant, removeParticipant,
		newBillingAddress,createBillingAddress,editBillingAddress,removeBillingAddress,update,error',
        'Participant' => 'edit,update,error',
        'Contact' => 'new,edit,create,update,error'
    ]
);

// Register command controllers for Scheduler and CLI
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['tx_t3eventsreservation_CloseBooking'] = 'CPSIT\\T3eventsReservation\\Command\\CloseBookingCommandController';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['tx_t3eventsreservation_CleanUp'] = 'CPSIT\\T3eventsReservation\\Command\\CleanUpCommandController';

// Add default routing
/** @var DWenzel\T3events\Service\RouteLoader $routeLoader */
$routeLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\DWenzel\T3events\Service\RouteLoader::class);
$dataProviderClasses = [
    \CPSIT\T3eventsReservation\DataProvider\RouteLoader\ReservationControllerDefaults::class,
    \CPSIT\T3eventsReservation\DataProvider\RouteLoader\ParticipantControllerDefaults::class,
    \CPSIT\T3eventsReservation\DataProvider\RouteLoader\ContactControllerDefaults::class
];
foreach ($dataProviderClasses as $providerClass) {
    $dataProvider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($providerClass);
    $routeLoader->loadFromProvider($dataProvider);
}

// connect slots to signals
/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
    'CPSIT\\T3eventsReservation\\Controller\\ReservationController',
    'handleEntityNotFoundError',
    'CPSIT\\T3eventsReservation\\Slot\\ReservationControllerSlot',
    'handleEntityNotFoundSlot'
);
