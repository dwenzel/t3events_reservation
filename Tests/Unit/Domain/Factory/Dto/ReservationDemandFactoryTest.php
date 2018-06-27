<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Factory\Dto;

use CPSIT\T3eventsReservation\Domain\Factory\Dto\ReservationDemandFactory;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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

/**
 * Class ReservationDemandFactoryTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Factory\Dto
 */
class ReservationDemandFactoryTest extends UnitTestCase
{
    /**
     * @var ReservationDemandFactory
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ReservationDemandFactory::class, ['dummy'], [], '', false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockObjectManager() {
        $mockObjectManager = $this->getMockBuilder(ObjectManager::class)
            ->setMethods(['get'])->getMock();
        $this->subject->injectObjectManager($mockObjectManager);

        return $mockObjectManager;
    }


    /**
     * @test
     */
    public function createFromSettingsReturnsReservationDemand() {
        $mockDemand = $this->getMockBuilder(ReservationDemand::class)->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->with(ReservationDemand::class)
            ->will($this->returnValue($mockDemand));

        $this->assertSame(
            $mockDemand,
            $this->subject->createFromSettings([])
        );
    }


    /**
     * @test
     */
    public function createFromSettingsSetsPeriodTypeForSpecificPeriod() {
        $periodType = 'foo';
        $settings = [
            'period' => 'specific',
            'periodType' => $periodType
        ];
        $mockDemand = $this->getMockBuilder(ReservationDemand::class)
            ->setMethods(['dummy'])->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertAttributeSame(
            $periodType,
            'periodType',
            $createdDemand
        );

    }


    /**
     * @test
     */
    public function createFromSettingsSetsPeriodStartAndDurationIfPeriodTypeIsNotByDate() {
        $periodType = 'fooPeriodType-notByDate';
        $periodStart = '30';
        $periodDuration = '20';
        $settings = [
            'periodType' => $periodType,
            'periodStart' => $periodStart,
            'periodDuration' => $periodDuration
        ];
        $mockDemand = $this->getMockBuilder(ReservationDemand::class)
            ->setMethods(['dummy'])->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertAttributeSame(
            (int)$periodStart,
            'periodStart',
            $createdDemand
        );

        $this->assertAttributeSame(
            (int)$periodDuration,
            'periodDuration',
            $createdDemand
        );
    }

    /**
     * @test
     */
    public function createFromSettingsSetsStartDateForPeriodTypeByDate() {
        $periodType = 'byDate';
        $startDate = '2012-10-10';
        $settings = [
            'periodType' => $periodType,
            'periodStartDate' => $startDate
        ];

        $mockDemand = $this->getMockBuilder(ReservationDemand::class)
            ->setMethods(['dummy'])->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $createdDemand = $this->subject->createFromSettings($settings);

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime($startDate, $timeZone);

        $this->assertAttributeEquals(
            $expectedStartDate,
            'startDate',
            $createdDemand
        );

    }

    /**
     * @test
     */
    public function createFromSettingsSetsEndDateForPeriodTypeByDate() {
        $periodType = 'byDate';
        $endDate = '2012-10-10';
        $settings = [
            'periodType' => $periodType,
            'periodEndDate' => $endDate
        ];

        $mockDemand = $this->getMockBuilder(ReservationDemand::class)
            ->setMethods(['dummy'])->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $createdDemand = $this->subject->createFromSettings($settings);

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedStartDate = new \DateTime($endDate, $timeZone);

        $this->assertAttributeEquals(
            $expectedStartDate,
            'endDate',
            $createdDemand
        );

    }

    /**
     * @return array
     */
    public function skippedPropertiesDataProvider () {
        return [
            ['foo', ''],
            ['periodType', 'bar'],
            ['periodStart', 'bar'],
            ['periodDuration', 'bar'],
            ['search', 'bar']
        ];
    }

    /**
     * @test
     * @dataProvider skippedPropertiesDataProvider
     * @param $propertyName
     * @param $propertyValue
     */
    public function createFromSettingsDoesNotSetSkippedValues($propertyName, $propertyValue) {
        $settings = [
            $propertyName => $propertyValue
        ];
        $mockDemand = $this->getMockBuilder(ReservationDemand::class)
            ->setMethods(['dummy'])->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertEquals(
            $createdDemand,
            $mockDemand
        );
    }


    /**
     * @return array
     */
    public function settablePropertiesDataProvider() {
        /** propertyName, $settingsValue, $expectedValue */
        return [
            ['genres', '1,2', '1,2'],
            ['status', '1,2', '1,2'],
            ['eventTypes', '5,6', '5,6'],
            ['categoryConjunction', 'and', 'and'],
            ['limit', '50', 50],
            ['offset', '10', 10],
            ['uidList', '7,8,9', '7,8,9'],
            ['storagePages', '7,8,9', '7,8,9'],
            ['order', 'foo|bar,baz|asc', 'foo|bar,baz|asc'],
            ['sortBy', 'headline', 'headline'],
            ['sortBy', 'date', 'date']
        ];
    }

    /**
     * @test
     * @dataProvider settablePropertiesDataProvider
     * @param string $propertyName
     * @param string|int $settingsValue
     * @param mixed $expectedValue
     */
    public function createFromSettingsSetsSettableProperties($propertyName, $settingsValue, $expectedValue) {
        $settings = [
            $propertyName => $settingsValue
        ];
        $mockDemand = $this->getMockBuilder(ReservationDemand::class)
            ->setMethods(['dummy'])->getMock();
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $createdDemand = $this->subject->createFromSettings($settings);
        $this->assertAttributeSame(
            $expectedValue,
            $propertyName,
            $createdDemand
        );
    }
}
