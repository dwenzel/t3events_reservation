<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Command;

/***************************************************************
 *  Copyright notice
 *  (c) 2026 Dirk Wenzel <dirk.wenzel@cps-it.de>
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

use CPSIT\T3eventsReservation\Command\CloseBookingCommand;
use CPSIT\T3eventsReservation\Domain\Model\Person;
use CPSIT\T3eventsReservation\Domain\Model\Reservation;
use CPSIT\T3eventsReservation\Domain\Model\Schedule;
use CPSIT\T3eventsReservation\Service\ReservationRemover;
use DWenzel\T3events\Service\NotificationService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class CloseBookingCommandTest
 *
 * Covers the crash fix for #82103.1: the BE scheduler cannot set the
 * --email option for the hourly `cleanupIncompleteReservations` task,
 * so Symfony passes null. Before the fix, `cleanupIncompleteReservationsCommand()`
 * declared a non-nullable `string $email` parameter, causing a TypeError
 * on every scheduled run.
 *
 * @package CPSIT\T3eventsReservation\Tests\Unit\Command
 */
class CloseBookingCommandTest extends UnitTestCase
{
    /**
     * @var CloseBookingCommand|MockObject
     */
    protected $subject;

    /**
     * @var NotificationService|MockObject
     */
    protected $notificationService;

    /**
     * set up the subject
     */
    protected function setUp(): void
    {
        $this->subject = $this->getAccessibleMock(
            CloseBookingCommand::class,
            ['deleteInvalidReservations']
        );
        $this->notificationService = $this->getMockBuilder(NotificationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['notify'])
            ->getMock();
        $this->subject->injectNotificationService($this->notificationService);
    }

    /**
     * Regression test for the scheduler crash: the BE scheduler UI cannot
     * set --email for this task, so Symfony passes null. This must not
     * raise a TypeError and must not attempt to send a notification.
     *
     * @test
     */
    public function cleanupIncompleteReservationsCommandCompletesWithoutTypeErrorWhenEmailIsNull()
    {
        $this->subject->expects($this->once())
            ->method('deleteInvalidReservations')
            ->with(false, 7200)
            ->willReturn(3);

        $this->notificationService->expects($this->never())
            ->method('notify');

        $this->subject->cleanupIncompleteReservationsCommand(7200, null, false);
    }

    /**
     * @test
     */
    public function cleanupIncompleteReservationsCommandDoesNotNotifyWhenEmailIsEmptyString()
    {
        $this->subject->expects($this->once())
            ->method('deleteInvalidReservations')
            ->with(false, 7200)
            ->willReturn(0);

        $this->notificationService->expects($this->never())
            ->method('notify');

        $this->subject->cleanupIncompleteReservationsCommand(7200, '', false);
    }

    /**
     * Regression test: existing notify-on-email behavior must not change
     * when a non-empty email address is supplied.
     *
     * @test
     */
    public function cleanupIncompleteReservationsCommandNotifiesExactlyOnceWhenEmailIsGiven()
    {
        $email = 'foo@example.test';

        $this->subject->expects($this->once())
            ->method('deleteInvalidReservations')
            ->willReturn(5);

        $this->notificationService->expects($this->once())
            ->method('notify')
            ->with(
                $email,
                'no-reply@example.com',
                'cleanup incomplete reservations',
                CloseBookingCommand::TEMPLATE_EMAIL,
                null,
                CloseBookingCommand::FOLDER_CLEANUP_INCOMPLETE,
                [
                    'dryRun' => false,
                    'age' => 7200,
                    'deletedCount' => 5
                ]
            );

        $this->subject->cleanupIncompleteReservationsCommand(7200, $email, false);
    }

    /**
     * @return array{0: MockObject, 1: MockObject, 2: MockObject, 3: MockObject}
     */
    protected function buildDeletionSubject(): array
    {
        $schedule = $this->getMockBuilder(Schedule::class)
            ->onlyMethods(['removeParticipant'])
            ->getMock();
        $schedule->initializeObject();
        $participants = new ObjectStorage();
        $participants->attach(new Person());
        $participants->attach(new Person());
        $withLesson = new Reservation();
        $withLesson->setLesson($schedule);
        $withLesson->setParticipants($participants);
        $iterator = new \ArrayIterator([$withLesson, new Reservation()]);
        $reservations = $this->createMock(QueryResultInterface::class);
        $reservations->method('count')->willReturn(2);
        $reservations->method('rewind')->willReturnCallback([$iterator, 'rewind']);
        $reservations->method('valid')->willReturnCallback([$iterator, 'valid']);
        $reservations->method('current')->willReturnCallback([$iterator, 'current']);
        $reservations->method('key')->willReturnCallback([$iterator, 'key']);
        $reservations->method('next')->willReturnCallback([$iterator, 'next']);

        $subject = $this->getAccessibleMock(
            CloseBookingCommand::class,
            ['getInvalidReservations']
        );
        $subject->method('getInvalidReservations')->willReturn($reservations);
        $persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $remover = $this->createMock(ReservationRemover::class);
        $subject->_set('persistenceManager', $persistenceManager);
        $subject->injectReservationRemover($remover);

        return [$subject, $remover, $persistenceManager, $schedule];
    }

    /**
     * @test
     */
    public function deleteInvalidReservationsRemovesEachReservationViaRemover()
    {
        [$subject, $remover] = $this->buildDeletionSubject();
        $remover->expects($this->exactly(2))->method('remove');

        $this->assertSame(2, $subject->_call('deleteInvalidReservations', false, 7200));
    }

    /**
     * @test
     */
    public function deleteInvalidReservationsPersistsOnce()
    {
        [$subject, , $persistenceManager] = $this->buildDeletionSubject();
        $persistenceManager->expects($this->once())->method('persistAll');

        $subject->_call('deleteInvalidReservations', false, 7200);
    }

    /**
     * @test
     */
    public function deleteInvalidReservationsDetachesParticipantsFromLesson()
    {
        [$subject, , , $schedule] = $this->buildDeletionSubject();
        $schedule->expects($this->exactly(2))->method('removeParticipant');

        $subject->_call('deleteInvalidReservations', false, 7200);
    }

    /**
     * @test
     */
    public function deleteInvalidReservationsDryRunRemovesNothing()
    {
        [$subject, $remover, $persistenceManager] = $this->buildDeletionSubject();
        $remover->expects($this->never())->method('remove');
        $persistenceManager->expects($this->never())->method('persistAll');

        $this->assertSame(2, $subject->_call('deleteInvalidReservations', true, 7200));
    }
}
