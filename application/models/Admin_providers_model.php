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
 * Admin providers model.
 *
 * Handles all the database operations of the admin-provider resource (combined admin and provider).
 *
 * @package Models
 */
class Admin_providers_model extends EA_Model
{
    /**
     * @var array
     */
    protected array $casts = [
        'id' => 'integer',
        'id_roles' => 'integer',
        'is_private' => 'boolean',
    ];

    /**
     * @var array
     */
    protected array $api_resource = [
        'id' => 'id',
        'firstName' => 'first_name',
        'lastName' => 'last_name',
        'email' => 'email',
        'mobile' => 'mobile_number',
        'phone' => 'phone_number',
        'address' => 'address',
        'city' => 'city',
        'state' => 'state',
        'zip' => 'zip_code',
        'timezone' => 'timezone',
        'language' => 'language',
        'notes' => 'notes',
        'roleId' => 'id_roles',
        'isPrivate' => 'is_private',
        'ldapDn' => 'ldap_dn',
    ];

    /**
     * Save (insert or update) an admin-provider.
     *
     * @param array $admin_provider Associative array with the admin-provider data.
     *
     * @return int Returns the admin-provider ID.
     *
     * @throws InvalidArgumentException
     */
    public function save(array $admin_provider): int
    {
        $this->validate($admin_provider);

        if (empty($admin_provider['id'])) {
            return $this->insert($admin_provider);
        } else {
            return $this->update($admin_provider);
        }
    }

