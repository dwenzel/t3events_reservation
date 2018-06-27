<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Repository;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use CPSIT\T3eventsReservation\Domain\Repository\PersonRepository;

/**
 * Test case for class \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository.
 *
 * @author Dirk Wenzel <dirk.wenzel@cps-it.de>
 * @coversDefaultClass \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository
 */
class PersonRepositoryTest extends UnitTestCase {

	/**
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository | \PHPUnit_Framework_MockObject_MockObject | \TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $subject;

	/** @var QueryInterface|MockObject */
	protected $query;

	public function setUp() {
		$this->subject = $this->getAccessibleMock(
			PersonRepository::class,
			['dummy'], [], '', FALSE);
        $this->query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['equals', 'logicalAnd', 'lessThan', 'logicalOr'])
            ->getMock();

    }

	/**
	 * @test
	 * @covers ::createConstraintsFromDemand
	 */
	public function createConstraintsFromDemandInitiallyReturnsEmptyArray() {
		$demand = $this->getMockBuilder(PersonDemand::class)->getMock();

		$this->assertEquals(
			[],
			$this->subject->createConstraintsFromDemand($this->query, $demand)
		);
	}

	/**
     * @test
     */
	public function createConstraintsFromDemandAddsTypeConstraints()
    {
        $types = '1,3';
        $demand = $this->getMockBuilder(PersonDemand::class)
            ->setMethods(['getTypes'])->getMock();

        $demand->expects($this->atLeastOnce())
            ->method('getTypes')
            ->will($this->returnValue($types));
        $this->query->expects($this->exactly(2))
            ->method('equals')
            ->withConsecutive(
                ['type', 1],
                ['type', 3]
            );

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandAddsDeadlineConstraints()
    {
        $deadline = 'yesterday';
        $demand = $this->getMockBuilder(PersonDemand::class)
            ->setMethods(['getLessonDeadline'])->getMock();

        $constraint = $this->getMockForAbstractClass(ConstraintInterface::class);
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $dateTime = new \DateTime($deadline, $timeZone);
        $demand->expects($this->atLeastOnce())
            ->method('getLessonDeadline')
            ->will($this->returnValue($dateTime));
        $this->query->expects($this->once())
            ->method('lessThan')
            ->with('reservation.lesson.deadline', $dateTime->getTimestamp())
            ->will($this->returnValue($constraint));
        $this->query->expects($this->once())
            ->method('logicalAnd')
            ->with($constraint);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }


    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesPeriodConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [   'createPeriodConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );

        $this->subject->expects($this->once())
            ->method('createPeriodConstraints')
            ->with($this->query, $demand);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesPeriodConstraintsLogicalAnd() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [
                'createPeriodConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );

        $constraints = [];
        $mockPeriodConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createPeriodConstraints')
            ->will($this->returnValue($mockPeriodConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($this->query, $constraints, $mockPeriodConstraints);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesGenreConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [   'createGenreConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );

        $this->subject->expects($this->once())
            ->method('createGenreConstraints')
            ->with($this->query, $demand);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesGenreConstraintsLogicalAnd() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [
                'createGenreConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $constraints = [];
        $mockGenreConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createGenreConstraints')
            ->will($this->returnValue($mockGenreConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($this->query, $constraints, $mockGenreConstraints);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesSearchConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [   'createSearchConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $this->subject->expects($this->once())
            ->method('createSearchConstraints')
            ->with($this->query, $demand);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesSearchConstraintsLogicalOr() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [
                'createSearchConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $constraints = [];
        $mockSearchConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createSearchConstraints')
            ->will($this->returnValue($mockSearchConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($this->query, $constraints, $mockSearchConstraints, 'OR');

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesEventTypeConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [   'createEventTypeConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $this->subject->expects($this->once())
            ->method('createEventTypeConstraints')
            ->with($this->query, $demand);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesEventTypeConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [
                'createEventTypeConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $constraints = [];
        $mockEventTypeConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createEventTypeConstraints')
            ->will($this->returnValue($mockEventTypeConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($this->query, $constraints, $mockEventTypeConstraints);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesCategoryConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [   'createCategoryConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $this->subject->expects($this->once())
            ->method('createCategoryConstraints')
            ->with($this->query, $demand);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesCategoryConstraintsLogicalAnd() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [
                'createCategoryConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $constraints = [];
        $mockCategoryConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createCategoryConstraints')
            ->will($this->returnValue($mockCategoryConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($this->query, $constraints, $mockCategoryConstraints);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesAudienceConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [   'createAudienceConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $this->subject->expects($this->once())
            ->method('createAudienceConstraints')
            ->with($this->query, $demand);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesAudienceConstraints() {
        $this->subject = $this->getAccessibleMock(
            PersonRepository::class,
            [
                'createAudienceConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            PersonDemand::class, [], '', true, true, true,
            []
        );


        $constraints = [];
        $mockAudienceConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createAudienceConstraints')
            ->will($this->returnValue($mockAudienceConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($this->query, $constraints, $mockAudienceConstraints);

        $this->subject->createConstraintsFromDemand($this->query, $demand);
    }
}

