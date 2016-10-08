<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Validator;

use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Validator\ContactValidator;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Error\Result;

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
class ContactValidatorTest extends UnitTestCase
{
    /**
     * @var ContactValidator
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ContactValidator::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function validateAddsErrorForWrongObjectType()
    {
        $objectOfWrongType = new \stdClass();
        $expectedResult = new Result();
        $expectedError = new Error('Contact must be a Person.', 1410958031);
        $expectedResult->addError($expectedError);

        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($objectOfWrongType)
        );
    }

    /**
     * @test
     */
    public function validateAddsErrorForMissingEmail()
    {
        $contact = new Person();
        $contact->setType(Person::PERSON_TYPE_CONTACT);

        $expectedResult = new Result();
        $expectedError = new Error('email is required.', 1410958066);
        $expectedResult->addError($expectedError);

        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($contact)
        );
    }

    /**
     * @test
     */
    public function validateReturnsEmptyResultForValidContact()
    {
        $email = 'nix@nix.com';
        $contact = new Person();
        $contact->setEmail($email);

        $expectedResult = new Result();
        $this->assertEquals(
            $expectedResult,
            $this->subject->validate($contact)
        );
    }
}
