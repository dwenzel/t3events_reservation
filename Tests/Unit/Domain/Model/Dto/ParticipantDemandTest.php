<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto;

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

use CPSIT\T3eventsReservation\Domain\Model\Dto\ParticipantDemand;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class PersonDemandTest
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Model\Dto
 */
class ParticipantDemandTest extends UnitTestCase
{
    /**
     * @var ParticipantDemand
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            ParticipantDemand::class, ['dummy']
        );
    }

    /**
     * @test
     */
    public function getTypesReturnsInitialValue()
    {
        $this->assertSame(
            Person::PERSON_TYPE_PARTICIPANT,
            $this->subject->getTypes()
        );
    }

    /**
     * @test
     */
    public function typesCanNotBeSet()
    {
        $this->subject->setTypes('foo');
        $this->assertSame(
            Person::PERSON_TYPE_PARTICIPANT,
            $this->subject->getTypes()
        );
    }
}
