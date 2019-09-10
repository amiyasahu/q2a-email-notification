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
		'plugin-enable'              => 'Activer ce plugin.',
		'user-follower-enable'       => 'Envoyer un mail aux utilisateurs qui ont mis en favoris l\'auteur du nouveau post',
		'tag-follower-enable'        => 'Envoyer un mail aux utilisateurs qui ont mis en favoris un des tags du nouveau post' ,
		'cat-follower-enable'        => 'Envoyer un mail aux utilisateurs qui ont mis en favoris une des catégories du nouveau post' ,
		'minimum-point-enable'       => 'Activer le seuil minimum de points avant de recevoir ces emails (veuillez choisir au minimum une option ci-dessus, et saisir un nombre supérieur à zéro ci-dessous)',
		'minimum-point-input-lable'  => 'Nombre minimum de points pour recevoir les emails',
		'debug-mode-enable'          => 'Activer le mode debug (nécessite le plugin "event logger" avec l\'option "Log events to daily log files", afin de stocker les recherches dans le log. Non recommendé si vous n\'êtes pas un développeur !) . ',
		'save-button'                => 'Enregistrer les changements',
		'q_posted_body'              => "Une nouvelle question a été posée par ^q_handle:\n\nQuestion : ^open^q_title\n\nDescription : ^open^q_content^close\n\nCliquez ci-dessous pour ouvrir la question:\n\n^url\n\nMerci,\n\n^site_title ^open^site_url",
		'choose_atleast_one_opt'     => 'Veuillez choisir au minimum une des options de suivi afin d\'activer ce plugin' ,
		'point_value_required'       => 'Veuillez saisir un nombre minimum de points afin d\'activer cette option !' ,
		'point_value_should_numeric' => 'Le nombre de points minimum doit être un entier positif',

	);


/*
	Omit PHP closing tag to help avoid accidental output
*/