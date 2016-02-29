<?php
namespace CPSIT\T3eventsReservation\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Class ContactValidator
 *
 * @package CPSIT\T3eventsReservation\Domain\Validator
 */
class ContactValidator extends AbstractValidator {

	/**
	 * Is contact valid
	 *
	 * @param mixed $contact
	 * @return bool
	 */
	public function isValid($contact) {
		if (!$contact instanceof \Webfox\T3events\Domain\Model\Person) {
			$this->addError('Contact must be a Person.', 1410958031);

			return FALSE;
		}
		$email = $contact->getEmail();
		if (is_null($email) OR $email === '') {
			$this->addError('E-Mail must not be empty.', 1410958066);

			return FALSE;
		}
		$phone = $contact->getPhone();
		if (is_null($phone) OR $phone === '') {
			$this->addError('Phone must not be empty.', 1417193585);
		}

		return TRUE;
	}
}
