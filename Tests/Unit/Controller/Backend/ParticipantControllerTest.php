<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller\Backend;

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
use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Schedule;
use DWenzel\T3events\Controller\FilterableControllerInterface;
use DWenzel\T3events\Domain\Model\Dto\ModuleData;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use CPSIT\T3eventsReservation\Controller\Backend\ParticipantController;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class ParticipantControllerTest
 * Tests for Backend/ParticipantController
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Controller\Backend
 */
class ParticipantControllerTest extends UnitTestCase
{
    /**
     * @var ParticipantController | \PHPUnit_Framework_MockObject_MockObject | AccessibleObjectInterface
     */
    protected $subject;

    /**
     * @var ModuleData | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleData;

    /**
     * @var ViewInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var PersonRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $personRepository;

    /**
     * @var ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var PersonDemandFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $demandFactory;

    /**
     * set up
     */

    public function setUp()
    {
        /**
         * todo we can not call the original constructor from
         * \DWenzel\T3events\Controller\AbstractBackendController since it uses
         * the GeneralUtility which needs a database connection
         * change this when class does not inherit from AbstractBackendController anymore
         */
        $this->subject = $this->getAccessibleMock(
            ParticipantController::class,
            [
                'getFilterOptions',
                'overwriteDemandObject',
                'getContentForDownload',
                'translate'
            ], [], '', false
        );
        $this->moduleData = $this->getMock(
            ModuleData::class, ['getOverwriteDemand', 'setOverwriteDemand', 'setDemand']
        );
        $this->view = $this->getMock(ViewInterface::class);
        $this->personRepository = $this->getMock(
            PersonRepository::class, ['findDemanded'], [], '', false
        );

        $this->objectManager = $this->getMock(ObjectManager::class, ['get']);
        $this->demandFactory = $this->getMock(
            PersonDemandFactory::class, ['dummy']
        );
        $this->demandFactory->injectObjectManager($this->objectManager);
        $this->subject->injectObjectManager($this->objectManager);
        $this->subject->injectPersonDemandFactory($this->demandFactory);
        $this->subject->injectPersonRepository($this->personRepository);
        $this->inject($this->subject, 'view', $this->view);
        $this->inject($this->subject, 'moduleData', $this->moduleData);
        $this->inject($this->subject, 'settings', []);
    }


