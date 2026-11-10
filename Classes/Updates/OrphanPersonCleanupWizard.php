<?php

namespace CPSIT\T3eventsReservation\Updates;

use CPSIT\T3eventsReservation\Service\OrphanPersonCleanup;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration as CoreExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class OrphanPersonCleanupWizard implements UpgradeWizardInterface, RepeatableInterface, ChattyInterface
{
    public const IDENTIFIER = 't3eventsReservationOrphanPersonCleanup';

    /** @var OrphanPersonCleanup */
    private $cleanup;

    /** @var CoreExtensionConfiguration */
    private $extensionConfiguration;

    /** @var OutputInterface|null */
    private $output;

    public function __construct(
        ?OrphanPersonCleanup $cleanup = null,
        ?CoreExtensionConfiguration $extensionConfiguration = null
    ) {
        $this->cleanup = $cleanup ?? GeneralUtility::makeInstance(OrphanPersonCleanup::class);
        $this->extensionConfiguration = $extensionConfiguration
            ?? GeneralUtility::makeInstance(CoreExtensionConfiguration::class);
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function getTitle(): string
    {
        return 'Soft-delete orphaned reservation person records';
    }

    public function getDescription(): string
    {
        return 'Soft-deletes participant, contact and billing address records without a live reservation '
            . 'on the pages listed in the extension setting t3events_reservation.orphanCleanupPids.';
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function updateNecessary(): bool
    {
        $pids = $this->getPids();
        if ($pids === []) {
            return false;
        }

        return array_sum($this->cleanup->countOrphans($pids)) > 0;
    }

    public function executeUpdate(): bool
    {
        $pids = $this->getPids();
        if ($pids === []) {
            $this->write('<error>Extension setting t3events_reservation.orphanCleanupPids is empty. No records were changed.</error>');
            return false;
        }

        $this->report('Before', $this->cleanup->countOrphans($pids));
        $deleted = $this->cleanup->softDeleteOrphans($pids);
        $this->write('Soft-deleted: ' . $deleted);
        $this->report('After', $this->cleanup->countOrphans($pids));

        return true;
    }

    /**
     * @return int[]
     */
    private function getPids(): array
    {
        try {
            $value = (string)$this->extensionConfiguration->get('t3events_reservation', 'orphanCleanupPids');
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $e) {
            return [];
        }
        $pids = array_filter(GeneralUtility::intExplode(',', $value, true), static function (int $pid) {
            return $pid > 0;
        });

        return array_values(array_unique($pids));
    }

    private function report(string $label, array $counts): void
    {
        $this->write($label . ': group A (reservation missing or deleted): ' . $counts['groupA']);
        $this->write($label . ': group B (unreferenced contact/billing address): ' . $counts['groupB']);
    }

    private function write(string $message): void
    {
        if ($this->output !== null) {
            $this->output->writeln($message);
        }
    }
}
