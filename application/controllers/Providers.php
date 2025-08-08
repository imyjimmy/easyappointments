<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Providers controller.
 *
 * Handles the providers related operations.
 *
 * @package Controllers
 */
class Providers extends EA_Controller
{
    public array $allowed_provider_fields = [
        'id',
        'first_name',
        'last_name',
        'email',
        'alt_number',
        'phone_number',
        'address',
        'city',
        'state',
        'zip_code',
        'notes',
        'timezone',
        'language',
        'is_private',
        'ldap_dn',
        'id_roles',
        'settings',
        'services',
    ];

    public array $optional_provider_fields = [
        'services' => [],
    ];

    public array $allowed_provider_setting_fields = [
        'username',
        'password',
        'working_plan',
        'working_plan_exceptions',
        'notifications',
        'calendar_view',
    ];

    public array $optional_provider_setting_fields = [
        'working_plan' => null,
        'working_plan_exceptions' => '{}',
    ];

    public array $allowed_service_fields = ['id', 'name'];

    /**
     * Providers constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('providers_model');
        $this->load->model('admin_providers_model');
        $this->load->model('services_model');
        $this->load->model('roles_model');

        $this->load->library('accounts');
        $this->load->library('timezones');
        $this->load->library('webhooks_client');

        $this->optional_provider_setting_fields['working_plan'] = setting('company_working_plan');
    }

    /**
     * Get all providers including admin-providers.
     *
     * @param array|string|null $where Where conditions.
     * @param int|null $limit Record limit.
     * @param int|null $offset Record offset.
     * @param string|null $order_by Order by.
     *
     * @return array Returns merged array of providers and admin-providers.
     */
    private function get_all_providers(
        array|string|null $where = null,
        ?int $limit = null,
        ?int $offset = null,
        ?string $order_by = null
    ): array {
        // Get regular providers
        $providers = $this->providers_model->get($where, $limit, $offset, $order_by);
        
        // Get admin-providers
        $admin_providers = $this->admin_providers_model->get($where, $limit, $offset, $order_by);
        
        // Merge and sort by name
        $all_providers = array_merge($providers, $admin_providers);
        
        // Sort by first name, then last name
        usort($all_providers, function($a, $b) {
            $name_a = $a['first_name'] . ' ' . $a['last_name'];
            $name_b = $b['first_name'] . ' ' . $b['last_name'];
            return strcasecmp($name_a, $name_b);
        });
        
        return $all_providers;
    }

    /**
     * Search all providers including admin-providers.
     *
     * @param string $keyword Search keyword.
     * @param int|null $limit Record limit.
     * @param int|null $offset Record offset.
     * @param string|null $order_by Order by.
     *
     * @return array Returns merged search results.
     */
    private function search_all_providers(
        string $keyword,
        ?int $limit = null,
        ?int $offset = null,
        ?string $order_by = null
    ): array {
        // Search regular providers
        $providers = $this->providers_model->search($keyword, $limit, $offset, $order_by);
        
        // Search admin-providers  
        $admin_providers = $this->admin_providers_model->search($keyword, $limit, $offset, $order_by);
        
        // Merge and sort
        $all_providers = array_merge($providers, $admin_providers);
        
        usort($all_providers, function($a, $b) {
            $name_a = $a['first_name'] . ' ' . $a['last_name'];
            $name_b = $b['first_name'] . ' ' . $b['last_name'];
            return strcasecmp($name_a, $name_b);
        });
        
        return $all_providers;
    }

