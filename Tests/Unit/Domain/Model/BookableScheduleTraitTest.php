<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model;

use CPSIT\T3eventsReservation\Domain\Model\BookableScheduleTrait;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

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
class BookableScheduleTraitTest extends UnitTestCase
{
    /**
     * @var BookableScheduleTrait
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            BookableScheduleTrait::class
        );
    }

    /**
     * @test
     */
    public function initializeObjectSetsParticipantsToObjectStorage()
    {
        $emptyObjectStorage = new ObjectStorage();
        $this->subject->initializeObject();
        $this->assertEquals(
            $emptyObjectStorage,
            $this->subject->getParticipants()
        );
    }

    /**
     * @test
     */
    public function initializeObjectSetsRegistrationDocumentsToObjectStorage()
    {
        $emptyObjectStorage = new ObjectStorage();
        $this->subject->initializeObject();
        $this->assertEquals(
            $emptyObjectStorage,
            $this->subject->getRegistrationDocuments()
        );
    }

    /**
     * @test
     */
    public function getDeadLineInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getDeadline()
        );
    }

    /**
     * @test
     */
    public function deadLineCanBeSet()
    {
        $dateTime = new \DateTime('now');
        $this->subject->setDeadline($dateTime);
        $this->assertSame(
            $dateTime,
            $this->subject->getDeadline()
        );
    }

    /**
     * @test
     */
    public function getPriceReturnsInitialValue()
    {
        $this->assertSame(
            0.0,
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function priceCanBeSet()
    {
        $price = 12.33;
        $this->subject->setPrice($price);
        $this->assertSame(
            $price,
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function getPlacesReturnsInitialValue()
    {
        $this->assertSame(
            0,
            $this->subject->getPlaces()
        );
    }

    /**
     * @test
     */
    public function placesCanBeSet()
    {
        $places = 10;
        $this->subject->setPlaces($places);
        $this->assertSame(
            $places,
            $this->subject->getPlaces()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function registrationBeginCanBeSet()
    {
        $dateTime = new \DateTime('now');
        $this->subject->setRegistrationBegin($dateTime);
        $this->assertSame(
            $dateTime,
            $this->subject->getRegistrationBegin()
        );
    }


    /**
     * @test
     */
    public function isFreeOfChargeInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->isFreeOfCharge()
        );
    }

    /**
     * @test
     */
    public function freeOfChargeCanBeSet()
    {
        $this->subject->setFreeOfCharge(true);
        $this->assertTrue(
            $this->subject->isFreeOfCharge()
        );
    }

    /**
     * @test
     */
    public function getRegistrationRemarksInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getRegistrationRemarks()
        );
    }

    /**
     * @test
     */
    public function registrationRemarksCanBeSet()
    {
        $remarks = 'foo';
        $this->subject->setRegistrationRemarks($remarks);
        $this->assertSame(
            $remarks,
            $this->subject->getRegistrationRemarks()
        );
    }

    /**
     * @test
     */
    public function isDocumentBasedRegistrationInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->isDocumentBasedRegistration()
        );
    }

    /**
     * @test
     */
    public function documentBasedRegistrationCanBeSet()
    {
        $this->subject->setDocumentBasedRegistration(true);
        $this->assertTrue(
            $this->subject->isDocumentBasedRegistration()
        );
    }

    /**
     * @test
     */
    public function isExternalRegistrationInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->isExternalRegistration()
        );
    }

    /**
     * @test
     */
    public function externalRegistrationCanBeSet()
    {
        $this->subject->setExternalRegistration(true);
        $this->assertTrue(
            $this->subject->isExternalRegistration()
        );
    }

    /**
     * @test
     */
    public function getExternalRegistrationLinkInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getExternalRegistrationLink()
        );
    }

    /**
     * @test
     */
    public function externalRegistrationLinkCanBeSet()
    {
        $link = 'foo';
        $this->subject->setExternalRegistrationLink($link);
        $this->assertSame(
            $link,
            $this->subject->getExternalRegistrationLink()
        );
    }

    /**
     * @test
     */
    public function registrationDocumentsCanBeSet()
    {
        $registrationDocuments = new ObjectStorage();
        $mockFileReference = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()->getMock();
        $registrationDocuments->attach($mockFileReference);
        $this->subject->setRegistrationDocuments($registrationDocuments);
        $this->assertSame(
            $registrationDocuments,
            $this->subject->getRegistrationDocuments()
        );
    }

    /**
     * @test
     */
    public function registrationDocumentCanBeAdded()
    {
        /** @var FileReference $mockFileReference */
        $mockFileReference = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()->getMock();
        $this->subject->initializeObject();
        $this->subject->addRegistrationDocument($mockFileReference);

        $this->assertTrue(
            $this->subject->getRegistrationDocuments()->contains($mockFileReference)
        );
    }

    /**
     * @test
     */
    public function registrationDocumentCanBeRemoved()
    {
        $registrationDocuments = new ObjectStorage();
        /** @var FileReference $mockFileReference */
        $mockFileReference = $this->getMockBuilder(FileReference::class)
            ->disableOriginalConstructor()->getMock();
        $registrationDocuments->attach($mockFileReference);
        $this->subject->setRegistrationDocuments($registrationDocuments);
        $this->subject->removeRegistrationDocument($mockFileReference);

        $this->assertFalse(
            $this->subject->getRegistrationDocuments()->contains($mockFileReference)
        );
    }

    /**
     * @test
     */
    public function participantsCanBeSet()
    {
        $participants = new ObjectStorage();
        $this->subject->setParticipants($participants);
        $this->assertSame(
            $participants,
            $this->subject->getParticipants()
        );
    }

    /**
     * @test
     */
    public function participantCanBeAdded()
    {
        /** @var Person $mockParticipant */
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $this->subject->initializeObject();
        $this->subject->addParticipant($mockParticipant);
        $this->assertTrue(
            $this->subject->getParticipants()->contains($mockParticipant)
        );
    }

    /**
     * @test
     */
    public function participantCanBeRemoved()
    {
        $participants = new ObjectStorage();
        /** @var Person $mockParticipant */
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $participants->attach($mockParticipant);
        $this->subject->setParticipants($participants);
        $this->subject->removeParticipant($mockParticipant);
        $this->assertFalse(
            $this->subject->getParticipants()->contains($mockParticipant)
        );
    }

    /**
     * @test
     */
    public function getFreePlacesReturnsInitialValue()
    {
        $this->subject->initializeObject();
        $this->assertSame(
            0,
            $this->subject->getFreePlaces()
        );
    }

    /**
     * @test
     */
    public function getFreePlacesReturnsDifferenceOfPlacesAndParticipantsCount()
    {
        $this->subject->initializeObject();
        $this->subject->setPlaces(5);
        /** @var Person $mockParticipant */
        $mockParticipant = $this->getMockBuilder(Person::class)->getMock();
        $this->subject->addParticipant($mockParticipant);

        $this->assertSame(
            4,
            $this->subject->getFreePlaces()
        );
    }
}
