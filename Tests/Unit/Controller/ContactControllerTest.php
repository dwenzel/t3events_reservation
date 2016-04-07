<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

use CPSIT\T3eventsReservation\Controller\AccessControlInterface;
use CPSIT\T3eventsReservation\Controller\ContactController;
use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

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
class ContactControllerTest extends UnitTestCase
{
    /**
     * @var ContactController
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ContactController::class, ['forward']
        );
    }

    /**
     * Creates a mock View, injects it and returns it
     *
     * @return mixed
     */
    protected function mockView() {
        $view = $this->getMock(ViewInterface::class);
        $this->inject($this->subject, 'view', $view);

        return $view;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContactRepository
     */
    protected function mockContactRepository()
    {
        $repository = $this->getMock(
            ContactRepository::class, ['add', 'remove', 'update'], [], '', false
        );

        $this->subject->injectContactRepository($repository);
        return $repository;
    }

    /**
     * @test
     */
    public function contactRepositoryCanBeInjected()
    {
        $mockRepository = $this->getMock(
            ContactRepository::class, [], [], '', false
        );
        $this->subject->injectContactRepository($mockRepository);
        $this->assertAttributeSame(
            $mockRepository,
            'contactRepository',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function subjectImplementsAccessControlInterface()
    {
        $this->assertInstanceOf(
            AccessControlInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @expectedExceptionCode 1460039887
     */
    public function editActionThrowsExceptionIfReservationDoesNotContainContact()
    {
        $contact = new Contact();
        $reservation = new Reservation();

        $this->subject->editAction($contact, $reservation);
    }

    /**
     * @test
     */
    public function editActionAssignsVariablesToView()
    {
        $contact = new Contact();
        $reservation = new Reservation();
        $reservation->setContact($contact);
        $view = $this->mockView();
        $view->expects($this->once())
            ->method('assign')
            ->with('contact', $contact);

        $this->subject->editAction($contact, $reservation);
    }

    /**
     * @test
     */
    public function updateActionForwardsToDefaultController()
    {
        $this->mockContactRepository();
        $contact = new Contact();
        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMock(
            Reservation::class
        );
        $contact->setReservation($mockReservation);

        $this->subject->expects($this->once())
            ->method('forward')
            ->with(
                'edit',
                ContactController::PARENT_CONTROLLER_NAME,
                null,
                ['reservation' => $mockReservation]
            );

        $this->subject->updateAction($contact);
    }
}