    /**
     * Validate the admin-provider data.
     *
     * @param array $admin_provider Associative array with the admin-provider data.
     *
     * @throws InvalidArgumentException
     */
    public function validate(array $admin_provider): void
    {
        // If an admin-provider ID is provided then check whether the record really exists in the database.
        if (!empty($admin_provider['id'])) {
            $count = $this->db->get_where('users', ['id' => $admin_provider['id']])->num_rows();

            if (!$count) {
                throw new InvalidArgumentException(
                    'The provided admin-provider ID does not exist in the database: ' . $admin_provider['id'],
                );
            }
        }

        // Make sure all required fields are provided.
        if (empty($admin_provider['first_name']) || empty($admin_provider['last_name']) || empty($admin_provider['email'])) {
            throw new InvalidArgumentException('Not all required fields are provided: ' . print_r($admin_provider, true));
        }

        // Validate the email address.
        if (!filter_var($admin_provider['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address provided: ' . $admin_provider['email']);
        }

        // Validate settings if provided.
        if (!empty($admin_provider['settings'])) {
            // Validate the username.
            if (!empty($admin_provider['settings']['username'])) {
                $admin_provider_id = $admin_provider['id'] ?? null;

                if (!$this->validate_username($admin_provider['settings']['username'], $admin_provider_id)) {
                    throw new InvalidArgumentException(
                        'Username already exists. Please select a different username.',
                    );
                }
            }

            // Validate the password.
            if (!empty($admin_provider['settings']['password'])) {
                if (strlen($admin_provider['settings']['password']) < MIN_PASSWORD_LENGTH) {
                    throw new InvalidArgumentException(
                        'The admin-provider password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long.',
                    );
                }
            }
        }
    }

    /**
     * Validate the admin-provider username.
     *
     * @param string $username Admin-provider username.
     * @param int|null $admin_provider_id Admin-provider ID.
     *
     * @return bool Returns the validation result.
     */
    public function validate_username(string $username, ?int $admin_provider_id = null): bool
    {
        if (!empty($admin_provider_id)) {
            $this->db->where('id_users !=', $admin_provider_id);
        }

        return $this->db
            ->from('users')
            ->join('user_settings', 'user_settings.id_users = users.id', 'inner')
            ->where(['username' => $username])
            ->get()
            ->num_rows() === 0;
    }

    /**
     * Get all admin-providers that match the provided criteria.
     *
     * @param array|string|null $where Where conditions.
     * @param int|null $limit Record limit.
     * @param int|null $offset Record offset.
     * @param string|null $order_by Order by.
     *
     * @return array Returns an array of admin-providers.
     */
    public function get(
        array|string|null $where = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $order_by = null,
    ): array {
        $role_id = $this->get_admin_provider_role_id();

        if ($where !== null) {
            $this->db->where($where);
        }

        if ($order_by !== null) {
            $this->db->order_by($order_by);
        }

        $admin_providers = $this->db->get_where('users', ['id_roles' => $role_id], $limit, $offset)->result_array();

        foreach ($admin_providers as &$admin_provider) {
            $this->cast($admin_provider);
            $admin_provider['settings'] = $this->get_settings($admin_provider['id']);
            $admin_provider['services'] = $this->get_service_ids($admin_provider['id']);
        }

        return $admin_providers;
    }

    /**
     * Get the admin-provider role ID.
     *
     * @return int Returns the role ID.
     */
    public function get_admin_provider_role_id(): int
    {
        $role = $this->db->get_where('roles', ['slug' => DB_SLUG_ADMIN_PROVIDER])->row_array();

        if (empty($role)) {
            throw new RuntimeException('The admin-provider role was not found in the database.');
        }

        return $role['id'];
    }

    /**
     * Insert a new admin-provider into the database.
     *
     * @param array $admin_provider Associative array with the admin-provider data.
     *
     * @return int Returns the admin-provider ID.
     *
     * @throws RuntimeException|Exception
     */
    protected function insert(array $admin_provider): int
    {
        $admin_provider['create_datetime'] = date('Y-m-d H:i:s');
        $admin_provider['update_datetime'] = date('Y-m-d H:i:s');
        $admin_provider['id_roles'] = $this->get_admin_provider_role_id();

        $service_ids = $admin_provider['services'] ?? [];
        $settings = $admin_provider['settings'];

        unset($admin_provider['services'], $admin_provider['settings']);

        if (!$this->db->insert('users', $admin_provider)) {
            throw new RuntimeException('Could not insert admin-provider.');
        }

        $admin_provider['id'] = $this->db->insert_id();
        $settings['salt'] = generate_salt();
        $settings['password'] = hash_password($settings['salt'], $settings['password']);

        $this->set_settings($admin_provider['id'], $settings);
        $this->set_service_ids($admin_provider['id'], $service_ids);

        return $admin_provider['id'];
    }

    /**
     * Update an existing admin-provider.
     *
     * @param array $admin_provider Associative array with the admin-provider data.
     *
     * @return int Returns the admin-provider ID.
     *
     * @throws RuntimeException
     */
    protected function update(array $admin_provider): int
    {
        $admin_provider['update_datetime'] = date('Y-m-d H:i:s');

        $service_ids = $admin_provider['services'] ?? [];
        $settings = $admin_provider['settings'];

        unset($admin_provider['services'], $admin_provider['settings']);

        if (!$this->db->update('users', $admin_provider, ['id' => $admin_provider['id']])) {
            throw new RuntimeException('Could not update admin-provider.');
        }

        $this->set_settings($admin_provider['id'], $settings);
        $this->set_service_ids($admin_provider['id'], $service_ids);

        return $admin_provider['id'];
    }

    /**
     * Remove an existing admin-provider from the database.
     *
     * @param int $admin_provider_id Admin-provider ID.
     *
     * @throws RuntimeException
     */
    public function delete(int $admin_provider_id): void
    {
        $this->db->delete('users', ['id' => $admin_provider_id]);
    }

    /**
     * Get a specific admin-provider from the database.
     *
     * @param int $admin_provider_id The ID of the record to be returned.
     *
     * @return array Returns an array with the admin-provider data.
     *
     * @throws InvalidArgumentException
     */
    public function find(int $admin_provider_id): array
    {
        $admin_provider = $this->db->get_where('users', ['id' => $admin_provider_id])->row_array();

        if (!$admin_provider) {
            throw new InvalidArgumentException(
                'The provided admin-provider ID was not found in the database: ' . $admin_provider_id,
            );
        }

        $this->cast($admin_provider);
        $admin_provider['settings'] = $this->get_settings($admin_provider['id']);
        $admin_provider['services'] = $this->get_service_ids($admin_provider['id']);

        return $admin_provider;
    }

    /**
     * Get a specific field value from the database.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param string $field Name of the value to be returned.
     *
     * @return mixed Returns the selected admin-provider value from the database.
     *
     * @throws InvalidArgumentException
     */
    public function value(int $admin_provider_id, string $field): mixed
    {
        if (empty($field)) {
            throw new InvalidArgumentException('The field argument cannot be empty.');
        }

        if (empty($admin_provider_id)) {
            throw new InvalidArgumentException('The admin-provider ID argument cannot be empty.');
        }

        // Check whether the admin-provider exists.
        $query = $this->db->get_where('users', ['id' => $admin_provider_id]);

        if (!$query->num_rows()) {
            throw new InvalidArgumentException('The provided admin-provider ID was not found in the database: ' . $admin_provider_id);
        }

        // Check if the required field is part of the admin-provider data.
        $admin_provider = $query->row_array();

        $this->cast($admin_provider);

        if (!array_key_exists($field, $admin_provider)) {
            throw new InvalidArgumentException('The requested field was not found in the admin-provider data: ' . $field);
        }

        return $admin_provider[$field];
    }

    /**
     * Get the admin-provider settings.
     *
     * @param int $admin_provider_id Admin-provider ID.
     *
     * @return array Returns an array with the settings.
     */
    public function get_settings(int $admin_provider_id): array
    {
        $settings = $this->db->get_where('user_settings', ['id_users' => $admin_provider_id])->row_array();

        if (empty($settings)) {
            throw new RuntimeException('The provided admin-provider ID was not found in the database: ' . $admin_provider_id);
        }

        // Remove the id_users field as it's not needed.
        unset($settings['id_users']);

        return $settings;
    }

    /**
     * Save the admin-provider settings.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param array $settings Associative array with the settings data.
     *
     * @throws InvalidArgumentException
     */
    public function set_settings(int $admin_provider_id, array $settings): void
    {
        if (empty($settings)) {
            throw new InvalidArgumentException('The settings argument cannot be empty.');
        }

        // Make sure the settings record exists in the database.
        $count = $this->db->get_where('user_settings', ['id_users' => $admin_provider_id])->num_rows();

        if (!$count) {
            $this->db->insert('user_settings', ['id_users' => $admin_provider_id]);
        }

        foreach ($settings as $name => $value) {
            // Sort working plans exceptions in descending order that they are easier to modify later on.
            if ($name === 'working_plan_exceptions') {
                $value = json_decode($value, true);

                if (!$value) {
                    $value = [];
                }

                krsort($value);

                $value = json_encode(empty($value) ? new stdClass() : $value);
            }

            $this->set_setting($admin_provider_id, $name, $value);
        }
    }

    /**
     * Save a single admin-provider setting in the database.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param string $name Setting name.
     * @param string $value Setting value.
     */
    public function set_setting(int $admin_provider_id, string $name, string $value): void
    {
        $this->db->where(['id_users' => $admin_provider_id]);
        $this->db->update('user_settings', [$name => $value]);
    }

    /**
     * Get a single setting value from the database.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param string $name Setting name.
     *
     * @return string Returns the value of the selected setting.
     */
    public function get_setting(int $admin_provider_id, string $name): string
    {
        $settings = $this->db->get_where('user_settings', ['id_users' => $admin_provider_id])->row_array();

        if (empty($settings[$name])) {
            throw new RuntimeException(
                'The requested setting (' . $name . ') was not found for admin-provider ID: ' . $admin_provider_id,
            );
        }

        return $settings[$name];
    }

    /**
     * Get the IDs of the services assigned to the admin-provider.
     *
     * @param int $admin_provider_id Admin-provider ID.
     *
     * @return array Returns an array of service IDs.
     */
    public function get_service_ids(int $admin_provider_id): array
    {
        return $this->db
            ->select('id_services')
            ->from('services_providers')
            ->where('id_users', $admin_provider_id)
            ->get()
            ->result_array();
    }

    /**
     * Save the services of an admin-provider.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param array $service_ids Service IDs.
     */
    public function set_service_ids(int $admin_provider_id, array $service_ids): void
    {
        // Delete existing service provider records.
        $this->db->delete('services_providers', ['id_users' => $admin_provider_id]);

        // Save the new services.
        foreach ($service_ids as $service_id) {
            $this->db->insert('services_providers', [
                'id_users' => $admin_provider_id,
                'id_services' => $service_id,
            ]);
        }
    }

    /**
     * Save a working plan exception for an admin-provider.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param string $date The working plan exception date (in YYYY-MM-DD format).
     * @param array $working_plan_exception Working plan exception data.
     *
     * @throws Exception If $admin_provider_id argument is invalid.
     */
    public function save_working_plan_exception(int $admin_provider_id, string $date, array $working_plan_exception): void
    {
        // Validate the admin-provider ID.
        $count = $this->db->get_where('users', ['id' => $admin_provider_id])->num_rows();

        if (!$count) {
            throw new InvalidArgumentException('The provided admin-provider ID was not found in the database.');
        }

        // Make sure the admin-provider record exists.
        $where = [
            'id' => $admin_provider_id,
            'id_roles' => $this->db->get_where('roles', ['slug' => DB_SLUG_ADMIN_PROVIDER])->row()->id,
        ];

        if ($this->db->get_where('users', $where)->num_rows() === 0) {
            throw new InvalidArgumentException('Admin-provider ID was not found in the database: ' . $admin_provider_id);
        }

        $admin_provider = $this->find($admin_provider_id);

        // Store the working plan exception.
        $working_plan_exceptions = json_decode($admin_provider['settings']['working_plan_exceptions'], true);

        if (is_array($working_plan_exception) && !isset($working_plan_exception['breaks'])) {
            $working_plan_exception['breaks'] = [];
        }

        $working_plan_exceptions[$date] = $working_plan_exception;

        $admin_provider['settings']['working_plan_exceptions'] = json_encode($working_plan_exceptions);

        $this->update($admin_provider);
    }

    /**
     * Delete a working plan exception for an admin-provider.
     *
     * @param int $admin_provider_id Admin-provider ID.
     * @param string $date The working plan exception date (in YYYY-MM-DD format).
     *
     * @throws Exception If $admin_provider_id argument is invalid.
     */
    public function delete_working_plan_exception(int $admin_provider_id, string $date): void
    {
        $admin_provider = $this->find($admin_provider_id);

        $working_plan_exceptions = json_decode($admin_provider['settings']['working_plan_exceptions'], true);

        if (!array_key_exists($date, $working_plan_exceptions)) {
            return; // The selected date does not exist in admin-provider's settings.
        }

        unset($working_plan_exceptions[$date]);

        $admin_provider['settings']['working_plan_exceptions'] = empty($working_plan_exceptions)
            ? '{}'
            : json_encode($working_plan_exceptions);

        $this->update($admin_provider);
    }

    /**
     * Get all the admin-provider records that are assigned to at least one service.
     *
     * @param bool $without_private Only include the public admin-providers.
     *
     * @return array Returns an array of admin-providers.
     */
    public function get_available_admin_providers(bool $without_private = false): array
    {
        if ($without_private) {
            $this->db->where('users.is_private', false);
        }

        $admin_providers = $this->db
            ->select('users.*')
            ->from('users')
            ->join('roles', 'roles.id = users.id_roles', 'inner')
            ->join('services_providers', 'services_providers.id_users = users.id', 'inner')
            ->where('roles.slug', DB_SLUG_ADMIN_PROVIDER)
            ->order_by('first_name ASC, last_name ASC, email ASC')
            ->group_by('users.id')
            ->get()
            ->result_array();

        foreach ($admin_providers as &$admin_provider) {
            $this->cast($admin_provider);
            $admin_provider['settings'] = $this->get_settings($admin_provider['id']);
            $admin_provider['services'] = $this->get_service_ids($admin_provider['id']);
        }

        return $admin_providers;
    }

    /**
     * Get the query builder interface, configured for use with the users (admin-provider-filtered) table.
     *
     * @return CI_DB_query_builder
     */
    public function query(): CI_DB_query_builder
    {
        $role_id = $this->get_admin_provider_role_id();

        return $this->db->from('users')->where('id_roles', $role_id);
    }

    /**
     * Search admin-providers by the provided keyword.
     *
     * @param string $keyword Search keyword.
     * @param int|null $limit Record limit.
     * @param int|null $offset Record offset.
     * @param string|null $order_by Order by.
     *
     * @return array Returns an array of admin-providers.
     */
    public function search(string $keyword, ?int $limit = null, ?int $offset = null, ?string $order_by = null): array
    {
        $role_id = $this->get_admin_provider_role_id();

        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db
                ->or_like('first_name', $keyword)
                ->or_like('last_name', $keyword)
                ->or_like('CONCAT_WS(" ", first_name, last_name)', $keyword)
                ->or_like('email', $keyword)
                ->or_like('mobile_number', $keyword)
                ->or_like('phone_number', $keyword)
                ->or_like('address', $keyword)
                ->or_like('city', $keyword)
                ->or_like('state', $keyword)
                ->or_like('zip_code', $keyword)
                ->or_like('notes', $keyword);
            $this->db->group_end();
        }

        if (!empty($order_by)) {
            $this->db->order_by($order_by);
        }

        $admin_providers = $this->db->get_where('users', ['id_roles' => $role_id], $limit, $offset)->result_array();

        foreach ($admin_providers as &$admin_provider) {
            $this->cast($admin_provider);
            $admin_provider['settings'] = $this->get_settings($admin_provider['id']);
            $admin_provider['services'] = $this->get_service_ids($admin_provider['id']);
        }

        return $admin_providers;
    }

