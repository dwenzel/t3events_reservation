<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// register frontend plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'CPSIT.t3events_reservation',
    'Pi1',
    'Reservations'
);
