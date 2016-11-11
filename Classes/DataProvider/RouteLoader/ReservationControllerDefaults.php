<?php
namespace CPSIT\T3eventsReservation\DataProvider\RouteLoader;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use CPSIT\T3eventsReservation\Controller\ReservationController;
use DWenzel\T3events\Controller\Routing\Route;
use DWenzel\T3events\DataProvider\RouteLoader\RouteLoaderDataProviderInterface;

/**
 * Class ReservationControllerDefaults
 * Provides configuration for default routing of ReservationController
 *
 * @package CPSIT\T3eventsReservation\DataProvider\RouteLoader
 */
class ReservationControllerDefaults implements RouteLoaderDataProviderInterface
{
    /**
     * Get the default routing configuration
     * for registration through RouteLoader
     *
     * @return array
     */
    public function getConfiguration()
    {
        $configuration = [];
        $methods = [
            'create' => 'edit',
            'createParticipant' => 'edit',
            'confirm' => 'show',
            'removeParticipant' => 'edit',
            'removeBillingAddress' => 'edit',
            'createBillingAddress' => 'edit',
            'update' => 'edit'
        ];
        $prefix = ReservationController::class . Route::ORIGIN_SEPARATOR;

        foreach ($methods as $origin => $actionName) {
            // actionName, controllerName, extensionName, arguments, pageUid, delay, statusCode
            $options = [
                'actionName' => $actionName,
                'controllerName' => null,
                'extensionName' => null,
                'arguments' => null,
                'pageUid' => null,
                'delay' => 0,
                'statusCode' => 303
            ];

            // identifier, method (default is 'redirect'), $options
            $configuration[] = [$prefix . $origin, null, $options];
        }

        return $configuration;
    }
}
