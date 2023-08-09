<?php
namespace CPSIT\T3eventsReservation\Domain\Model;

use DWenzel\T3events\Domain\Model\Person as BasePerson;

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
class BillingAddress extends BasePerson {
	final public const PERSON_TYPE_BILLING_ADDRESS = 'Tx_T3eventsReservation_BillingAddress';

	/**
	 * Record type
	 *
	 * @var string
	 */
	protected $type = self::PERSON_TYPE_BILLING_ADDRESS;

	/**
	 * VAT ID
	 *
	 * @var string
	 */
	protected $vatId;

	/**
	 * Accounting office
	 *
	 * @var string
	 */
	protected $accountingOffice;

	/**
	 * @var string
	 */
	protected $companyName;

	/**
	 * @return string
	 */
	public function getCompanyName() {
		return $this->companyName;
	}

	/**
	 * @param string $companyName
	 */
	public function setCompanyName($companyName) {
		$this->companyName = $companyName;
	}

	/**
	 * Gets the vat id
	 *
	 * @return string
	 */
	public function getVatId() {
		return $this->vatId;
	}

	/**
	 * Sets the vat id
	 *
	 * @param string $vatId
	 */
	public function setVatId($vatId) {
		$this->vatId = $vatId;
	}

	/**
	 * Gets the accounting office
	 *
	 * @return string
	 */
	public function getAccountingOffice() {
		return $this->accountingOffice;
	}

	/**
	 * Sets the accounting office
	 *
	 * @param string $accountingOffice
	 */
	public function setAccountingOffice($accountingOffice) {
		$this->accountingOffice = $accountingOffice;
	}
}
