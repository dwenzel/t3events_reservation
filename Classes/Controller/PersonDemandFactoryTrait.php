<?php
namespace CPSIT\T3eventsReservation\Controller;

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

/**
 * Class PersonDemandFactoryTrait
 * Provides a PersonDemandFactory
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
trait PersonDemandFactoryTrait
{
    /**
     * @var \CPSIT\T3eventsReservation\Domain\Factory\Dto\PersonDemandFactory
     */
    protected $personDemandFactory;

    /**
     * Injects the personDemandFactory
     *
     * @return void
     */
    public function injectPersonDemandFactory(PersonDemandFactory $personDemandFactory)
    {
        $this->personDemandFactory = $personDemandFactory;
    }
}
