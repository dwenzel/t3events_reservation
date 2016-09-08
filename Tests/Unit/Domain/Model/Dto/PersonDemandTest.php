<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto;

use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use TYPO3\CMS\Core\Tests\UnitTestCase;

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
 * Class PersonDemandTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto
 */
class PersonDemandTest extends UnitTestCase
{
    /**
     * @var PersonDemand
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            PersonDemand::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function getTypesInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getTypes()
        );
    }

    /**
     * @test
     */
    public function typesCanBeSet()
    {
        $types = '1,2,3';
        $this->subject->setTypes($types);

        $this->assertEquals(
            $types,
            $this->subject->getTypes()
        );
    }

    /**
     * @test
     */
    public function getLessonDeadlineInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getLessonDeadline()
        );
    }

    /**
     * @test
     */
    public function lessonDeadlineCanBeSet()
    {
        $deadline = $this->getMock(\DateTime::class);
        $this->subject->setLessonDeadline($deadline);

        $this->assertSame(
            $deadline,
            $this->subject->getLessonDeadline()
        );
    }

    /**
     * @test
     */
    public function getLessonDateInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getLessonDate()
        );
    }

    /**
     * @test
     */
    public function lessonDateCanBeSet()
    {
        $date = $this->getMock(\DateTime::class);
        $this->subject->setLessonDate($date);

        $this->assertSame(
            $date,
            $this->subject->getLessonDate()
        );
    }

    /**
     * @test
     */
    public function getLessonPeriodInitiallyReturnsNull()
    {
        $this->assertNull(
            $this->subject->getLessonPeriod()
        );
    }

    /**
     * @test
     */
    public function lessonPeriodCanBeSet()
    {
        $types = 'foo';
        $this->subject->setlessonPeriod($types);

        $this->assertEquals(
            $types,
            $this->subject->getlessonPeriod()
        );
    }

    /**
     * @test
     */
    public function getGenreFieldReturnsClassConstant()
    {
        $this->assertSame(
            PersonDemand::GENRE_FIELD,
            $this->subject->getGenreField()
        );
    }

    /**
     * @test
     */
    public function getEventTypeFieldReturnsClassConstant()
    {
        $this->assertSame(
            PersonDemand::EVENT_TYPE_FIELD,
            $this->subject->getEventTypeField()
        );
    }

    /**
     * @test
     */
    public function getCategoryFieldReturnsClassConstant()
    {
        $this->assertSame(
            PersonDemand::CATEGORY_FIELD,
            $this->subject->getCategoryField()
        );
    }

    /**
     * @test
     */
    public function getEndDateFieldReturnsClassConstant()
    {
        $this->assertSame(
            PersonDemand::END_DATE_FIELD,
            $this->subject->getEndDateField()
        );
    }

    /**
     * @test
     */
    public function getAudienceFieldReturnsClassConstant()
    {
        $this->assertSame(
            PersonDemand::AUDIENCE_FIELD,
            $this->subject->getAudienceField()
        );
    }
}
