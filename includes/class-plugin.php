<?php

    namespace WPFPD;

    final class Plugin
    {
        private static $instance;

        private static $settings;
        private static $integrations;

        private function __construct()
        {
            self::$settings = null;
        }

        public static function instance()
        {

            if (
                self::$instance === null ||
                ! self::$instance instanceof self
            ) {

                self::$instance = new self();

                self::$instance->load_dependencies();

                self::$integrations['pipedrive-api'] = new Pipedrive();

                if ( is_admin() ) {
                    self::$settings = new Settings();
                }
            }

            return self::$instance;
        }

        private function load_dependencies()
        {
            require_once WPFPD_PLUGIN_DIR . 'includes/class-pipedrive.php';

            if ( is_admin() ) {
                require_once WPFPD_PLUGIN_DIR . 'includes/class-settings.php';
            }

        }

    }
