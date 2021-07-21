<?php
namespace CPSIT\T3eventsReservation\Controller;

use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Utility\SettingsInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use DWenzel\T3events\Controller\FlashMessageTrait;
use DWenzel\T3events\Session\Typo3Session;

/**
 * Class ReservationAccessTrait
 * Provides access control for reservations
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
trait ReservationAccessTrait
{
    use FlashMessageTrait;

    /**
     * @var \DWenzel\T3events\Session\SessionInterface
     */
    protected $session;

    /**
     * Name of the action method
     *
     * @var string
     */
    protected $actionMethodName = 'indexAction';

    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * The current request.
     *
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     */
    protected $request;

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\Arguments Arguments passed to the controller
     */
    protected $arguments;

    /**
     * @var string
     */
    protected $accessError = Reservation::ERROR_ACCESS_UNKNOWN;

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\FlashMessageContainer
     * @api
     */
    protected $flashMessageContainer;


    /**
     * Clear cache of current page on error.
     *
     * @return void
     */
    abstract protected function clearCacheOnError();

    /**
     * @return int|false
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function getReservationIdFromRequest()
    {
        $reservationId = false;
        $argument = $this->request->getArgument(SettingsInterface::RESERVATION);
        if (is_string($argument)) {
            $reservationId = (int)$argument;
        }
        if ($argument instanceof Reservation) {
            $reservationId = $argument->getUid();
        }
        if (is_array($argument) && isset($argument[SettingsInterface::__IDENTITY])) {
            return (int)$argument[SettingsInterface::__IDENTITY];
        }

        return $reservationId;
    }

    /**
     * Redirects the request to another action and / or controller.
     *
     * @param string $actionName Name of the action to forward to
     * @param string $controllerName Unqualified object name of the controller to forward to. If not specified, the current controller is used.
     * @param string $extensionName Name of the extension containing the controller to forward to. If not specified, the current extension is assumed.
     * @param array $arguments Arguments to pass to the target action
     * @param integer $pageUid Target page uid. If NULL, the current page uid is used
     * @param integer $delay (optional) The delay in seconds. Default is no delay.
     * @param integer $statusCode (optional) The HTTP status code for the redirect. Default is "303 See Other
     * @return void
     */
    abstract protected function redirect(
        $actionName,
        $controllerName = null,
        $extensionName = null,
        array $arguments = null,
        $pageUid = null,
        $delay = 0,
        $statusCode = 303
    );

    /**
     * Forwards the request to another action and / or controller.
     * Request is directly transferred to the other action / controller
     * without the need for a new request.
     *
     * @param string $actionName Name of the action to forward to
     * @param string $controllerName Unqualified object name of the controller to forward to. If not specified, the current controller is used.
     * @param string $extensionName Name of the extension containing the controller to forward to. If not specified, the current extension is assumed.
     * @param array $arguments Arguments to pass to the target action
     * @return void
     */
    abstract public function forward(
        $actionName,
        $controllerName = null,
        $extensionName = null,
        array $arguments = null
    );

    /**
     * Translate a given key
     *
     * @param string $key
     * @param string $extension
     * @param array $arguments
     * @return string
     */
    abstract public function translate($key, $extension = 't3events', $arguments = null);

    /**
     * Checks if access is allowed
     *
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function isAccessAllowed()
    {
        if ($this->request->getControllerActionName() === 'error') {
            return true;
        }

        $sessionHasReservation = $this->session->has(ReservationController::SESSION_IDENTIFIER_RESERVATION);
        $requestHasReservation = $this->request->hasArgument(SettingsInterface::RESERVATION);

        if (!$requestHasReservation) {
            if ($sessionHasReservation) {
                $this->accessError = Reservation::ERROR_INCOMPLETE_RESERVATION_IN_SESSION;
            }

            return !$sessionHasReservation;
        }

        $sessionValue = (int)$this->session->get(ReservationController::SESSION_IDENTIFIER_RESERVATION);
        $reservationId = $this->getReservationIdFromRequest();

        if (!$sessionHasReservation && $requestHasReservation) {
            $this->accessError = Reservation::ERROR_MISSING_RESERVATION_KEY_IN_SESSION;

            return false;
        }

        if ((bool)$reservationId) {
            if (!($sessionHasReservation && ($sessionValue === $reservationId))) {
                $this->accessError = Reservation::ERROR_MISMATCH_SESSION_KEY_REQUEST_ARGUMENT;
            }

            // allow access if argument reservation matches session value
            return ($sessionHasReservation && ($sessionValue === $reservationId));
        }

        $this->accessError = Reservation::ERROR_MISSING_SESSION_KEY_AND_REQUEST_ARGUMENT;

        return false;
    }

    /**
     * Deny access
     * Issues an error message and redirects
     *
     * @return void
     * @throws InvalidSourceException
     */
    public function denyAccess()
    {
        $this->clearCacheOnError();
        $this->addFlashMessage(
            $this->getErrorFlashMessage(),
            '',
            FlashMessage::ERROR
        );

        throw new InvalidSourceException(
            'Access not allowed',
            1459870578
        );
    }

    /**
     * initialize action methods
     *
     * @throws InvalidSourceException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeAction()
    {
        $this->session = $this->objectManager->get(Typo3Session::class, ReservationController::SESSION_NAME_SPACE);

        if (!$this->isAccessAllowed()) {
            $this->denyAccess();
        }
    }

    /**
     * error action
     */
    public function errorAction()
    {
        $this->clearCacheOnError();

        if ($this->arguments instanceof Arguments) {

            $validationResult = $this->arguments->validate();
            if ($validationResult->hasErrors()) {
                $referringRequest = $this->request->getReferringRequest();
                if ($referringRequest !== null) {
                    $originalRequest = clone $this->request;
                    $this->request->setOriginalRequest($originalRequest);
                    $this->request->setOriginalRequestMappingResults($this->arguments->validate());
                    $this->forward($referringRequest->getControllerActionName(), $referringRequest->getControllerName(),
                        $referringRequest->getControllerExtensionName(), $referringRequest->getArguments());
                }
            }
        }

        $this->session->clean();
        // clear any previous flash message in order to avoid double entries
        $this->getFlashMessageQueue()->clear();

        $this->addFlashMessage(
            $this->getErrorFlashMessage(),
            '',
            FlashMessage::ERROR
        );
    }

    /**
     * Gets a localized error message
     *
     * @return string
     */
    public function getErrorFlashMessage()
    {
        $controllerName = strtolower($this->request->getControllerName());
        $actionName = strtolower($this->request->getControllerActionName());

        return $this->translate(
            'error.' . $controllerName . '.' . $actionName . '.' . $this->accessError,
            't3events_reservation');
    }
}
