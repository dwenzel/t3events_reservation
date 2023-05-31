<?php
namespace CPSIT\T3eventsReservation\Domain\Validator;

use DWenzel\T3events\Domain\Model\Person;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class ContactValidator
 *
 * @package CPSIT\T3eventsReservation\Domain\Validator
 */
class ContactValidator extends AbstractValidator {
    use RequiredPropertiesTrait;

    /**
     * Required properties
     *
     * @var array
     */
    protected static $requiredProperties = [
        'email' => 1_410_958_066
    ];

	/**
	 * Is contact valid
	 *
	 * @param mixed $contact
	 * @return bool
	 */
	protected function isValid($contact) {
		if (!$contact instanceof Person) {
			$this->addError('Contact must be a Person.', 1_410_958_031);

			return false;
		}

		 $this->validateRequiredProperties($contact, static::$requiredProperties);

        if($this->result->hasErrors()) {
            return false;
        }

        return true;
	}
}
