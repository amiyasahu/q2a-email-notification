<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}

function reset_all_notification_options() {
      qa_opt(ALLOW_CAT_FOLLOWER_EMAILS_OPT, false);
      qa_opt(ALLOW_TAG_FOLLOWER_EMAILS_OPT, false);
      qa_opt(ALLOW_USER_FOLLOWER_EMAILS_OPT, false);
      qa_opt(MINIMUM_USER_POINT_OPT, false);
      qa_opt(MINIMUM_USER_POINT_VAL_OPT, false);
}

function reset_all_notification_points_options() {
      qa_opt(MINIMUM_USER_POINT_OPT, false);
      qa_opt(MINIMUM_USER_POINT_VAL_OPT, false);
}

function set_all_notification_options() {

      $error = array();
      //if plugin is enabled then atlest one option has to be enabled 
      if (options_selected()) {
            qa_opt(ALLOW_CAT_FOLLOWER_EMAILS_OPT, !!qa_post_text(ALLOW_CAT_FOLLOWER_EMAILS_FIELD));
            qa_opt(ALLOW_TAG_FOLLOWER_EMAILS_OPT, !!qa_post_text(ALLOW_TAG_FOLLOWER_EMAILS_FIELD));
            qa_opt(ALLOW_USER_FOLLOWER_EMAILS_OPT, !!qa_post_text(ALLOW_USER_FOLLOWER_EMAILS_FIELD));
            $minimum_user_point_option = !!qa_post_text(MINIMUM_USER_POINT_FIELD);
            if ($minimum_user_point_option) { //if minimum point option is checked 
                  $minimum_user_point_value = qa_post_text(MINIMUM_USER_POINT_VAL_FIELD);
                  if (!!$minimum_user_point_value && is_numeric($minimum_user_point_value) && $minimum_user_point_value > 0) { //if the minimum point value is provided then only set else reset
                        qa_opt(MINIMUM_USER_POINT_OPT, $minimum_user_point_option);
                        qa_opt(MINIMUM_USER_POINT_VAL_OPT, (int) $minimum_user_point_value);
                  } else if (!is_numeric($minimum_user_point_value) || $minimum_user_point_value <= 0) {
                        reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = "The points value should be a numeric and non-zero positive integer ";
                  } else {
                        reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = "The points value is required to enable the option ";
                  }
            } else {
                  reset_all_notification_points_options();
            }
      } else {
            //if none of the elements are selected disable the plugin and send a error message UI 
            qa_opt(ENABLE_PLUGIN, false);
            reset_all_notification_options();
            $error['no_options_selected'] = "Please choose atleast follower option to enable this plugin ";
      }
      return $error;
}

function options_selected() {
      return ((!!qa_post_text(ALLOW_CAT_FOLLOWER_EMAILS_FIELD)) ||
              (!!qa_post_text(ALLOW_TAG_FOLLOWER_EMAILS_FIELD)) ||
              (!!qa_post_text(ALLOW_USER_FOLLOWER_EMAILS_FIELD)) );
}
