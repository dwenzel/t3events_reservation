<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Domain\Validator;

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

use CPSIT\T3eventsReservation\Domain\Validator\RequiredPropertiesTrait;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class RequiredPropertiesTraitTest
 * @package CPSIT\T3eventsReservation\Tests\Unit\Domain\Validator
 */
class RequiredPropertiesTraitTest extends UnitTestCase
{

    /**
     * @var RequiredPropertiesTrait | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            RequiredPropertiesTrait::class
        );
    }

    /**
     * @test
     */
    public function validateRequiredPropertiesAddsErrorForEmptyProperty()
    {
        $propertyName = 'foo';
        $errorCode = 122345;
        $propertyValue = '';
        $requiredProperties = [$propertyName => $errorCode];
        $mockObject = $this->getAccessibleMock(
            \stdClass::class, ['getFoo']
        );
        $mockObject->expects($this->any())
            ->method('getFoo')
            ->will($this->returnValue($propertyValue));
        $this->subject->expects($this->once())
            ->method('isEmpty')
            ->with($propertyValue)
            ->will($this->returnValue(true));
        $this->subject->expects($this->once())
            ->method('addError')
            ->with($propertyName . ' is required.', $errorCode);

        $this->subject->validateRequiredProperties($mockObject, $requiredProperties);
    }
}