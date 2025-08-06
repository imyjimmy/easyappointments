<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_nostr_integration extends CI_Migration {

    public function up() {
        // Check if column already exists to avoid errors
        $fields = $this->db->field_data('users');
        $has_nostr_field = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'nostr_pubkey') {
                $has_nostr_field = true;
                break;
            }
        }
        
        if (!$has_nostr_field) {
            // Add nostr_pubkey column to users table
            $this->dbforge->add_column('users', [
                'nostr_pubkey' => [
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => TRUE
                ]
            ]);
            
            // Add unique constraint separately (more reliable)
            $this->db->query('ALTER TABLE users ADD UNIQUE KEY unique_nostr_pubkey (nostr_pubkey)');
            
            // Add index for performance
            $this->db->query('CREATE INDEX idx_nostr_pubkey ON users(nostr_pubkey)');
            
            log_message('info', 'Added nostr_pubkey column and index to users table');
        }
    }

    public function down() {
        // Remove the column if rolling back
        if ($this->db->field_exists('nostr_pubkey', 'users')) {
            $this->dbforge->drop_column('users', 'nostr_pubkey');
            log_message('info', 'Removed nostr_pubkey column from users table');
        }
    }
}