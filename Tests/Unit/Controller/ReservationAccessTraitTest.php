<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Controller\ReservationAccessTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use Webfox\T3events\Session\SessionInterface;

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
    protected function mockObjectManager() {
        $mockObjectManager = $this->getMock(
            ObjectManager::class, ['get']
        );
        $this->inject($this->subject, 'objectManager', $mockObjectManager);

        return $mockObjectManager;
    }

    /**
     * @return mixed
     */
    protected function mockSession() {
        $mockSession = $this->getMock(
            SessionInterface::class
        );
        $this->inject($this->subject, 'session', $mockSession);

        return $mockSession;
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsFalseIfObjectIsNotReservation() {
        $object = $this->getMockForAbstractClass(
            DomainObjectInterface::class
        );
        $this->assertFalse(
            $this->subject->isAccessAllowed($object)
        );
    }

    /**
     * @test
     */
    public function isAccessAllowedReturnsFalseIfReservationUidIsNotInSession() {
        $mockSession = $this->mockSession();
        $object = $this->getMock(
            Reservation::class
        );
        $mockSession->expects($this->once())
            ->method('has')
            ->with('reservationUid')
            ->will($this->returnValue(false));

        $this->assertFalse(
            $this->subject->isAccessAllowed($object)
        );
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @expectedExceptionCode 1459870578
     */
    public function initializeActionThrowsExceptionForMissingReservationArgument()
    {
        $this->mockObjectManager();
        $mockRequest = $this->getMock(
            Request::class, ['hasArgument']
        );
        $this->inject($this->subject, 'request', $mockRequest);

        $mockRequest->expects($this->once())
            ->method('hasArgument')
            ->will($this->returnValue(false));

        $this->subject->initializeAction();
    }
}
