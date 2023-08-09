<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Validator;

use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Validator\ParticipantValidator;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Error;

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
    public function validateAddsErrorForWrongObjectType()
    {
        $objectOfWrongType = new \stdClass();
        $expectedResult = new Result();
        $expectedError = new Error('Participant must be a Person.', 1_465_382_176);
        $expectedResult->addError($expectedError);
        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($objectOfWrongType)
        );
    }

    /**
     * @test
     */
    public function validateAddsErrorForWrongPersonType()
    {
        $personWithWrongType = new Person();
        $personWithWrongType->setType('foo');

        $expectedResult = new Result();
        $expectedError = new Error(
            'Wrong person type: foo.  Participant must be of type '
            . Person::class . '::PERSON_TYPE_PARTICIPANT.',
            1_465_382_335);
        $expectedResult->addError($expectedError);
        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($personWithWrongType)
        );
    }

    /**
     * @test
     */
    public function validateAddsErrorForMissingReservation()
    {
        $participant = new Person();
        $participant->setType(Person::PERSON_TYPE_PARTICIPANT);

        $expectedResult = new Result();
        $expectedError = new Error('Missing reservation.', 1_465_389_725);
        $expectedResult->addError($expectedError);

        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($participant)
        );
    }

    /**
     * @test
     */
    public function validateReturnsEmptyResultForValidParticipant()
    {
        $participant = new Person();
        /** @var Reservation $mockReservation */
        $mockReservation = $this->getMockBuilder(Reservation::class)->getMock();
        $participant->setReservation($mockReservation);

        $expectedResult = new Result();
        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($participant)
        );
    }
}
