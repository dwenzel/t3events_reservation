<?php

declare(strict_types=1);

namespace CPSIT\T3eventsReservation\Event;

use CPSIT\T3eventsReservation\Domain\Model\Reservation;

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
final class ReservationCreatedEvent implements MessageAwareInterface
{
    use MessageAwareTrait;

    public function __construct(
        private readonly Reservation $newReservation,
        private readonly array $settings
    )
    {

    }

    public function getNewReservation(): Reservation
    {
        return $this->newReservation;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
