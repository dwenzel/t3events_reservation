<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

use CPSIT\T3eventsReservation\Controller\ReservationAccessTrait;
use CPSIT\T3eventsReservation\Controller\ReservationController;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use Webfox\T3events\Session\SessionInterface;
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
class ReservationAccessTraitTest extends UnitTestCase
{
    /**
     * @var ReservationAccessTrait
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class
        );
    }

    /**
     * @return mixed
     */
    protected function mockObjectManager()
    {
        $mockObjectManager = $this->getMock(
            ObjectManager::class, ['get']
        );
        $this->inject($this->subject, 'objectManager', $mockObjectManager);

        return $mockObjectManager;
    }

    /**
     * @return mixed
     */
    protected function mockSession()
    {
        $mockSession = $this->getMock(
            SessionInterface::class, ['has', 'get', 'set', 'clean']
        );
        $this->inject($this->subject, 'session', $mockSession);

        return $mockSession;
    }

    /**
     *
     */
    protected function mockAllowAccessReturnsTrue()
    {
        $this->subject->expects($this->once())
            ->method('isAccessAllowed')
            ->will($this->returnValue(true));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockRequest()
    {
        $mockRequest = $this->getMock(
            Request::class,
            ['hasArgument', 'getArgument', 'getControllerName', 'getControllerActionName']
        );
        $this->inject($this->subject, 'request', $mockRequest);

        return $mockRequest;
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsTrueForReservationWithMatchingUid()
    {
        $validReservationId = 1234;
        $mockSession = $this->mockSession();
        $validReservation = $this->getMock(
            Reservation::class, ['getUid']
        );
        $mockRequest = $this->mockRequest();
        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->with('reservation')
            ->will($this->returnValue(true));
        $mockRequest->expects($this->once())
            ->method('getArgument')
            ->will($this->returnValue($validReservation));

        $validReservation->expects($this->once())
            ->method('getUid')
            ->will($this->returnValue($validReservationId));
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(true));
        $mockSession->expects($this->once())
            ->method('get')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue($validReservationId));

        $this->assertTrue(
            $this->subject->isAccessAllowed()
        );
    }

