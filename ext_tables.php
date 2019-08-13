<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE === 'BE') {
    \CPSIT\T3eventsReservation\Configuration\ExtensionConfiguration::registerAndConfigureModules();
}

\CPSIT\IhkofEvents\Configuration\ExtensionConfiguration::configureTables();
