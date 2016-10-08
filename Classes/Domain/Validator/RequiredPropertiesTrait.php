<?php
namespace CPSIT\T3eventsReservation\Domain\Validator;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

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
trait RequiredPropertiesTrait
{

    /**
     * Creates a new validation error object and adds it to $this->errors
     *
     * @param string $message The error message
     * @param integer $code The error code (a unix timestamp)
     * @param array $arguments Arguments to be replaced in message
     * @param string $title title of the error
     * @return void
     */
    abstract protected function addError($message, $code, array $arguments = [], $title = '');

    /**
     * @param mixed $value
     * @return boolean TRUE if the given $value is NULL or an empty string ('')
     */
    abstract protected function isEmpty($value);

    /**
     * Validates required properties and adds an error for
     * each empty property is empty
     * Expects an array with property names as keys and
     * error codes as values:
     * [
     *  <propertyName> => <errorCode>
     * ]
     *
     * @param $object
     * @param array $requiredProperties An array with property names and error codes
     * @return void
     * @throws \TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException
     */
    public function validateRequiredProperties($object, $requiredProperties)
    {
        foreach ($requiredProperties as $propertyName => $errorCode) {
            $propertyValue = ObjectAccess::getProperty($object, $propertyName);
            if ($this->isEmpty($propertyValue)) {
                $this->addError($propertyName . ' is required.', $errorCode);
            }
        }
    }
}
