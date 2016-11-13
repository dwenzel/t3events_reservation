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

use CPSIT\T3eventsReservation\Controller\BillingAddressController;
use DWenzel\T3events\Controller\Routing\Route;
use DWenzel\T3events\DataProvider\RouteLoader\RouteLoaderDataProviderInterface;

/**
 * Class BillingAddressControllerDefaults
 * Provides configuration for default routing of BillingAddressController
 *
 * @package CPSIT\T3eventsReservation\DataProvider\RouteLoader
 */
class BillingAddressControllerDefaults implements RouteLoaderDataProviderInterface
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
        // origin => target
        $methods = [
            'update' => 'edit',
            'create' => 'edit',
            'remove' => 'edit'
        ];
        $prefix = BillingAddressController::class . Route::ORIGIN_SEPARATOR;

        foreach ($methods as $origin => $actionName) {
            $options = [
                'actionName' => $actionName,
                // default target controller
                'controllerName' => BillingAddressController::PARENT_CONTROLLER_NAME,
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
