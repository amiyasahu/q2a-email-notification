<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}
//define the option prefix 
define('PLUGIN_NAME', 'qa_email_notf_');

//define enable and disable options 
define('ENABLE_PLUGIN', PLUGIN_NAME . 'enable_plugin');
define('ENABLE_PLUGIN_FIELD', PLUGIN_NAME . 'enable_plugin_field');

//define the options 
define('EAMIL_NOTF_DEBUG_MODE_OPT', PLUGIN_NAME . 'debug_mode');
define('ALLOW_CAT_FOLLOWER_EMAILS_OPT', PLUGIN_NAME . 'allow_cat_follower_emails');
define('ALLOW_TAG_FOLLOWER_EMAILS_OPT', PLUGIN_NAME . 'allow_tag_follower_emails');
define('ALLOW_USER_FOLLOWER_EMAILS_OPT', PLUGIN_NAME . 'allow_user_follower_emails');
define('MINIMUM_USER_POINT_OPT', PLUGIN_NAME . 'min_point');
define('MINIMUM_USER_POINT_VAL_OPT', PLUGIN_NAME . 'min_point_val');

//define the admin form fields and buttons 
define('EAMIL_NOTF_DEBUG_MODE_FIELD', PLUGIN_NAME . 'debug_mode_field');
define('ALLOW_CAT_FOLLOWER_EMAILS_FIELD', PLUGIN_NAME . 'allow_cat_follower_emails_field');
define('ALLOW_TAG_FOLLOWER_EMAILS_FIELD', PLUGIN_NAME . 'allow_tag_follower_emails_field');
define('ALLOW_USER_FOLLOWER_EMAILS_FIELD', PLUGIN_NAME . 'allow_user_follower_emails_field');
define('MINIMUM_USER_POINT_FIELD', PLUGIN_NAME . 'min_point_field');
define('MINIMUM_USER_POINT_VAL_FIELD', PLUGIN_NAME . 'min_point_val_field');
define('SAVE_BUTTON', PLUGIN_NAME . 'save_button');
