<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// register frontend plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'CPSIT.' . $_EXTKEY,
	'Pi1',
	'Reservations'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Reservations');

$versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

if (TYPO3_MODE === 'BE') {
    $pathReservationIcon = 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/list.svg';
    if ($versionNumber < 7000000) {
        $pathReservationIcon = 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module_icon_reservation.png';
        $pathParticipantIcon = 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/module_icon_participant.png';
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'CPSIT.' . $_EXTKEY,
		'Events',
		'm1',
		'',
		[
			'Backend\Bookings' => 'list,reset',
		],
		[
			'access' => 'user,group',
			'icon' => $pathReservationIcon,
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_m1.xlf',
		]
	);

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'CPSIT.' . $_EXTKEY,
		'Events',
        'm3',
        '',
        [
			'Backend\Participant' => 'list,reset',
		],
		[
			'access' => 'user,group',
			'icon' => $pathParticipantIcon,
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_m3.xlf',
		]
	);
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_t3eventsreservation_domain_model_reservation',
	'EXT:t3events_reservation/Resources/Private/Language/locallang_csh_tx_t3eventsreservation_domain_model_reservation.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3eventsreservation_domain_model_reservation');

// add sprite icons
if ($versionNumber < 7000000) {
    $icons = [
        'download-excel-white' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/icon_excel_white.png',
        'download-excel-blue' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/icon_excel_blue.png',
    ];
    \TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
        $icons,
        $_EXTKEY
    );
}
if ($versionNumber >= 7000000) {
    $icons = [
        'download-excel-white' => 'Resources/Public/Icons/icon_excel_white.png',
        'download-excel-blue' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/icon_excel_blue.png',
    ];
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $identifier=>$path) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:' . $_EXTKEY . $path]
        );
    }
}unset($pathParticipantIcon, $pathReservationIcon, $path, $iconRegistry, $identifier, $icons);
