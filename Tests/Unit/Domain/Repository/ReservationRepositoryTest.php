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

use CPSIT\T3eventsReservation\Domain\Model\Dto\ReservationDemand;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository;

/**
 * Test case for class \DWenzel\T3eventsReservation\Domain\Repository\ReservationRepository.
 *
 * @author Dirk Wenzel <dirk.wenzel@cps-it.de>
 * @coversDefaultClass \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository
 */
class ReservationRepositoryTest extends UnitTestCase {

	/**
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository | \PHPUnit_Framework_MockObject_MockObject | \TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $subject;

	public function setUp() {
		$this->subject = $this->getAccessibleMock(
			ReservationRepository::class,
			['dummy'], [], '', FALSE);
	}

	/**
	 * @test
	 * @covers ::createConstraintsFromDemand
	 */
	public function createConstraintsFromDemandInitiallyReturnsEmptyArray() {
		$demand = $this->getMock(ReservationDemand::class);
		$query = $this->getMock(QueryInterface::class, [], [], '', FALSE);

		$this->assertEquals(
			[],
			$this->subject->createConstraintsFromDemand($query, $demand)
		);
	}

    /**
     * @test
     */
    public function createConstraintsFromDemandAddsDeadlineConstraints()
    {
        $deadline = 'yesterday';
        $demand = $this->getMock(
            ReservationDemand::class,
            ['getLessonDeadline']
        );
        $query = $this->getMock(
            Query::class,
            ['lessThan', 'logicalAnd'],
            [], '', false
        );
        $constraint = $this->getMockForAbstractClass(ConstraintInterface::class);
        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $dateTime = new \DateTime($deadline, $timeZone);
        $demand->expects($this->atLeastOnce())
            ->method('getLessonDeadline')
            ->will($this->returnValue($dateTime));
        $query->expects($this->once())
            ->method('lessThan')
            ->with('lesson.deadline', $dateTime->getTimestamp())
            ->will($this->returnValue($constraint));
        $query->expects($this->once())
            ->method('logicalAnd')
            ->with($constraint);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandAddsMinAgeConstraints()
    {
        $minAge = 500;
        $demand = $this->getMock(
            ReservationDemand::class,
            ['getMinAge']
        );
        $query = $this->getMock(
            Query::class,
            ['lessThan', 'logicalAnd'],
            [], '', false
        );
        $constraint = $this->getMockForAbstractClass(ConstraintInterface::class);
        $demand->expects($this->atLeastOnce())
            ->method('getMinAge')
            ->will($this->returnValue($minAge));
        $query->expects($this->once())
            ->method('lessThan')
            ->with('tstamp', time() - $minAge)
            ->will($this->returnValue($constraint));
        $query->expects($this->once())
            ->method('logicalAnd')
            ->with($constraint);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesPeriodConstraints() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [   'createPeriodConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $this->subject->expects($this->once())
            ->method('createPeriodConstraints')
            ->with($query, $demand);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesPeriodConstraintsLogicalAnd() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [
                'createPeriodConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $constraints = [];
        $mockPeriodConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createPeriodConstraints')
            ->will($this->returnValue($mockPeriodConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($query, $constraints, $mockPeriodConstraints);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesGenreConstraints() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [   'createGenreConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $this->subject->expects($this->once())
            ->method('createGenreConstraints')
            ->with($query, $demand);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesGenreConstraintsLogicalAnd() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [
                'createGenreConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $constraints = [];
        $mockGenreConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createGenreConstraints')
            ->will($this->returnValue($mockGenreConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($query, $constraints, $mockGenreConstraints);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesSearchConstraints() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [   'createSearchConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $this->subject->expects($this->once())
            ->method('createSearchConstraints')
            ->with($query, $demand);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesSearchConstraintsLogicalOr() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [
                'createSearchConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $constraints = [];
        $mockSearchConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createSearchConstraints')
            ->will($this->returnValue($mockSearchConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($query, $constraints, $mockSearchConstraints, 'OR');

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCreatesEventTypeConstraints() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [   'createEventTypeConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $this->subject->expects($this->once())
            ->method('createEventTypeConstraints')
            ->with($query, $demand);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }

    /**
     * @test
     */
    public function createConstraintsFromDemandCombinesEventTypeConstraints() {
        $this->subject = $this->getAccessibleMock(
            ReservationRepository::class,
            [
                'createEventTypeConstraints',
                'combineConstraints'
            ], [], '', false);
        /** @var DemandInterface $demand */
        $demand = $this->getMockForAbstractClass(
            ReservationDemand::class, [], '', true, true, true,
            []
        );
        $query = $this->getMock(
            QueryInterface::class,
            [], [], '', false
        );

        $constraints = [];
        $mockEventTypeConstraints = ['foo'];

        $this->subject->expects($this->once())
            ->method('createEventTypeConstraints')
            ->will($this->returnValue($mockEventTypeConstraints)
            );
        $this->subject->expects($this->once())
            ->method('combineConstraints')
            ->with($query, $constraints, $mockEventTypeConstraints);

        $this->subject->createConstraintsFromDemand($query, $demand);
    }
}

