<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';
$tmpColumns = array(
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
	'role' => [
		'label' => $ll . 'tx_t3events_domain_model_person.role',
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
	'vat_id' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3events_domain_model_person.vat_id',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		],
	],
	'accounting_office' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3events_domain_model_person.accounting_office',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		],
	],
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
	'tx_t3events_domain_model_person',
	'tx_extbase_type',
	[
		$ll . 'tx_t3events_domain_model_person.type.participant',
		\CPSIT\T3eventsReservation\Domain\Model\Person::PERSON_TYPE_PARTICIPANT
	]
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
	'tx_t3events_domain_model_person',
	'tx_extbase_type',
	[
		$ll . 'tx_t3events_domain_model_person.type.billingAddress',
		\CPSIT\T3eventsReservation\Domain\Model\BillingAddress::PERSON_TYPE_BILLING_ADDRESS
	]
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_t3events_domain_model_person',
	$tmpColumns,
	TRUE
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_person', 'reservation,birthplace,company_name,role', '', 'after:email');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_person', 'vat_id', '', 'after:company_name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_person', 'accounting_office', '', 'after:vat_id');
