<?php

/*
	Plugin Name: Email Notifications
	Plugin URI: http://amiyasahu.com
	Plugin Description: Sends email for new questions, to users who favoritised the category where it was posted
	Plugin Version: 0.1
	Plugin Date: 2014-04-20
	Plugin Author: Amiya Sahu
	Plugin Author URI: http://amiyasahu.com
	Plugin License: MIT License
	Plugin Minimum Question2Answer Version: 1.6
	Plugin Update Check URI: 
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

	qa_register_plugin_module('event', 'qa-category-email-notifications-event.php', 'qa_category_email_notifications_event', 'Category Email Notifications');
	qa_register_plugin_phrases('language/qa-email-notification-lang-*.php', 'notify');

/*
        Omit PHP closing tag to help avoid accidental output
*/

