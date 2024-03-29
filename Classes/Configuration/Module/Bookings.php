<?php

namespace CPSIT\T3eventsReservation\Configuration\Module;

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

use CPSIT\T3eventsReservation\Controller\Backend\BookingsController;
use DWenzel\T3extensionTools\Configuration\ModuleRegistrationInterface;
use DWenzel\T3extensionTools\Configuration\ModuleRegistrationTrait;

abstract class Bookings extends DefaultRegistration implements ModuleRegistrationInterface
{
    use ModuleRegistrationTrait;

    protected static $subModuleName = 'm1';
    protected static $controllerActions = [
        BookingsController::class => 'list,reset',
    ];
    protected static $moduleConfiguration = [
        'access' => 'user,group',
        'icon' => 'EXT:t3events_reservation/Resources/Public/Icons/module_icon_reservation.svg',
        'labels' => 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_m1.xlf',
    ];
}
