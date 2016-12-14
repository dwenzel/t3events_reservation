<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto;

use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
class ReservationDemandTest extends UnitTestCase {
	/**
	 * @var ReservationDemand
	 */
	protected $subject;

	/**
	 * set up
	 */
	public function setUp() {
		$this->subject = $this->getAccessibleMock(
			ReservationDemand::class, ['dummy']
		);
	}

	/**
	 * @test
	 */
	public function getStatusInitiallyReturnsNull() {
		$this->assertNull(
			$this->subject->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function statusCanBeSet() {
		$status = '1,2';
		$this->subject->setStatus($status);

		$this->assertSame(
			$status,
			$this->subject->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function getLessonDeadLineInitiallyReturnsNull() {
		$this->assertNull(
			$this->subject->getLessonDeadline()
		);
	}

	/**
	 * @test
	 */
	public function lessonDeadLineCanBeSet() {
		$lessonDeadLine = new \DateTime();

		$this->subject->setLessonDeadline($lessonDeadLine);

		$this->assertSame(
			$lessonDeadLine,
			$this->subject->getLessonDeadline()
		);
	}


	/**
	 * @test
	 */
	public function getLessonDateInitiallyReturnsNull() {
		$this->assertNull(
			$this->subject->getLessonDate()
		);
	}

	/**
	 * @test
	 */
	public function lessonDateCanBeSet() {
		$lessonDate = new \DateTime();

		$this->subject->setLessonDate($lessonDate);

		$this->assertSame(
			$lessonDate,
			$this->subject->getLessonDate()
		);
	}

	/**
	 * @test
	 */
	public function getMinAgeInitiallyReturnsNull() {
		$this->assertNull(
			$this->subject->getMinAge()
		);
	}

	/**
	 * @test
	 */
	public function minAgeCanBeSet() {
		$minAge = 12;
		$this->subject->setMinAge($minAge);

		$this->assertSame(
			$minAge,
			$this->subject->getMinAge()
		);
	}

	/**
	 * @test
	 */
	public function getStartDateFieldReturnsClassConstant() {
		$this->assertSame(
			ReservationDemand::START_DATE_FIELD,
			$this->subject->getStartDateField()
		);
	}

	/**
	 * @test
	 */
	public function getEndDateFieldReturnsClassConstant() {
		$this->assertSame(
			ReservationDemand::END_DATE_FIELD,
			$this->subject->getEndDateField()
		);
	}

	/**
	 * @test
	 */
	public function getGenreFieldReturnsClassConstant() {
		$this->assertSame(
			ReservationDemand::GENRE_FIELD,
			$this->subject->getGenreField()
		);
	}

	/**
	 * @test
	 */
	public function getAudienceFieldReturnsClassConstant() {
		$this->assertSame(
			ReservationDemand::AUDIENCE_FIELD,
			$this->subject->getAudienceField()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeFieldReturnsClassConstant() {
		$this->assertSame(
			ReservationDemand::EVENT_TYPE_FIELD,
			$this->subject->getEventTypeField()
		);
	}

}