    /**
     * @return \DWenzel\T3events\Domain\Model\Dto\DemandInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockObjectManagerCreatesDemand()
    {
        /** @var PersonDemand $mockDemand */
        $mockDemand = $this->getMock(
            PersonDemand::class, ['dummy']
        );
        $mockObjectManager = $this->objectManager;
        $mockObjectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($mockDemand));
        return $mockDemand;
    }

    /**
     * @test
     */
    public function subjectImplementsFilterableControllerInterface()
    {
        $this->assertInstanceOf(
            FilterableControllerInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function listActionGetsPersonDemandFromObjectManager()
    {
        $mockPersonDemand = $this->getMock(
            PersonDemand::class
        );
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(PersonDemand::class)
            ->will($this->returnValue($mockPersonDemand));

        $this->subject->listAction();
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
    public function listActionGetsPersonDemandWithLessonDateByPeriod($lessonPeriod, $expectedDate)
    {
        $settings = [
            'lessonPeriod' => $lessonPeriod
        ];
        $this->inject($this->subject, 'settings', $settings);
        /** @var PersonDemand | \PHPUnit_Framework_MockObject_MockObject $mockPersonDemand */
        $mockPersonDemand = $this->mockObjectManagerCreatesDemand();

        $this->subject->listAction();

        $this->assertEquals(
            $expectedDate,
            $mockPersonDemand->getLessonDate()
        );
    }

    /**
     * @test
     */
    public function listActionGetsPersonDemandWithCorrectType()
    {
        /** @var PersonDemand | \PHPUnit_Framework_MockObject_MockObject $mockPersonDemand */
        $mockPersonDemand = $this->mockObjectManagerCreatesDemand();

        $this->subject->listAction();

        $this->assertEquals(
            Person::PERSON_TYPE_PARTICIPANT,
            $mockPersonDemand->getTypes()
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
    public function listActionGetsDemandFromSettingsWithSettableProperties($propertyName, $settingsValue, $expectedValue)
    {
        $settings = [
            $propertyName => $settingsValue
        ];
        $this->inject($this->subject, 'settings', $settings);
        $createdDemand = $this->mockObjectManagerCreatesDemand();

        $this->subject->listAction();
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
            ['category', 'categories', '7,8', '7,8'],
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
        $this->inject($this->subject, 'settings', $settings);
        $createdDemand = $this->mockObjectManagerCreatesDemand();
        $this->subject->listAction();

        $this->assertAttributeSame(
            $expectedValue,
            $propertyName,
            $createdDemand
        );
    }

    /**
     * Data provider for skipped properties
     *
     * @return array
     */
    public function skippedPropertiesDataProvider()
    {
        return [
            ['types', 'types', '9,4', 'Tx_T3eventsReservation_Participant'],
            ['periodType', 'periodType', 'foo', null]
        ];
    }

    /**
     * Make sure some properties are *not* set directly because
     * they require a special logic or must have a certain value
     * @test
     * @dataProvider skippedPropertiesDataProvider
     * @param string $settingsKey
     * @param string $propertyName
     * @param string|int|null|bool $settingsValue
     * @param mixed $expectedValue
     */
    public function createFromSettingsSkipsCompositeProperties($settingsKey, $propertyName, $settingsValue, $expectedValue)
    {
        $settings = [
            $settingsKey => $settingsValue
        ];
        $this->inject($this->subject, 'settings', $settings);
        $createdDemand = $this->mockObjectManagerCreatesDemand();
        $this->subject->listAction();

        $this->assertAttributeSame(
            $expectedValue,
            $propertyName,
            $createdDemand
        );
    }

    /**
     * @test
     */
    public function listActionDoesNotSetEmptyPropertiesInDemand()
    {
        // we use an existing property from parent
        $propertyName = 'uidList';
        $settings = [
            $propertyName => ''
        ];
        $this->inject($this->subject, 'settings', $settings);
        $createdDemand = $this->mockObjectManagerCreatesDemand();
        $createdDemand->expects($this->never())
            ->method('setUidList');

        $this->subject->listAction();

        $this->assertAttributeSame(
            null,
            $propertyName,
            $createdDemand
        );
    }

    /**
     * @test
     */
    public function listActionGetsOverwriteDemandFromModuleData()
    {
        $this->mockObjectManagerCreatesDemand();
        $this->moduleData->expects($this->once())
            ->method('getOverwriteDemand');
        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function listActionSetsOverwriteDemandInModuleData()
    {
        $overwriteDemand = ['foo'];
        $this->mockObjectManagerCreatesDemand();
        $this->moduleData->expects($this->once())
            ->method('setOverwriteDemand')
            ->with($overwriteDemand);
        $this->subject->listAction($overwriteDemand);
    }

    /**
     * @test
     */
    public function listActionSetsDemandInModuleData()
    {
        $mockDemand = $this->mockObjectManagerCreatesDemand();
        $this->moduleData->expects($this->once())
            ->method('setDemand')
            ->with($mockDemand);
        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function listActionOverwritesDemandObject()
    {
        $mockDemand = $this->mockObjectManagerCreatesDemand();
        $this->subject->expects($this->once())
            ->method('overwriteDemandObject')
            ->with($mockDemand);
        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function listActionAssignsVariablesToView()
    {
        $overwriteDemand = ['foo'];
        $mockDemand = $this->mockObjectManagerCreatesDemand();
        $expectedTemplateVariables = [
            'participants' => null,
            'overwriteDemand' => $overwriteDemand,
            'demand' => $mockDemand,
            'filterOptions' => null
        ];
        $this->view->expects($this->once())
            ->method('assignMultiple')
            ->with($expectedTemplateVariables);

        $this->subject->listAction($overwriteDemand);

    }

    /**
     * @test
     */
    public function downloadActionGetsPersonDemandFromObjectManagerIfScheduleIsNull()
    {
        $mockPersonDemand = $this->getMock(
            PersonDemand::class
        );
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(PersonDemand::class)
            ->will($this->returnValue($mockPersonDemand));

        $this->subject->downloadAction();
    }

    /**
     * @test
     */
    public function downloadActionGetsOverwriteDemandFromModuleDataIfScheduleIsNull()
    {
        $this->mockObjectManagerCreatesDemand();
        $this->moduleData->expects($this->once())
            ->method('getOverwriteDemand');
        $this->subject->downloadAction();
    }

    /**
     * @test
     */
    public function downloadActionOverwritesDemandObjectIfScheduleIsNull()
    {
        $mockDemand = $this->mockObjectManagerCreatesDemand();
        $this->subject->expects($this->once())
            ->method('overwriteDemandObject')
            ->with($mockDemand);
        $this->subject->downloadAction();
    }

    /**
     * @test
     */
    public function downloadActionGetParticipantsFromRepositoryIfScheduleIsNull()
    {
        $mockDemand = $this->mockObjectManagerCreatesDemand();
        $this->personRepository->expects($this->once())
            ->method('findDemanded')
            ->with($mockDemand);
        $this->subject->downloadAction();
    }

    /**
     * @test
     */
    public function downloadActionAssignsVariablesToView()
    {
        $mockResult = $this->getMock(
            QueryResultInterface::class
        );
        $this->mockObjectManagerCreatesDemand();
        $this->personRepository->expects($this->once())
            ->method('findDemanded')
            ->will($this->returnValue($mockResult));
        $this->view->expects($this->once())
            ->method('assign')
            ->with('participants', $mockResult);
        $this->subject->downloadAction();
    }

    /**
     * @test
     */
    public function downloadActionGetsContentForDownloadIfScheduleIsNull()
    {
        $this->mockObjectManagerCreatesDemand();
        $this->subject->expects($this->once())
            ->method('getContentForDownload')
            ->with('csv');
        $this->subject->downloadAction();
    }

    /**
     * @test
     */
    public function downloadActionGetsParticipantsFromSchedule()
    {
        $mockSchedule = $this->getMock(Schedule::class, ['getParticipants']);
        $mockObjectStorageWithParticipant = $this->getMock(ObjectStorage::class, ['rewind', 'current']);
        $mockPerson = $this->getMock(Person::class);

        $mockSchedule->expects($this->once())
            ->method('getParticipants')
            ->will($this->returnValue($mockObjectStorageWithParticipant));
        $mockObjectStorageWithParticipant->expects($this->once())
            ->method('rewind');
        $mockObjectStorageWithParticipant->expects($this->once())
            ->method('current')
            ->will($this->returnValue($mockPerson));
        $this->subject->expects($this->once())
            ->method('getContentForDownload')
            ->with('csv', $mockPerson);

        $this->subject->downloadAction($mockSchedule);
    }

    /**
     * @test
     */
    public function getErrorFlashMessageInitiallyReturnsFalse()
    {
        $this->assertFalse(
            $this->subject->_call('getErrorFlashMessage')
        );
    }

    /**
     * @test
     */
    public function getErrorFlashMessageReturnsTranslatedMessage()
    {
        $actionMethodName = 'barAction';
        $this->inject($this->subject, 'actionMethodName', $actionMethodName);
        $defaultKey = 'error' . '.participant.'
            . str_replace('Action', '', $actionMethodName)
            . '.' . $this->subject->_get('errorMessage');
        $expectedMessage = 'foo';
        $this->subject->expects($this->once())
            ->method('translate')
            ->with($defaultKey, 't3events', null)
            ->will($this->returnValue($expectedMessage));

        $this->assertSame(
            $expectedMessage,
            $this->subject->_call('getErrorFlashMessage')
        );
    }
}
