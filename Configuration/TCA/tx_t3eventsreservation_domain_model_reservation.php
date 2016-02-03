<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$ll = 'LLL:EXT:t3events_reservation/Resources/Private/Language/locallang_db.xlf:';

return array(
	'ctrl' => array(
		'title' => $ll . 'tx_t3eventsreservation_domain_model_reservation',
		'label' => 'status',
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
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'status,company,contact, billing_address, participants,lesson,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('t3events_reservation') . 'Resources/Public/Icons/tx_t3eventsreservation_domain_model_reservation.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, status, company, contact, billing_address, participants, lesson, notifications, total_price, note',
	),
	'types' => array(
		'1' => array(
			'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, status, company, contact,
							billing_address, privacy_statement_accepted, offers_accepted, lesson, disclaim_revocation, total_price, note, feedback,
							postreservation_storage,
							--div--;' . $ll . 'tabs.participants, participants,
							--div--;' . $ll . 'tabs.notifications, notifications,
							--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime'
		),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_t3eventsreservation_domain_model_reservation',
				'foreign_table_where' => 'AND tx_t3eventsreservation_domain_model_reservation.pid=###CURRENT_PID### AND tx_t3eventsreservation_domain_model_reservation.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'status' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.status',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.0', 0), // new - neu
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.1', 1), // draft - Entwurf
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.2', 2), // submitted - gebucht
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.3', 3), // canceled (no charge) - storniert (kostenlos)
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.4', 4), // canceled (with costs) - storniert (kostenpflichtig)
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.5', 5), // closed - abgeschlossen
					array($ll . 'tx_t3eventsreservation_domain_model_reservation.status.6', 6), // canceled by Organizer- abgesagt durch Veranstalter
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => ''
			),
		),
		'company' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3events_domain_model_company',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_t3events_domain_model_company',
				'foreign_table_field' => 'name',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'contact' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.contact',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_t3events_domain_model_person',
				'foreign_table_field' => 'name',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'billing_address' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.billingAddress',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_t3events_domain_model_person',
				'foreign_table_field' => 'name',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'participants' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.participants',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_t3events_domain_model_person',
				'MM' => 'tx_t3eventsreservation_reservation_participants_person_mm',
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
		'contact_is_participant' => array(
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.contactIsParticipant',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'privacy_statement_accepted' => array(
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.privacyStatementAccepted',
			'config' => array(
				'type' => 'check',
				'default' => '0',
				'readOnly' => 1,
			),
		),
		'offers_accepted' => array(
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.offersAccepted',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'lesson' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.lesson',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_t3events_domain_model_performance',
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'notifications' => array(
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.notifications',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_t3events_domain_model_notification',
				'foreign_field' => 'reservation',
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
		'disclaim_revocation' => array(
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.disclaimRevocation',
			'config' => array(
				'type' => 'check',
				'default' => '0',
				'readOnly' => 1,
			),
		),
		'feedback' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.feedback',
			'config' => [
				'type' => 'text',
				'cols' => 40,
				'rows' => 10,
				'readOnly' => 1,
			]
		],
		'postreservation_storage' => [
			'exclude' => 1,
			'label' => $ll . 'tx_t3eventsreservation_domain_model_reservation.postReservationStorage',
			'config' => [
				'type' => 'text',
				'cols' => 40,
				'rows' => 10,
				'readOnly' => 1,
			]
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
	),
);
