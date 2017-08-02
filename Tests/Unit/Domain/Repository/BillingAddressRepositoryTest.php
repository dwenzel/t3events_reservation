<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *  (c) 2016 Dirk Wenzel <dirk.wenzel@cps-it.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository;

/**
 * Test case for class \CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository.
 *
 * @author Dirk Wenzel <dirk.wenzel@cps-it.de>
 * @coversDefaultClass \CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository
 */
class BillingAddressRepositoryTest extends UnitTestCase {

	/**
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\BillingAddressRepository | \PHPUnit_Framework_MockObject_MockObject | \TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = $this->getAccessibleMock(
			BillingAddressRepository::class,
			['dummy'], [], '', FALSE);
	}

	/**
	 * @test
	 * @covers ::createConstraintsFromDemand
	 */
	public function createConstraintsFromDemandInitiallyReturnsEmptyArray() {
		$demand = $this->getMock(DemandInterface::class);
		$query = $this->getMock(QueryInterface::class, [], [], '', FALSE);

		$this->assertEquals(
			[],
			$this->fixture->createConstraintsFromDemand($query, $demand)
		);
	}

}

