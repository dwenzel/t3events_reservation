<?php

namespace CPSIT\T3eventsReservation\Controller;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2025 Dirk Wenzel <wenzel@cps-it.de>
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
trait ClearCacheOnErrorTrait
{
    use CacheServiceTrait;

    /**
     * Clear cache of current page on error. Needed because we want a re-evaluation of the data.
     */
    public function clearCacheOnError(): void
    {
        $extbaseSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (
            isset($extbaseSettings['persistence']['enableAutomaticCacheClearing'], $GLOBALS['TSFE'])
            && $extbaseSettings['persistence']['enableAutomaticCacheClearing'] === '1') {
            $this->cacheService->clearPageCache([$GLOBALS['TSFE']->id]);
        }
    }
}
