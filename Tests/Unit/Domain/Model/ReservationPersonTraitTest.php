<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model;

use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Model\ReservationPersonTrait;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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
class ReservationPersonTraitTest extends UnitTestCase
{
    /**
     * @var ReservationPersonTrait
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            ReservationPersonTrait::class
        );
    }

    /**
     * @test
     */
    public function getReservationInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getReservation()
        );
    }

    /**
     * @test
     */
    public function reservationCanBeSet()
    {
        $mockReservation = $this->getMock(
            Reservation::class
        );
        $this->subject->setReservation($mockReservation);

        $this->assertSame(
            $mockReservation,
            $this->subject->getReservation()
        );
    }

    /**
     * @test
     */
    public function getBirthPlaceReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getBirthplace()
        );
    }
    
    /**
     * @test
     */
    public function birthPlaceCanBeSet()
    {
        $birthplace = 'foo';
        $this->subject->setBirthplace($birthplace);
        
        $this->assertSame(
            $birthplace,
            $this->subject->getBirthplace()
        );
    }

    /**
     * @test
     */
    public function getCompanyNameReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getCompanyName()
        );
    }

    /**
     * @test
     */
    public function companyNameCanBeSet()
    {
        $companyName = 'foo';
        $this->subject->setCompanyName($companyName);

        $this->assertSame(
            $companyName,
            $this->subject->getCompanyName()
        );
    }

    /**
     * @test
     */
    public function getRoleReturnsInitiallyNull()
    {
        $this->assertNull(
            $this->subject->getRole()
        );
    }

    /**
     * @test
     */
    public function roleCanBeSet()
    {
        $role = 'foo';
        $this->subject->setRole($role);

        $this->assertSame(
            $role,
            $this->subject->getRole()
        );
    }
}
