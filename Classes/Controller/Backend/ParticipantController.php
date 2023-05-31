<?php
namespace CPSIT\T3eventsReservation\Controller\Backend;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Controller\ParticipantDemandFactoryTrait;
use CPSIT\T3eventsReservation\Controller\PersonRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use DWenzel\T3events\CallStaticTrait;
use DWenzel\T3events\Controller\AbstractBackendController;
use DWenzel\T3events\Controller\AudienceRepositoryTrait;
use DWenzel\T3events\Controller\Backend\FormTrait;
use DWenzel\T3events\Controller\CategoryRepositoryTrait;
use DWenzel\T3events\Controller\CompanyRepositoryTrait;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\DownloadTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\EventTypeRepositoryTrait;
use DWenzel\T3events\Controller\GenreRepositoryTrait;
use DWenzel\T3events\Controller\ModuleDataTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use DWenzel\T3events\Controller\VenueRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use DWenzel\T3events\Controller\FilterableControllerInterface;
use DWenzel\T3events\Controller\FilterableControllerTrait;

/**
 * Class ParticipantController
 *
 * @package CPSIT\T3eventsReservation\Controller\Backend
 */
class ParticipantController extends AbstractBackendController
    implements FilterableControllerInterface
{
    use
        AudienceRepositoryTrait, CallStaticTrait, CategoryRepositoryTrait,
        CompanyRepositoryTrait, DemandTrait, DownloadTrait,
        EntityNotFoundHandlerTrait, EventTypeRepositoryTrait, FormTrait,
        FilterableControllerTrait, GenreRepositoryTrait, ModuleDataTrait,
        PersonRepositoryTrait, ParticipantDemandFactoryTrait,
        ReservationRepositoryTrait, SearchTrait, SettingsUtilityTrait,
        TranslateTrait, VenueRepositoryTrait;

    /**
     * @const Extension key
     */
    final public const EXTENSION_KEY =  't3events_reservation';

    /**
     * @var string
     */
    protected $errorMessage = 'unknownError';

    /**
     * List action
     *
     * @return void
     */
    public function listAction(array $overwriteDemand = null)
    {
        $demand = $this->demandFactory->createFromSettings($this->settings);
        $filterOptions = $this->getFilterOptions($this->settings['filter']);

        if ($overwriteDemand === null) {
            $overwriteDemand = $this->moduleData->getOverwriteDemand();
        } else {
            $this->moduleData->setOverwriteDemand($overwriteDemand);
        }

        $this->overwriteDemandObject($demand, $overwriteDemand);
        $this->moduleData->setDemand($demand);

        $participants = $this->personRepository->findDemanded($demand);

        $this->view->assignMultiple(
            [
                'participants' => $participants,
                'overwriteDemand' => $overwriteDemand,
                'demand' => $demand,
                'filterOptions' => $filterOptions
            ]
        );
    }

    /**
     * Download action
     *
     * @param \CPSIT\T3eventsReservation\Domain\Model\Schedule $schedule
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("schedule")
     * @param string $ext File extension for download
     * @return string
     * @throws \DWenzel\T3events\InvalidFileTypeException
     */
    public function downloadAction($schedule = null, $ext = 'csv')
    {
        $objectForFileName = null;
        if (is_null($schedule)) {
            $demand = $this->demandFactory->createFromSettings($this->settings);
            $this->overwriteDemandObject($demand, $this->moduleData->getOverwriteDemand());
            $participants = $this->personRepository->findDemanded($demand);
        } else {
            $participants = $schedule->getParticipants();
            $participants->rewind();
            /** @var Person $objectForFileName */
            $objectForFileName = $participants->current();
        }
        $this->view->assign('participants', $participants);

        return $this->getContentForDownload($ext, $objectForFileName);
    }

    /**
     * Returns custom error flash messages, or
     * display no flash message at all on errors.
     *
     * @return string|boolean The flash message or false if no flash message should be set
     * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected function getErrorFlashMessage(): string|bool
    {
        $key = 'error' . '.participant.' . str_replace('Action', '', $this->actionMethodName) . '.' . $this->errorMessage;
        $message = $this->translate($key);
        if ($message == null) {
            return false;
        }

        return $message;
    }

    /**
     * Create demand from settings
     * This method is only for backwards compatibility
     *
     * @param array $settings
     * @return \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand
     * @deprecated Use ParticipantDemandFactory with $this->demandFactory->createFromSettings
     * instead (provided by ParticipantDemandFactoryTrait)
     */
    protected function createDemandFromSettings($settings)
    {
        return $this->demandFactory->createFromSettings($settings);
    }

}
