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
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use DWenzel\T3events\Controller\CompanyRepositoryTrait;
use DWenzel\T3events\Controller\DemandTrait;
use DWenzel\T3events\Controller\EntityNotFoundHandlerTrait;
use DWenzel\T3events\Controller\NotificationServiceTrait;
use DWenzel\T3events\Controller\PersistenceManagerTrait;
use DWenzel\T3events\Controller\RoutableControllerInterface;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SearchTrait;
use DWenzel\T3events\Controller\SettingsUtilityTrait;
use DWenzel\T3events\Controller\TranslateTrait;
use DWenzel\T3events\Domain\Model\Performance;
use DWenzel\T3events\Session\SessionInterface;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Configuration\Exception;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;

/**
 * ReservationController
 */
class ReservationController
    extends ActionController
    implements AccessControlInterface, RoutableControllerInterface
{
    use BillingAddressRepositoryTrait, ContactRepositoryTrait,
        CompanyRepositoryTrait, DemandTrait,
        EntityNotFoundHandlerTrait, NotificationServiceTrait,
        PersistenceManagerTrait, PersonRepositoryTrait, ReservationAccessTrait,
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
     * Lesson Repository
     *
     * @var \DWenzel\T3events\Domain\Repository\PerformanceRepository
     */
    protected $lessonRepository = null;

    public function injectLessonRepository(\DWenzel\T3events\Domain\Repository\PerformanceRepository $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * action show
     *
     * @param Reservation $reservation
     * @return void
     */
    public function showAction(Reservation $reservation)
    {
        $this->session->clean();
        $this->view->assign(SettingsInterface::RESERVATION, $reservation);
    }

    /**
     * action new
     *
     * @param BookableInterface|Performance $lesson
     * @param Reservation $newReservation
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("newReservation")
     * @throws NoSuchArgumentException
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
            $newReservation = $this->request->getOriginalRequest()->getArgument(SettingsInterface::NEW_RESERVATION);
        }
        $this->view->assignMultiple(
            [
                SettingsInterface::NEW_RESERVATION => $newReservation,
                SettingsInterface::LESSON => $lesson
            ]
        );
    }

    /**
     * action create
     *
     * @param Reservation $newReservation
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidSourceException
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

        $this->dispatch([SettingsInterface::RESERVATION => $newReservation]);
    }

    /**
     * action edit
     *
     * @param Reservation $reservation
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function editAction(Reservation $reservation)
    {
        $this->reservationRepository->update($reservation);
        $this->persistenceManager->persistAll();

        $this->view->assignMultiple(
            [
                SettingsInterface::RESERVATION => $reservation
            ]
        );
    }

    /**
     * action delete
     *
     * @param Reservation $reservation
     * @return void
     * @throws IllegalObjectTypeException
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
     * @return void
     * @throws InvalidSourceException
     * @throws NoSuchArgumentException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("newParticipant")
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
            $newParticipant = $this->request->getOriginalRequest()->getArgument(SettingsInterface::NEW_PARTICIPANT);
        }
        $this->view->assignMultiple(
            [
                SettingsInterface::NEW_PARTICIPANT => $newParticipant,
                SettingsInterface::RESERVATION => $reservation
            ]
        );
    }

    /**
     * action createParticipant
     *
     * @param Reservation $reservation
     * @param Person $newParticipant
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
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
            $this->reservationRepository->update($reservation);
            $this->lessonRepository->update($reservation->getLesson());
            $this->persistenceManager->persistAll();
            $this->addFlashMessage(
                $this->translate('message.reservation.createParticipant.success')
            );
        }

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * Checkout Action
     *
     * @param Reservation $reservation
     * @return void
     */
    public function checkoutAction(Reservation $reservation)
    {
        $this->view->assign(SettingsInterface::RESERVATION, $reservation);
    }

    /**
     * Confirm Action
     *
     * @param Reservation $reservation
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function confirmAction(Reservation $reservation)
    {
        $reservation->setStatus(Reservation::STATUS_SUBMITTED);
        $this->addFlashMessage(
            $this->translate('message.reservation.confirm.success')
        );
        if (is_array($this->settings[SettingsInterface::RESERVATION][SettingsInterface::CONFIRM][SettingsInterface::NOTIFICATION])) {
            foreach ($this->settings[SettingsInterface::RESERVATION][SettingsInterface::CONFIRM][SettingsInterface::NOTIFICATION] as $identifier => $config) {
                $this->sendNotification($reservation, $identifier, $config);
            }
        }
        $this->reservationRepository->update($reservation);
        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
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
        if (isset($config[SettingsInterface::FROM_EMAIL])) {
            $fromEmail = $config[SettingsInterface::FROM_EMAIL];
        } else {
            throw new Exception('Missing sender for email notification', 1454518855);
        }

        $recipientEmail = $this->settingsUtility->getValue($reservation, $config[SettingsInterface::TO_EMAIL]);
        if (!isset($recipientEmail)) {
            throw new Exception('Missing recipient for email notification ' . $identifier, 1454865240);
        }

        $subject = $this->settingsUtility->getValue($reservation, $config[SettingsInterface::SUBJECT]);
        if (!isset($subject)) {
            throw new Exception('Missing subject for email notification ' . $identifier, 1454865250);
        }

        $format = 'plain';
        if (isset($config['format']) && is_string($config['format'])) {
            $format = $config['format'];
        }
        $fileName = ucfirst($identifier);
        if (isset($config[SettingsInterface::TEMPLATE]['fileName'])) {
            $fileName = $config[SettingsInterface::TEMPLATE]['fileName'];
        }
        $folderName = 'Reservation/Email';
        if (isset($config[SettingsInterface::TEMPLATE][SettingsInterface::FOLDER_NAME])) {
            $folderName = $config[SettingsInterface::TEMPLATE][SettingsInterface::FOLDER_NAME];
        }
        /** @var Notification $notification */
        $notification = $this->objectManager->get(Notification::class);
        if (isset($config[SettingsInterface::ATTACHMENTS][SettingsInterface::FILES]) && is_array($config[SettingsInterface::ATTACHMENTS][SettingsInterface::FILES])) {
            $filesToAttach = $this->settingsUtility->getFileStorage(
                $reservation, $config[SettingsInterface::ATTACHMENTS][SettingsInterface::FILES]
            );
            $notification->setAttachments($filesToAttach);
        }
        $notification->setRecipient($recipientEmail);
        $notification->setSenderEmail($fromEmail);
        if (isset($config[SettingsInterface::SENDER_NAME])) {
            $notification->setSenderName($config[SettingsInterface::SENDER_NAME]);
        }
        $notification->setSubject($subject);
        $notification->setFormat($format);
        $bodyText = $this->notificationService->render(
            $fileName,
            $format,
            $folderName,
            [SettingsInterface::RESERVATION => $reservation, SettingsInterface::SETTINGS => $this->settings]
        );
        $notification->setBodytext($bodyText);
        $reservation->addNotification($notification);

        return $this->notificationService->send($notification);
    }

    /**
     * action removeParticipant
     *
     * @param Reservation $reservation
     * @param Person $participant
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function removeParticipantAction(Reservation $reservation, Person $participant)
    {
        $reservation->removeParticipant($participant);
        $this->personRepository->remove($participant);
        $this->reservationRepository->update($reservation);
        $this->addFlashMessage(
            $this->translate('message.reservation.removeParticipant.success')
        );

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
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
                SettingsInterface::RESERVATION => $reservation,
            ]
        );
    }

    /**
     * Removes a billing address from reservation
     *
     * @param Reservation $reservation
     * @throws IllegalObjectTypeException
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

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * New billing address action
     *
     * @param Reservation $reservation
     * @param BillingAddress|null $newBillingAddress
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("newBillingAddress")
     */
    public function newBillingAddressAction(Reservation $reservation, BillingAddress $newBillingAddress = null)
    {
        $this->view->assignMultiple(
            [
                SettingsInterface::NEW_BILLING_ADDRESS => $newBillingAddress,
                SettingsInterface::RESERVATION => $reservation
            ]
        );
    }

    /**
     * @param Reservation $reservation
     * @param BillingAddress $newBillingAddress
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function createBillingAddressAction(Reservation $reservation, BillingAddress $newBillingAddress)
    {
        $reservation->setBillingAddress($newBillingAddress);
        $this->personRepository->add($newBillingAddress);
        $this->reservationRepository->update($reservation);
        $this->addFlashMessage(
            $this->translate('message.reservation.createBillingAddress.success')
        );

        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }

    /**
     * updates the reservation
     *
     * @param Reservation $reservation
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(Reservation $reservation)
    {
        $this->addFlashMessage(
            $this->translate('message.reservation.update.success')
        );

        $this->reservationRepository->update($reservation);
        $this->dispatch([SettingsInterface::RESERVATION => $reservation]);
    }
}
