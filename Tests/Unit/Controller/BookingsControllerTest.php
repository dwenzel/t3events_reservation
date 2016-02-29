<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

use CPSIT\T3eventsReservation\Controller\Backend\BookingsController;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;
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
class BookingsControllerTest extends UnitTestCase {

	/**
	 * @var BookingsController
	 */
	protected $subject;

	/**
	 * set up
	 */
	public function setUp() {
		$this->subject = $this->getAccessibleMock(
			BookingsController::class, ['dummy'], [], '', false
		);
	}

	/**
	 * @test
	 */
	public function personRepositoryCanBeInjected() {
		$mockRepository = $this->getMock(
			PersonRepository::class, [], [], '', false
		);

		$this->subject->injectPersonRepository($mockRepository);

		$this->assertAttributeSame(
			$mockRepository,
			'personRepository',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function reservationRepositoryCanBeInjected() {
		$mockRepository = $this->getMock(
			ReservationRepository::class, [], [], '', false
		);

		$this->subject->injectReservationRepository($mockRepository);

		$this->assertAttributeSame(
			$mockRepository,
			'reservationRepository',
			$this->subject
		);
	}
}
