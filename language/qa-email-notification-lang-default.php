<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/


	File: qa-plugin/example-page/qa-example-lang-default.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: US English language phrases for example plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

	return array(
		'plugin-enable'              => 'Enable this plugin.',
		'user-follower-enable'       => 'Send an email to the users who follow the author of a new question' ,
		'tag-follower-enable'        => 'Send an email to the users who follow one of the tags of a new question' ,
		'cat-follower-enable'        => 'Send an email to the users who follow the category of a new question' ,
		'minimum-point-enable'       => 'Activate a minimum point threashold before recieving email (you must choose at least one option from above list and provide a non-zero positive integer below)',
		'minimum-point-input-lable'  => 'Minimum points before recieving notification emails',
		'debug-mode-enable'          => 'Enable debug mode (requires event logger plugin to be enabled with log file option, to get the search results to the log file. Not recomended if you are not a developer).',
		'save-button'                => 'Save changes',
		'q_posted_body'              => "A new question has been asked by ^q_handle:\n\nThe Question is: ^open^q_title\n\nDescription: ^open^q_content^close\n\nClick below to see the question:\n\n^url\n\nThank you,\n\n^site_title ^open^site_url",
		'choose_atleast_one_opt'     => 'Please choose at least one trigger option to enable this plugin' ,
		'point_value_required'       => 'The points value is required to enable the option' ,
		'point_value_should_numeric' => 'The points value should be a numeric and non-zero positive integer',

	);


/*
	Omit PHP closing tag to help avoid accidental output
*/