    /**
     * Load related resources to an admin-provider.
     *
     * @param array $admin_provider Associative array with the admin-provider data.
     * @param array $resources Resource names to be attached.
     *
     * @throws InvalidArgumentException
     */
    public function load(array &$admin_provider, array $resources): void
    {
        // Admin-providers do not currently have any related resources to load.
        // This method is implemented for consistency with other models and future extensibility.
    }

    /**
     * Convert the database admin-provider record to the equivalent API resource.
     *
     * @param array $admin_provider Admin-provider data.
     */
    public function api_encode(array &$admin_provider): void
    {
        $encoded_resource = [
            'id' => array_key_exists('id', $admin_provider) ? (int) $admin_provider['id'] : null,
            'firstName' => $admin_provider['first_name'],
            'lastName' => $admin_provider['last_name'],
            'email' => $admin_provider['email'],
            'mobile' => $admin_provider['mobile_number'],
            'phone' => $admin_provider['phone_number'],
            'address' => $admin_provider['address'],
            'city' => $admin_provider['city'],
            'state' => $admin_provider['state'],
            'zip' => $admin_provider['zip_code'],
            'timezone' => $admin_provider['timezone'],
            'language' => $admin_provider['language'],
            'notes' => $admin_provider['notes'],
            'roleId' => array_key_exists('id_roles', $admin_provider) ? (int) $admin_provider['id_roles'] : null,
            'isPrivate' => array_key_exists('is_private', $admin_provider) ? (bool) $admin_provider['is_private'] : null,
            'ldapDn' => $admin_provider['ldap_dn'],
        ];

        if (array_key_exists('services', $admin_provider)) {
            $encoded_resource['services'] = $admin_provider['services'];
        }

        if (array_key_exists('settings', $admin_provider)) {
            $encoded_resource['settings'] = [
                'username' => $admin_provider['settings']['username'],
                'notifications' => filter_var($admin_provider['settings']['notifications'], FILTER_VALIDATE_BOOLEAN),
                'calendarView' => $admin_provider['settings']['calendar_view'],
            ];

            if (!empty($admin_provider['settings']['working_plan'])) {
                $encoded_resource['settings']['workingPlan'] = json_decode($admin_provider['settings']['working_plan'], true);
            }

            if (!empty($admin_provider['settings']['working_plan_exceptions'])) {
                $encoded_resource['settings']['workingPlanExceptions'] = json_decode($admin_provider['settings']['working_plan_exceptions'], true);
            }
        }

        $admin_provider = $encoded_resource;
    }

