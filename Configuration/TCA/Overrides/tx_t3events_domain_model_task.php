<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';
$tmpColumns = [
	'deadline_period' => [
		'label' => $ll. 'label.deadlinePeriod',
		'config' => [
			'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['', ''],
                [$ll . 'label.deadlinePeriod.past', \DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface::PERIOD_PAST],
                [$ll . 'label.deadlinePeriod.future', \DWenzel\T3events\Domain\Repository\PeriodConstraintRepositoryInterface::PERIOD_FUTURE],
            ],
            'maxitems' => 1,
		],
	]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_t3events_domain_model_task',
	$tmpColumns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_task', 'deadline_period', '', 'before:period');
