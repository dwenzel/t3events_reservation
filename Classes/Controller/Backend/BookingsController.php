<?php
namespace CPSIT\T3eventsReservation\Controller\Backend;

/***************************************************************
 *  Copyright notice
 *  (c) 2014 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *           Boerge Franck <franck@cps-it.de>, CPS IT
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

use CPSIT\T3eventsReservation\Controller\PersonRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationDemandFactoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use DWenzel\T3events\CallStaticTrait;
use DWenzel\T3events\Controller\AudienceRepositoryTrait;
use DWenzel\T3events\Controller\Backend\FormTrait;
use DWenzel\T3events\Controller\CategoryRepositoryTrait;
use DWenzel\T3events\Controller\CompanyRepositoryTrait;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\DownloadTrait;
use DWenzel\T3events\Controller\EventTypeRepositoryTrait;
use DWenzel\T3events\Controller\GenreRepositoryTrait;
use DWenzel\T3events\Controller\ModuleDataTrait;
use DWenzel\T3events\Controller\NotificationRepositoryTrait;
use DWenzel\T3events\Controller\NotificationServiceTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use DWenzel\T3events\Controller\VenueRepositoryTrait;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Mvc\Request;
use DWenzel\T3events\Controller\AbstractBackendController;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use DWenzel\T3events\Controller\FilterableControllerInterface;
use DWenzel\T3events\Controller\FilterableControllerTrait;

/**
 * Class BookingsController
 * @package CPSIT\T3eventsReservation\Controller\Backend
 */
class BookingsController extends AbstractBackendController
    implements FilterableControllerInterface
{
    use AudienceRepositoryTrait, CallStaticTrait, CategoryRepositoryTrait,
        CompanyRepositoryTrait, DemandTrait, DownloadTrait,
        EventTypeRepositoryTrait,  FilterableControllerTrait, FormTrait,
        GenreRepositoryTrait, ModuleDataTrait, NotificationRepositoryTrait,
        NotificationServiceTrait, PersistenceManagerTrait, SearchTrait,
        SettingsUtilityTrait, ReservationDemandFactoryTrait, ReservationRepositoryTrait,
        TranslateTrait, VenueRepositoryTrait;

    /**
     * @const Extension key
     */
    const EXTENSION_KEY =  't3events_reservation';

    /**
     * List action
     *
     * @param array $overwriteDemand
     * @return void
     */
    public function listAction(array $overwriteDemand = NULL)
    {
        /** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand $demand */
        $demand = $this->reservationDemandFactory->createFromSettings($this->settings);

        if ($overwriteDemand === NULL) {
            $overwriteDemand = $this->moduleData->getOverwriteDemand();
        } else {
            $this->moduleData->setOverwriteDemand($overwriteDemand);
        }

        $this->overwriteDemandObject($demand, $overwriteDemand);
        $this->moduleData->setDemand($demand);

        $reservations = $this->reservationRepository->findDemanded($demand);
        $this->view->assignMultiple(
            [
                'reservations' => $reservations,
                'overwriteDemand' => $overwriteDemand,
                'demand' => $demand,
                'filterOptions' => $this->getFilterOptions($this->settings['filter'])
            ]
        );
    }

    /**
     * Returns custom error flash messages, or
     * display no flash message at all on errors.
     *
     * @return string|boolean The flash message or FALSE if no flash message should be set
     * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected function getErrorFlashMessage()
    {
        $key = 'error' . '.' . $this->settingsUtility->getControllerKey($this) . '.'
            . str_replace('Action', '', $this->actionMethodName);
        $message = $this->translate($key);
        if ($message == null) {
            return FALSE;
        } else {
            return $message;
        }
    }
}