    /**
     * Convert the API resource to the equivalent database admin-provider record.
     *
     * @param array $admin_provider API resource.
     * @param array|null $base Base admin-provider data to be overwritten with the provided values (useful for updates).
     */
    public function api_decode(array &$admin_provider, ?array $base = null): void
    {
        $decoded_resource = $base ?: [];

        if (array_key_exists('id', $admin_provider)) {
            $decoded_resource['id'] = $admin_provider['id'];
        }

        if (array_key_exists('firstName', $admin_provider)) {
            $decoded_resource['first_name'] = $admin_provider['firstName'];
        }

        if (array_key_exists('lastName', $admin_provider)) {
            $decoded_resource['last_name'] = $admin_provider['lastName'];
        }

        if (array_key_exists('email', $admin_provider)) {
            $decoded_resource['email'] = $admin_provider['email'];
        }

        if (array_key_exists('mobile', $admin_provider)) {
            $decoded_resource['mobile_number'] = $admin_provider['mobile'];
        }

        if (array_key_exists('phone', $admin_provider)) {
            $decoded_resource['phone_number'] = $admin_provider['phone'];
        }

        if (array_key_exists('address', $admin_provider)) {
            $decoded_resource['address'] = $admin_provider['address'];
        }

        if (array_key_exists('city', $admin_provider)) {
            $decoded_resource['city'] = $admin_provider['city'];
        }

        if (array_key_exists('state', $admin_provider)) {
            $decoded_resource['state'] = $admin_provider['state'];
        }

        if (array_key_exists('zip', $admin_provider)) {
            $decoded_resource['zip_code'] = $admin_provider['zip'];
        }

        if (array_key_exists('timezone', $admin_provider)) {
            $decoded_resource['timezone'] = $admin_provider['timezone'];
        }

        if (array_key_exists('language', $admin_provider)) {
            $decoded_resource['language'] = $admin_provider['language'];
        }

        if (array_key_exists('notes', $admin_provider)) {
            $decoded_resource['notes'] = $admin_provider['notes'];
        }

        if (array_key_exists('roleId', $admin_provider)) {
            $decoded_resource['id_roles'] = $admin_provider['roleId'];
        }

        if (array_key_exists('isPrivate', $admin_provider)) {
            $decoded_resource['is_private'] = $admin_provider['isPrivate'];
        }

        if (array_key_exists('ldapDn', $admin_provider)) {
            $decoded_resource['ldap_dn'] = $admin_provider['ldapDn'];
        }

        if (array_key_exists('services', $admin_provider)) {
            $decoded_resource['services'] = $admin_provider['services'];
        }

        if (array_key_exists('settings', $admin_provider)) {
            $decoded_resource['settings'] = [];

            if (array_key_exists('username', $admin_provider['settings'])) {
                $decoded_resource['settings']['username'] = $admin_provider['settings']['username'];
            }

            if (array_key_exists('password', $admin_provider['settings'])) {
                $decoded_resource['settings']['password'] = $admin_provider['settings']['password'];
            }

            if (array_key_exists('notifications', $admin_provider['settings'])) {
                $decoded_resource['settings']['notifications'] = $admin_provider['settings']['notifications'];
            }

            if (array_key_exists('calendarView', $admin_provider['settings'])) {
                $decoded_resource['settings']['calendar_view'] = $admin_provider['settings']['calendarView'];
            }

            if (array_key_exists('workingPlan', $admin_provider['settings'])) {
                $decoded_resource['settings']['working_plan'] = json_encode($admin_provider['settings']['workingPlan']);
            }

            if (array_key_exists('workingPlanExceptions', $admin_provider['settings'])) {
                $decoded_resource['settings']['working_plan_exceptions'] = json_encode($admin_provider['settings']['workingPlanExceptions']);
            }
        }

        $admin_provider = $decoded_resource;
    }
}