<?php
namespace CPSIT\T3eventsReservation\Controller;

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

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\BookableInterface;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use DWenzel\T3events\Controller\CompanyRepositoryTrait;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Controller\RoutableControllerInterface;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Configuration\Exception;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use DWenzel\T3events\Domain\Model\Performance;
use DWenzel\T3events\Session\SessionInterface;

/**
 * ReservationController
 */
class ReservationController
    extends ActionController
    implements AccessControlInterface, RoutableControllerInterface
{
    use BillingAddressRepositoryTrait, ContactRepositoryTrait,
        CompanyRepositoryTrait, DemandTrait,
        EntityNotFoundHandlerTrait, PersistenceManagerTrait,
        PersonRepositoryTrait, ReservationAccessTrait,
        ReservationRepositoryTrait, RoutingTrait,
        SearchTrait, SettingsUtilityTrait, TranslateTrait;

    /**
     * @const Session namespace for reservations
     */
    const SESSION_NAME_SPACE = 'tx_t3eventsreservation';

    /**
     * @const Identifier for reservation in session
     */
    const SESSION_IDENTIFIER_RESERVATION = 'reservationUid';

    /**
     * @const Extension key
     */
    const EXTENSION_KEY =  't3events_reservation';

    /**
     * Notification Service
     *
     * @var \DWenzel\T3events\Service\NotificationService
     * @inject
     */
    protected $notificationService;

    /**
     * Lesson Repository
     *
     * @var \DWenzel\T3events\Domain\Repository\PerformanceRepository
     * @inject
     */
    protected $lessonRepository = null;

    /**
     * action show
     *
     * @param Reservation $reservation
     * @return void
     */
    public function showAction(Reservation $reservation)
    {
        $this->session->clean();
        $this->view->assign('reservation', $reservation);
    }

    /**
     * action new
     *
     * @param BookableInterface|Performance $lesson
     * @param Reservation $newReservation
     * @ignorevalidation $newReservation
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function newAction(Performance $lesson = null, Reservation $newReservation = null)
    {
        if (is_null($lesson)) {
            $error = 'message.selectLesson';
        } elseif (!$lesson->getFreePlaces()) {
            $error = 'message.noFreePlacesForThisLesson';
        }
        if (isset($error)) {
            $this->addFlashMessage(
                $this->translate($error), '', AbstractMessage::ERROR, true
            );
        }
        if ($this->request->getOriginalRequest() instanceof Request) {
            $newReservation = $this->request->getOriginalRequest()->getArgument('newReservation');
        }
        $this->view->assignMultiple(
            [
                'newReservation' => $newReservation,
                'lesson' => $lesson
            ]
        );
    }

    /**
     * action create
     *
     * @param Reservation $newReservation
     * @return void
     */
    public function createAction(Reservation $newReservation)
    {
        if (
            !is_null($newReservation->getUid())
            || ($this->session instanceof SessionInterface
                && $this->session->has(self::SESSION_IDENTIFIER_RESERVATION))
        ) {
            $this->denyAccess();

            return;
        }

        if ($contact = $newReservation->getContact()) {
            $contact->setReservation($newReservation);
        }
        $newReservation->setStatus(Reservation::STATUS_DRAFT);
        $this->addFlashMessage(
            $this->translate('message.reservation.create.success')
        );
        $this->reservationRepository->add($newReservation);
        $this->persistenceManager->persistAll();
        $this->session->set(self::SESSION_IDENTIFIER_RESERVATION, $newReservation->getUid());

        $this->dispatch(['reservation' => $newReservation]);
    }

    /**
     * action edit
     *
     * @param Reservation $reservation
     * @return void
     */
    public function editAction(Reservation $reservation)
    {
        $this->reservationRepository->update($reservation);
        $this->persistenceManager->persistAll();

        $this->view->assignMultiple(
            [
                'reservation' => $reservation
            ]
        );
    }

    /**
     * action delete
     *
     * @param Reservation $reservation
     * @return void
     */
    public function deleteAction(Reservation $reservation)
    {
        $this->addFlashMessage(
            $this->translate('message.reservation.delete.success')
        );
        if ($participants = $reservation->getParticipants()) {
            foreach ($participants as $participant) {
                $this->personRepository->remove($participant);
            }
        }
        if ($company = $reservation->getCompany()) {
            $this->companyRepository->remove($company);
        }
        if ($contact = $reservation->getContact()) {
            $this->contactRepository->remove($contact);
        }
        $this->reservationRepository->remove($reservation);
        $this->session->clean();
    }

    /**
     * action newParticipant
     *
     * @param Reservation $reservation
     * @param Person $newParticipant
     * @ignorevalidation $newParticipant
     * @return void
     */
    public function newParticipantAction(Reservation $reservation, Person $newParticipant = null)
    {
        if (
        !($reservation->getStatus() === Reservation::STATUS_DRAFT || $reservation->getStatus() === Reservation::STATUS_NEW
        )
        ) {
            $this->denyAccess();

            return;
        }

        if (!$reservation->getStatus() == Reservation::STATUS_DRAFT) {
            $reservation->setStatus(Reservation::STATUS_DRAFT);
        }

        $lesson = $reservation->getLesson();
        if ($lesson instanceof BookableInterface &&
            $lesson->getFreePlaces() < 1
        ) {
            $this->addFlashMessage(
                $this->translate('message.noFreePlacesForThisLesson'), '', AbstractMessage::ERROR, true
            );
        } elseif (!count($reservation->getParticipants())) {
            $this->addFlashMessage(
                $this->translate('message.reservation.newParticipant.addAtLeastOneParticipant'), '',
                AbstractMessage::NOTICE
            );
        }
        if ($this->request->getOriginalRequest() instanceof \TYPO3\CMS\Extbase\Mvc\Request) {
            $newParticipant = $this->request->getOriginalRequest()->getArgument('newParticipant');
        }
        $this->view->assignMultiple(
            [
                'newParticipant' => $newParticipant,
                'reservation' => $reservation
            ]
        );
    }

    /**
     * action createParticipant
     *
     * @param Reservation $reservation
     * @param Person $newParticipant
     * @return void
     */
    public function createParticipantAction(Reservation $reservation, Person $newParticipant)
    {
        if (!$reservation->getStatus() == Reservation::STATUS_DRAFT) {
            $reservation->setStatus(Reservation::STATUS_DRAFT);
        }
        if ($reservation->getLesson()->getFreePlaces()) {
            $newParticipant->setReservation($reservation);
            $newParticipant->setType(Person::PERSON_TYPE_PARTICIPANT);
            $reservation->addParticipant($newParticipant);
            $reservation->getLesson()->addParticipant($newParticipant);
            $this->reservationRepository->update($reservation);
            $this->lessonRepository->update($reservation->getLesson());
            $this->persistenceManager->persistAll();
            $this->addFlashMessage(
                $this->translate('message.reservation.createParticipant.success')
            );
        }

        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * Checkout Action
     *
     * @param Reservation $reservation
     * @return void
     */
    public function checkoutAction(Reservation $reservation)
    {
        $this->view->assign('reservation', $reservation);
    }

    /**
     * Confirm Action
     *
     * @param Reservation $reservation
     * @return void
     */
    public function confirmAction(Reservation $reservation)
    {
        // @todo optionally read reservation status from settings
        $reservation->setStatus(Reservation::STATUS_SUBMITTED);
        $this->addFlashMessage(
            $this->translate('message.reservation.confirm.success')
        );
        if (is_array($this->settings['reservation']['confirm']['notification'])) {
            foreach ($this->settings['reservation']['confirm']['notification'] as $identifier => $config) {
                $this->sendNotification($reservation, $identifier, $config);
            }
        }
        $this->reservationRepository->update($reservation);
        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * action removeParticipant
     *
     * @param Reservation $reservation
     * @param Person $participant
     * @return void
     */
    public function removeParticipantAction(Reservation $reservation, Person $participant)
    {
        $reservation->removeParticipant($participant);
        $reservation->getLesson()->removeParticipant($participant);
        $this->personRepository->remove($participant);
        $this->reservationRepository->update($reservation);
        $this->addFlashMessage(
            $this->translate('message.reservation.removeParticipant.success')
        );

        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * Edit billing address
     *
     * @param Reservation $reservation
     */
    public function editBillingAddressAction(Reservation $reservation)
    {
        $this->view->assignMultiple(
            [
                'reservation' => $reservation,
            ]
        );
    }

    /**
     * Removes a billing address from reservation
     *
     * @param Reservation $reservation
     * @throws InvalidSourceException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function removeBillingAddressAction(Reservation $reservation)
    {
        if ($billingAddress = $reservation->getBillingAddress()) {
            $reservation->removeBillingAddress();
            $this->billingAddressRepository->remove($billingAddress);
            $this->addFlashMessage(
                $this->translate('message.reservation.removeBillingAddress.success')
            );
        }

        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * New billing address action
     *
     * @param Reservation $reservation
     * @param BillingAddress|null $newBillingAddress
     * @ignorevalidation $newBillingAddress
     */
    public function newBillingAddressAction(Reservation $reservation, BillingAddress $newBillingAddress = null)
    {
        $this->view->assignMultiple(
            [
                'newBillingAddress' => $newBillingAddress,
                'reservation' => $reservation
            ]
        );
    }

    /**
     * @param Reservation $reservation
     * @param BillingAddress $newBillingAddress
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createBillingAddressAction(Reservation $reservation, BillingAddress $newBillingAddress)
    {
        $reservation->setBillingAddress($newBillingAddress);
        $this->personRepository->add($newBillingAddress);
        $this->reservationRepository->update($reservation);
        $this->addFlashMessage(
            $this->translate('message.reservation.createBillingAddress.success')
        );

        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * updates the reservation
     *
     * @param Reservation $reservation
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function updateAction(Reservation $reservation)
    {
        $this->addFlashMessage(
            $this->translate('message.reservation.update.success')
        );

        $this->reservationRepository->update($reservation);
        $this->dispatch(['reservation' => $reservation]);
    }

    /**
     * @param Reservation $reservation
     * @param string $identifier
     * @param array $config
     * @return bool
     * @throws Exception
     */
    protected function sendNotification(Reservation $reservation, $identifier, $config)
    {
        if (isset($config['fromEmail'])) {
            $fromEmail = $config['fromEmail'];
        } else {
            throw new Exception('Missing sender for email notification', 1454518855);
        }

        $recipientEmail = $this->settingsUtility->getValue($reservation, $config['toEmail']);
        if (!isset($recipientEmail)) {
            throw new Exception('Missing recipient for email notification ' . $identifier, 1454865240);
        }

        $subject = $this->settingsUtility->getValue($reservation, $config['subject']);
        if (!isset($subject)) {
            throw new Exception('Missing subject for email notification ' . $identifier, 1454865250);
        }

        $format = 'plain';
        if (isset($config['format']) && is_string($config['format'])) {
            $format = $config['format'];
        }
        $fileName = ucfirst($identifier);
        if (isset($config['template']['fileName'])) {
            $fileName = $config['template']['fileName'];
        }
        $folderName = 'Reservation/Email';
        if (isset($config['template']['folderName'])) {
            $folderName = $config['template']['folderName'];
        }
        /** @var Notification $notification */
        $notification = $this->objectManager->get(Notification::class);
        if (isset($config['attachments']['files']) && is_array($config['attachments']['files'])) {
            $filesToAttach = $this->settingsUtility->getFileStorage(
                $reservation, $config['attachments']['files']
            );
            $notification->setAttachments($filesToAttach);
        }
        $notification->setRecipient($recipientEmail);
        $notification->setSenderEmail($fromEmail);
        if (isset($config['senderName'])) {
            $notification->setSenderName($config['senderName']);
        }
        $notification->setSubject($subject);
        $notification->setFormat($format);
        $bodyText = $this->notificationService->render(
            $fileName,
            $format,
            $folderName,
            ['reservation' => $reservation, 'settings' => $this->settings]
        );
        $notification->setBodytext($bodyText);
        $reservation->addNotification($notification);

        return $this->notificationService->send($notification);
    }
}
