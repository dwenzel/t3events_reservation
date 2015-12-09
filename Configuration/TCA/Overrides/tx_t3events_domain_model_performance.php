<?php

$tmpColumns = array(
	'participants' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:t3events_course/Resources/Private/Language/locallang_db.xlf:tx_dakosyreservations_domain_model_reservation.participants',
		'config' => array(
			'type' => 'inline',
			'foreign_table' => 'tx_dakosyreservations_domain_model_person',
			'MM' => 'tx_dakosyreservations_lesson_participants_person_mm',
			'appearance' => array(
				'levelLinksPosition' => 'none',
				'enabledControls' => array(
					'info' => FALSE,
					'new' => FALSE,
					'dragdrop' => FALSE,
					'sort' => FALSE,
					'hide' => FALSE,
					'delete' => FALSE,
					'localize' => FALSE,
				),
			),
		),
	),
	'deadline' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.deadline',
		'config' => array(
			'type' => 'input',
			'size' => 7,
			'eval' => 'date',
			'checkbox' => 1,
			'default' => time()
		),
	),
	'registration_begin' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.registration_begin',
		'config' => array(
			'type' => 'input',
			'size' => 7,
			'eval' => 'date',
			'checkbox' => 1
		),
	),
	'price' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:t3events/Resources/Private/Language/locallang_db.xml:tx_t3events_domain_model_ticketclass.price',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'double2'
		),
	),
	'free_of_charge' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.free_of_charge',
		'config' => array(
			'type' => 'check',
			'default' => '0'
		)
	),
	'registration_remarks' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.registration_remarks',
		'config' => array(
			'type' => 'text',
			'cols' => 40,
			'rows' => 5,
			'eval' => 'trim'
		)
	),
	'document_based_registration' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.document_based_registration',
		'config' => array(
			'type' => 'check',
			'default' => '0'
		)
	),
	'external_registration' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.external_registration',
		'config' => array(
			'type' => 'check',
			'default' => '1'
		)
	),
	'external_registration_link' => array(
		'exclude' => 1,
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.external_registration_link',
		'config' => array(
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim'
		)
	),
	'registration_documents' => array(
		'label' => $ll . 'tx_dakosyreservations_domain_model_lesson.registration_documents',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'sys_file',
			'MM' => 'sys_file_reference',
			'MM_match_fields' => array(
				'fieldname' => 'registration_documents'
			),
			'prepend_tname' => TRUE,
			'appearance' => array(
				'elementBrowserAllowed' => 'doc,dox,pdf',
				'elementBrowserType' => 'file'
			),
			'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
			'show_thumbs' => '1',
			'size' => '3',
			'minitems' => '0',
			'maxitems' => '200',
			'autoSizeMax' => 40,
		),
	),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'tx_t3events_domain_model_performance',
	$temporaryColumns,
	TRUE
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'tx_t3events_domain_model_performance', 'price', '', 'before:price_notice');
