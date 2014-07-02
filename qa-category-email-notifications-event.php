<?php

/*
  Amiya Sahu

  File: qa-plugin/qa-category-email-notifications/qa-category-email-notifications-event.php
  Version: 0.9
  Date: 2013-02-21
  Description: Event module class for category email notifications plugin
 */
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
      header('Location: ../../');
      exit;
}
define('EMAIL_NOTF_PLUGIN_DIR', __DIR__);

include_once EMAIL_NOTF_PLUGIN_DIR . '/plugin-constants.php';

class qa_category_email_notifications_event {

      public $log_file_name, $log_file_exists, $log_file;

      function process_event($event, $userid, $handle, $cookieid, $params) {
            if ($this->plugin_enabled_from_admin_panel()) {  //proceed only if the plugin is enabled 
                  require_once QA_INCLUDE_DIR . 'qa-app-emails.php';
                  require_once QA_INCLUDE_DIR . 'qa-app-format.php';
                  require_once QA_INCLUDE_DIR . 'qa-util-string.php';
                  switch ($event) {
                        case 'q_post':

                              $categoryid = $this->qa_get($params, 'categoryid');
                              $tags = $this->qa_get($params, 'tags');
                              $emails = qa_db_select_with_pending($this->qa_db_notificaton_emails_selectspec(qa_get_logged_in_userid(), $tags, $categoryid));
                              $emails = $this->combine_emails($emails);

                              for ($i = 0; $i < count($emails); $i++) {
                                    $bcclist = array();
                                    for ($j = 0; $j < 75 && $i < count($emails); $j++, $i++) {
                                          $bcclist[] = $emails[$i]['email'];
                                    }

                                    $this->category_email_notification_send_notification($bcclist, null, null, qa_lang('emails/q_posted_subject'), qa_lang('notify/q_posted_body'), 
                                        array(
                                        '^q_handle' => isset($handle) ? $handle : qa_lang('main/anonymous'),
                                        '^q_title' => $params['title'], // don't censor title or content here since we want the admin to see bad words
                                        '^q_content' => $params['text'],
                                        '^url' => qa_q_path($params['postid'], $params['title'], true),
                                        '^site_url' => qa_opt("site_url"),
                                            )
                                    );
                              }
                              break;
                  } //switch 
            }//if 
      }

      function qa_db_notificaton_emails_selectspec($userid, $tags, $categoryid) {
            if ($this->plugin_enabled_from_admin_panel()) {  //proceed only if the plugin is enabled
                  require_once QA_INCLUDE_DIR . 'qa-app-updates.php';

                  $source = '';
                  $arguments = array();
                  if (!!qa_opt(ALLOW_USER_FOLLOWER_EMAILS_OPT)) {
                        $source .= (!!$source) ? ' UNION ' : '';
                        $source .= "( SELECT ^users.email , 'U' as favorited , ^userpoints.points from ^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^users.userid=^userfavorites.userid WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$  AND ^users.email !=$ )";
                        $args = array($userid, QA_ENTITY_USER, qa_get_logged_in_user_field('email'));
                        $arguments = array_merge($arguments, $args);
                  }
                  if (!!qa_opt(ALLOW_TAG_FOLLOWER_EMAILS_OPT) && !!$tags) {
                        $source .= (!!$source) ? ' UNION ' : '';
                        $source .= "( SELECT ^users.email , 'T' as favorited , ^userpoints.points from ^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^userfavorites.userid=^users.userid WHERE ^userfavorites.entityid IN 
                            ( SELECT wordid from ^words where ^words.word IN ($) ) AND ^userfavorites.entitytype=$ AND ^users.email !=$ )";
                        $args = array(qa_tagstring_to_tags($tags), QA_ENTITY_TAG, qa_get_logged_in_user_field('email'));
                        $arguments = array_merge($arguments, $args);
                  }
                  if (!!qa_opt(ALLOW_CAT_FOLLOWER_EMAILS_OPT) && !!$categoryid) {
                        $source .= (!!$source) ? ' UNION ' : '';
                        $source .= "( SELECT ^users.email , 'C' as favorited , ^userpoints.points from ^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^userfavorites.userid=^users.userid "
                                . "WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$ AND ^users.email !=$ )";
                        $args = array($categoryid, QA_ENTITY_CATEGORY, qa_get_logged_in_user_field('email'));
                        $arguments = array_merge($arguments, $args);
                  }
                  $where_clause = '';
                  if (!!qa_opt(MINIMUM_USER_POINT_OPT)) {
                        //generate where clause 
                        $min_user_points = qa_opt(MINIMUM_USER_POINT_VAL_OPT);
                        $where_clause = ((!!$min_user_points && ( $min_user_points > 0) )) ? 'where result.points > ' . $min_user_points : '';
                  }
                  return array(
                      'columns' => array(' * '),
                      'source' => ' ( ' . $source . ' ) as result ' . $where_clause,
                      'arguments' => $arguments,
                      'sortasc' => 'title',
                  );
            }  //if plugin is enabled 
      }

