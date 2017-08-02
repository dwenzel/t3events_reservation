<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\DataProvider\RouteLoader;

use CPSIT\T3eventsReservation\DataProvider\RouteLoader\BillingAddressControllerDefaults;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */
class BillingAddressControllerDefaultsTest extends UnitTestCase
{
    /**
     * @var BillingAddressControllerDefaults|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            BillingAddressControllerDefaults::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function dataProviderReturnsDefaultConfiguration()
    {
        $this->assertNotEmpty(
            $this->subject->getConfiguration()
        );
    }
}
