#
# Table structure for table 'tx_t3eventsreservation_domain_model_reservation'
#
CREATE TABLE tx_t3eventsreservation_domain_model_reservation (

	uid INT(11) NOT NULL AUTO_INCREMENT,
	pid INT(11) DEFAULT '0' NOT NULL,

	status INT(11) DEFAULT '0' NOT NULL,
	company INT(11) UNSIGNED DEFAULT '0',
	contact INT(11) UNSIGNED DEFAULT '0',
	participants INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	notifications INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	lesson INT(11) UNSIGNED DEFAULT '0',
	privacy_statement_accepted TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	offers_accepted TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	contact_is_participant TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,

	tstamp INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	crdate INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	cruser_id INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	deleted TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	hidden TINYINT(4) UNSIGNED DEFAULT '0' NOT NULL,
	starttime INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	endtime INT(11) UNSIGNED DEFAULT '0' NOT NULL,

	t3ver_oid INT(11) DEFAULT '0' NOT NULL,
	t3ver_id INT(11) DEFAULT '0' NOT NULL,
	t3ver_wsid INT(11) DEFAULT '0' NOT NULL,
	t3ver_label VARCHAR(255) DEFAULT '' NOT NULL,
	t3ver_state TINYINT(4) DEFAULT '0' NOT NULL,
	t3ver_stage INT(11) DEFAULT '0' NOT NULL,
	t3ver_count INT(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp INT(11) DEFAULT '0' NOT NULL,
	t3ver_move_id INT(11) DEFAULT '0' NOT NULL,

	sys_language_uid INT(11) DEFAULT '0' NOT NULL,
	l10n_parent INT(11) DEFAULT '0' NOT NULL,
	l10n_diffsource MEDIUMBLOB,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid, t3ver_wsid),
	KEY language (l10n_parent, sys_language_uid)

);

#
# Table structure for table 'tx_t3events_domain_model_person'
#
CREATE TABLE tx_t3events_domain_model_person (
	type INT(11) DEFAULT '0' NOT NULL,
	reservation INT(11) DEFAULT '0' NOT NULL,
	tx_extbase_type VARCHAR(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_t3events_domain_model_performance'
#
CREATE TABLE tx_t3events_domain_model_performance (
	participants int(11) unsigned DEFAULT '0' NOT NULL
);

#
# Table structure for table 'tx_t3eventsreservation_reservation_participants_person_mm'
#
CREATE TABLE tx_t3eventsreservation_reservation_participants_person_mm (
	uid_local INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	uid_foreign INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	sorting INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	sorting_foreign INT(11) UNSIGNED DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_t3eventsreservation_lesson_participants_person_mm'
#
CREATE TABLE tx_t3eventreservation_lesson_participants_person_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);
