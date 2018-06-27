<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Controller\Backend;

use CPSIT\T3eventsReservation\Controller\Backend\BookingsController;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ReservationDemandFactory;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use DWenzel\T3events\Controller\FilterableControllerInterface;
use DWenzel\T3events\Domain\Model\Dto\ModuleData;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */


/**
 * Class BookingsControllerTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Controller\Backend
 */
class BookingsControllerTest extends UnitTestCase
{
    /**
     * @var BookingsController|MockObject|AccessibleMockObjectInterface
     */
    protected $subject;

    /**
     * @var ModuleData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleData;

    /**
     * @var ReservationRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reservationRepository;

    /**
     * @var ReservationDemandFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $demandFactory;
    /**
     * @var ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            BookingsController::class,
            ['dummy', 'setTsConfig', 'overwriteDemandObject', 'getFilterOptions']
        );
        $this->moduleData = $this->getMockBuilder(ModuleData::class)
            ->setMethods(['getOverwriteDemand', 'setOverwriteDemand', 'setDemand'])->getMock();
        $this->inject($this->subject, 'moduleData', $this->moduleData);
        $this->reservationRepository = $this->getMockBuilder(ReservationRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findDemanded'])
            ->getMock();
        $this->subject->injectReservationRepository($this->reservationRepository);
        $this->view = $this->getMockForAbstractClass(ViewInterface::class, ['assignMultiple']);
        $this->inject($this->subject, 'view', $this->view);
        $this->inject($this->subject, 'settings', $this->settings);
        $this->demandFactory = $this->getMockBuilder(ReservationDemandFactory::class)
            ->setMethods(['createFromSettings'])->getMock();
        $this->subject->injectReservationDemandFactory($this->demandFactory);

    }

    /**
     * @test
     */
    public function classImplementsFilterableControllerInterface()
    {
        $this->assertInstanceOf(
            FilterableControllerInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function listActionGetsDemandFromFactory()
    {
        $demand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $this->demandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($this->settings)
            ->will($this->returnValue($demand));

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function listActionOverwritesDemandObject()
    {
        $overwriteDemand = ['foo'];
        $demand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $this->demandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($this->settings)
            ->will($this->returnValue($demand));
        $this->subject->expects($this->once())
            ->method('overwriteDemandObject')
            ->with($demand, $overwriteDemand);

        $this->subject->listAction($overwriteDemand);
    }

    /**
     * @test
     */
    public function listActionSetsDemandInModuleData()
    {
        $demand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $this->demandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($this->settings)
            ->will($this->returnValue($demand));
        $this->moduleData->expects($this->once())
            ->method('setDemand')
            ->with($demand);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function listActionAssignsVariablesToView()
    {
        $settings = [
            'filter' => ['foo']
        ];
        $this->inject($this->subject, 'settings', $settings);
        $filterOptions = ['bar'];
        $reservations = $this->getMockForAbstractClass(QueryResultInterface::class);
        $demand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $this->demandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($settings)
            ->will($this->returnValue($demand));
        $this->reservationRepository->expects($this->once())
            ->method('findDemanded')
            ->with($demand)
            ->will($this->returnValue($reservations));
        $this->subject->expects($this->once())
            ->method('getFilterOptions')
            ->with($settings['filter'])
            ->will($this->returnValue($filterOptions));
        $expectedVariables = [
            'reservations' => $reservations,
            'overwriteDemand' => null,
            'demand' => $demand,
            'filterOptions' => $filterOptions
        ];
        $this->view->expects($this->once())
            ->method('assignMultiple')
            ->with($expectedVariables);
        $this->subject->listAction();
    }
}
