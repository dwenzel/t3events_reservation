<?php
namespace CPSIT\T3eventsReservation\Controller\Backend;

use CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand;
use Webfox\T3events\Controller\AbstractController;
use CPSIT\T3eventsReservation\Domain\Model\Person;

/***************************************************************
 *  Copyright notice
 *  (c) 2014 Dirk Wenzel <wenzel@cps-it.de>, CPS IT
 *           Boerge Franck <franck@cps-it.de>, CPS IT
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class ParticipantController extends AbstractController {

	/**
	 * reservationRepository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\ReservationRepository
	 * @inject
	 */
	protected $reservationRepository = NULL;

	/**
	 * Company Repository
	 *
	 * @var \Webfox\T3events\Domain\Repository\CompanyRepository
	 * @inject
	 */
	protected $companyRepository = NULL;

	/**
	 * Participant Repository
	 *
	 * @var \CPSIT\T3eventsReservation\Domain\Repository\PersonRepository
	 * @inject
	 */
	protected $personRepository = NULL;

	/**
	 * List action
	 *
	 * @return void
	 */
	public function listAction() {
		$demand = $this->createDemandFromSettings($this->settings['participant']);
		$participants = $this->personRepository->findDemanded($demand);
		$this->view->assignMultiple(
			array(
				'participants' => $participants,
				'settings' => $this->settings,
				'demand' => $demand,
			)
		);
	}

	/**
	 * Download action
	 *
	 * @param \array $participants
	 * @param \string $ext File extension for download
	 */
	public function downloadAction($participants = NULL, $ext = 'csv') {
		if (is_null($participants)) {
			$demand = $this->createDemandFromSettings($this->settings);
			$participants = $this->personRepository->findDemanded($demand);
		} elseif (is_array($participants)) {
			$participants = $this->personRepository->findMultipleByUid(implode(',', $participants));
		}
		$lesson = $participants->getFirst()->getReservation()->getLesson();
		$fileName = date('Y-m-d_H-m') . '_';
		if (isset($this->settings['participant']['download']['fileName'])) {
			$fileName .= $this->settings['participant']['download']['fileName'] . '_';
		}
		if ($lesson) {
			$fileName .= $lesson->getCourse()->getHeadline();
		}

		/** @var \TYPO3\CMS\Core\Resource\Driver\LocalDriver $localDriver */
		$localDriver = $this->objectManager->get('\TYPO3\CMS\Core\Resource\Driver\LocalDriver');
		$fileName = $localDriver->sanitizeFileName($fileName);

		$this->view->assign('participants', $participants);
		switch ($ext) {
			case 'csv':
				$cType = 'text/csv';
				break;
			case 'txt':
				$cType = 'text/plain';
				break;
			case 'pdf':
				$cType = 'application/pdf';
				break;
			case 'exe':
				$cType = 'application/octet-stream';
				break;
			case 'zip':
				$cType = 'application/zip';
				break;
			case 'doc':
				$cType = 'application/msword';
				break;
			case 'xls':
				$cType = 'application/vnd.ms-excel';
				break;
			case 'ppt':
				$cType = 'application/vnd.ms-powerpoint';
				break;
			case 'gif':
				$cType = 'image/gif';
				break;
			case 'png':
				$cType = 'image/png';
				break;
			case 'jpeg':
			case 'jpg':
				$cType = 'image/jpg';
				break;
			case 'mp3':
				$cType = 'audio/mpeg';
				break;
			case 'wav':
				$cType = 'audio/x-wav';
				break;
			case 'mpeg':
			case 'mpg':
			case 'mpe':
				$cType = 'video/mpeg';
				break;
			case 'mov':
				$cType = 'video/quicktime';
				break;
			case 'avi':
				$cType = 'video/x-msvideo';
				break;

			//forbidden filetypes
			case 'inc':
			case 'conf':
			case 'sql':
			case 'cgi':
			case 'htaccess':
			case 'php':
			case 'php3':
			case 'php4':
			case 'php5':
				exit;

			default:
				$cType = 'application/force-download';
				break;
		}

		$headers = array(
			'Pragma' => 'public',
			'Expires' => 0,
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Cache-Control' => 'public',
			'Content-Description' => 'File Transfer',
			'Content-Type' => $cType,
			'Content-Disposition' => 'attachment; filename="' . $fileName . '.' . $ext . '"',
			'Content-Transfer-Encoding' => 'binary',
			//'Content-Length'            => $fileLen
		);

		foreach ($headers as $header => $data) {
			$this->response->setHeader($header, $data);
		}
		$this->response->sendHeaders();
		echo($this->view->render());
		exit;
	}

	/**
	 * Returns custom error flash messages, or
	 * display no flash message at all on errors.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 * @override \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
	 */
	protected function getErrorFlashMessage() {
		$key = 'error' . '.participant.' . str_replace('Action', '', $this->actionMethodName) . '.' . $this->errorMessage;
		$message = $this->translate($key);
		if ($message == NULL) {
			return FALSE;
		} else {
			return $message;
		}
	}

	/**
	 * Create demand from settings
	 *
	 * @param \array $settings
	 * @return \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand
	 */
	protected function createDemandFromSettings($settings) {
		/**@var \CPSIT\T3eventsReservation\Domain\Model\Dto\PersonDemand $demand */
		$demand = $this->objectManager->get(PersonDemand::class);
		$demand->setTypes((string) Person::PERSON_TYPE_PARTICIPANT);
		if (isset($settings['list']['lessonPeriod'])) {
			$demand->setLessonPeriod($settings['list']['lessonPeriod']);
		}
		if ($demand->getLessonPeriod() === 'futureOnly'
			OR $demand->getLessonPeriod() === 'pastOnly'
		) {
			$timeZone = new \DateTimeZone(date_default_timezone_get());
			$demand->setLessonDate(new \DateTime('midnight'), $timeZone);
		}

		return $demand;
	}
}
