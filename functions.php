<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}

function ami_reset_all_notification_options() {
      qa_opt('ami_email_notf_allow_cat_follower',  false);
      qa_opt('ami_email_notf_allow_tag_follower',  false);
      qa_opt('ami_email_notf_allow_user_follower', false);
      qa_opt('ami_email_notf_min_point',           false);
      qa_opt('ami_email_notf_min_point_val',       false);
}

function ami_reset_all_notification_points_options() {
      qa_opt('ami_email_notf_min_point',     false);
      qa_opt('ami_email_notf_min_point_val', false);
}

function ami_set_all_notification_options() {

      $error = array();
      //if plugin is enabled then atlest one option has to be enabled 
      if (ami_options_selected()) {
            qa_opt('ami_email_notf_allow_cat_follower',  !!qa_post_text('ami_email_notf_allow_cat_follower'));
            qa_opt('ami_email_notf_allow_tag_follower',  !!qa_post_text('ami_email_notf_allow_tag_follower'));
            qa_opt('ami_email_notf_allow_user_follower', !!qa_post_text('ami_email_notf_allow_user_follower'));
            $minimum_user_point_option = !!qa_post_text('ami_email_notf_min_point');
            if ($minimum_user_point_option) { //if minimum point option is checked 
                  $minimum_user_point_value = qa_post_text('ami_email_notf_min_point_val');
                  if (!!$minimum_user_point_value && is_numeric($minimum_user_point_value) && $minimum_user_point_value > 0) { 
                        //if the minimum point value is provided then only set else reset
                        qa_opt('ami_email_notf_min_point', $minimum_user_point_option);
                        qa_opt('ami_email_notf_min_point_val', (int) $minimum_user_point_value);
                  } else if (!!$minimum_user_point_value && (!is_numeric($minimum_user_point_value) || $minimum_user_point_value <= 0)) {
                        // the minimum_user_point_value is set but the value is not valid
                        ami_reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = qa_lang('notify/point_value_should_numeric');
                  } else {
                        ami_reset_all_notification_points_options();
                        //send a error message to UI 
                        $error['enter_point_value'] = qa_lang('notify/point_value_required'); ;
                  }
            } else {
                  ami_reset_all_notification_points_options();
            }
      } else {
            //if none of the elements are selected disable the plugin and send a error message UI 
            qa_opt('ami_email_notf_enable_plugin', false);
            ami_reset_all_notification_options();
            $error['no_options_selected'] = qa_lang('notify/choose_atleast_one_opt');
      }
      return $error;
}

function ami_options_selected() {
      return ((!!qa_post_text('ami_email_notf_allow_cat_follower')) ||
              (!!qa_post_text('ami_email_notf_allow_tag_follower')) ||
              (!!qa_post_text('ami_email_notf_allow_user_follower')) );
}
