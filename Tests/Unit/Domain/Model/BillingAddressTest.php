<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model;

use CPSIT\T3eventsReservation\Domain\Model\BillingAddress;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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
class BillingAddressTest extends UnitTestCase
{
    /**
     * @var BillingAddress
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new BillingAddress();
    }

    /**
     * @test
     */
    public function getCompanyNameInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getCompanyName()
        );
    }

    /**
     * @test
     */
    public function companyNameCanBeSet()
    {
        $companyName = 'foo';
        $this->subject->setCompanyName($companyName);
        $this->assertSame(
            $companyName,
            $this->subject->getCompanyName()
        );
    }

    /**
     * @test
     */
    public function getVatIdInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getVatId()
        );
    }

    /**
     * @test
     */
    public function vatIdCanBeSet()
    {
        $vatId = 'foo';
        $this->subject->setVatId($vatId);
        $this->assertSame(
            $vatId,
            $this->subject->getVatId()
        );
    }

    /**
     * @test
     */
    public function getAccountingOfficeInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getAccountingOffice()
        );
    }

    /**
     * @test
     */
    public function accountingOfficeCanBeSet()
    {
        $accountingOffice = 'foo';
        $this->subject->setAccountingOffice($accountingOffice);
        $this->assertSame(
            $accountingOffice,
            $this->subject->getAccountingOffice()
        );
    }

    /**
     * @test
     */
    public function getTypeInitiallyReturnsClassConstant()
    {
        $this->assertSame(
            BillingAddress::PERSON_TYPE_BILLING_ADDRESS,
            $this->subject->getType()
        );
    }
}
