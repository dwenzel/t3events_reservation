<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';
$tmpColumns = array(
	'reservation' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_t3events_domain_model_notification.reservation',
		'config' => array(
			'type' => 'passthrough',
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_t3events_domain_model_notification',
	$tmpColumns,
	TRUE
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_notification', 'reservation', '', 'after:subject');
