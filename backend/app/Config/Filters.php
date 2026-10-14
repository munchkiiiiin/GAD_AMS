<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\JwtFilter;
use App\Filters\RateLimitFilter;
use App\Filters\AdminFilter;
use App\Filters\StaffOrAdminFilter;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array<string, class-string|list<class-string>>
     *
     * [filter_name => classname]
     * or [filter_name => [classname1, classname2, ...]]
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'cors'          => Cors::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'jwtAuth'       => JwtFilter::class,
        'rateLimit'     => RateLimitFilter::class,
        'adminOnly'     => AdminFilter::class,
        'staffOrAdmin'  => StaffOrAdminFilter::class,
    ];

    /**
     * List of special required filters.
     *
     * The filters listed here are special. They are applied before and after
     * other kinds of filters, and always applied even if a route does not exist.
     *
     * Filters set by default provide framework functionality. If removed,
     * those functions will no longer work.
     *
     * @see https://codeigniter.com/user_guide/incoming/filters.html#provided-filters
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'pagecache',  // Web Page Caching
        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'performance', // Performance Metrics
            'toolbar',     // Debug Toolbar
        ],
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array{
     *     before: array<string, array{except: list<string>|string}>|list<string>,
     *     after: array<string, array{except: list<string>|string}>|list<string>
     * }
     */
    public array $globals = [
        'before' => [
            'cors', // <-- Add this here to enable CORS globally!
            // 'csrf',
        ],
        'after' => [
            'cors',
            'toolbar',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     *
     * @var array<string, list<string>>
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [
        // ----------------------------------------------------------------
        // 1. RATE LIMITING — public auth endpoints only (no JWT needed)
        //    10 requests per minute per IP.
        // ----------------------------------------------------------------
        'rateLimit' => [
            'before' => [
                'api/login',
                'api/register',
                'api/forgot-password',
                'api/reset-password',
                'api/add_office',
            ]
        ],

        // ----------------------------------------------------------------
        // 2. JWT AUTH — must run before role filters so payload is set.
        //    All protected routes listed here.
        // ----------------------------------------------------------------
        'jwtAuth' => [
            'before' => [
                'api/users*',
                'api/offices*',
                'api/activity-logs',
                'api/submit-activity-design',
                'api/activity-designs*',
                'api/activity-design*',
                'api/update-design*',
                'api/approve-design*',
                'api/disapprove-design*',
                'api/revert-design*',
                'api/revision-design*',
                'api/update-deadline*',
                'api/get-next-control-number',
                'api/get-form-types',
                'api/get-gad-mandates',
                'api/get-gender-issues*',
                'api/get-activity-classifications',
                'api/approved-controls*',
                'api/submit-activity-report',
                'api/accomplishment-reports*',
                'api/accomplishment-report*',
                'api/activity-reports*',
                'api/activity-report*',
                'api/update-report*',
                'api/approve-report*',
                'api/revision-report*',
                'api/mandates*',
                'api/gender-issues*',
                'api/messages*',
                'api/notifications*',
                'api/budget*',
                'api/staff/budget*',
                'api/staff/budget-monitoring*',
                'api/plan*',
                'api/gpb*',
                'api/settings*',
                'api/admin*',
                'api/archives*',
                'api/archive-design*',
                'api/archive-report*',
                'api/documents*',
                'api/files/overwrite*',
                'api/venues*',
                'api/contact-inquiries*',
                'api/storage*',
            ]
        ],

        // ----------------------------------------------------------------
        // 3. STAFF OR ADMIN RBAC — actions both GAD Staff and Admin can do.
        //    Runs after jwtAuth so $request->jwtPayload is already set.
        //    Regular college/TWG users hitting these routes get 403.
        // ----------------------------------------------------------------
        'staffOrAdmin' => [
            'before' => [
                // Manage users (full permissions for both Admin and Staff)
                'api/users/create',
                'api/users/update*',
                'api/users/suspend*',
                'api/users/restore*',
                'api/users/delete*',
                // Document approval / revision workflow
                'api/approve-design*',
                'api/disapprove-design*',
                'api/revert-design*',
                'api/revision-design*',
                'api/approve-report*',
                'api/revision-report*',
                'api/update-deadline*',
                // Viewing all submissions across all users
                'api/activity-designs',
                'api/activity-reports',
                'api/admin/twg-submissions',
                // Contact inquiries
                'api/contact-inquiries*',
                // Archiving
                'api/archive-design*',
                'api/archive-report*',
                // Budget management
                'api/staff/budget-monitoring*',
                'api/budget/gad-plan',
                // Mandates & gender issues (create/update/delete)
                'api/mandates',
                'api/gender-issues*',
                // Campus Resources (venues, offices, holidays)
                'api/venues*',
                'api/offices*',
                // System-wide settings
                'api/settings*',
                // Plan & budget configuration
                'api/plan',
                'api/plan/mandate-allocations',
                // GPB import (destructive)
                'api/gpb/import',
            ]
        ],

        // ----------------------------------------------------------------
        // 4. ADMIN ONLY RBAC — runs after staffOrAdmin.
        //    Staff users hitting these routes get 403 even if they passed
        //    staffOrAdmin.
        // ----------------------------------------------------------------
        'adminOnly' => [
            'before' => [
                'api/dummy-admin-route',
            ]
        ],
    ];

    /**
     * Constructor automatically expands all route rules to match BOTH
     * 'api/...' (used in local development) and '...' (used in production
     * on Hostinger where the route group has an empty prefix).
     */
    public function __construct()
    {
        parent::__construct();

        foreach ($this->filters as $filterName => &$config) {
            if (isset($config['before']) && is_array($config['before'])) {
                $expanded = [];
                foreach ($config['before'] as $route) {
                    $expanded[] = $route;
                    if (str_starts_with($route, 'api/')) {
                        $expanded[] = substr($route, 4); // Add route without 'api/'
                    } else {
                        $expanded[] = 'api/' . ltrim($route, '/'); // Add route with 'api/'
                    }
                }
                $config['before'] = array_values(array_unique($expanded));
            }
        }
        unset($config);
    }
}

