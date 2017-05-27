<?php if (!defined('ABSPATH')) exit;

/**
 * Class NF_Action_Save
 */
if (class_exists('NF_Abstracts_Action')) {

    final class NF_Actions_Codeable_Notify_On_Repeat_Submission extends NF_Abstracts_Action {
        /**
         * @var string
         */
        protected $_name = 'codeable-notify-administrator-on-repeat-submission';

        /**
         * @var array
         */
        protected $_tags = array();

        /**
         * @var string
         */
        protected $_timing = 'normal';

        /**
         * @var int
         */
        protected $_priority = '10';

        /**
         * Constructor
         */
        public function __construct() {

            parent::__construct();

            $this->_nicename = __('Codeable: Notify on Repeat Applicant', 'codeable-amberresources');

            $this->_settings['notify-on-repeat-email'] =  array(
                    'name' => 'email_address',
                    'type' => 'textbox',
                    'width' => 'one-half',
                    'label' => __('Notify Email Address', 'codeable-amberresources'),
                    'width' => 'full',
                    'value' => get_option('admin_email'),
                    'group' => 'primary',
                    'use_merge_tags' => false,
                    'help' => __('For multiple email addresses, separate with commas ', 'codeable-amberresources')
            );

            $this->_settings['notify-on-repeat-email-subject'] =  array(
                    'name' => 'subject',
                    'type' => 'textbox',
                    'width' => 'one-half',
                    'label' => __('Subject line', 'codeable-amberresources'),
                    'width' => 'full',
                    'value' => __('Notice of repeat applicant.', 'codeable-amberresources'),
                    'group' => 'primary',
                    'use_merge_tags' => false,
                    'help' => __('For multiple email addresses, separate with commas ', 'codeable-amberresources')
            );

            $this->_settings['notify-on-repeat-email-body'] =  array(
                    'name' => 'email_body',
                    'type' => 'textarea',
                    'width' => 'one-half',
                    'label' => __('Message Body', 'codeable-amberresources'),
                    'width' => 'full',
                    'value' => __('An applicant with a prior application has filled out a form.', 'codeable-amberresources'),
                    'group' => 'primary',
                    'use_merge_tags' => false,
                    'help' => __('Displays above link to view all submissions.', 'codeable-amberresources')
            );



        }

        /*
        * PUBLIC METHODS
        */

        public function save($action_settings) {

        }

        /**
         * Process the custom action
         * @param  [type] $action_settings [description]
         * @param  [type] $form_id         [description]
         * @param  [type] $data            [description]
         * @return [type]                  [description]
         */
        public function process($action_settings, $form_id, $data) {
            global $wpdb;

            //error_log($form_id);
            //error_log(print_r($action_settings,true));
            //error_log(print_r($data,true));

            /* get email input value from form data */
            $email = self::get_email_input($data);


            /* check subscription meta for email address */
            $duplicates = $wpdb->get_results(  "SELECT * FROM ". $wpdb->prefix . "postmeta WHERE meta_key LIKE '%field_%' AND meta_value = '".$email."'"  );

            /* if there are no submissions that share the email address then abandon job */
            if (!$duplicates) {
                return $data;
            }

            /* Send admin notification */
            //error_log(print_r($duplicates,true));
            //error_log(print_r($action_settings,true));
            self::send_admin_notification( $form_id, $duplicates , $action_settings , $email);

            return $data;
        }



        /**
         * Plucks value of first occurance of an email address field type
         * @param  [type] $data [description]
         * @return [type]       [description]
         */
        public static function get_email_input($data) {

            foreach ($data['fields'] as $field) {
                if ($field['type'] == 'email') {
                    return $field['value'];
                }
            }
        }

        /**
         * Send email notification about repeat applicant
         * @param  [type] $form_id         [description]
         * @param  [type] $email_addresses [description]
         * @param  [type] $action_settings [description]
         * @param  [type] $lookup_email    [description]
         * @return [type]                  [description]
         */
        public static function send_admin_notification( $form_id ,  $email_addresses , $action_settings , $lookup_email) {
            $emails = explode(',',$action_settings['email_address']);
            $html = self::get_email_template();

            /* replace tokens in template */
            $lookup_url = admin_url('edit.php?s='.$lookup_email.'&post_status=all&post_type=nf_sub&action=-1&form_id='.$form_id.'&begin_date&end_date&paged=1&action2=-1');
            $html = str_replace('{{email}}' , $lookup_email , $html );
            $html = str_replace('{{submissions_url}}' , $lookup_url , $html );
            $html = str_replace('{{message_body}}' , $action_settings['email_body'] , $html );

            $headers = "From: " . get_bloginfo('name') . " <" .  get_bloginfo('admin_email') . ">\n";
            $headers .= 'Content-type: text/html';

            foreach ($emails as $email) {
                wp_mail( trim($email) , $action_settings['subject'] , $html , $headers);
            }
        }

        /**
         * get email html template
         * @return [type] [description]
         */
        public static function get_email_template() {
            $html = '<!DOCTYPE html>
                        <html>
                          <head>
                            <meta name="viewport" content="width=device-width">
                            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                            <title>Simple Transactional Email</title>
                            <style type="text/css">
                            /* -------------------------------------
                                INLINED WITH https://putsmail.com/inliner
                            ------------------------------------- */
                            /* -------------------------------------
                                RESPONSIVE AND MOBILE FRIENDLY STYLES
                            ------------------------------------- */
                            @media only screen and (max-width: 620px) {
                              table[class=body] h1 {
                                font-size: 28px !important;
                                margin-bottom: 10px !important; }
                              table[class=body] p,
                              table[class=body] ul,
                              table[class=body] ol,
                              table[class=body] td,
                              table[class=body] span,
                              table[class=body] a {
                                font-size: 16px !important; }
                              table[class=body] .wrapper,
                              table[class=body] .article {
                                padding: 10px !important; }
                              table[class=body] .content {
                                padding: 0 !important; }
                              table[class=body] .container {
                                padding: 0 !important;
                                width: 100% !important; }
                              table[class=body] .main {
                                border-left-width: 0 !important;
                                border-radius: 0 !important;
                                border-right-width: 0 !important; }
                              table[class=body] .btn table {
                                width: 100% !important; }
                              table[class=body] .btn a {
                                width: 100% !important; }
                              table[class=body] .img-responsive {
                                height: auto !important;
                                max-width: 100% !important;
                                width: auto !important; }}
                            /* -------------------------------------
                                PRESERVE THESE STYLES IN THE HEAD
                            ------------------------------------- */
                            @media all {
                              .ExternalClass {
                                width: 100%; }
                              .ExternalClass,
                              .ExternalClass p,
                              .ExternalClass span,
                              .ExternalClass font,
                              .ExternalClass td,
                              .ExternalClass div {
                                line-height: 100%; }
                              .apple-link a {
                                color: inherit !important;
                                font-family: inherit !important;
                                font-size: inherit !important;
                                font-weight: inherit !important;
                                line-height: inherit !important;
                                text-decoration: none !important; }
                              .btn-primary table td:hover {
                                background-color: #34495e !important; }
                              .btn-primary a:hover {
                                background-color: #34495e !important;
                                border-color: #34495e !important; } }
                            </style>
                          </head>
                          <body class="" style="background-color:#f6f6f6;font-family:sans-serif;-webkit-font-smoothing:antialiased;font-size:14px;line-height:1.4;margin:0;padding:0;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
                            <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;background-color:#f6f6f6;width:100%;">
                              <tr>
                                <td style="font-family:sans-serif;font-size:14px;vertical-align:top;">&nbsp;</td>
                                <td class="container" style="font-family:sans-serif;font-size:14px;vertical-align:top;display:block;max-width:580px;padding:10px;width:580px;Margin:0 auto !important;">
                                  <div class="content" style="box-sizing:border-box;display:block;Margin:0 auto;max-width:580px;padding:10px;">
                                    <!-- START CENTERED WHITE CONTAINER -->
                                    <span class="preheader" style="color:transparent;display:none;height:0;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;visibility:hidden;width:0;">This is preheader text. Some clients will show this text as a preview.</span>
                                    <table class="main" style="border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;background:#fff;border-radius:3px;width:100%;">
                                      <!-- START MAIN CONTENT AREA -->
                                      <tr>
                                        <td class="wrapper" style="font-family:sans-serif;font-size:14px;vertical-align:top;box-sizing:border-box;padding:20px;">
                                          <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;">
                                            <tr>
                                              <td style="font-family:sans-serif;font-size:14px;vertical-align:top;">
                                                <p style="font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;Margin-bottom:15px;">Hi there,</p>
                                                <p style="font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;Margin-bottom:15px;">
                                                    {{message_body}}
                                                </p>
                                                <table border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;box-sizing:border-box;width:100%;">
                                                  <tbody>
                                                    <tr>
                                                      <td align="left" style="font-family:sans-serif;font-size:14px;vertical-align:top;padding-bottom:15px;">
                                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;width:100%;width:auto;">
                                                          <tbody>
                                                            <tr>
                                                              <td style="font-family:sans-serif;font-size:14px;vertical-align:top;background-color:#ffffff;border-radius:5px;text-align:center;background-color:#3498db;">
                                                                  <a href="{{submissions_url}}" target="_blank" style="text-decoration:underline;background-color:#ffffff;border:solid 1px #3498db;border-radius:5px;box-sizing:border-box;color:#3498db;cursor:pointer;display:inline-block;font-size:14px;font-weight:bold;margin:0;padding:12px 25px;text-decoration:none;text-transform:capitalize;background-color:#3498db;border-color:#3498db;color:#ffffff;">
                                                                  View all submissions for {{email}}
                                                                  </a>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>

                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                      <!-- END MAIN CONTENT AREA -->
                                    </table>
                                    <!-- END CENTERED WHITE CONTAINER -->
                                  </div>
                                </td>
                                <td style="font-family:sans-serif;font-size:14px;vertical-align:top;">&nbsp;</td>
                              </tr>
                            </table>
                          </body>
                        </html>';

            return $html;
        }


        /**
         * Get Ninja Form submissions given a form id
         * @param  [type] $form_id [description]
         * @return [type]          [description]
         */
        public static function get_submissions( $form_id ) {
            $args = array(
                'post_type' => 'nf_sub',
                'posts_per_page' => -1,
                'meta_query'=> array(
    				array(
    					'key' => '_form_id',
    					'value' => $form_id,
    					'compare' => '=',
    				)
                )
			);

            $submissions = get_posts($args);

            return $submissions;
        }
    }
}
