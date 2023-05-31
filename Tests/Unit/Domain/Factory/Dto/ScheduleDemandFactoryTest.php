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

use CPSIT\T3eventsReservation\Domain\Model\Dto\ScheduleDemand;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ScheduleDemandFactory;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use DWenzel\T3events\Domain\Factory\Dto\PerformanceDemandFactory;
use DWenzel\T3events\Domain\Model\Dto\PerformanceDemand;
use DWenzel\T3events\Domain\Model\Dto\PeriodAwareDemandInterface;

class ScheduleDemandFactoryTest extends UnitTestCase {

	/**
	 * @var ScheduleDemandFactory|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $subject;

	/**
	 * set up
	 */
	public function setUp() {
		$this->subject = $this->getAccessibleMock(
			ScheduleDemandFactory::class, ['dummy'], [], '', false
		);
	}

	/**
	 * @return ObjectManager|\PHPUnit_Framework_MockObject_MockObject
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
	public function objectManagerCanBeInjected() {
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
	public function createFromSettingsReturnsScheduleDemand() {
		$mockDemand = $this->getMockBuilder(ScheduleDemand::class)->getMock();
		$mockObjectManager = $this->mockObjectManager();
		$mockObjectManager->expects($this->once())
			->method('get')
			->with(ScheduleDemand::class)
			->will($this->returnValue($mockDemand));

		$this->assertSame(
			$mockDemand,
			$this->subject->createFromSettings([])
		);
	}

	/**
	 * @return array
	 */
	public function settablePropertiesDataProvider() {
        // todo test properties specific for ScheduleDemand
        // like deadlineBefore and deadlineAfter
        // see createFromSettingsSetsSettableProperties
		/** propertyName, $settingsValue, $expectedValue */
		return [
			['storagePages', '7,8,9', '7,8,9'],
		];
	}

	/**
  * @dataProvider settablePropertiesDataProvider
  * @param string $propertyName
  */
 public function createFromSettingsSetsSettableProperties($propertyName, string|int $settingsValue, mixed $expectedValue) {
		$settings = [
			$propertyName => $settingsValue
		];
		$mockDemand = $this->getMockBuilder(ScheduleDemand::class)
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