    /**
     * Render the backend providers page.
     *
     * On this page admin users will be able to manage providers, which are eventually selected by customers during the
     * booking process.
     */
    public function index(): void
    {
        session(['dest_url' => site_url('providers')]);

        $user_id = session('user_id');
        log_message('info', 'Providers: session_user_id ' . $user_id);

        if (cannot('view', PRIV_USERS)) {
            if ($user_id) {
                log_message('info', 'user is forbidden!!! ' . $user_id);
                abort(403, 'Forbidden');
            }

            redirect('login');

            return;
        }

        $role_slug = session('role_slug');

        $services = $this->services_model->get();

        foreach ($services as &$service) {
            $this->services_model->only($service, $this->allowed_service_fields);
        }

        script_vars([
            'user_id' => $user_id,
            'role_slug' => $role_slug,
            'company_working_plan' => setting('company_working_plan'),
            'date_format' => setting('date_format'),
            'time_format' => setting('time_format'),
            'first_weekday' => setting('first_weekday'),
            'min_password_length' => MIN_PASSWORD_LENGTH,
            'timezones' => $this->timezones->to_array(),
            'services' => $services,
            'default_language' => setting('default_language'),
            'default_timezone' => setting('default_timezone'),
        ]);

        html_vars([
            'page_title' => lang('providers'),
            'active_menu' => PRIV_USERS,
            'user_display_name' => $this->accounts->get_user_display_name($user_id),
            'grouped_timezones' => $this->timezones->to_grouped_array(),
            'privileges' => $this->roles_model->get_permissions_by_slug($role_slug),
            'services' => $this->services_model->get(),
        ]);

        $this->load->view('pages/providers');
    }

    /**
     * Filter providers by the provided keyword.
     */
    public function search(): void
    {
        try {
            if (cannot('view', PRIV_USERS)) {
                abort(403, 'Forbidden');
            }

            $keyword = request('keyword', '');

            $order_by = request('order_by', 'update_datetime DESC');

            $limit = request('limit', 1000);

            $offset = (int) request('offset', '0');

            $providers = $this->search_all_providers($keyword, $limit, $offset, $order_by);

            json_response($providers);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Store a new provider.
     */
    public function store(): void
    {
        try {
            if (cannot('add', PRIV_USERS)) {
                abort(403, 'Forbidden');
            }

            $provider = request('provider');

            $this->providers_model->only($provider, $this->allowed_provider_fields);

            $this->providers_model->only($provider['settings'], $this->allowed_provider_setting_fields);

            $this->providers_model->optional($provider, $this->optional_provider_fields);

            $this->providers_model->optional($provider['settings'], $this->optional_provider_setting_fields);

            $provider_id = $this->providers_model->save($provider);

            $provider = $this->providers_model->find($provider_id);

            $this->webhooks_client->trigger(WEBHOOK_PROVIDER_SAVE, $provider);

            json_response([
                'success' => true,
                'id' => $provider_id,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Find a provider.
     */
    public function find(): void
    {
        try {
            if (cannot('view', PRIV_USERS)) {
                abort(403, 'Forbidden');
            }

            $provider_id = request('provider_id');

            $provider = $this->providers_model->find($provider_id);

            json_response($provider);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Update a provider.
     */
    public function update(): void
    {
        try {
            if (cannot('edit', PRIV_USERS)) {
                abort(403, 'Forbidden');
            }

            $provider = request('provider');

            $this->providers_model->only($provider, $this->allowed_provider_fields);

            $this->providers_model->only($provider['settings'], $this->allowed_provider_setting_fields);

            $this->providers_model->optional($provider, $this->optional_provider_fields);

            $this->providers_model->optional($provider['settings'], $this->optional_provider_setting_fields);

            $provider_id = $this->providers_model->save($provider);

            $provider = $this->providers_model->find($provider_id);

            $this->webhooks_client->trigger(WEBHOOK_PROVIDER_SAVE, $provider);

            json_response([
                'success' => true,
                'id' => $provider_id,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Remove a provider.
     */
    public function destroy(): void
    {
        try {
            if (cannot('delete', PRIV_USERS)) {
                abort(403, 'Forbidden');
            }

            $provider_id = request('provider_id');

            $provider = $this->providers_model->find($provider_id);

            $this->providers_model->delete($provider_id);

            $this->webhooks_client->trigger(WEBHOOK_PROVIDER_DELETE, $provider);

            json_response([
                'success' => true,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }
}
