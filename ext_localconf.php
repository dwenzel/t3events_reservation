<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
\CPSIT\T3eventsReservation\Configuration\ExtensionConfiguration::configurePlugins();

/** @noinspection PhpUnhandledExceptionInspection */
\CPSIT\T3eventsReservation\Configuration\ExtensionConfiguration::registerIcons();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['t3events_reservation'] = \CPSIT\T3eventsReservation\Hooks\ReservationDeleteHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'][\CPSIT\T3eventsReservation\Updates\OrphanPersonCleanupWizard::IDENTIFIER] = \CPSIT\T3eventsReservation\Updates\OrphanPersonCleanupWizard::class;

call_user_func(function() {

    // Add default routing
    $routeLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\DWenzel\T3events\Service\RouteLoader::class);
    $dataProviderClasses = [
        \CPSIT\T3eventsReservation\DataProvider\RouteLoader\ReservationControllerDefaults::class,
        \CPSIT\T3eventsReservation\DataProvider\RouteLoader\ParticipantControllerDefaults::class,
        \CPSIT\T3eventsReservation\DataProvider\RouteLoader\ContactControllerDefaults::class,
        \CPSIT\T3eventsReservation\DataProvider\RouteLoader\BillingAddressControllerDefaults::class,
    ];
    foreach ($dataProviderClasses as $providerClass) {
        $dataProvider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($providerClass);
        if ($dataProvider instanceof \DWenzel\T3events\DataProvider\RouteLoader\RouteLoaderDataProviderInterface) {
            $routeLoader->loadFromProvider($dataProvider);
        }
    }

    // connect slots to signals
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \CPSIT\T3eventsReservation\Controller\ReservationController::class,
        'handleEntityNotFoundError',
        \CPSIT\T3eventsReservation\Slot\ReservationControllerSlot::class,
        'handleEntityNotFoundSlot'
    );
});
