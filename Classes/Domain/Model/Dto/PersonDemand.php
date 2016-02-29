<?php
namespace CPSIT\T3eventsReservation\Domain\Model\Dto;

use Webfox\T3events\Domain\Model\Dto\AudienceAwareDemandInterface;
use Webfox\T3events\Domain\Model\Dto\AudienceAwareDemandTrait;
use Webfox\T3events\Domain\Model\Dto\CategoryAwareDemandInterface;
use Webfox\T3events\Domain\Model\Dto\CategoryAwareDemandTrait;
use Webfox\T3events\Domain\Model\Dto\DemandInterface;
use Webfox\T3events\Domain\Model\Dto\AbstractDemand;
use Webfox\T3events\Domain\Model\Dto\EventTypeAwareDemandInterface;
use Webfox\T3events\Domain\Model\Dto\EventTypeAwareDemandTrait;
use Webfox\T3events\Domain\Model\Dto\GenreAwareDemandInterface;
use Webfox\T3events\Domain\Model\Dto\GenreAwareDemandTrait;
use Webfox\T3events\Domain\Model\Dto\PeriodAwareDemandInterface;
use Webfox\T3events\Domain\Model\Dto\PeriodAwareDemandTrait;
use Webfox\T3events\Domain\Model\Dto\SearchAwareDemandInterface;
use Webfox\T3events\Domain\Model\Dto\SearchAwareDemandTrait;

/**
 * Class PersonDemand
 *
 * @package CPSIT\T3eventsCourse\Domain\Model\Dto
 */
class PersonDemand extends AbstractDemand
	implements DemandInterface, SearchAwareDemandInterface,
	GenreAwareDemandInterface, EventTypeAwareDemandInterface,
	CategoryAwareDemandInterface, PeriodAwareDemandInterface,
	AudienceAwareDemandInterface {
	use SearchAwareDemandTrait, GenreAwareDemandTrait,
		EventTypeAwareDemandTrait, CategoryAwareDemandTrait,
		PeriodAwareDemandTrait, AudienceAwareDemandTrait;

	const GENRE_FIELD = 'reservation.lesson.event.genre';
	const EVENT_TYPE_FIELD = 'reservation.lesson.event.eventType';
	const CATEGORY_FIELD = 'reservation.lesson.event.categories';
	const START_DATE_FIELD = 'reservation.lesson.date';
	const END_DATE_FIELD = 'reservation.lesson.endDate';
	const AUDIENCE_FIELD = 'reservation.lesson.event.audience';

	/**
	 * Types
	 *
	 * @var \string
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
	 * @var \string
	 */
	protected $lessonPeriod;

	/**
	 * get the types
	 *
	 * @return \string
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * sets the types
	 *
	 * @param \string $types
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
	 * @param $date
	 */
	public function setLessonDate($date) {
		$this->lessonDate = $date;
	}

	/**
	 * Gets the lesson period (in reservation)
	 *
	 * @return string
	 */
	public function getLessonPeriod() {
		return $this->lessonPeriod;
	}

	/**
	 * Sets the period of the lesson (in reservation)
	 *
	 * @param \string $period
	 */
	public function setLessonPeriod($period) {
		$this->lessonPeriod = $period;
	}

	/**
	 * @return string
	 */
	public function getGenreField() {
		return self::GENRE_FIELD;
	}

	/**
	 * @return string
	 */
	public function getEventTypeField() {
		return self::EVENT_TYPE_FIELD;
	}

	/**
	 * @return string
	 */
	public function getCategoryField() {
		return self::CATEGORY_FIELD;
	}

	/**
	 * @return mixed
	 */
	public function getStartDateField() {
		return self::START_DATE_FIELD;
	}

	/**
	 * @return mixed
	 */
	public function getEndDateField() {
		return self::END_DATE_FIELD;
	}

	/**
	 * @return string
	 */
	public function getAudienceField() {
		return self::AUDIENCE_FIELD;
	}

}
