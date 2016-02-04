<?php
namespace CPSIT\T3eventsReservation;

/**
 * Interface PriceableInterface
 *
 * @package CPSIT\T3eventsReservation
 */
interface PriceableInterface {

	/**
	 * Gets the price
	 *
	 * @return float
	 */
	public function getPrice();

	/**
	 * Sets the price
	 *
	 * @param float $price
	 * @return void
	 */
	public function setPrice($price);
}