<?php
namespace CPSIT\T3eventsReservation\Controller\Backend;

/***************************************************************
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

use CPSIT\T3eventsReservation\Controller\PersonDemandFactoryTrait;
use CPSIT\T3eventsReservation\Controller\PersonRepositoryTrait;
use CPSIT\T3eventsReservation\Controller\ReservationRepositoryTrait;
use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use DWenzel\T3events\Controller\AudienceRepositoryTrait;
use DWenzel\T3events\Controller\CategoryRepositoryTrait;
use DWenzel\T3events\Controller\CompanyRepositoryTrait;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\DownloadTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\EventTypeRepositoryTrait;
use DWenzel\T3events\Controller\GenreRepositoryTrait;
use DWenzel\T3events\Controller\ModuleDataTrait;
use DWenzel\T3events\Controller\NotificationRepositoryTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use DWenzel\T3events\Controller\VenueRepositoryTrait;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use DWenzel\T3events\Controller\AbstractBackendController;
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
        AudienceRepositoryTrait, CategoryRepositoryTrait,
        CompanyRepositoryTrait, DemandTrait, DownloadTrait,
        EntityNotFoundHandlerTrait, EventTypeRepositoryTrait,
        FilterableControllerTrait, GenreRepositoryTrait,
        ModuleDataTrait, NotificationRepositoryTrait,
        PersonRepositoryTrait, PersonDemandFactoryTrait,
        ReservationRepositoryTrait, SearchTrait,
        TranslateTrait, VenueRepositoryTrait;

    /**
     * @var string
     */
    protected $errorMessage = 'unknownError';

    /**
     * List action
     *
     * @param array $overwriteDemand
     * @return void
     */
    public function listAction(array $overwriteDemand = null)
    {
        $demand = $this->createDemandFromSettings($this->settings);
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
     * @ignorevalidation $schedule
     * @param string $ext File extension for download
     * @return string
     */
    public function downloadAction($schedule = null, $ext = 'csv')
    {
        if (is_null($schedule)) {
            $demand = $this->createDemandFromSettings($this->settings);
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
    protected function getErrorFlashMessage()
    {
        $key = 'error' . '.participant.' . str_replace('Action', '', $this->actionMethodName) . '.' . $this->errorMessage;
        // todo use correct extension key t3events_course
        $message = $this->translate($key);
        if ($message == null) {
            return false;
        }

        return $message;
    }

    /**
     * Create demand from settings
     *
     * @param array $settings
     * @return \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand
     */
    protected function createDemandFromSettings($settings)
    {
        /**@var \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand $demand */
        $demand = $this->objectManager->get(PersonDemand::class);
        $demand->setTypes((string)Person::PERSON_TYPE_PARTICIPANT);
        foreach ($settings as $propertyName => $propertyValue) {
            if (empty($propertyValue)) {
                continue;
            }
            switch ($propertyName) {
                case 'maxItems':
                    $demand->setLimit($propertyValue);
                    break;
                case 'category':
                    $demand->setCategories($propertyValue);
                    break;
                // all following fall through (see below)
                case 'types':
                case 'periodType':
                case 'periodStart':
                case 'periodEndDate':
                case 'periodDuration':
                case 'search':
                    break;
                default:
                    if (ObjectAccess::isPropertySettable($demand, $propertyName)) {
                        ObjectAccess::setProperty($demand, $propertyName, $propertyValue);
                    }
            }
        }

        if ($demand->getLessonPeriod() === 'futureOnly'
            OR $demand->getLessonPeriod() === 'pastOnly'
        ) {
            $timeZone = new \DateTimeZone(date_default_timezone_get());
            $demand->setLessonDate(new \DateTime('midnight', $timeZone));
        }

        return $demand;
    }

}
