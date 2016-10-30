<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Factory\Dto;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Factory\Dto\PersonDemandFactory;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;

class PersonDemandFactoryTest extends UnitTestCase
{

    /**
     * @var PersonDemandFactory
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            PersonDemandFactory::class, ['dummy'], [], '', false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockObjectManager()
    {
        $mockObjectManager = $this->getMock(
            ObjectManager::class, ['get']
        );
        $this->subject->injectObjectManager($mockObjectManager);

        return $mockObjectManager;
    }

    /**
     * @param $settings
     * @return \DWenzel\T3events\Domain\Model\Dto\DemandInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockObjectManagerCreatesDemand($settings)
    {
        /** @var PersonDemand $mockDemand */
        $mockDemand = $this->getMock(
            PersonDemand::class, ['dummy']
        );
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        return $mockDemand;
    }

    /**
     * @test
     */
    public function objectManagerCanBeInjected()
    {
        $mockObjectManager = $this->mockObjectManager();

        $this->assertAttributeSame(
            $mockObjectManager,
            'objectManager',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function createFromSettingsReturnsPersonDemand()
    {
        $mockDemand = $this->getMock(
            PersonDemand::class
        );
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->with(PersonDemand::class)
            ->will($this->returnValue($mockDemand));

        $this->assertSame(
            $mockDemand,
            $this->subject->createFromSettings([])
        );
    }

    /**
     * @return array
     */
    public function settablePropertiesDataProvider()
    {
        /** propertyName, $settingsValue, $expectedValue */
        return [
            ['audiences', '5,8', '5,8'],
            ['categories', '7,8', '7,8'],
            ['genres', '3,4', '3,4'],
            ['types', '9,4', '9,4'],
            ['lessonPeriod', 'futureOnly', 'futureOnly'],
            ['eventTypes', '1,2', '1,2'],
            ['categoryConjunction', 'and', 'and'],
            ['limit', '50', 50],
            ['offset', '10', 10],
            ['uidList', '7,8,9', '7,8,9'],
            ['storagePages', '7,8,9', '7,8,9'],
            ['order', 'foo|bar,baz|asc', 'foo|bar,baz|asc'],
            ['sortBy', 'firstName', 'firstName']
        ];
    }

    /**
     * @test
     * @dataProvider settablePropertiesDataProvider
     * @param string $propertyName
     * @param string|int $settingsValue
     * @param mixed $expectedValue
     */
    public function createFromSettingsSetsSettableProperties($propertyName, $settingsValue, $expectedValue)
    {
        $settings = [
            $propertyName => $settingsValue
        ];
        $this->mockObjectManagerCreatesDemand($settings);
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertAttributeSame(
            $expectedValue,
            $propertyName,
            $createdDemand
        );
    }

    /**
     * @return array
     */
    public function mappedPropertiesDataProvider()
    {
        /** settingsKey, propertyName, $settingsValue, $expectedValue */
        return [
            ['maxItems', 'limit', '50', 50],
        ];
    }

    /**
     * @test
     * @dataProvider mappedPropertiesDataProvider
     * @param string $settingsKey
     * @param string $propertyName
     * @param string|int $settingsValue
     * @param mixed $expectedValue
     */
    public function createFromSettingsSetsMappedProperties($settingsKey, $propertyName, $settingsValue, $expectedValue)
    {
        $settings = [
            $settingsKey => $settingsValue
        ];
        $this->mockObjectManagerCreatesDemand($settings);
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertAttributeSame(
            $expectedValue,
            $propertyName,
            $createdDemand
        );
    }

    /**
     * @return array
     */
    public function skippedPropertiesDataProvider()
    {
        return [
            ['foo', ''],
            ['search', 'bar']
        ];
    }

    /**
     * @test
     * @dataProvider skippedPropertiesDataProvider
     */
    public function createFromSettingsDoesNotSetSkippedValues($propertyName, $propertyValue)
    {
        $settings = [
            $propertyName => $propertyValue
        ];
        $mockDemand = $this->mockObjectManagerCreatesDemand($settings);

        $createdDemand = $this->subject->createFromSettings($settings);


        $this->assertEquals(
            $createdDemand,
            $mockDemand
        );
    }

    /**
     * @test
     */
    public function createFromSettingsSetsOrderFromLegacySettings()
    {
        $settings = [
            'sortBy' => 'foo',
            'sortDirection' => 'bar'
        ];
        $expectedOrder = 'foo|bar';

        $this->mockObjectManagerCreatesDemand($settings);

        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertSame(
            $expectedOrder,
            $createdDemand->getOrder()
        );
    }

    /**
     * Data provider for lesson period and lesson date
     *
     * @return array
     */
    public function lessonPeriodDataProvider()
    {
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedDate = new \DateTime('midnight', $timeZone);
        return [
            ['futureOnly', $expectedDate],
            ['pastOnly', $expectedDate]
        ];
    }

    /**
     * @test
     * @dataProvider lessonPeriodDataProvider
     * @param string $lessonPeriod
     * @param \DateTime $expectedDate
     */
    public function createFromSettingsSetsLessonDateByLessonPeriod($lessonPeriod, $expectedDate)
    {
        $settings = [
            'lessonPeriod' => $lessonPeriod
        ];

        $this->mockObjectManagerCreatesDemand($settings);
        /** @var PersonDemand $createdDemand */
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertEquals(
            $expectedDate,
            $createdDemand->getLessonDate()
        );
    }

}
