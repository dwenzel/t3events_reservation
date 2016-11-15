<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller\Backend;

use CPSIT\T3eventsReservation\Controller\Backend\BookingsController;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ReservationDemandFactory;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
use DWenzel\T3events\Controller\FilterableControllerInterface;
use DWenzel\T3events\Domain\Model\Dto\ModuleData;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

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
     * @var BookingsController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
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
        $this->moduleData = $this->getMock(
            ModuleData::class,
            ['getOverwriteDemand', 'setOverwriteDemand']
        );
        $this->inject($this->subject, 'moduleData', $this->moduleData);
        $this->reservationRepository = $this->getMock(
            ReservationRepository::class, ['findDemanded'], [], '', false
        );
        $this->subject->injectReservationRepository($this->reservationRepository);
        $this->view = $this->getMockForAbstractClass(ViewInterface::class, ['assignMultiple']);
        $this->inject($this->subject, 'view', $this->view);
        $this->inject($this->subject, 'settings', $this->settings);
        $this->demandFactory = $this->getMock(ReservationDemandFactory::class, ['createFromSettings']);
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
        $demand = $this->getMock(ReservationDemand::class);
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
        $demand = $this->getMock(ReservationDemand::class);
        $this->demandFactory->expects($this->once())
            ->method('createFromSettings')
            ->with($this->settings)
            ->will($this->returnValue($demand));
        $this->subject->expects($this->once())
            ->method('overwriteDemandObject')
            ->with($demand, $overwriteDemand);

        $this->subject->listAction($overwriteDemand);
    }
}
