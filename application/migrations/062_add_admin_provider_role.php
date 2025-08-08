<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.5.1
 * ---------------------------------------------------------------------------- */

/**
 * Add admin-provider role for solo practices.
 */
class Migration_Add_admin_provider_role extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        // Insert the new admin-provider role with combined permissions
        $this->db->insert('roles', [
            'name' => 'Admin Provider',
            'slug' => 'admin-provider',
            'is_admin' => true,  // Has admin privileges
            'appointments' => 15,  // Full appointment permissions
            'customers' => 15,     // Full customer permissions  
            'services' => 15,      // Full service permissions (from admin)
            'users' => 15,         // Full user permissions (from admin)
            'system_settings' => 15, // Full system settings (from admin)
            'user_settings' => 15,   // Full user settings
        ]);
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        $this->db->delete('roles', ['slug' => 'admin-provider']);
    }
}