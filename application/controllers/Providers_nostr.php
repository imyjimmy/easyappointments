<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Providers_nostr extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('providers_model');
        $this->load->model('users_model');
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
        $validation_url = 'http://mgitreposerver-mgit-repo-server_web_1:3003/api/appointments/validate-login-token';
        
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