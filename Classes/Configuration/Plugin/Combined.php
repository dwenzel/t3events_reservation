<?php

namespace CPSIT\T3eventsReservation\Configuration\Plugin;

use DWenzel\T3extensionTools\Configuration\PluginConfigurationInterface;
use DWenzel\T3extensionTools\Configuration\PluginConfigurationTrait;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Dirk Wenzel <wenzel@cps-it.de>
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use CPSIT\T3eventsReservation\Configuration\ExtensionConfiguration;

/**
 * Class Combined
 *
 * Provides configuration for combined plugin
 */
abstract class Combined implements PluginConfigurationInterface
{
    use PluginConfigurationTrait;

    static protected $pluginName = 'Pi1';
    static protected $controllerActions = [
        'Reservation' => 'new,show,create,edit,checkout,confirm,delete,newParticipant,createParticipant,removeParticipant,'
            . 'newBillingAddress, createBillingAddress, editBillingAddress, removeBillingAddress, update,error',
        'Participant' => 'edit,update,error',
        'Contact' => 'new,edit,create,update,remove,error',
        'BillingAddress' => 'new,edit,create,update,remove,error',
    ];

    static protected $nonCacheableControllerActions = [
        'Reservation' => 'new,show,create,edit,checkout,confirm,delete,newParticipant,createParticipant,removeParticipant,'
            . 'newBillingAddress,createBillingAddress,editBillingAddress,removeBillingAddress,update,error',
        'Participant' => 'edit,update,error',
        'Contact' => 'new,edit,create,update,remove,error',
        'BillingAddress' => 'new,edit,create,update,remove,error',
    ];

    static protected $vendorExtensionName = ExtensionConfiguration::VENDOR . '.' . ExtensionConfiguration::EXTENSION_KEY;

}
