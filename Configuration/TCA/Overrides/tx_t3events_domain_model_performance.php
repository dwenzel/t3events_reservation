<?php
$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';

$GLOBALS['TCA']['tx_t3events_domain_model_performance']['ctrl']['type'] = 'tx_extbase_type';

$GLOBALS['TCA']['tx_t3events_domain_model_performance']['palettes']['paletteLessonRegistration'] = [
	'showitem' => 'registration_begin,deadline',
	'canNotCollapse' => TRUE
];
$extbaseType = 'Tx_T3eventsReservation_Schedule';
$scheduleShowItems = '
						--palette--;;paletteLessonDates,
        				date_remarks,class_time,event_location,status,course,
					--div--;' . $ll . 'label.tab.price,
						price,free_of_charge,price_notice,
					--div--;' . $ll . 'label.tab.registration,
						--palette--;;paletteLessonRegistration,places,registration_remarks,document_based_registration,
						registration_documents,external_registration,external_registration_link,
					--div--;' . $ll . 'label.tab.participants,
						participants,
					--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,
					tx_extbase_type,hidden,starttime, endtime';
$GLOBALS['TCA']['tx_t3events_domain_model_performance']['types'][$extbaseType]['showitem'] = $scheduleShowItems;

$temporaryColumns = [
	'participants' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.participants',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_t3events_domain_model_person',
			'MM' => 'tx_t3eventsreservation_performance_participants_person_mm',
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
	'deadline' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.deadline',
		'config' => [
			'type' => 'input',
			'size' => 7,
			'eval' => 'date',
			'checkbox' => 1,
			'default' => time()
		],
	],
	'registration_begin' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.registration_begin',
		'config' => [
			'type' => 'input',
			'size' => 7,
			'eval' => 'date',
			'checkbox' => 1
		],
	],
	'price' => [
		'exclude' => 0,
		'label' => 'LLL:EXT:t3events/Resources/Private/Language/locallang_db.xml:tx_t3events_domain_model_ticketclass.price',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'double2'
		],
	],
	'places' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.places',
		'config' => [
			'type' => 'input',
			'size' => 4,
			'eval' => 'int'
		]
	],
	'free_of_charge' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.free_of_charge',
		'config' => [
			'type' => 'check',
			'default' => '0'
		]
	],
	'registration_remarks' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.registration_remarks',
		'config' => [
			'type' => 'text',
			'cols' => 40,
			'rows' => 5,
			'eval' => 'trim'
		]
	],
	'document_based_registration' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.document_based_registration',
		'config' => [
			'type' => 'check',
			'default' => '0'
		]
	],
	'external_registration' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.external_registration',
		'config' => [
			'type' => 'check',
			'default' => '1'
		]
	],
	'external_registration_link' => [
		'exclude' => 1,
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.external_registration_link',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		]
	],
	'registration_documents' => [
		'label' => $ll . 'tx_t3eventsreservation_domain_model_performance.registration_documents',
		'config' => [
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'sys_file',
			'MM' => 'sys_file_reference',
			'MM_match_fields' => [
				'fieldname' => 'registration_documents'
			],
			'prepend_tname' => TRUE,
			'appearance' => [
				'elementBrowserAllowed' => 'doc,dox,pdf',
				'elementBrowserType' => 'file'
			],
			'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
			'show_thumbs' => '1',
			'size' => '3',
			'minitems' => '0',
			'maxitems' => '200',
			'autoSizeMax' => 40,
		],
	],
];

// add type field if missing
if (!isset($GLOBALS['TCA']['tx_t3events_domain_model_performance']['columns']['tx_extbase_type'])) {
	$temporaryColumns['tx_extbase_type'] = [
		'config' => [
			'label' => $ll . 'label.tx_extbase_type',
			'type' => 'select',
			'items' => [
				[$ll . 'label.tx_extbase_type.default', ''],
				[$ll . 'label.tx_extbase_type.Tx_T3eventsReservation_Schedule', $extbaseType]
			],
		]
	];
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
		'tx_t3events_domain_model_performance', 'tx_extbase_type', '', 'before:hidden');
} else {
	// add type item
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
		'tx_t3events_domain_model_performance',
		'tx_extbase_type',
		[$ll . 'label.tx_extbase_type.Tx_T3eventsReservation_Schedule', $extbaseType]
	);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_t3events_domain_model_performance',
	$temporaryColumns,
	TRUE
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_performance', 'price', '', 'before:price_notice');
