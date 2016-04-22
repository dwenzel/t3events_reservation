<?php
namespace CPSIT\T3eventsReservation\Controller;

use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use Webfox\T3events\Session\Typo3Session;

/**
 * Class ReservationAccessTrait
 * Provides access control for reservations
 *
 * @package CPSIT\T3eventsReservation\Controller
 */
trait ReservationAccessTrait
{
    /**
     * @var \Webfox\T3events\Session\SessionInterface
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
     * Clear cache of current page on error.
     *
     * @return void
     */
    abstract protected function clearCacheOnError();

    /**
     * Creates a Message object and adds it to the FlashMessageQueue.
     *
     * @param string $messageBody The message
     * @param string $messageTitle Optional message title
     * @param integer $severity Optional severity, must be one of \TYPO3\CMS\Core\Messaging\FlashMessage constants
     * @param boolean $storeInSession Optional, defines whether the message should be stored in the session (default) or not
     * @return void
     * @see \TYPO3\CMS\Extbase\Controller\AbstractController
     */
    abstract public function addFlashMessage(
        $messageBody,
        $messageTitle = '',
        $severity = AbstractMessage::OK,
        $storeInSession = true
    );

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
     */
    public function isAccessAllowed()
    {
        $sessionHasReservation = $this->session->has(ReservationController::SESSION_IDENTIFIER_RESERVATION);
        $requestHasReservation = $this->request->hasArgument('reservation');

        if (!$requestHasReservation) {
            return !$sessionHasReservation;
        }

        $sessionValue = (int)$this->session->get(ReservationController::SESSION_IDENTIFIER_RESERVATION);

        $argument = $this->request->getArgument('reservation');
        if (is_string($argument)) {
            $reservationId =  (int)$argument;
        }
        if ($argument instanceof Reservation) {
            $reservationId = $argument->getUid();
        }
        if (is_array($argument) && isset($argument['__identity'])) {
            $reservationId = (int)$argument['__identity'];
        }

        if (isset($reservationId)) {
            // allow access if argument reservation matches session value
            return ($sessionHasReservation &&  $sessionValue === $reservationId);
        }

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

        throw new InvalidSourceException(
            'Access not allowed',
            1459870578
        );
    }

    /**
     * initialize action methods
     *
     * @throws InvalidSourceException
     */
    public function initializeAction()
    {
        $this->session = $this->objectManager->get(Typo3Session::class, ReservationController::SESSION_NAME_SPACE);

        if (!$this->isAccessAllowed()) {
            $this->denyAccess();
        }
    }
}
