<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use CPSIT\T3eventsReservation\Controller\BillingAddressController;
use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use CPSIT\T3eventsReservation\Domain\Model\Contact;
use CPSIT\T3eventsReservation\Domain\Model\Notification;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Model\Schedule;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Webfox\T3events\Domain\Model\Company;
use Webfox\T3events\Domain\Model\Performance;

/**
 * Test case for class \CPSIT\T3eventsReservation\Domain\Model\Reservation.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Dirk Wenzel <wenzel@cps-it.de>
 */
class ReservationTest extends UnitTestCase {
	/**
	 * @var \CPSIT\T3eventsReservation\Domain\Model\Reservation
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = $this->getAccessibleMock(
			Reservation::class, ['dummy']
		);
	}

	/**
	 * @test
	 */
	public function getStatusReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusForIntegerSetsStatus() {
		$this->subject->setStatus(12);

		$this->assertAttributeEquals(
			12,
			'status',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getCompanyReturnsInitialValueForCompany() {
		$this->assertEquals(
			NULL,
			$this->subject->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setCompanyForCompanySetsCompany() {
		$companyFixture =$this->getMock(
			Company::class
		);
		$this->subject->setCompany($companyFixture);

		$this->assertAttributeEquals(
			$companyFixture,
			'company',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getContactReturnsInitialValueForPerson() {
		$this->assertEquals(
			NULL,
			$this->subject->getContact()
		);
	}

	/**
	 * @test
	 */
	public function setContactForPersonSetsContact() {
		$contactFixture = $this->getMock(Contact::class);
		$this->subject->setContact($contactFixture);

		$this->assertAttributeEquals(
			$contactFixture,
			'contact',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getParticipantsReturnsInitialValueForPerson() {
		$newObjectStorage = new ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getParticipants()
		);
	}

	/**
	 * @test
	 */
	public function setParticipantsForObjectStorageContainingPersonSetsParticipants() {
		$participant = new Person();
		$objectStorageHoldingExactlyOneParticipants = new ObjectStorage();
		$objectStorageHoldingExactlyOneParticipants->attach($participant);
		$this->subject->setParticipants($objectStorageHoldingExactlyOneParticipants);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneParticipants,
			'participants',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addParticipantToObjectStorageHoldingParticipants() {
		$participant = new Person();
		$participantsObjectStorageMock = $this->getMock(
			ObjectStorage::class, ['attach'], [], '', false);
		$participantsObjectStorageMock->expects($this->once())
			->method('attach')
			->with($participant);
		$this->inject($this->subject, 'participants', $participantsObjectStorageMock);

		$this->subject->addParticipant($participant);
	}

	/**
	 * @test
	 */
	public function removeParticipantFromObjectStorageHoldingParticipants() {
		$participant = new Person();
		$participantsObjectStorageMock = $this->getMock(
			ObjectStorage::class, ['detach'], [], '', FALSE);
		$participantsObjectStorageMock->expects($this->once())
			->method('detach')
			->with($participant);
		$this->inject($this->subject, 'participants', $participantsObjectStorageMock);

		$this->subject->removeParticipant($participant);

	}

	/**
	 * @test
	 */
	public function getLessonReturnsInitialValueForLesson() {
		$this->assertNull(
			$this->subject->getLesson()
		);
	}

	/**
	 * @test
	 */
	public function setLessonForLessonSetsLesson() {
		$lesson = new Performance();
		$this->subject->setLesson($lesson);
		$this->assertSame(
			$lesson,
			$this->subject->getLesson()
		);
	}

	/**
	 * @test
	 */
	public function getTotalPriceForFloatReturnsInitialValue() {
		$this->assertSame(
			0.0,
			$this->subject->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function totalPriceCanBeSet() {
		$total = 123.45;
		$this->subject->setTotalPrice($total);
		$this->assertSame(
			$total,
			$this->subject->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function getPrivacyStatementAcceptedForBoolInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->subject->getPrivacyStatementAccepted()
		);
	}

	/**
	 * @test
	 */
	public function getPrivacyStatementAcceptedCanBeSet() {
		$this->subject->setPrivacyStatementAccepted(true);
		$this->assertTrue(
			$this->subject->getPrivacyStatementAccepted()
		);
	}

	/**
	 * @test
	 */
	public function addParticipantUpdatesTotalPrice() {
		$this->subject = $this->getAccessibleMock(
			Reservation::class, ['getLesson']
		);
		$singlePrice = 123.45;
		$totalPrice = 2 * $singlePrice;
		$firstParticipant = new Person();
		$secondParticipant = new Person();
		$lesson = $this->getMock(
			Schedule::class, ['getPrice']
		);

		$lesson->expects($this->any())
			->method('getPrice')
			->will($this->returnValue($singlePrice));
		$this->subject->expects($this->any())
			->method('getLesson')
			->will($this->returnValue($lesson));

		$this->subject->setParticipants(new ObjectStorage());
		$this->subject->addParticipant($secondParticipant);
		$this->subject->addParticipant($firstParticipant);

		$this->assertSame(
			$totalPrice,
			$this->subject->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function removeParticipantUpdatesTotalPrice() {
		$this->subject = $this->getAccessibleMock(
			Reservation::class, ['getLesson']
		);
		$singlePrice = 123.45;
		$totalPrice = $singlePrice;
		$firstParticipant = new Person();
		$secondParticipant = new Person();
		$objectStorageContainingTwoParticipants = new ObjectStorage();
		$objectStorageContainingTwoParticipants->attach($firstParticipant);
		$objectStorageContainingTwoParticipants->attach($secondParticipant);

		$lesson = $this->getMock(
			Schedule::class, ['getPrice']
		);

		$lesson->expects($this->any())
			->method('getPrice')
			->will($this->returnValue($singlePrice));
		$this->subject->expects($this->any())
			->method('getLesson')
			->will($this->returnValue($lesson));

		$this->subject->setParticipants($objectStorageContainingTwoParticipants);

		$this->subject->removeParticipant($secondParticipant);

		$this->assertSame(
			$totalPrice,
			$this->subject->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function setParticipantsUpdatesTotalPrice() {
		$this->subject = $this->getAccessibleMock(
			Reservation::class, ['getLesson']
		);
		$singlePrice = 123.45;
		$totalPrice = 2 * $singlePrice;
		$firstParticipant = new Person();
		$secondParticipant = new Person();
		$objectStorageContainingTwoParticipants = new ObjectStorage();
		$objectStorageContainingTwoParticipants->attach($firstParticipant);
		$objectStorageContainingTwoParticipants->attach($secondParticipant);

		$lesson = $this->getMock(
			Schedule::class, ['getPrice']
		);

		$lesson->expects($this->any())
			->method('getPrice')
			->will($this->returnValue($singlePrice));
		$this->subject->expects($this->any())
			->method('getLesson')
			->will($this->returnValue($lesson));

		$this->subject->setParticipants($objectStorageContainingTwoParticipants);

		$this->assertSame(
			$totalPrice,
			$this->subject->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function removeBillingAddressSetsBillingAddressToNull()
	{
		$this->subject->setBillingAddress(new BillingAddress());
		$this->subject->removeBillingAddress();
		$this->assertNull(
			$this->subject->getBillingAddress()
		);
	}

	/**
	 * @test
	 */
	public function getHiddenInitiallyReturnsNull()
	{
		$this->assertNull(
			$this->subject->getHidden()
		);
	}

	/**
	 * @test
	 */
	public function hiddenCanBeSet()
	{
		$this->subject->setHidden(true);
		$this->assertTrue(
			$this->subject->getHidden()
		);
	}

    /**
     * @test
     */
    public function getContactIsParticipantInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getContactIsParticipant()
        );
    }

    /**
     * @test
     */
    public function contactIsParticipantCanBeSet()
    {
        $this->subject->setContactIsParticipant(true);
        $this->assertTrue(
            $this->subject->getContactIsParticipant()
        );
    }

    /**
     * @test
     */
    public function getNoteInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getNote()
        );
    }

    /**
     * @test
     */
    public function noteCanBeSet()
    {
        $note = 'foo';
        $this->subject->setNote($note);

        $this->assertSame(
            $note,
            $this->subject->getNote()
        );
    }

    /**
     * @test
     */
    public function getDisclaimRevocationInitiallyReturnsFalse()
    {
        $this->assertFalse(
            $this->subject->getDisclaimRevocation()
        );
    }

    /**
     * @test
     */
    public function disClaimRevocationCanBeSet()
    {
        $this->subject->setDisclaimRevocation(true);
        $this->assertTrue(
            $this->subject->getDisclaimRevocation()
        );
    }

    /**
     * @test
     */
    public function getNotificationsInitiallyReturnsEmptyObjectStorage()
    {
        $emptyObjectStorage = new ObjectStorage();
        $this->subject->initializeObject();
        $this->assertEquals(
            $emptyObjectStorage,
            $this->subject->getNotifications()
        );
    }

    /**
     * @test
     */
    public function notificationsCanBeSet()
    {
        $emptyObjectStorage = new ObjectStorage();
        $this->subject->setNotifications($emptyObjectStorage);

        $this->assertSame(
            $emptyObjectStorage,
            $this->subject->getNotifications()
        );
    }

    /**
     * @test
     */
    public function notificationCanBeAdded()
    {
        $notification = new Notification();
        $this->subject->addNotification($notification);
        $this->assertTrue(
            $this->subject->getNotifications()->contains($notification)
        );
    }

    /**
     * @test
     */
    public function notificationCanBeRemoved()
    {
        $notification = new Notification();
        $this->subject->addNotification($notification);
        $this->subject->removeNotification($notification);
        $this->assertFalse(
            $this->subject->getNotifications()->contains($notification)
        );
    }
}
