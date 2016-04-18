<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Slot;

use CPSIT\T3eventsReservation\Controller\ReservationController;
use CPSIT\T3eventsReservation\Slot\ReservationControllerSlot;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use Webfox\T3events\Session\Typo3Session;

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
class ReservationControllerSlotTest extends UnitTestCase
{
    /**
     * @var ReservationControllerSlot
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ReservationControllerSlot::class, ['dummy'], [], '', false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected function mockObjectManager()
    {
        /** @var ObjectManager $mockObjectManager */
        $mockObjectManager = $this->getMock(
            ObjectManager::class, ['get']
        );
        $this->subject->injectObjectManager($mockObjectManager);

        return $mockObjectManager;
    }

    /**
     * Mocks the session
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Typo3Session
     */
    protected function mockSession()
    {
        /** @var Typo3Session $mockSession */
        $mockSession = $this->getMock(
            Typo3Session::class, ['has', 'get', 'setNamspace'], [], '', false
        );
        $this->subject->injectSession($mockSession);

        return $mockSession;
    }

    /**
     * @test
     */
    public function objectManagerCanBeInjected() {
        $mockObjectManager = $this->mockObjectManager();

        $this->assertAttributeSame(
            $mockObjectManager,
            'objectManager',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function sessionCanBeInjected()
    {
        /** @var Typo3Session $mockSession */
        $mockSession = $this->getMock(
            Typo3Session::class, ['dummy'], [], '', false
        );
        $this->subject->injectSession($mockSession);

        $this->assertAttributeSame(
            $mockSession,
            'session',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function injectSessionSetsSessionNamespace()
    {
        /** @var Typo3Session $mockSession */
        $mockSession = $this->getMock(
            Typo3Session::class, ['dummy'], [], '', false
        );
        $this->subject->injectSession($mockSession);

        $this->assertAttributeSame(
            ReservationController::SESSION_NAME_SPACE,
            'namespace',
            $mockSession
        );
    }

    /**
     * @test
     */
    public function handleEntityNotFoundSlotInitiallyReturnsParams()
    {
        $params = ['foo'];
        $this->assertSame(
            [$params],
            $this->subject->handleEntityNotFoundSlot($params)
        );
    }

    /**
     * @test
     */
    public function handleEntityNotFoundSlotSetsHandlerAndAction()
    {
        $this->mockSession();
        $handler = 'foo';
        $actionName = 'bar';
        $params = [
            'config' => [$handler, $actionName]
        ];
        $expectedResult = [
            'config' => [
                $handler, $actionName
            ],
            $handler => ['actionName' => $actionName]
        ];

        $this->assertSame(
            [$expectedResult],
            $this->subject->handleEntityNotFoundSlot($params)
        );
    }

    /**
     * @test
     */
    public function handleEntityNotFoundSlotSetsReservationIdFromSession()
    {
        $reservationId = 1234;
        $mockSession = $this->mockSession();
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(true));
        $mockSession->expects($this->once())
            ->method('get')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue($reservationId));

        $handler = 'foo';
        $actionName = 'bar';
        $params = [
            'config' => [$handler, $actionName]
        ];
        $expectedResult = [
            'config' => [
                $handler, $actionName
            ],
            $handler => [
                'actionName' => $actionName,
                'arguments' => ['reservation' => (string)$reservationId]
            ]
        ];

        $this->assertSame(
            [$expectedResult],
            $this->subject->handleEntityNotFoundSlot($params)
        );
    }
}
