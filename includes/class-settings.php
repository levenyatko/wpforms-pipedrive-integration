<?php

    namespace WPFPD;

    class Settings
    {
        public function __construct()
        {
            $this->actions_and_filters();
        }

        private function actions_and_filters()
        {
            add_filter('wpforms_builder_settings_sections', [$this, 'settings_sections'], 20, 2 );
            add_action('wpforms_form_settings_panel_content', [$this, 'show_sections'], 20);

            add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 10);
        }

        public function settings_sections($sections, $form_data)
        {
            $sections[ 'pipedrive' ] = esc_html__( 'Pipedrive' );
            return $sections;
        }

        public function show_sections($instance)
        {
            echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-pipedrive" data-panel="pipedrive">';
            echo '<div class="wpforms-panel-content-section-title">' . __( 'Pipedrive Integration' ) . '</div>';

                wpforms_panel_field(
                    'toggle',
                    'settings',
                    'pipedrive_send',
                    $instance->form_data,
                    esc_html__( 'Send data to Pipedrive' ),
                    [
                        'value' => empty( $form_settings['pipedrive_send'] ) ? 0 : 1,
                    ]
                );

                wpforms_panel_field(
                    'text',
                    'settings',
                    'pipedrive_companydomain',
                    $instance->form_data,
                    esc_html__( 'Company domain (in Pipedrive)' ),
                    [
                        'default' => '',
                    ]
                );

                wpforms_panel_field(
                    'text',
                    'settings',
                    'pipedrive_apikey',
                    $instance->form_data,
                    esc_html__( 'Api Key' ),
                    [
                        'default' => '',
                    ]
                );

                wpforms_panel_field(
                    'text',
                    'settings',
                    'pipedrive_person_name',
                    $instance->form_data,
                    esc_html__( 'Person name' ),
                    [
                        'default' => '',
                        'tooltip'    => __( 'Enter the person name format. You can use multiple fields value' ),
                        'smarttags'  => array(
                            'type'   => 'fields'
                        ),
                    ]
                );

                wpforms_panel_field(
                    'text',
                    'settings',
                    'pipedrive_leadtitle',
                    $instance->form_data,
                    esc_html__( 'Lead name' ),
                    [
                        'default' => '',
                        'tooltip'    => __( 'Enter the lead name format. You can use multiple fields value' ),
                        'smarttags'  => array(
                            'type'   => 'fields'
                        ),
                    ]
                );

                wpforms_panel_field(
                    'text',
                    'settings',
                    'pipedrive_leadlabel',
                    $instance->form_data,
                    esc_html__( 'Lead label ID' ),
                    [
                        'default' => '',
                    ]
                );

                wpforms_panel_field(
                    'toggle',
                    'settings',
                    'pipedrive_send_files',
                    $instance->form_data,
                    esc_html__( 'Send files to Pipedrive' ),
                    [
                        'value' => empty( $form_settings['pipedrive_send_files'] ) ? 0 : 1,
                    ]
                );

            echo '</div>';
            echo '<style>.lead-label-card{padding: 5px 10px;display: inline-block;background: #d5d5d5;border-radius: 6px;margin: 5px;cursor: pointer;}</style>';
        }

        public function admin_scripts()
        {
            if ( empty($_GET['page']) || 'wpforms-builder' != $_GET['page'] || empty($_GET['view']) || 'settings' != $_GET['view'] ) {
                return;
            }

            wp_enqueue_script( 'wpforms-pipedrive-admin', WPFPD_PLUGIN_DIR_URL . 'assets/js/admin.js', ['jquery'], '1.0' );
        }

    }