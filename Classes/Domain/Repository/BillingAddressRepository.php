<?php
namespace CPSIT\T3eventsReservation\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *  (c) 2014 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *           Boerge Franck <franck@cps-it.de>, CPS IT
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
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Repository\AbstractDemandedRepository;
use DWenzel\T3events\Domain\Repository\DemandedRepositoryInterface;

/**
 * The repository for billing addresses
 */
class BillingAddressRepository
	extends AbstractDemandedRepository
	implements DemandedRepositoryInterface {
	/**
  * Returns an array of constraints created from a given demand object.
  *
  * @return array<\TYPO3\CMS\Extbase\Persistence\Generic\Qom\Constraint>
  */
 public function createConstraintsFromDemand(QueryInterface $query, DemandInterface $demand) {
		/** @var \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand $demand */
		$constraints = [];
		return $constraints;
	}

}
