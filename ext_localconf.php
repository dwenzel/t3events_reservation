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
		'Contact' => 'edit,update,error'
	]
	,
	// non-cacheable actions
	[
		'Reservation' => 'new, show, create, edit, checkout, confirm, delete, newParticipant, createParticipant, removeParticipant,
		newBillingAddress,createBillingAddress,editBillingAddress,removeBillingAddress,update,error',
        'Participant' => 'edit,update,error',
        'Contact' => 'edit,update,error'
	]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['tx_t3eventsreservation_CloseBooking'] = 'CPSIT\\T3eventsReservation\\Command\\CloseBookingCommandController';

// connect slots to signals
/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
    'CPSIT\\T3eventsReservation\\Controller\\ReservationController',
    'handleEntityNotFoundError',
    'CPSIT\\T3eventsReservation\\Slot\\ReservationControllerSlot',
    'handleEntityNotFoundSlot'
);
