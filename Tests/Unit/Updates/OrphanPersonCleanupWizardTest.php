<?php

namespace CPSIT\T3eventsReservation\Tests\Unit\Updates;

use CPSIT\T3eventsReservation\Service\OrphanPersonCleanup;
use CPSIT\T3eventsReservation\Updates\OrphanPersonCleanupWizard;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\BufferedOutput;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

class OrphanPersonCleanupWizardTest extends UnitTestCase
{
    /** @var OrphanPersonCleanup|MockObject */
    private $cleanup;

    /** @var ExtensionConfiguration|MockObject */
    private $extensionConfiguration;

    /** @var OrphanPersonCleanupWizard */
    private $subject;

    protected function setUp(): void
    {
        $this->cleanup = $this->getMockBuilder(OrphanPersonCleanup::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['countOrphans', 'softDeleteOrphans'])
            ->getMock();
        $this->extensionConfiguration = $this->getMockBuilder(ExtensionConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $this->subject = new OrphanPersonCleanupWizard($this->cleanup, $this->extensionConfiguration);
    }

    private function givenSetting(string $value): void
    {
        $this->extensionConfiguration->method('get')
            ->with('t3events_reservation', 'orphanCleanupPids')
            ->willReturn($value);
    }

    /**
     * @test
     */
    public function getIdentifierReturnsT3eventsReservationOrphanPersonCleanup(): void
    {
        $this->assertSame('t3eventsReservationOrphanPersonCleanup', $this->subject->getIdentifier());
    }

    /**
     * @test
     */
    public function getPrerequisitesRequiresDatabaseUpdated(): void
    {
        $this->assertSame([DatabaseUpdatedPrerequisite::class], $this->subject->getPrerequisites());
    }

    /**
     * @test
     */
    public function updateNecessaryReturnsFalseWithEmptySetting(): void
    {
        $this->givenSetting('');
        $this->cleanup->expects($this->never())->method('countOrphans');

        $this->assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryReturnsFalseWhenSettingIsMissing(): void
    {
        $this->extensionConfiguration->method('get')
            ->willThrowException(new ExtensionConfigurationPathDoesNotExistException());
        $this->cleanup->expects($this->never())->method('countOrphans');

        $this->assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryPassesParsedPositiveUniquePids(): void
    {
        $this->givenSetting(' 4086, abc,0,-5, 12 ,4086');
        $this->cleanup->expects($this->once())->method('countOrphans')
            ->with([4086, 12])
            ->willReturn(['groupA' => 0, 'groupB' => 0]);

        $this->subject->updateNecessary();
    }

    /**
     * @test
     */
    public function updateNecessaryReturnsTrueWhileOrphansExist(): void
    {
        $this->givenSetting('4086');
        $this->cleanup->method('countOrphans')->willReturn(['groupA' => 1, 'groupB' => 0]);

        $this->assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryReturnsFalseWhenNoOrphansLeft(): void
    {
        $this->givenSetting('4086');
        $this->cleanup->method('countOrphans')->willReturn(['groupA' => 0, 'groupB' => 0]);

        $this->assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function executeUpdateRefusesWithEmptySettingAndTouchesNothing(): void
    {
        $this->givenSetting('');
        $this->cleanup->expects($this->never())->method('softDeleteOrphans');
        $output = new BufferedOutput();
        $this->subject->setOutput($output);

        $this->assertFalse($this->subject->executeUpdate());
        $this->assertStringContainsString('orphanCleanupPids', $output->fetch());
    }

    /**
     * @test
     */
    public function executeUpdateSoftDeletesAndReportsBeforeAndAfter(): void
    {
        $this->givenSetting('4086');
        $this->cleanup->expects($this->exactly(2))->method('countOrphans')
            ->with([4086])
            ->willReturnOnConsecutiveCalls(
                ['groupA' => 3, 'groupB' => 2],
                ['groupA' => 0, 'groupB' => 0]
            );
        $this->cleanup->expects($this->once())->method('softDeleteOrphans')
            ->with([4086])
            ->willReturn(5);
        $output = new BufferedOutput();
        $this->subject->setOutput($output);

        $this->assertTrue($this->subject->executeUpdate());
        $text = $output->fetch();
        $this->assertStringContainsString('Before', $text);
        $this->assertStringContainsString('Soft-deleted: 5', $text);
        $this->assertStringContainsString('After', $text);
    }
}
