<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Validator;

use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Validator\ParticipantValidator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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
class ParticipantValidatorTest extends UnitTestCase
{
    /**
     * @var ParticipantValidator
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ParticipantValidator::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfObjectIsNotAPerson()
    {
        $objectOfWrongType = new \stdClass();
        $this->assertFalse(
            $this->subject->isValid($objectOfWrongType)
        );
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfPersonHasWrongType()
    {
        $personWithWrongType = new Person();
        $personWithWrongType->setType('foo');

        $this->assertFalse(
            $this->subject->isValid($personWithWrongType)
        );
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfReservationIsNotSet()
    {
        $participant = new Person();
        $this->assertFalse(
            $this->subject->isValid($participant)
        );
    }

    /**
     * @test
     */
    public function isValidReturnsTrueForValidParticipant()
    {
        $participant = new Person();
        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMock(
            Reservation::class
        );
        $participant->setReservation($mockReservation);
        $this->assertTrue(
            $this->subject->isValid($participant)
        );
    }
}
