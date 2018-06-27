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
use Nimut\TestingFramework\TestCase\UnitTestCase;
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
        $mockObjectManager = $this->getMockBuilder(ObjectManager::class)
            ->setMethods(['get'])->getMock();
        $this->subject->injectObjectManager($mockObjectManager);

        return $mockObjectManager;
    }

    /**
     * @return \DWenzel\T3events\Domain\Model\Dto\DemandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockObjectManagerCreatesDemand()
    {
        /** @var PersonDemand $mockDemand */
        $mockDemand = $this->getMockBuilder(PersonDemand::class)
            ->setMethods(['dummy'])->getMock();
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
        $mockDemand = $this->getMockBuilder(PersonDemand::class)->getMock();
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
        $this->mockObjectManagerCreatesDemand();
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
        $this->mockObjectManagerCreatesDemand();
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
            ['search', 'bar'],
            ['types', '9,4'],
            ['lessonDeadline', 'midnight']
        ];
    }

    /**
     * Make sure properties in compositeProperties attribute are not set directly
     *
     * @test
     * @dataProvider skippedPropertiesDataProvider
     * @param string $propertyName
     * @param string $propertyValue
     */
    public function createFromSettingsDoesNotSetSkippedValues($propertyName, $propertyValue)
    {
        $objectManager = $this->mockObjectManager();
        $settings = [
            $propertyName => $propertyValue
        ];
        $method = 'set' . ucfirst($propertyName);

        $mockDemand = $this->getMockBuilder(PersonDemand::class)
            ->setMethods([$method])->getMock();
        $objectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        $mockDemand->expects($this->any())
            ->method($method)
            ->with($this->logicalNot($this->equalTo($propertyValue)));
        $this->subject->createFromSettings($settings);
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

        $this->mockObjectManagerCreatesDemand();

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

        $this->mockObjectManagerCreatesDemand();
        /** @var PersonDemand $createdDemand */
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertEquals(
            $expectedDate,
            $createdDemand->getLessonDate()
        );
    }

    /**
     * @test
     */
    public function createFromSettingsSetsLessonDeadline()
    {
        $settings = [
            'lessonDeadline' => 'yesterday'
        ];

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expectedDeadline = new \DateTime($settings['lessonDeadline'], $timeZone);

        $this->mockObjectManagerCreatesDemand();
        /** @var PersonDemand $createdDemand */
        $createdDemand = $this->subject->createFromSettings($settings);

        $this->assertEquals(
            $expectedDeadline,
            $createdDemand->getLessonDeadline()
        );
    }
}
