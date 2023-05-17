<?php

    namespace WPFPD;

    class Pipedrive
    {
        private $fields;
        private $form_data;
        private $entry;
        private $entry_id;

        public function __construct()
        {
            $this->actions_and_filters();
        }

        private function actions_and_filters()
        {
            add_action('wpforms_process_complete', [$this, 'send'], 20, 4);

            add_filter('wpfpd_pipedrive_api_parameters', [$this, 'api_required_params'], 10, 3);
        }

        public function send($fields, $entry, $form_data, $entry_id)
        {
            // if integration enabled
            if ( ! $form_data['settings']['pipedrive_send']
                || empty( $form_data['settings']['pipedrive_apikey'] )
                || empty( $form_data['settings']['pipedrive_companydomain'] )
            ) {
                return;
            }

            $this->fields = $fields;
            $this->form_data = $form_data;
            $this->entry = $entry;
            $this->entry_id = $entry_id;

            $person = $this->add_person();
            $lead = $this->add_lead($person);

            $note_text = '<b>Form submission summary</b><br><br>';

            foreach ($this->entry['fields'] as $field_id => $field_val) {
                if ( ! empty($field_val) && ! is_array($field_val) ) {
                    $note_text .= '<b>' . $this->form_data['fields'][ $field_id ]['label'] . '</b><br>';
                    $note_text .= $field_val . '<br><br>';
                }
            }

            $this->add_lead_note($lead->id, $note_text);

            $this->maybe_send_lead_files($lead->id);

        }

        public function api_required_params($params, $endpoint_name, $form_data)
        {
            return array_merge($params, ['api_token' => $this->form_data['settings']['pipedrive_apikey'] ]);
        }

        protected function api_post($endpoint, $parameters, $body = null, $headers = [])
        {
            $url = apply_filters('wpfpd_pipedrive_api_url', 'https://' . $this->form_data['settings']['pipedrive_companydomain'] . '.pipedrive.com/v1');

            if ( ! empty($endpoint) ) {
                $url .= '/' . $endpoint;
            }

            if ( ! empty($parameters) ) {
                $url = add_query_arg($parameters, $url);
            }

            $response = wp_remote_post( $url, [ 'body' => $body, 'headers' => $headers] );

            $response_body    = json_decode(wp_remote_retrieve_body( $response ));

            if ( empty($response_body) ) {
                return [];
            }

            return $response_body;
        }

        private function process_settings_smart_tags($field_name)
        {
            return wpforms_process_smart_tags($this->form_data['settings'][ $field_name ], $this->form_data, $this->fields, $this->entry_id);
        }

        private function add_person()
        {
            $api_params = apply_filters('wpfpd_pipedrive_api_parameters', [], 'add_person', $this->form_data);

            $data = [
                "name" => $this->process_settings_smart_tags('pipedrive_person_name')
            ];

            foreach ($this->form_data['fields'] as $field_id => $field) {
                if ( 'email' == $field['type'] && ! empty($this->entry['fields'][$field_id]) ) {

                    $data['email'][] = [
                        "label"   => "Work",
                        "primary" => "false",
                        "value"   => $this->entry['fields'][$field_id]
                    ];

                } elseif ( 'phone' == $field['type'] && ! empty($this->entry['fields'][$field_id]) ) {

                    $data['phone'][] = [
                        "label"   => "Work",
                        "primary" => "false",
                        "value"   => $this->entry['fields'][$field_id]
                    ];

                } elseif ( 'text' == $field['type'] && str_contains($field['label'], 'Phone') && ! empty($this->entry['fields'][$field_id]) ) {

                    $data['phone'][] = [
                        "label"   => "Work",
                        "primary" => "false",
                        "value"   => $this->entry['fields'][$field_id]
                    ];

                }
            }

            $result = $this->api_post('persons', $api_params, json_encode($data), ['Content-Type' => 'application/json; charset=utf-8']);

            if ( ! empty($result) && $result->success ) {
                return $result->data;
            }

            return [];
        }

        private function add_lead($person_data)
        {
            $api_params = apply_filters('wpfpd_pipedrive_api_parameters', [], 'add_lead', $this->form_data);

            $data = [
                "title"     => $this->process_settings_smart_tags('pipedrive_leadtitle'),
                "person_id" => $person_data->id
            ];

            if ( ! empty($this->form_data['settings']['pipedrive_leadlabel']) ) {
                $data['label_ids'] = [
                    $this->form_data['settings']['pipedrive_leadlabel']
                ];
            }

            $result = $this->api_post('leads', $api_params, json_encode($data), ['Content-Type' => 'application/json; charset=utf-8']);

            if ( ! empty($result) && $result->success ) {
                return $result->data;
            }

            return [];
        }

        private function add_lead_note($lead_id, $note_text)
        {
            $api_params = apply_filters('wpfpd_pipedrive_api_parameters', [], 'add_lead_note', $this->form_data);

            $data = [
                "content"   => $note_text,
                "lead_id"   => $lead_id
            ];

            $result = $this->api_post('notes', $api_params, json_encode($data), ['Content-Type' => 'application/json; charset=utf-8']);

            if ( ! empty($result) && $result->success ) {
                return $result->data;
            }

            return [];
        }

        private function maybe_send_lead_files($lead_id)
        {
            if ( $this->form_data['settings']['pipedrive_send_files'] ) {

                foreach ($this->form_data['fields'] as $field_id => $field) {

                    if ( 'file-upload' == $field['type'] && ! empty($this->fields[$field_id]) ) {
                        $this->add_lead_file($lead_id, $this->fields[$field_id]);
                    }

                }

            }
        }

        private function add_lead_file($lead_id, $file)
        {
            $api_params = apply_filters('wpfpd_pipedrive_api_parameters', [], 'add_lead_file', $this->form_data);

            $headers = array(
                'Content-type: multipart/form-data'
            );

            $url = apply_filters('wpfpd_pipedrive_api_url', 'https://' . $this->form_data['settings']['pipedrive_companydomain'] . '.pipedrive.com/v1');
            $url .= '/files';

            if ( ! empty($api_params) ) {
                $url = add_query_arg($api_params, $url);
            }

            $file_path = str_replace(
                wp_get_upload_dir()['baseurl'],
                wp_get_upload_dir()['basedir'],
                $file['value']
            );

            $file_path = realpath($file_path);

            $file_obj = new \CURLFile($file_path, mime_content_type($file['file_user_name']), $file['file']);

            $post_data = array(
                "lead_id"   => $lead_id,
                "file"      => $file_obj
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            $response = json_decode( $response );

            if ( 201 == $status ) {
                return $response;
            }

            return [];
        }

    }
