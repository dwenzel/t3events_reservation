<?php
namespace CPSIT\T3eventsReservation\Slot;

use CPSIT\T3eventsReservation\Controller\ReservationController;
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use DWenzel\T3events\Session\Typo3Session;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class ReservationControllerSlot implements SingletonInterface
{
    /**
     * @var \DWenzel\T3events\Session\SessionInterface
     */
    protected $session;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Injects the session and sets its namespace ReservationController::SESSION_NAME_SPACE
     *
     * @param \DWenzel\T3events\Session\Typo3Session $typo3Session
     */
    public function injectSession(Typo3Session $typo3Session)
    {
        $this->session = $typo3Session;
        $this->session->setNamespace(ReservationController::SESSION_NAME_SPACE);
    }

    /**
     * Injects the objectManager
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Slot method for CPSIT\T3eventsReservation\ReservationController signal 'handleEntityNotFoundError'
     *
     * @param array $params
     * @return array
     */
    public function handleEntityNotFoundSlot(array $params)
    {
        if (
            isset($params[SettingsInterface::CONFIG])
            && is_array($params[SettingsInterface::CONFIG])
        ) {
            $handler = $params[SettingsInterface::CONFIG][0];
            $actionName = $params[SettingsInterface::CONFIG][1];
            $params[$handler]['actionName'] = $actionName;
            if ($handler === SettingsInterface::REDIRECT) {
                $params[$handler]['statusCode'] = 302;
            }
            if ($this->session->has(ReservationController::SESSION_IDENTIFIER_RESERVATION)) {
                $reservationId = (string)$this->session->get(ReservationController::SESSION_IDENTIFIER_RESERVATION);
                $params[$handler]['arguments'] = [
                    SettingsInterface::RESERVATION => $reservationId
                ];
            }
            if (isset($params['requestArguments'][SettingsInterface::RESERVATION])) {
                $params[$handler]['controllerName'] = 'Reservation';
                $params[$handler]['arguments'][SettingsInterface::RESERVATION] = $params['requestArguments'][SettingsInterface::RESERVATION];
            }
        }

        return [$params];
    }

}
