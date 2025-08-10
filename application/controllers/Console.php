<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.3.2
 * ---------------------------------------------------------------------------- */

use Jsvrcek\ICS\Exception\CalendarEventException;

require_once __DIR__ . '/Google.php';
require_once __DIR__ . '/Caldav.php';

/**
 * Console controller.
 *
 * Handles all the Console related operations.
 */
class Console extends EA_Controller
{
    /**
     * Console constructor.
     */
    public function __construct()
    {
        if (!is_cli()) {
            exit('No direct script access allowed');
        }

        parent::__construct();

        $this->load->dbutil();

        $this->load->library('instance');

        $this->load->model('admins_model');
        $this->load->model('customers_model');
        $this->load->model('providers_model');
        $this->load->model('services_model');
        $this->load->model('settings_model');
    }

    /**
     * Perform a console installation.
     *
     * Use this method to install Easy!Appointments directly from the terminal.
     *
     * Usage:
     *
     * php index.php console install
     *
     * @throws Exception
     */
public function install(): void
{
    // Drop existing tables if they exist (for clean install)
    $drop_statements = [
        'SET FOREIGN_KEY_CHECKS = 0',
        'DROP TABLE IF EXISTS `user_settings`',
        'DROP TABLE IF EXISTS `secretaries_providers`', 
        'DROP TABLE IF EXISTS `services_providers`',
        'DROP TABLE IF EXISTS `appointments`',
        'DROP TABLE IF EXISTS `users`',
        'DROP TABLE IF EXISTS `services`',
        'DROP TABLE IF EXISTS `service_categories`',
        'DROP TABLE IF EXISTS `roles`',
        'DROP TABLE IF EXISTS `settings`',
        'DROP TABLE IF EXISTS `consents`',
        'DROP TABLE IF EXISTS `webhooks`',
        'DROP TABLE IF EXISTS `blocked_periods`',
        'SET FOREIGN_KEY_CHECKS = 1'
    ];
    
    echo "Dropping existing tables...\n";
    foreach ($drop_statements as $statement) {
        $this->db->query($statement);
    }

    // Execute the final database schema directly (no migrations)
    $schema_sql = file_get_contents(FCPATH . 'install_schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema_sql)));
    // DEBUG: Print all statements
    echo "=== DEBUGGING STATEMENTS ARRAY ===\n";
    echo "Total statements: " . count($statements) . "\n\n";
    
    foreach ($statements as $index => $statement) {
        echo "Statement " . ($index + 1) . ":\n";
        echo "Length: " . strlen($statement) . " chars\n";
        echo "First 100 chars: " . substr($statement, 0, 100) . "\n";
        echo "Last 50 chars: " . substr($statement, -50) . "\n";
        echo "Is comment? " . (preg_match('/^--/', $statement) ? 'YES' : 'NO') . "\n";
        echo "Is empty? " . (empty($statement) ? 'YES' : 'NO') . "\n";
        echo "---\n\n";
    }
    
    echo "=== END DEBUG ===\n";

    foreach ($statements as $index => $statement) {
	$statement = trim($statement);
        if (!empty($statement)) {
            echo "Executing statement " . ($index + 1) . ": " . substr($statement, 0, 50) . "...\n";
            try {
                $this->db->query($statement);
                echo "SUCCESS\n";
	    } catch (Exception $e) {
	        if (strpos($e->getMessage(), 'Duplicate foreign key constraint') !== false) {
                    echo "SKIPPED (constraint already exists)\n";
                } else {
                    echo "FAILED: " . $e->getMessage() . "\n";
                    echo "Full statement: " . $statement . "\n";
		    throw $e;
		}
            }
        }
    }

    $password = $this->instance->seed();

    response(
        PHP_EOL . '⇾ Installation completed, login with "administrator" / "' . $password . '".' . PHP_EOL . PHP_EOL,
    );
}
    /**
     * Migrate the database to the latest state.
     *
     * Use this method to upgrade an Easy!Appointments instance to the latest database state.
     *
     * Notice:
     *
     * Do not use this method to install the app as it will not seed the database with the initial entries (admin,
     * provider, service, settings etc.).
     *
     * Usage:
     *
     * php index.php console migrate
     *
     * php index.php console migrate fresh
     *
     * @param string $type
     */
    public function migrate(string $type = ''): void
    {
        $this->instance->migrate($type);
    }

    /**
     * Seed the database with test data.
     *
     * Use this method to add test data to your database
     *
     * Usage:
     *
     * php index.php console seed
     * @throws Exception
     */
    public function seed(): void
    {
        $this->instance->seed();
    }

    /**
     * Create a database backup file.
     *
     * Use this method to back up your Easy!Appointments data.
     *
     * Usage:
     *
     * php index.php console backup
     *
     * php index.php console backup /path/to/backup/folder
     *
     * @throws Exception
     */
    public function backup(): void
    {
        $this->instance->backup($GLOBALS['argv'][3] ?? null);
    }

    /**
     * Trigger the synchronization of all provider calendars with Google Calendar.
     *
     * Use this method in a cronjob to automatically sync events between Easy!Appointments and Google Calendar.
     *
     * Notice:
     *
     * Google syncing must first be enabled for each individual provider from inside the backend calendar page.
     *
     * Usage:
     *
     * php index.php console sync
     *
     * @throws CalendarEventException
     * @throws Exception
     * @throws Throwable
     */
    public function sync(): void
    {
        $providers = $this->providers_model->get();

        foreach ($providers as $provider) {
            if (filter_var($provider['settings']['google_sync'], FILTER_VALIDATE_BOOLEAN)) {
                Google::sync((string) $provider['id']);
            }

            if (filter_var($provider['settings']['caldav_sync'], FILTER_VALIDATE_BOOLEAN)) {
                Caldav::sync((string) $provider['id']);
            }
        }
    }

    /**
     * Show help information about the console capabilities.
     *
     * Use this method to see the available commands.
     *
     * Usage:
     *
     * php index.php console help
     */
    public function help(): void
    {
        $help = [
            '',
            'Easy!Appointments ' . config('version'),
            '',
            'Usage:',
            '',
            '⇾ php index.php console [command] [arguments]',
            '',
            'Commands:',
            '',
            '⇾ php index.php console migrate',
            '⇾ php index.php console migrate fresh',
            '⇾ php index.php console migrate up',
            '⇾ php index.php console migrate down',
            '⇾ php index.php console seed',
            '⇾ php index.php console install',
            '⇾ php index.php console backup',
            '⇾ php index.php console sync',
            '',
            '',
        ];

        response(implode(PHP_EOL, $help));
    }
}
