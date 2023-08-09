<?php

namespace CPSIT\T3eventsReservation\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Dirk Wenzel <wenzel@cps-it.de>
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use CPSIT\T3eventsReservation\Configuration\Module\Bookings;
use CPSIT\T3eventsReservation\Configuration\Module\Participant;
use CPSIT\T3eventsReservation\Configuration\Plugin\Combined;
use CPSIT\T3eventsReservation\Utility\SettingsInterface;

class ExtensionConfiguration extends \DWenzel\T3extensionTools\Configuration\ExtensionConfiguration
{
    final public const EXTENSION_KEY = 't3events_reservation';
    final public const VENDOR = 'CPSIT';

    protected const MODULES_TO_REGISTER = [
        Bookings::class,
        Participant::class
    ];

    protected const PLUGINS_TO_REGISTER = [
        Combined::class
    ];

    final public const TABLES_ALLOWED_ON_STANDARD_PAGES = [
        'tx_t3eventsreservation_domain_model_reservation'
    ];

    final public const LOCALIZED_TABLE_DESCRIPTION = [
        'tx_t3eventsreservation_domain_model_reservation' => 'EXT:t3events_reservation/Resources/Private/Language/locallang_csh_tx_t3eventsreservation_domain_model_reservation.xlf'
    ];

    final public const BITMAP_ICONS_TO_REGISTER = [
        'download-excel-white' => 'EXT:t3events_reservation/Resources/Public/Icons/icon_excel_white.png',
        'download-excel-blue' => 'EXT:t3events_reservation/Resources/Public/Icons/icon_excel_blue.png',
    ];
}
