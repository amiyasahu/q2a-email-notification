<?php

/*
  Amiya Sahu 

  File: qa-plugin/qa-category-email-notifications/qa-category-email-notifications-event.php
  Version: 0.9
  Date: 2013-02-21
  Description: Event module class for category email notifications plugin
 */
define('ALLOW_CAT_FOLLOWER_EMAILS', true);
define('ALLOW_TAG_FOLLOWER_EMAILS', false);
define('ALLOW_USER_FOLLOWER_EMAILS', true);

class qa_category_email_notifications_event {

    function process_event($event, $userid, $handle, $cookieid, $params) {
        require_once QA_INCLUDE_DIR . 'qa-app-emails.php';
        require_once QA_INCLUDE_DIR . 'qa-app-format.php';
        require_once QA_INCLUDE_DIR . 'qa-util-string.php';
        switch ($event) {
            case 'q_post':
               /* $categoryid = $params['categoryid'];
                $tags = $params['tags'];*/
                $categoryid = qa_get($params , 'categoryid');
                $tags       = qa_get($params , 'tags');
                $emails = array();
                if (ALLOW_CAT_FOLLOWER_EMAILS && !! $categoryid ) {
                    $cat_follower_emails = qa_db_select_with_pending($this->qa_db_category_favorite_emails_selectspec($categoryid));
                    $emails = array_merge($emails , $cat_follower_emails);
                }

                if (ALLOW_TAG_FOLLOWER_EMAILS && !!$tags ) {
                    $tag_follower_emails = qa_db_select_with_pending($this->qa_db_tag_favorite_emails_selectspec($tags));
                    $emails = array_merge($emails , $tag_follower_emails);
                }

                if (ALLOW_USER_FOLLOWER_EMAILS) {
                    $user_follower_emails = qa_db_select_with_pending($this->qa_db_user_follower_emails_selectspec(qa_get_logged_in_userid()));
                    $emails = array_merge($emails , $user_follower_emails);
                }

               $emails = $this->combine_emails($emails);


                for ($i = 0; $i < count($emails); $i++) {
                    $bcclist = array();
                    for ($j = 0; $j < 75 && $i < count($emails); $j++, $i++) {
                        $bcclist[] = $emails[$i]['email'];
                    }

                    $this->category_email_notification_send_notification($bcclist, null, null, qa_lang('emails/q_posted_subject'), qa_lang('emails/q_posted_body'), array(
                        '^q_handle' => isset($handle) ? $handle : qa_lang('main/anonymous'),
                        '^q_title' => $params['title'], // don't censor title or content here since we want the admin to see bad words
                        '^q_content' => $params['text'],
                        '^url' => qa_q_path($params['postid'], $params['title'], true),
                            )
                    );
                }
                break;
        }
    }

    function qa_db_category_favorite_emails_selectspec($categoryid)
    /*
     */ {
        require_once QA_INCLUDE_DIR . 'qa-app-updates.php';

        return array(
            'columns' => array('DISTINCT ^users.email'),
            'source' => "^users JOIN ^userfavorites ON ^userfavorites.userid=^users.userid "
            . "WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$ AND ^users.email !=$",
            'arguments' => array($categoryid, QA_ENTITY_CATEGORY, qa_get_logged_in_user_field('email')),
            'sortasc' => 'title',
        );
    }
    
    function qa_db_tag_favorite_emails_selectspec($tags){
        require_once QA_INCLUDE_DIR . 'qa-app-updates.php';

        return array(
            'columns' => array('DISTINCT ^users.email'),
            'source' => "^users JOIN ^userfavorites ON ^userfavorites.userid=^users.userid WHERE ^userfavorites.entityid IN 
                ( SELECT wordid from ^words where ^words.word IN ($) ) AND ^userfavorites.entitytype=$ AND ^users.email !=$",
            'arguments' => array(qa_tagstring_to_tags($tags), QA_ENTITY_TAG, qa_get_logged_in_user_field('email')),
            'sortasc' => 'title',
        );
    }
    function qa_db_user_follower_emails_selectspec($userid) {
        require_once QA_INCLUDE_DIR.'qa-app-updates.php';
        return array(
            'columns' => array('DISTINCT ^users.email'),
            'source' => "^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^users.userid=^userfavorites.userid WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$",
            'arguments' => array($userid, QA_ENTITY_USER),
            'sortasc' => 'handle',
        );
    }

    function qa_db_user_follower_emails_selectspec1($user_id) {
        require_once QA_INCLUDE_DIR . 'qa-app-updates.php';

        return array(
            'columns' => array('DISTINCT ^users.email'),
            'source' => "^users JOIN ^userfavorites ON ^userfavorites.userid=^users.userid WHERE ^userfavorites.entityid = $ AND ^userfavorites.entitytype=$ ",
            'arguments' => array( $user_id , QA_ENTITY_USER ),
            'sortasc' => 'title',
        );
    }

    function combine_emails( $emails_id_list ) {

        $unique_email_ids   = array();
        $return_email_datas = array();
        
        foreach ($emails_id_list as $email_data) {
            $email = $email_data['email'];
            if (!in_array($email, $unique_email_ids)){
                $return_email_datas[] = $email_data ;
                $unique_email_ids[] = $email ;
            }  
        }
        return $return_email_datas;
    }


    function category_email_notification_send_notification($bcclist, $email, $handle, $subject, $body, $subs)
    /*
     */ {
        if (qa_to_override(__FUNCTION__)) {
            $args = func_get_args();
            return qa_call_override(__FUNCTION__, $args);
        }

        global $qa_notifications_suspended;

        if ($qa_notifications_suspended > 0)
            return false;

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

    function category_email_send_email($params){
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

        if ($params['html'])
            $mailer->IsHTML(true);

        if (qa_opt('smtp_active')) {
            $mailer->IsSMTP();
            $mailer->Host = qa_opt('smtp_address');
            $mailer->Port = qa_opt('smtp_port');

            if (qa_opt('smtp_secure'))
                $mailer->SMTPSecure = qa_opt('smtp_secure');

            if (qa_opt('smtp_authenticate')) {
                $mailer->SMTPAuth = true;
                $mailer->Username = qa_opt('smtp_username');
                $mailer->Password = qa_opt('smtp_password');
            }
        } else {
        }
        return $mailer->Send();
    }
    public function qa_get($param , $name=''){
        return isset($param[$name]) ? $param[$name] : '';
    }

}

/*
	Omit PHP closing tag to help avoid accidental output
*/
