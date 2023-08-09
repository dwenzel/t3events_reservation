<?php
return [
    DWenzel\T3events\Domain\Model\Performance::class => [
        'recordType' => 0,
        'subclasses' => [
            'Tx_T3eventsReservation_Schedule' => CPSIT\T3eventsReservation\Domain\Model\Schedule::class
        ],
    ],

    CPSIT\T3eventsReservation\Domain\Model\Schedule::class => [
        'tableName' => 'tx_t3events_domain_model_performance',
        'recordType' => 'Tx_T3eventsReservation_Schedule',
    ],

    CPSIT\T3eventsReservation\Domain\Model\Contact::class => [
        'tableName' => 'tx_t3events_domain_model_person',
        'recordType' => 'Tx_T3events_Contact',
    ],

    CPSIT\T3eventsReservation\Domain\Model\BillingAddress::class => [
        'tableName' => 'tx_t3events_domain_model_person',
        'recordType' => 'Tx_T3eventsReservation_BillingAddress',
    ],

    CPSIT\T3eventsReservation\Domain\Model\Person::class => [
        'tableName' => 'tx_t3events_domain_model_person',
        'recordType' => 'Tx_T3eventsReservation_Participant',
    ],

    DWenzel\T3events\Domain\Model\Person::class => [
        'subclasses' => [
            'Tx_T3events_Contact' => CPSIT\T3eventsReservation\Domain\Model\Contact::class,
            'Tx_T3eventsReservation_Participant' => CPSIT\T3eventsReservation\Domain\Model\Person::class,
            'Tx_T3eventsReservation_BillingAddress' => CPSIT\T3eventsReservation\Domain\Model\BillingAddress::class,
        ],
    ],

    CPSIT\T3eventsReservation\Domain\Model\Notification::class => [
        'tableName' => 'tx_t3events_domain_model_notification',
    ],

    CPSIT\T3eventsReservation\Domain\Model\Task::class => [
        'tableName' => 'tx_t3events_domain_model_task'
    ],
];