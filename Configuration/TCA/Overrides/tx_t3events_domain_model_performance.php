<?php
$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';

$GLOBALS['TCA']['tx_t3events_domain_model_performance']['ctrl']['type'] = 'tx_extbase_type';

$GLOBALS['TCA']['tx_t3events_domain_model_performance']['palettes']['paletteLessonRegistration'] = [
	'showitem' => 'registration_begin,deadline',
	'canNotCollapse' => TRUE
];
$extbaseType = 'Tx_T3eventsReservation_Schedule';
$scheduleShowItems = '
						sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource;;1,
        				--palette--;;paletteTitle,
        				--palette--;;paletteTime,
						--palette--;;paletteLessonDates,
        					event_location,status,
					--div--;' . $ll . 'label.tab.price,
						price,free_of_charge,price_notice,
					--div--;' . $ll . 'label.tab.registration,
						--palette--;;paletteLessonRegistration,places,registration_remarks,
						registration_documents,external_registration_link,
					--div--;' . $ll . 'label.tab.participants,
						participants,
					--div--;Access,
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
		'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
			'registration_documents',
			[
				'appearance' => [
					'headerThumbnail' => [
						'width' => '100',
						'height' => '100',
					],
					'createNewRelationLinkTitle' => $ll. 'label.addDocument'
				],
				'foreign_types' => [
					'0' => [
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					],
					\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					],
					\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					],
					\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
						'showitem' => '
						--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
						--palette--;;filePalette'
					]
				],
			],
			'doc,docx,pdf'
		)
	],
];

// add type field if missing
if (!isset($GLOBALS['TCA']['tx_t3events_domain_model_performance']['columns']['tx_extbase_type'])) {
	$temporaryColumns['tx_extbase_type'] = [
		'config' => [
			'label' => $ll . 'label.tx_extbase_type',
			'type' => 'select',
			'items' => [
				[$ll . 'label.tx_extbase_type.default', '0'],
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
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_performance', 'tx_extbase_type', '', 'before:hidden');
