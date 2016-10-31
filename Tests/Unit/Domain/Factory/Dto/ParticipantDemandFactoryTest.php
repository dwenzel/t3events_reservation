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

use CPSIT\T3eventsReservation\Domain\Factory\Dto\ParticipantDemandFactory;
use CPSIT\T3eventsReservation\Domain\Model\Dto\ParticipantDemand;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;

/**
 * Class ParticipantDemandFactoryTest
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Factory\Dto
 */
class ParticipantDemandFactoryTest extends UnitTestCase
{

    /**
     * @var ParticipantDemandFactory
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ParticipantDemandFactory::class, ['dummy'], [], '', false
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
            ParticipantDemand::class, ['dummy']
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
    public function createFromSettingsReturnsParticipantDemand()
    {
        $mockDemand = $this->getMock(
            ParticipantDemand::class
        );
        $mockObjectManager = $this->mockObjectManager();
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->with(ParticipantDemand::class)
            ->will($this->returnValue($mockDemand));

        $this->assertSame(
            $mockDemand,
            $this->subject->createFromSettings([])
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
            ['types', '9,4']
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
}
