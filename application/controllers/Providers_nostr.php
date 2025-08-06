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
        // Check if we're in development environment
        // In development, we can reach localhost, in production we use container names
        if ($this->is_development_environment()) {
            return 'http://localhost:3003';
        }
        return 'http://mgitreposerver-mgit-repo-server_web_1:3003';
    }

    private function is_development_environment() {
        // Simple check: if we can reach localhost:3003, we're in development
        $context = stream_context_create([
            'http' => [
                'timeout' => 1,
                'method' => 'GET'
            ]
        ]);
        
        $result = @file_get_contents('http://localhost:3003/api/health', false, $context);
        return $result !== false;
    }

    public function nostr_login() {
        $token = $this->input->get('token');
        
        if (empty($token)) {
            show_error('Invalid login token', 400);
            return;
        }
        
        // Validate token with mgit-repo-server
        $validation = $this->validate_nostr_token($token);
        
        if (!$validation || !$validation['success']) {
            show_error('Login token expired or invalid', 401);
            return;
        }
        
        $provider = $validation['provider'];
        
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
    
    private function validate_nostr_token($token) {
        $validation_url = $this->get_mgit_server_url() . '/api/appointments/validate-login-token';
        log_message('info', 'heres the url: $validation_url');
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $validation_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['token' => $token]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code !== 200) {
            return false;
        }
        
        return json_decode($response, true);
    }
}