<?php
namespace CPSIT\T3eventsReservation\Tests\Unit\Controller;

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

use CPSIT\T3eventsReservation\Controller\ParticipantDemandFactoryTrait;
use CPSIT\T3eventsReservation\Domain\Factory\Dto\ParticipantDemandFactory;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class ParticipantDemandFactoryTraitTest extends UnitTestCase
{
    /**
     * @var ParticipantDemandFactoryTrait
     */
    protected $subject;

    /**
     * set up the subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            ParticipantDemandFactoryTrait::class
        );
    }

    /**
     * @test
     */
    public function participantDemandFactoryCanBeInjected()
    {
        $participantDemandFactory = $this->getMockBuilder(ParticipantDemandFactory::class)
            ->disableOriginalConstructor()->getMock();

        $this->subject->injectParticipantDemandFactory($participantDemandFactory);

        $this->assertAttributeSame(
            $participantDemandFactory,
            'demandFactory',
            $this->subject
        );
    }
}
