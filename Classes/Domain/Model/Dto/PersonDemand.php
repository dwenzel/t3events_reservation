<?php
namespace CPSIT\T3eventsReservation\Domain\Model\Dto;

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

use DWenzel\T3events\Domain\Model\Dto\AudienceAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\AudienceAwareDemandTrait;
use DWenzel\T3events\Domain\Model\Dto\CategoryAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\CategoryAwareDemandTrait;
use DWenzel\T3events\Domain\Model\Dto\DemandInterface;
use DWenzel\T3events\Domain\Model\Dto\AbstractDemand;
use DWenzel\T3events\Domain\Model\Dto\EventTypeAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\EventTypeAwareDemandTrait;
use DWenzel\T3events\Domain\Model\Dto\GenreAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\GenreAwareDemandTrait;
use DWenzel\T3events\Domain\Model\Dto\OrderAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\OrderAwareDemandTrait;
use DWenzel\T3events\Domain\Model\Dto\PeriodAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\PeriodAwareDemandTrait;
use DWenzel\T3events\Domain\Model\Dto\SearchAwareDemandInterface;
use DWenzel\T3events\Domain\Model\Dto\SearchAwareDemandTrait;

/**
 * Class PersonDemand
 *
 * @package CPSIT\T3eventsCourse\Domain\Model\Dto
 */
class PersonDemand extends AbstractDemand
	implements DemandInterface,
    AudienceAwareDemandInterface, CategoryAwareDemandInterface,
    EventTypeAwareDemandInterface, GenreAwareDemandInterface,
    OrderAwareDemandInterface, PeriodAwareDemandInterface,
    SearchAwareDemandInterface
	 {
	use AudienceAwareDemandTrait, CategoryAwareDemandTrait,
        EventTypeAwareDemandTrait, GenreAwareDemandTrait,
        OrderAwareDemandTrait, PeriodAwareDemandTrait,
        SearchAwareDemandTrait;

	const GENRE_FIELD = 'reservation.lesson.event.genre';
	const EVENT_TYPE_FIELD = 'reservation.lesson.event.eventType';
	const CATEGORY_FIELD = 'reservation.lesson.event.categories';
	const START_DATE_FIELD = 'reservation.lesson.date';
	const END_DATE_FIELD = 'reservation.lesson.endDate';
	const AUDIENCE_FIELD = 'reservation.lesson.event.audience';

	/**
	 * Types
	 *
	 * @var string
	 */
	protected $types;

	/**
	 * lesson deadline
	 *
	 * @var \DateTime Deadline of lesson (in reservation)
	 */
	protected $lessonDeadline;

	/**
	 * Date of lesson
	 *
	 * @var \DateTime Date of lesson (in reservation)
	 */
	protected $lessonDate;

	/**
	 * Period of lesson (in reservation)
	 *
	 * @var string
	 */
	protected $lessonPeriod;

	/**
	 * gets the types
	 *
	 * @return string A comma separated list of type ids
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * sets the types
	 *
	 * @param string $types A comma separated list of type ids
	 * @return void
	 */
	public function setTypes($types) {
		$this->types = $types;
	}

	/**
	 * Get the lesson deadline
	 *
	 * @return \DateTime
	 */
	public function getLessonDeadline() {
		return $this->lessonDeadline;
	}

	/**
	 * Sets the lesson deadline
	 *
	 * @var \DateTime $deadline
	 */
	public function setLessonDeadline($deadline) {
		$this->lessonDeadline = $deadline;
	}

	/**
	 * Gets the lesson date
	 *
	 * @return \DateTime
	 */
	public function getLessonDate() {
		return $this->lessonDate;
	}

	/**
	 * Sets the lesson date
	 *
	 * @param \DateTime $date The date of the lesson
	 */
	public function setLessonDate($date) {
		$this->lessonDate = $date;
	}

	/**
	 * Gets the lesson period (in reservation)
	 *
	 * @return string A period name. Allowed: all, futureOnly, pastOnly, specific
	 */
	public function getLessonPeriod() {
		return $this->lessonPeriod;
	}

	/**
	 * Sets the period of the lesson (in reservation)
	 *
	 * @param string $period A period name.
	 */
	public function setLessonPeriod($period) {
		$this->lessonPeriod = $period;
	}

	/**
	 * Returns the field name for genres in dot notation.
	 *
	 * @return string
	 */
	public function getGenreField() {
		return self::GENRE_FIELD;
	}

	/**
	 * Returns the field name for the event types in dot notation.
	 *
	 * @return string
	 */
	public function getEventTypeField() {
		return self::EVENT_TYPE_FIELD;
	}

	/**
	 * Returns the field name for categories in dot notation.
	 *
	 * @return string
	 */
	public function getCategoryField() {
		return self::CATEGORY_FIELD;
	}

	/**
	 * Returns the field name for the start date in dot notation.
	 * @return string
	 */
	public function getStartDateField() {
		return self::START_DATE_FIELD;
	}

	/**
	 * Returns the field name for the end date in dot notation.
	 * @return string
	 */
	public function getEndDateField() {
		return self::END_DATE_FIELD;
	}

	/**
	 * Returns the field name for the audience field in dot notation.
	 *
	 * @return string
	 */
	public function getAudienceField() {
		return self::AUDIENCE_FIELD;
	}

}
