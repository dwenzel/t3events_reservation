<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "t3events_reservation".
 *
 * Auto generated 13-03-2018 11:52
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Reservations',
	'description' => 'Manages reservations for events. Extends t3events ',
	'category' => 'plugin',
	'author' => 'Dirk Wenzel, Sebastian Kreideweiss',
	'author_email' => 'wenzel@cps-it.de, kreideweiss@cps-it.de',
	'state' => 'beta',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '0.15.1',
	'constraints' =>
	array (
		'depends' =>
		array (
			'typo3' => '8.7.0-8.99.99',
			't3events' => '1.1.0-0.0.0',
		),
		'conflicts' =>
		array (
		),
		'suggests' =>
		array (
		),
	)
);

