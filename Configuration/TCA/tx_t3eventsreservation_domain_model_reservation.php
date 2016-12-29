<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';

return [
	'ctrl' => [
		'title' => $ll . 'tx_t3eventsreservation_domain_model_reservation',
		'label' => 'uid',
		'label_alt' => 'status',
        'label_alt_force' => true,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		],
		'searchFields' => 'status,company,contact, billing_address, participants,lesson,',
		'iconfile' => 'EXT:t3events_reservation/Resources/Public/Icons/tx_t3eventsreservation_domain_model_reservation.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, status, company, contact, billing_address, participants, lesson, notifications, total_price, note',
	],
	'types' => [
		'1' => [
			'showitem' => 'status, lesson, contact,
							billing_address, privacy_statement_accepted, disclaim_revocation, total_price, note,
							--div--;' . $ll . 'tabs.participants, participants,
							--div--;' . $ll . 'tabs.notifications, notifications,
							--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, sys_language_uid, l10n_parent, l10n_diffsource, hidden, starttime, endtime'
		],
	],
	'palettes' => [
		'1' => ['showitem' => ''],
	],
	'columns' => [
		'sys_language_uid' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
					['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
				],
			],
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
					['', 0],
				],
				'foreign_table' => 'tx_t3eventsreservation_domain_model_reservation',
				'foreign_table_where' => 'AND tx_t3eventsreservation_domain_model_reservation.pid=###CURRENT_PID### AND tx_t3eventsreservation_domain_model_reservation.sys_language_uid IN (-1,0)',
			],
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
			],
		],

		't3ver_label' => [
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			]
		],

		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				],
			],
		],
		'endtime' => [
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => [
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				],
			],
		],

		'status' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.status',
			'config' => [
				'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.0', 0], // new - neu
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.1', 1], // draft - Entwurf
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.2', 2], // submitted - gebucht
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.3', 3], // canceled (no charge) - storniert (kostenlos)
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.4', 4], // canceled (with costs) - storniert (kostenpflichtig)
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.5', 5], // closed - abgeschlossen
					[$ll . 'tx_t3eventsreservation_domain_model_reservation.status.6', 6], // canceled by Organizer- abgesagt durch Veranstalter
				],
				'size' => 1,
				'maxitems' => 1,
				'eval' => ''
			],
		],
		// TODO; remove as obsolete due merge with model company into person using contact field
		'company' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.company',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_t3events_domain_model_company',
				'size' => '1',
				'minitems' => 0,
				'maxitems' => 1,
				'readOnly' => 1,
			],
		],
		'contact' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.contact',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_t3events_domain_model_person',
				'foreign_table' => 'tx_t3events_domain_model_person',
				'size' => '1',
				'minitems' => 0,
				'maxitems' => 1,
				'readOnly' => 1,
			],
		],
		'billing_address' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.billingAddress',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_t3events_domain_model_person',
				'size' => '1',
				'minitems' => 0,
				'maxitems' => 1,
				'readOnly' => 1,
			],
		],
		'participants' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.participants',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_t3events_domain_model_person',
				'MM' => 'tx_t3eventsreservation_reservation_participants_person_mm',
				'appearance' => [
					'levelLinksPosition' => 'none',
					'enabledControls' => [
						'info' => FALSE,
						'new' => FALSE,
						'dragdrop' => FALSE,
						'sort' => FALSE,
						'hide' => FALSE,
						'delete' => FALSE,
						'localize' => FALSE,
					],
				],
			],
		],
		'contact_is_participant' => [
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.contactIsParticipant',
			'config' => [
				'type' => 'check',
				'default' => '0',
			],
		],
		'privacy_statement_accepted' => [
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.privacyStatementAccepted',
			'config' => [
				'type' => 'check',
				'default' => '0',
				'readOnly' => 1,
			],
		],
		'lesson' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.lesson',
			'config' => [
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_t3events_domain_model_performance',
				'foreign_table' => 'tx_t3events_domain_model_performance',
				'size' => '1',
				'minitems' => 0,
				'maxitems' => 1,
				'readOnly' => 1,
			],
		],
		'notifications' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.notifications',
			'config' => [
				'type' => 'inline',
				'foreign_table' => 'tx_t3events_domain_model_notification',
				'foreign_field' => 'reservation',
				'appearance' => [
					'levelLinksPosition' => 'none',
					'enabledControls' => [
						'info' => FALSE,
						'new' => FALSE,
						'dragdrop' => FALSE,
						'sort' => FALSE,
						'hide' => FALSE,
						'delete' => FALSE,
						'localize' => FALSE,
					],
				],
			],
		],
		'disclaim_revocation' => [
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.disclaimRevocation',
			'config' => [
				'type' => 'check',
				'default' => '0',
				'readOnly' => 1,
			],
		],
		'total_price' => [
			'exclude' => 0,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.total_price',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'eval' => 'double2'
			],
		],
		'note' => [
			'exclude' => 0,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.note',
			'config' => [
				'type' => 'text',
				'cols' => 32,
				'rows' => 5,
				'eval' => 'trim'
			],
		],
	],
];