//qa_db_notificaton_emails_selectspec

      function combine_emails($emails_id_list) {

            $unique_email_ids = array();
            $return_email_datas = array();

            foreach ($emails_id_list as $email_data) {
                  $email = $email_data['email'];
                  if (!in_array($email, $unique_email_ids)) {
                        $return_email_datas[] = $email_data;
                        $unique_email_ids[] = $email;
                  }
            }
            return $return_email_datas;
      }

      function category_email_notification_send_notification($bcclist, $email, $handle, $subject, $body, $subs){
            if (qa_to_override(__FUNCTION__)) {
                  $args = func_get_args();
                  return qa_call_override(__FUNCTION__, $args);
            }

            global $qa_notifications_suspended;

            if ($qa_notifications_suspended > 0) return false;

            require_once QA_INCLUDE_DIR . 'qa-db-selects.php';
            require_once QA_INCLUDE_DIR . 'qa-util-string.php';

            $subs['^site_title'] = qa_opt('site_title');
            $subs['^handle'] = $handle;
            $subs['^email'] = $email;
            $subs['^open'] = "\n";
            $subs['^close'] = "\n";

            return $this->category_email_send_email(array(
                        'fromemail' => qa_opt('from_email'),
                        'fromname' => qa_opt('site_title'),
                        'toemail' => $email,
                        'toname' => $handle,
                        'bcclist' => $bcclist,
                        'subject' => strtr($subject, $subs),
                        'body' => (empty($handle) ? '' : qa_lang_sub('emails/to_handle_prefix', $handle)) . strtr($body, $subs),
                        'html' => false,
            ));
      }

      function category_email_send_email($params) {
            if (qa_to_override(__FUNCTION__)) {
                  $args = func_get_args();
                  return qa_call_override(__FUNCTION__, $args);
            }

            require_once QA_INCLUDE_DIR . 'qa-class.phpmailer.php';

            $mailer = new PHPMailer();
            $mailer->CharSet = 'utf-8';

            $mailer->From = $params['fromemail'];
            $mailer->Sender = $params['fromemail'];
            $mailer->FromName = $params['fromname'];
            if (isset($params['toemail'])) {
                  $mailer->AddAddress($params['toemail'], $params['toname']);
            }
            $mailer->Subject = $params['subject'];
            $mailer->Body = $params['body'];
            if (isset($params['bcclist'])) {
                  foreach ($params['bcclist'] as $email) {
                        $mailer->AddBCC($email);
                  }
            }

            if ($params['html']) $mailer->IsHTML(true);

            if (qa_opt('smtp_active')) {
                  $mailer->IsSMTP();
                  $mailer->Host = qa_opt('smtp_address');
                  $mailer->Port = qa_opt('smtp_port');

                  if (qa_opt('smtp_secure')) $mailer->SMTPSecure = qa_opt('smtp_secure');

                  if (qa_opt('smtp_authenticate')) {
                        $mailer->SMTPAuth = true;
                        $mailer->Username = qa_opt('smtp_username');
                        $mailer->Password = qa_opt('smtp_password');
                  }
            }
            return $mailer->Send();
      }

      public function qa_get($param, $name = '') {
            return isset($param[$name]) ? $param[$name] : '';
      }


      function admin_form(&$qa_content) {

            //add the functions 
            require_once EMAIL_NOTF_PLUGIN_DIR . '/functions.php';
            //	Process form input

            $saved = false;

            if (qa_clicked(SAVE_BUTTON)) {
                  $enable_plugin = !!qa_post_text(ENABLE_PLUGIN_FIELD);
                  qa_opt(ENABLE_PLUGIN, $enable_plugin);
                  if (!$enable_plugin) {
                        //if the plugin is disabled then turn off all features 
                        reset_all_notification_options();
                  } else {
                        $response = set_all_notification_options();
                        //$error will be false if the 
                        $error = (isset($response) && is_array($response) && !empty($response)) ? true : false;
                  }

                  if (isset($response) && isset($error) && !!$error) {
                        $err_enter_point_value = $this->qa_get($response, 'enter_point_value');
                        $err_no_options_selected = $this->qa_get($response, 'no_options_selected');
                  }

                  $saved = true;
            }


            //	Create the form for display

            qa_set_display_rules($qa_content, array(
                EAMIL_NOTF_DEBUG_MODE_OPT => ENABLE_PLUGIN_FIELD,
                ALLOW_CAT_FOLLOWER_EMAILS_OPT => ENABLE_PLUGIN_FIELD,
                ALLOW_TAG_FOLLOWER_EMAILS_OPT => ENABLE_PLUGIN_FIELD,
                ALLOW_USER_FOLLOWER_EMAILS_OPT => ENABLE_PLUGIN_FIELD,
                MINIMUM_USER_POINT_OPT => ENABLE_PLUGIN_FIELD,
                MINIMUM_USER_POINT_VAL_OPT => ENABLE_PLUGIN_FIELD,
            ));

            return array(
                'ok' => ($saved && !$error ) ? 'Email Notification Settings Saved ' : null,
                'fields' => array(
                    array(
                        'label' => qa_lang('notify/plugin-enable'),
                        'tags' => 'name="' . ENABLE_PLUGIN_FIELD . '" id="' . ENABLE_PLUGIN_FIELD . '"',
                        'value' => qa_opt(ENABLE_PLUGIN),
                        'type' => 'checkbox',
                        'error' => qa_html(@$err_no_options_selected),
                    ),
                    array(
                        'id' => ALLOW_USER_FOLLOWER_EMAILS_OPT,
                        'label' => qa_lang('notify/user-follower-enable'),
                        'tags' => 'name="' . ALLOW_USER_FOLLOWER_EMAILS_FIELD . '" id="' . ALLOW_USER_FOLLOWER_EMAILS_FIELD . '"',
                        'value' => qa_opt(ALLOW_USER_FOLLOWER_EMAILS_OPT),
                        'type' => 'checkbox',
                    ),
                    array(
                        'id' => ALLOW_TAG_FOLLOWER_EMAILS_OPT,
                        'label' => qa_lang('notify/tag-follower-enable'),
                        'tags' => 'name="' . ALLOW_TAG_FOLLOWER_EMAILS_FIELD . '" id="' . ALLOW_TAG_FOLLOWER_EMAILS_FIELD . '"',
                        'value' => qa_opt(ALLOW_TAG_FOLLOWER_EMAILS_OPT),
                        'type' => 'checkbox',
                    ),
                    array(
                        'id' => ALLOW_CAT_FOLLOWER_EMAILS_OPT,
                        'label' => qa_lang('notify/cat-follower-enable'),
                        'tags' => 'name="' . ALLOW_CAT_FOLLOWER_EMAILS_FIELD . '" id="' . ALLOW_CAT_FOLLOWER_EMAILS_FIELD . '"',
                        'value' => qa_opt(ALLOW_CAT_FOLLOWER_EMAILS_OPT),
                        'type' => 'checkbox',
                    ),
                    array(
                        'id' => MINIMUM_USER_POINT_OPT,
                        'label' => qa_lang('notify/minimum-point-enable'),
                        'tags' => 'name="' . MINIMUM_USER_POINT_FIELD . '" id="' . MINIMUM_USER_POINT_FIELD . '"',
                        'value' => qa_opt(MINIMUM_USER_POINT_OPT),
                        'type' => 'checkbox',
                    ),
                    array(
                        'id' => MINIMUM_USER_POINT_VAL_OPT,
                        'label' => qa_lang('notify/minimum-point-input-lable'),
                        'value' => qa_html(qa_opt(MINIMUM_USER_POINT_VAL_OPT)),
                        'tags' => 'name="' . MINIMUM_USER_POINT_VAL_FIELD . '" id="' . MINIMUM_USER_POINT_VAL_FIELD . '" ',
                        'error' => qa_html(@$err_enter_point_value),
                    ),
                    
                ),
                'buttons' => array(
                    array(
                        'label' => qa_lang('notify/save-button'),
                        'tags' => 'name="' . SAVE_BUTTON . '"',
                    ),
                ),
            );
      }

      public function plugin_enabled_from_admin_panel() {
            return ( (!!qa_opt(ENABLE_PLUGIN)) &&
                    (
                    (!!qa_opt(ALLOW_CAT_FOLLOWER_EMAILS_OPT)) ||
                    (!!qa_opt(ALLOW_TAG_FOLLOWER_EMAILS_OPT)) ||
                    (!!qa_opt(ALLOW_USER_FOLLOWER_EMAILS_OPT))
                    )
                    );
      }

}

;


/*
	Omit PHP closing tag to help avoid accidental output
*/
