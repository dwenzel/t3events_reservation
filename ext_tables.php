<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    $versionNumber = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version);

    if (TYPO3_MODE === 'BE') {
        $pathParticipantIcon = 'EXT:t3events_reservation/Resources/Public/Icons/module_icon_participant.png';
        $pathReservationIcon = 'EXT:t3events_reservation/Resources/Public/Icons/list.svg';
        if ($versionNumber < 7000000) {
            $pathReservationIcon = 'EXT:t3events_reservation/Resources/Public/Icons/module_icon_reservation.png';
        }

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'CPSIT.t3events_reservation',
            'Events',
            'm1',
            '',
            [
                'Backend\Bookings' => 'list,reset',
            ],
            [
                'access' => 'user,group',
                'icon' => $pathReservationIcon,
                'labels' => 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_m1.xlf',
            ]
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'CPSIT.t3events_reservation',
            'Events',
            'm3',
            '',
            [
                'Backend\Participant' => 'list,reset',
            ],
            [
                'access' => 'user,group',
                'icon' => $pathParticipantIcon,
                'labels' => 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_m3.xlf',
            ]
        );
    }
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_t3eventsreservation_domain_model_reservation',
        'EXT:t3events_reservation/Resources/Private/Language/locallang_csh_tx_t3eventsreservation_domain_model_reservation.xlf'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3eventsreservation_domain_model_reservation');

    // add sprite icons
    if ($versionNumber < 7000000) {
        $relativePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3events_reservation');
        $icons = [
            'download-excel-white' => $relativePath . 'Resources/Public/Icons/icon_excel_white.png',
            'download-excel-blue' => $relativePath . 'Resources/Public/Icons/icon_excel_blue.png',
        ];
        \TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
            $icons,
            't3events_reservation'
        );
    } else {
        $icons = [
            'download-excel-white' => 'Resources/Public/Icons/icon_excel_white.png',
            'download-excel-blue' => 'Resources/Public/Icons/icon_excel_blue.png',
        ];
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        foreach ($icons as $identifier => $path) {
            $iconRegistry->registerIcon(
                $identifier,
                \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
                ['source' => 'EXT:t3events_reservation' . $path]
            );
        }
    }
});