    /**
     * @test
     */
    public function accessErrorHasInitialValue()
    {
        $this->assertAttributeSame(
            Reservation::ERROR_ACCESS_UNKNOWN,
            'accessError',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsFalseIfReservationUidIsNotInSession()
    {
        $mockSession = $this->mockSession();
        $object = $this->getMock(
            Reservation::class, ['getUid']
        );
        $object->expects($this->once())
            ->method('getUid')
            ->will($this->returnValue(5));
        $mockRequest = $this->mockRequest();
        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->with('reservation')
            ->will($this->returnValue(true));
        $mockRequest->expects($this->once())
            ->method('getArgument')
            ->will($this->returnValue($object));
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(false));

        $this->subject->isAccessAllowed();

        $this->assertAttributeEquals(
            Reservation::ERROR_MISSING_RESERVATION_KEY_IN_SESSION,
            'accessError',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsFalseForMissingReservationArgument()
    {
        $mockSession = $this->mockSession();
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(true));
        $mockRequest = $this->getMock(
            Request::class, ['hasArgument']
        );
        $this->inject($this->subject, 'request', $mockRequest);

        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->with('reservation')
            ->will($this->returnValue(false));

        $this->assertFalse(
            $this->subject->isAccessAllowed()
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedSetsErrorForMissingReservationArgument()
    {
        $mockSession = $this->mockSession();
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(true));
        $mockRequest = $this->getMock(
            Request::class, ['hasArgument']
        );
        $this->inject($this->subject, 'request', $mockRequest);

        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->with('reservation')
            ->will($this->returnValue(false));

        $this->subject->isAccessAllowed();
        $this->assertAttributeSame(
            Reservation::ERROR_INCOMPLETE_RESERVATION_IN_SESSION,
            'accessError',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsTrueForValidRequestArgumentOfTypeString()
    {
        $validReservationId = 1234;
        $mockRequest = $this->mockRequest();
        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->will($this->returnValue(true));
        $mockRequest->expects($this->once())
            ->method('getArgument')
            ->will($this->returnValue((string)$validReservationId));

        $mockSession = $this->mockSession();
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(true));
        $mockSession->expects($this->once())
            ->method('get')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue($validReservationId));

        $this->assertTrue(
            $this->subject->isAccessAllowed()
        );
    }

    /**
     * @test
     */
    public function initializeActionSetsSession()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class,
            [],
            '',
            true,
            true,
            true,
            ['isAccessAllowed']
        );
        $mockObjectManager = $this->mockObjectManager();
        $this->mockRequest();
        $this->mockAllowAccessReturnsTrue();
        $mockSession = $this->getMockForAbstractClass(
            SessionInterface::class
        );
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->with(Typo3Session::class, ReservationController::SESSION_NAME_SPACE)
            ->will($this->returnValue($mockSession));

        $this->subject->initializeAction();
        $this->assertAttributeEquals(
            $mockSession,
            'session',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsTrueIfNoReservationInSessionAndNoArgumentReservation()
    {
        $mockRequest = $this->mockRequest();
        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->with('reservation')
            ->will($this->returnValue(false));

        $mockSession = $this->mockSession();
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(false));

        $this->assertTrue(
            $this->subject->isAccessAllowed()
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsTrueIfIdentityMatchesSessionValue()
    {
        $sessionValue = 1234;
        $validRequestArgument = [
            '__identity' => '1234'
        ];
        $mockSession = $this->mockSession();
        $mockRequest = $this->mockRequest();
        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->with('reservation')
            ->will($this->returnValue(true));
        $mockRequest->expects($this->once())
            ->method('getArgument')
            ->with('reservation')
            ->will($this->returnValue($validRequestArgument));
        $mockSession->expects($this->once())
            ->method('has')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue(true));
        $mockSession->expects($this->once())
            ->method('get')
            ->with(ReservationController::SESSION_IDENTIFIER_RESERVATION)
            ->will($this->returnValue($sessionValue));

        $this->assertTrue(
            $this->subject->isAccessAllowed()
        );
    }

    /**
     * @test
     */
    public function getErrorFlashMessageTranslatesMessage()
    {
        $controllerName = 'foo';
        $actionName = 'bar';
        $mockRequest = $this->mockRequest();
        $mockRequest->expects($this->once())
            ->method('getControllerName')
            ->will($this->returnValue($controllerName));
        $mockRequest->expects($this->once())
            ->method('getControllerActionName')
            ->will($this->returnValue($actionName));
        $expectedKey = 'error.' . $controllerName . '.' . $actionName . '.' . Reservation::ERROR_ACCESS_UNKNOWN;
        $expectedMessage = 'fooMessage';
        $this->subject->expects($this->once())
            ->method('translate')
            ->with($expectedKey, 't3events_reservation')
            ->will($this->returnValue($expectedMessage));
        $this->assertSame(
            $expectedMessage,
            $this->subject->getErrorFlashMessage()
        );
    }

    /**
     * @test
     */
    public function errorActionClearsCache()
    {
        $this->subject = $this->getMockForTrait(
        ReservationAccessTrait::class,
        [], '', true, true, true, ['clearCacheOnError', 'addFlashMessage', 'getErrorFlashMessage']
        );

        $this->mockSession();
        $this->subject->expects($this->once())
            ->method('clearCacheOnError');
        $this->subject->errorAction();
    }

    /**
     * @test
     */
    public function errorActionCleansSession()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class,
            [], '', true, true, true, ['clearCacheOnError', 'addFlashMessage', 'getErrorFlashMessage']
        );

        $mockSession = $this->mockSession();
        $mockSession->expects($this->once())
            ->method('clean');
        $this->subject->errorAction();
    }

    /**
     * @test
     */
    public function errorActionAddsFlashMessage()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class,
            [], '', true, true, true, ['clearCacheOnError', 'addFlashMessage', 'getErrorFlashMessage']
        );

        $this->mockSession();
        $this->subject->expects($this->once())
            ->method('getErrorFlashMessage');
        $this->subject->expects($this->once())
            ->method('addFlashMessage');
        $this->subject->errorAction();
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @expectedExceptionCode 1459870578
     */
    public function denyAccessClearsCache()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class,
            [], '', true, true, true, ['clearCacheOnError', 'addFlashMessage', 'getErrorFlashMessage']
        );

        $this->subject->expects($this->once())
            ->method('clearCacheOnError');
        $this->subject->denyAccess();
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @expectedExceptionCode 1459870578
     */
    public function denyAccessAddsFlashMessage()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class,
            [], '', true, true, true, ['clearCacheOnError', 'addFlashMessage', 'getErrorFlashMessage']
        );

        $this->subject->expects($this->once())
            ->method('getErrorFlashMessage');
        $this->subject->expects($this->once())
            ->method('addFlashMessage');
        $this->subject->denyAccess();
    }

    /**
     * @test
     */
    public function initializeActionDeniesAccess()
    {
        $this->subject = $this->getMockForTrait(
            ReservationAccessTrait::class,
            [], '', true, true, true, ['isAccessAllowed', 'denyAccess']
        );
        $this->mockObjectManager();
        $this->subject->expects($this->once())
            ->method('isAccessAllowed')
            ->will($this->returnValue(false));
        $this->subject->expects($this->once())
            ->method('denyAccess');
        $this->subject->initializeAction();
    }
}
