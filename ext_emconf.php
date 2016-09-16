<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "t3events_reservation".
 *
 * Auto generated 16-09-2016 07:54
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
	'version' => '0.8.1',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.2.0-7.99.99',
			't3events' => '0.27.0-0.0.0',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
	'_md5_values_when_last_written' => 'a:125:{s:9:"ChangeLog";s:4:"76f0";s:13:"composer.json";s:4:"7008";s:12:"ext_icon.gif";s:4:"3f11";s:17:"ext_localconf.php";s:4:"6a1c";s:14:"ext_tables.php";s:4:"a152";s:14:"ext_tables.sql";s:4:"16d5";s:28:"ext_typoscript_constants.txt";s:4:"a91f";s:24:"ext_typoscript_setup.txt";s:4:"7137";s:30:"Classes/PriceableInterface.php";s:4:"79fe";s:49:"Classes/Command/CloseBookingCommandController.php";s:4:"8a94";s:45:"Classes/Controller/AccessControlInterface.php";s:4:"8339";s:40:"Classes/Controller/ContactController.php";s:4:"e9ec";s:44:"Classes/Controller/ParticipantController.php";s:4:"8827";s:45:"Classes/Controller/ReservationAccessTrait.php";s:4:"5a63";s:44:"Classes/Controller/ReservationController.php";s:4:"61e0";s:49:"Classes/Controller/Backend/BookingsController.php";s:4:"bc68";s:52:"Classes/Controller/Backend/ParticipantController.php";s:4:"5bfb";s:39:"Classes/Domain/Model/BillingAddress.php";s:4:"cc3c";s:42:"Classes/Domain/Model/BookableInterface.php";s:4:"c117";s:46:"Classes/Domain/Model/BookableScheduleTrait.php";s:4:"bc04";s:32:"Classes/Domain/Model/Contact.php";s:4:"f3ba";s:37:"Classes/Domain/Model/Notification.php";s:4:"81a2";s:31:"Classes/Domain/Model/Person.php";s:4:"a455";s:36:"Classes/Domain/Model/Reservation.php";s:4:"614e";s:47:"Classes/Domain/Model/ReservationPersonTrait.php";s:4:"cd6e";s:33:"Classes/Domain/Model/Schedule.php";s:4:"ad4f";s:41:"Classes/Domain/Model/Dto/PersonDemand.php";s:4:"69f8";s:46:"Classes/Domain/Model/Dto/ReservationDemand.php";s:4:"3b74";s:54:"Classes/Domain/Repository/BillingAddressRepository.php";s:4:"9155";s:47:"Classes/Domain/Repository/ContactRepository.php";s:4:"4009";s:46:"Classes/Domain/Repository/PersonRepository.php";s:4:"b088";s:51:"Classes/Domain/Repository/ReservationRepository.php";s:4:"b03d";s:45:"Classes/Domain/Validator/ContactValidator.php";s:4:"f853";s:49:"Classes/Domain/Validator/ParticipantValidator.php";s:4:"d1c4";s:42:"Classes/Slot/ReservationControllerSlot.php";s:4:"1d84";s:69:"Configuration/TCA/tx_t3eventsreservation_domain_model_reservation.php";s:4:"8910";s:69:"Configuration/TCA/Overrides/tx_t3events_domain_model_notification.php";s:4:"1e68";s:68:"Configuration/TCA/Overrides/tx_t3events_domain_model_performance.php";s:4:"85c6";s:63:"Configuration/TCA/Overrides/tx_t3events_domain_model_person.php";s:4:"6b38";s:38:"Configuration/TypoScript/constants.txt";s:4:"404e";s:34:"Configuration/TypoScript/setup.txt";s:4:"89c9";s:43:"Resources/Private/Language/de.locallang.xlf";s:4:"7452";s:46:"Resources/Private/Language/de.locallang_db.xlf";s:4:"4049";s:46:"Resources/Private/Language/de.locallang_m1.xlf";s:4:"3e51";s:46:"Resources/Private/Language/de.locallang_m3.xlf";s:4:"d02e";s:40:"Resources/Private/Language/locallang.xlf";s:4:"36d7";s:48:"Resources/Private/Language/locallang_booking.xlf";s:4:"6e7a";s:88:"Resources/Private/Language/locallang_csh_tx_t3eventsreservation_domain_model_contact.xlf";s:4:"5470";s:92:"Resources/Private/Language/locallang_csh_tx_t3eventsreservation_domain_model_reservation.xlf";s:4:"6f29";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"a98c";s:43:"Resources/Private/Language/locallang_m1.xlf";s:4:"f6e0";s:43:"Resources/Private/Language/locallang_m3.xlf";s:4:"5e52";s:52:"Resources/Private/Language/locallang_reservation.xlf";s:4:"e042";s:38:"Resources/Private/Layouts/Default.html";s:4:"3c8a";s:42:"Resources/Private/Partials/FormErrors.html";s:4:"b0f9";s:50:"Resources/Private/Partials/Backend/FormErrors.html";s:4:"4d63";s:62:"Resources/Private/Partials/Backend/Participant/FormFields.html";s:4:"fc3f";s:68:"Resources/Private/Partials/Backend/Reservation/LessonProperties.html";s:4:"7f22";s:62:"Resources/Private/Partials/Backend/Reservation/Properties.html";s:4:"ac2b";s:57:"Resources/Private/Partials/BillingAddress/FormFields.html";s:4:"9673";s:50:"Resources/Private/Partials/Contact/FormFields.html";s:4:"e7f3";s:55:"Resources/Private/Partials/Notification/FormFields.html";s:4:"a4d7";s:56:"Resources/Private/Partials/Participant/BackendShort.html";s:4:"a156";s:54:"Resources/Private/Partials/Participant/FormFields.html";s:4:"a117";s:49:"Resources/Private/Partials/Participant/Short.html";s:4:"0497";s:48:"Resources/Private/Partials/Participant/Show.html";s:4:"b2bd";s:53:"Resources/Private/Partials/Participant/ShowShort.html";s:4:"8f11";s:54:"Resources/Private/Partials/Reservation/FormFields.html";s:4:"ac88";s:60:"Resources/Private/Partials/Reservation/LessonProperties.html";s:4:"e38a";s:54:"Resources/Private/Partials/Reservation/Properties.html";s:4:"2a68";s:58:"Resources/Private/Templates/Backend/Bookings/Download.html";s:4:"fc5b";s:54:"Resources/Private/Templates/Backend/Bookings/Edit.html";s:4:"ff02";s:54:"Resources/Private/Templates/Backend/Bookings/List.html";s:4:"ea01";s:53:"Resources/Private/Templates/Backend/Bookings/New.html";s:4:"633d";s:65:"Resources/Private/Templates/Backend/Bookings/NewNotification.html";s:4:"254f";s:64:"Resources/Private/Templates/Backend/Bookings/NewParticipant.html";s:4:"1e26";s:54:"Resources/Private/Templates/Backend/Bookings/Show.html";s:4:"7d53";s:74:"Resources/Private/Templates/Backend/Bookings/Email/Cancel/ByOrganizer.html";s:4:"9fcf";s:71:"Resources/Private/Templates/Backend/Bookings/Email/Cancel/NoCharge.html";s:4:"3cad";s:72:"Resources/Private/Templates/Backend/Bookings/Email/Cancel/WithCosts.html";s:4:"c8bc";s:85:"Resources/Private/Templates/Backend/Bookings/Email/RemoveParticipant/ByOrganizer.html";s:4:"1f55";s:82:"Resources/Private/Templates/Backend/Bookings/Email/RemoveParticipant/NoCharge.html";s:4:"b873";s:83:"Resources/Private/Templates/Backend/Bookings/Email/RemoveParticipant/WithCosts.html";s:4:"65a9";s:61:"Resources/Private/Templates/Backend/Participant/Download.html";s:4:"c958";s:57:"Resources/Private/Templates/Backend/Participant/List.html";s:4:"cb5f";s:56:"Resources/Private/Templates/CleanupIncomplete/Email.html";s:4:"1b02";s:54:"Resources/Private/Templates/CloseBooking/Download.html";s:4:"3943";s:51:"Resources/Private/Templates/CloseBooking/Email.html";s:4:"0aed";s:45:"Resources/Private/Templates/Contact/Edit.html";s:4:"0979";s:49:"Resources/Private/Templates/Participant/Edit.html";s:4:"b01c";s:55:"Resources/Private/Templates/ReportExpired/Download.html";s:4:"3943";s:52:"Resources/Private/Templates/ReportExpired/Email.html";s:4:"0c5a";s:53:"Resources/Private/Templates/Reservation/Checkout.html";s:4:"c582";s:51:"Resources/Private/Templates/Reservation/Delete.html";s:4:"4b90";s:49:"Resources/Private/Templates/Reservation/Edit.html";s:4:"c92e";s:63:"Resources/Private/Templates/Reservation/EditBillingAddress.html";s:4:"8f2d";s:50:"Resources/Private/Templates/Reservation/Error.html";s:4:"2bff";s:49:"Resources/Private/Templates/Reservation/List.html";s:4:"e54d";s:48:"Resources/Private/Templates/Reservation/New.html";s:4:"67a2";s:62:"Resources/Private/Templates/Reservation/NewBillingAddress.html";s:4:"d3b2";s:59:"Resources/Private/Templates/Reservation/NewParticipant.html";s:4:"0d20";s:49:"Resources/Private/Templates/Reservation/Show.html";s:4:"2151";s:56:"Resources/Private/Templates/Reservation/Email/Admin.html";s:4:"4c4d";s:55:"Resources/Private/Templates/Reservation/Email/User.html";s:4:"c37a";s:30:"Resources/Public/Css/forms.css";s:4:"1c2f";s:28:"Resources/Public/Css/sbt.css";s:4:"58b4";s:36:"Resources/Public/Css/sbt_backend.css";s:4:"7e3e";s:42:"Resources/Public/Icons/icon_excel_blue.png";s:4:"4374";s:43:"Resources/Public/Icons/icon_excel_white.png";s:4:"4e09";s:50:"Resources/Public/Icons/module_icon_participant.png";s:4:"f53d";s:50:"Resources/Public/Icons/module_icon_reservation.png";s:4:"c4f4";s:70:"Resources/Public/Icons/tx_t3eventsreservation_domain_model_contact.gif";s:4:"905a";s:74:"Resources/Public/Icons/tx_t3eventsreservation_domain_model_reservation.gif";s:4:"905a";s:25:"Tests/Build/UnitTests.xml";s:4:"44ae";s:48:"Tests/Unit/Controller/BookingsControllerTest.php";s:4:"e00b";s:47:"Tests/Unit/Controller/ContactControllerTest.php";s:4:"dd14";s:51:"Tests/Unit/Controller/ParticipantControllerTest.php";s:4:"6087";s:52:"Tests/Unit/Controller/ReservationAccessTraitTest.php";s:4:"b59b";s:51:"Tests/Unit/Controller/ReservationControllerTest.php";s:4:"c232";s:46:"Tests/Unit/Domain/Model/BillingAddressTest.php";s:4:"19e4";s:53:"Tests/Unit/Domain/Model/BookableScheduleTraitTest.php";s:4:"3444";s:43:"Tests/Unit/Domain/Model/ReservationTest.php";s:4:"88e6";s:53:"Tests/Unit/Domain/Model/Dto/ReservationDemandTest.php";s:4:"a46f";s:56:"Tests/Unit/Domain/Validator/ParticipantValidatorTest.php";s:4:"daf0";s:49:"Tests/Unit/Slot/ReservationControllerSlotTest.php";s:4:"634c";}',
);

