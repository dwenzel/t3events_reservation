<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

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

use CPSIT\T3eventsReservation\Controller\AccessControlInterface;
use CPSIT\T3eventsReservation\Controller\BillingAddressController;
use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use DWenzel\T3events\Domain\Repository\PerformanceRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Class BillingAddressControllerTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Controller
 */
class BillingAddressControllerTest extends UnitTestCase
{
    /**
     * @var BillingAddressController|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var PersistenceManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistenceManager;

    /**
     * @var BillingAddressRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressRepository;

    /**
     * @var ReservationRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reservationRepository;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            BillingAddressController::class, ['dispatch', 'addFlashMessage', 'translate']
        );
        $this->mockRequest();
        $this->mockView();
        $this->billingAddressRepository = $this->mockBillingAddressRepository();
        $this->reservationRepository = $this->mockReservationRepository();
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockRequest()
    {
        $this->request = $this->getMock(
            Request::class, ['getOriginalRequest', 'hasArgument', 'getArgument']
        );
        $this->inject(
            $this->subject,
            'request',
            $this->request
        );

        return $this->request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PersonRepository
     */
    protected function mockBillingAddressRepository()
    {
        /** @var PersonRepository $mockRepository */
        $mockRepository = $this->getMock(
            BillingAddressRepository::class, ['add', 'remove', 'update'], [], '', false
        );
        $this->subject->injectBillingAddressRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReservationRepository
     */
    protected function mockReservationRepository()
    {
        /** @var ReservationRepository $mockRepository */
        $mockRepository = $this->getMock(
            ReservationRepository::class, ['add', 'remove', 'update'], [], '', false
        );
        $this->subject->injectReservationRepository($mockRepository);

        return $mockRepository;
    }

    /**
     * Creates a mock View, injects it and returns it
     *
     * @return mixed
     */
    protected function mockView()
    {
        $view = $this->getMock(ViewInterface::class);
        $this->inject($this->subject, 'view', $view);

        return $view;
    }

    /**
     * @param $mockBillingAddress
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockReservationWithMatchingBillingAddress($mockBillingAddress)
    {
        $mockReservation = $this->getMock(
            Reservation::class, ['getBillingAddress', 'removeBillingAddress']
        );
        $mockReservation->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($mockBillingAddress));

        return $mockReservation;
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
     */
    public function newActionAssignsVariablesToView()
    {
        $billingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $expectedVariables = [
            'billingAddress' => $billingAddress,
            'reservation' => $mockReservation
        ];
        $view = $this->mockView();
        $view->expects($this->once())
            ->method('assignMultiple')
            ->with($expectedVariables);

        $this->subject->newAction($mockReservation, $billingAddress);
    }

    /**
     * @test
     */
    public function createActionCallsDispatch()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->createAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function createActionSetsBillingAddressInReservation()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(
            Reservation::class, ['setBillingAddress']
        );
        $mockReservation->expects($this->once())
            ->method('setBillingAddress')
            ->with($mockBillingAddress);

        $this->subject->createAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function createActionAddsBillingAddressToRepository()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->billingAddressRepository->expects($this->once())
            ->method('add')
            ->with($mockBillingAddress);

        $this->subject->createAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function createActionUpdatesReservation()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->reservationRepository->expects($this->once())
            ->method('update')
            ->with($mockReservation);
        $this->subject->createAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function createActionAddsFlashMessageOnSuccess()
    {
        $translatedMessage = 'foo';
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.billingAddress.create.success')
            ->will($this->returnValue($translatedMessage));

        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->createAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function removeActionRemovesMatchingBillingAddressFromReservation()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->mockReservationWithMatchingBillingAddress($mockBillingAddress);
        $mockReservation->expects($this->once())
            ->method('removeBillingAddress');

        $this->subject->removeAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function createActionRemovesMatchingBillingAddressFromRepository()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->mockReservationWithMatchingBillingAddress($mockBillingAddress);
        $this->billingAddressRepository->expects($this->once())
            ->method('remove')
            ->with($mockBillingAddress);

        $this->subject->removeAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function removeActionAddsFlashMessage()
    {
        $translatedMessage = 'foo';
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->mockReservationWithMatchingBillingAddress($mockBillingAddress);

        $this->subject->expects($this->once())
            ->method('translate')
            ->with('message.billingAddress.remove.success')
            ->will($this->returnValue($translatedMessage));

        $this->subject->expects($this->once())
            ->method('addFlashMessage')
            ->with($translatedMessage);

        $this->subject->removeAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function removeActionCallsDispatch()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->removeAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function editActionAssignsVariablesToView()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $expectedVariables = [
            'reservation' => $mockReservation,
            'billingAddress' => $mockBillingAddress
        ];
        $view = $this->mockView();
        $view->expects($this->once())
            ->method('assignMultiple')
            ->with($expectedVariables);

        $this->subject->editAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function updateActionUpdatesParticipant()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->billingAddressRepository->expects($this->once())
            ->method('update')
            ->with($mockBillingAddress);

        $this->subject->updateAction($mockReservation, $mockBillingAddress);
    }

    /**
     * @test
     */
    public function updateActionCallsDispatch()
    {
        $mockBillingAddress = $this->getMock(BillingAddress::class);
        $mockReservation = $this->getMock(Reservation::class);

        $this->subject->expects($this->once())
            ->method('dispatch')
            ->with(['reservation' => $mockReservation]);

        $this->subject->updateAction($mockReservation, $mockBillingAddress);
    }
}
