<?php
/*
Plugin Name: Codeable.io - Extends Ninja Forms, WP Job Manager.
Plugin URI: https://github.com/atwellpub/ninja-forms-notify-on-repeat-submission
Description: Custom plugin designed to detect which job an application is related to while sending email notifications for repeat applicants.
Version: 0.0.1
Author: Hudson Atwell
Author URI: http://www.github.com/atwellpub
Text Domain: codeable-amberresources
Domain Path: assets/lang
*/

if (!class_exists('Codeable_AmberResources')) {

    final class Codeable_AmberResources {

        /**
         * Construct Codeable_AmberResources
         */
        public function __construct() {
            self::define_constants();
            self::includes();
            self::load_hooks();
        }

        /*
        * Setup plugin constants
        *
        */
        private static function define_constants() {
            define('CIO_AMBER_CURRENT_VERSION', '1.0.1' );
            define('CIO_AMBER_URLPATH', plugins_url( '/' , __FILE__ ) );
            define('CIO_AMBER_PATH', plugin_dir_path( __FILE__ ) );
            define('CIO_AMBER_SLUG', 'codeable-amberresources' );
            define('CIO_AMBER_FILE', __FILE__ );
        }

        /**
         * Load WordPress Hooks and Filters
         */
        private static function load_hooks() {
            add_filter( 'ninja_forms_render_default_value', array( __CLASS__ , 'autopopulate_hidden_values') , 10, 3 );

			add_filter( 'ninja_forms_register_actions', array(__CLASS__ , 'register_actions'));

        }

        /**
         * Include PHP Files
         */
        private static function includes() {

            switch (is_admin()) :
                case true :
                    /* load admin only files */
                    include_once CIO_AMBER_PATH . 'actions/notify-on-repeat-submissions.php';
                    BREAK;

                case false :
                    /* load front-end files */
                    include_once CIO_AMBER_PATH . 'actions/notify-on-repeat-submissions.php';
                    BREAK;
            endswitch;
        }

        /**
         * Enqueue FrontEnd Scripts
         */
        public static function enqueue_frontend_scripts() {
            global $post;

            /* Only load script on posts and pages or object pages */
            if (!$post || !isset($post->ID) ) {
                return;
            }

            /* ignore archive and author pages */
            if (is_author() || is_archive() || is_search()) {
                return;
            }
        }

        /**
         * Autopopulate hidden ninja form values
         * @param  string $value          [description]
         * @param  object $field_class    [description]
         * @param  array $field_settings [description]
         * @return string                 [description]
         */
        public static function autopopulate_hidden_values( $value , $field_class , $field_settings ) {

            /* if field type is not hidden then skip */
            if ($field_class != 'hidden') {
                return $value;
            }

            /* if field does not contian job_id as a key then return */
            if ( isset($field_settings['key']) && $field_settings['key'] == 'job_id') {

                /* get job id from referring URL */
                $referrer = wp_get_referer();
                $parts = explode('job/' , $referrer);
                $job_id = (isset($parts[1])) ? str_replace('/','',$parts[1]) : 'no job id detected';

                return $job_id;
            }

            /* if field does not contian job_id as a key then return */
            if ( isset($field_settings['key']) && $field_settings['key'] == 'job_date') {

                /* get job id from referring URL */
                $referrer = wp_get_referer();
                $parts = explode('job/' , $referrer);
                $job_id = (isset($parts[1])) ? str_replace('/','',$parts[1]) : 'no job id detected';

                /* get post_date from job id id from referring URL */
                $job = get_post($job_id);
                return (isset($job->post_date)) ? $job->post_date : 'not set';
            }


            return $value;
        }

        /**
         * Registers action to notify administrator on duplicate email address in job submission
         * @param  [type] $actions [description]
         * @return [type]          [description]
         */
		public static function register_actions($actions) {
			$actions[ 'codeable-notify-administrator-on-repeat-submission' ] = new NF_Actions_Codeable_Notify_On_Repeat_Submission();

			return $actions;
		}



    }

    /* launch plugin */
    add_action('plugins_loaded' , 'codeable_load_amberresources' , 1);
	function codeable_load_amberresources() {
        new Codeable_AmberResources;
	}


}
