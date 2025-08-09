<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Providers_nostr extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('migration');
        $this->load->model('providers_model');
        $this->load->model('users_model');
        $this->migration->latest();
    }
    
    private function get_mgit_server_url() {
        $php_env = $_ENV['PHP_ENV'] ?? $_SERVER['PHP_ENV'] ?? 'production';
        
        if ($php_env === 'development' || $php_env === 'local') {
            log_message('info', 'php detected as development!' . $php_env);
            return 'http://mgit-repo-server_web_1:3003';
        }
        return 'http://mgitreposerver-mgit-repo-server_web_1:3003';
    }

    public function nostr_login() {
        $token = $this->input->get('token');
        
        if (empty($token)) {
            show_error('Invalid login token', 400);
            return;
        }
        
        // Validate token and get provider directly
        $provider = $this->validate_nostr_token($token);
        
        if (!$provider) {
            show_error('Login token expired or invalid', 401);
            return;
        }
        
        // Create EasyAppointments session
        $this->session->set_userdata([
            'user_id' => $provider['id'],
            'user_email' => $provider['email'],
            'role_slug' => 'provider',
            'timezone' => 'UTC',
            'language' => 'english'
        ]);
        
        // Redirect to provider dashboard
        redirect('providers');
    }

    public function direct_login() {
        $jwt_token = $this->input->get('token');
        
        if (empty($jwt_token)) {
            show_error('Invalid login token', 400);
            return;
        }
        
        // Validate JWT with mgit-repo-server
        $provider = $this->validate_nostr_token($jwt_token);
        
        if (!$provider) {
            show_error('Login token expired or invalid', 401);
            return;
        }
        
        // Create session and redirect
        $this->session->set_userdata([
            'user_id' => $provider['id'],
            'user_email' => $provider['email'],
            'role_slug' => 'admin',
            'timezone' => 'UTC',
            'language' => 'english'
        ]);
        
        redirect(base_url('providers'));
    }
    
    
    private function validate_nostr_token($jwt_token) {
        $validation_url = $this->get_mgit_server_url() . '/api/auth/validate';
        $response = null;

        try {
            $client = new GuzzleHttp\Client();
            $guzzle_response = $client->get($validation_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $jwt_token
                ]
            ]);
            
            // Convert Guzzle response to the format your existing code expects
            $response = [
                'status' => $guzzle_response->getStatusCode(),
                'body' => $guzzle_response->getBody()->getContents()
            ];
            
        } catch (GuzzleHttp\Exception\RequestException $e) {
            // Handle HTTP errors - treat as failed request
            log_message('error', 'HTTP request failed: ' . $e->getMessage());
            $response = [
                'status' => 0,  // or whatever indicates failure in your logic
                'body' => null
            ];
        }

        // Now $response is accessible here because it was declared outside the try block
        if ($response['status'] !== 200) {
            return null; // Return null on failure
        }

        $validation_data = json_decode($response['body'], true);
        if ($validation_data['status'] !== 'valid') {
            return null;
        }

        $pubkey = $validation_data['pubkey'];

        log_message('info', 'validation_data["pubkey"]: ' . $pubkey);

        // Now get the provider from EasyAppointments database
        $this->load->model('providers_model');
        $provider = $this->providers_model->get_provider_by_nostr_pubkey(
            $validation_data['pubkey']
        );

        // log_message('info', 'Provider info fetched: ' . $provider);
        return $provider; // Return provider directly (or null if not found)
    }
}