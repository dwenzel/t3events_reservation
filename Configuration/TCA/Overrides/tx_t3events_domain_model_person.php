<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';
$tmpColumns = array(
	'tx_extbase_type' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_t3events_domain_model_person.type',
		'config' => array(
			'type' => 'select',
			'items' => array(
				array($ll . 'tx_t3events_domain_model_person.type.empty', 0),
				array($ll . 'tx_t3events_domain_model_person.type.contact', 1),
				array($ll . 'tx_t3events_domain_model_person.type.participant', 2),
			),
			'size' => 1,
			'maxitems' => 1,
			'eval' => ''
		),
	),
	'reservation' => array(
		'label' => 'LLL: Reservation',
		'config' => array(
			'type' => 'select',
			'foreign_table' => 'tx_t3eventsreservation_domain_model_reservation',
			'maxitems' => 1,
			'minitems' => 1,
			'readOnly' => 1
		),
	),
	'birthplace' => [
		'label' => $ll . 'tx_t3events_domain_model_person.birthplace',
		'config' => [
			'type' => 'input',
		]
	],
	'company_name' => [
		'label' => $ll . 'tx_t3events_domain_model_person.company_name',
		'config' => [
			'type' => 'input',
		]
	],
	'role' => [
		'label' => $ll . 'tx_t3events_domain_model_person.role',
		'config' => [
			'type' => 'input',
		]
	],
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_t3events_domain_model_person',
	$tmpColumns,
	TRUE
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_person', 'tx_extbase_type', '', 'before:person_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_person', 'reservation,birthplace,company_name,role', '', 'after:email');
