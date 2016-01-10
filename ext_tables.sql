#
# Table structure for table 'tx_t3eventsreservation_domain_model_reservation'
#
CREATE TABLE tx_t3eventsreservation_domain_model_reservation (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	status int(11) DEFAULT '0' NOT NULL,
	company int(11) unsigned DEFAULT '0',
	contact int(11) unsigned DEFAULT '0',
	billing_address int(11) unsigned DEFAULT '0',
	participants int(11) unsigned DEFAULT '0' NOT NULL,
	notifications int(11) unsigned DEFAULT '0' NOT NULL,
	lesson int(11) unsigned DEFAULT '0',
	privacy_statement_accepted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	offers_accepted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	contact_is_participant tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

#
# Table structure for table 'tx_t3events_domain_model_person'
#
CREATE TABLE tx_t3events_domain_model_person (
	type int(11) DEFAULT '0' NOT NULL,
	reservation int(11) DEFAULT '0' NOT NULL,
	birthplace varchar(255) DEFAULT '' NOT NULL,
	company_name varchar(255) DEFAULT '' NOT NULL,
	role varchar(255) DEFAULT '' NOT NULL,
	tx_extbase_type varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tx_t3events_domain_model_performance'
#
CREATE TABLE tx_t3events_domain_model_performance (
	participants int(11) unsigned DEFAULT '0' NOT NULL,
	deadline int(11) DEFAULT '0' NOT NULL,
	date_end int(11) DEFAULT '0' NOT NULL,
	registration_begin int(11) DEFAULT '0' NOT NULL,
	price double(11,2) DEFAULT '0.00' NOT NULL,
	places int(11) DEFAULT '0' NOT NULL,
	free_of_charge tinyint(1) unsigned DEFAULT '0' NOT NULL,
	date_remarks text NOT NULL,
	registration_remarks text NOT NULL,
	external_registration_link tinytext NOT NULL,
	document_based_registration tinyint(1) unsigned DEFAULT '0' NOT NULL,
	external_registration tinyint(1) unsigned DEFAULT '0' NOT NULL,
	registration_documents tinytext NOT NULL
);

#
# Table structure for table 'tx_t3eventsreservation_reservation_participants_person_mm'
#
CREATE TABLE tx_t3eventsreservation_reservation_participants_person_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_t3eventsreservation_performance_participants_person_mm'
#
CREATE TABLE tx_t3eventsreservation_performance_participants_person_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);

###
#
# Table structure for table 'tx_t3events_domain_model_notification'
#
CREATE TABLE tx_t3events_domain_model_notification (
	reservation int(11) unsigned DEFAULT '0' NOT NULL
);