<?php return array (
  'broadcasting' => 
  array (
    'default' => 'log',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'cluster' => NULL,
          'host' => 'api-mt1.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => '12',
      'verify' => true,
      'limit' => NULL,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'app' => 
  array (
    'name' => 'License Management System',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost/my-logos',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:lvPcN7im7D0yBZu0NvjMEEp48yUNKILVARgx7ntcmXs=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
      'store' => 'database',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'App\\Providers\\EnvatoSocialiteProvider',
      24 => 'App\\Providers\\AppServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Benchmark' => 'Illuminate\\Support\\Benchmark',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'Uri' => 'Illuminate\\Support\\Uri',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
    'supported_locales' => 
    array (
      'en' => 
      array (
        'name' => 'English',
        'native' => 'English',
        'flag' => '🇺🇸',
      ),
      'ar' => 
      array (
        'name' => 'Arabic',
        'native' => 'Arabic',
        'flag' => '🇸🇦',
      ),
      'hi' => 
      array (
        'name' => 'Hindi',
        'native' => 'हिन्दी',
        'flag' => '🇮🇳',
      ),
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'cache' => 
  array (
    'default' => 'database',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'session' => 
      array (
        'driver' => 'session',
        'key' => '_cache',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\framework/cache/data',
        'lock_path' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => '',
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'test',
        'prefix' => '',
        'foreign_key_constraints' => true,
        'busy_timeout' => NULL,
        'journal_mode' => NULL,
        'synchronous' => NULL,
        'transaction_mode' => 'DEFERRED',
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
      'testing' => 
      array (
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => false,
        'busy_timeout' => NULL,
        'journal_mode' => NULL,
        'synchronous' => NULL,
        'transaction_mode' => 'IMMEDIATE',
      ),
    ),
    'migrations' => 
    array (
      'table' => 'migrations',
      'update_date_on_publish' => true,
    ),
    'redis' => 
    array (
      'client' => 'phpredis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'license-management-system-database-',
        'persistent' => false,
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
        'max_retries' => 3,
        'backoff_algorithm' => 'decorrelated_jitter',
        'backoff_base' => 100,
        'backoff_cap' => 1000,
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
        'max_retries' => 3,
        'backoff_algorithm' => 'decorrelated_jitter',
        'backoff_base' => 100,
        'backoff_cap' => 1000,
      ),
    ),
  ),
  'envato' => 
  array (
    'client_id' => NULL,
    'client_secret' => NULL,
    'redirect_uri' => 'http://localhost/my-logos/auth/envato/callback',
    'token' => NULL,
    'api_base' => 'https://api.envato.com',
    'scopes' => 
    array (
      0 => 'user:username',
      1 => 'user:email',
      2 => 'purchase:verify',
    ),
    'cache' => 
    array (
      'purchase_verification' => 30,
      'user_info' => 1,
      'item_info' => 6,
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\app',
        'serve' => true,
        'throw' => false,
        'report' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\app/public',
        'url' => 'http://localhost/my-logos/storage',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
        'report' => false,
      ),
      'private' => 
      array (
        'driver' => 'local',
        'root' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\app/private',
        'serve' => false,
        'throw' => false,
        'report' => false,
      ),
    ),
    'links' => 
    array (
      'D:\\xampp1\\htdocs\\my-logos\\public\\storage' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\app/public',
    ),
  ),
  'ide-helper' => 
  array (
    'filename' => '_ide_helper.php',
    'models_filename' => '_ide_helper_models.php',
    'meta_filename' => '.phpstorm.meta.php',
    'include_fluent' => false,
    'include_factory_builders' => false,
    'write_model_magic_where' => true,
    'write_model_external_builder_methods' => true,
    'write_model_relation_count_properties' => true,
    'write_model_relation_exists_properties' => false,
    'write_eloquent_model_mixins' => false,
    'include_helpers' => false,
    'helper_files' => 
    array (
      0 => 'D:\\xampp1\\htdocs\\my-logos/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
      1 => 'D:\\xampp1\\htdocs\\my-logos/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php',
    ),
    'model_locations' => 
    array (
      0 => 'app',
    ),
    'ignored_models' => 
    array (
    ),
    'model_hooks' => 
    array (
    ),
    'extra' => 
    array (
      'Eloquent' => 
      array (
        0 => 'Illuminate\\Database\\Eloquent\\Builder',
        1 => 'Illuminate\\Database\\Query\\Builder',
      ),
      'Session' => 
      array (
        0 => 'Illuminate\\Session\\Store',
      ),
    ),
    'magic' => 
    array (
    ),
    'interfaces' => 
    array (
    ),
    'model_camel_case_properties' => false,
    'type_overrides' => 
    array (
      'integer' => 'int',
      'boolean' => 'bool',
    ),
    'include_class_docblocks' => false,
    'force_fqn' => false,
    'use_generics_annotations' => true,
    'macro_default_return_types' => 
    array (
      'Illuminate\\Http\\Client\\Factory' => 'Illuminate\\Http\\Client\\PendingRequest',
    ),
    'additional_relation_types' => 
    array (
    ),
    'additional_relation_return_types' => 
    array (
    ),
    'enforce_nullable_relationships' => true,
    'post_migrate' => 
    array (
    ),
  ),
  'license' => 
  array (
    'auto_generate_license' => true,
    'default_license_length' => 32,
    'types' => 
    array (
      'regular' => 
      array (
        'name' => 'Regular License',
        'description' => 'Standard license for single end product',
        'max_domains' => 1,
        'support_period_days' => 365,
        'can_resell' => false,
        'commercial_use' => true,
      ),
      'extended' => 
      array (
        'name' => 'Extended License',
        'description' => 'Extended license for multiple end products',
        'max_domains' => 5,
        'support_period_days' => 365,
        'can_resell' => true,
        'commercial_use' => true,
      ),
      'developer' => 
      array (
        'name' => 'Developer License',
        'description' => 'License for developers and agencies',
        'max_domains' => -1,
        'support_period_days' => 730,
        'can_resell' => true,
        'commercial_use' => true,
      ),
      'trial' => 
      array (
        'name' => 'Trial License',
        'description' => 'Limited trial license',
        'max_domains' => 1,
        'support_period_days' => 30,
        'can_resell' => false,
        'commercial_use' => false,
        'expires_after_days' => 30,
      ),
    ),
    'validation' => 
    array (
      'verify_with_envato' => true,
      'fallback_to_internal' => true,
      'cache_verification_results' => true,
      'cache_duration_minutes' => 60,
      'allow_offline_verification' => false,
      'grace_period_days' => 7,
      'max_verification_attempts' => 5,
      'lockout_duration_minutes' => 15,
    ),
    'domains' => 
    array (
      'allow_localhost' => true,
      'allow_ip_addresses' => false,
      'allow_wildcards' => true,
      'validate_ssl_certificates' => false,
      'auto_approve_subdomains' => false,
      'max_domains_per_license' => 5,
      'domain_change_cooldown_hours' => 24,
    ),
    'templates' => 
    array (
      'template_directory' => 'D:\\xampp1\\htdocs\\my-logos\\resources\\templates/licenses',
      'auto_create_templates' => true,
      'template_cache_enabled' => true,
      'template_cache_duration' => 3600,
      'include_verification_code' => true,
      'obfuscate_generated_code' => false,
      'minify_generated_code' => true,
    ),
    'api' => 
    array (
      'verification_endpoint' => '/api/license/verify',
      'status_endpoint' => '/api/license/status',
      'domain_endpoint' => '/api/license/domains',
      'api_token' => '',
      'require_api_key' => false,
      'api_key_header' => 'X-License-API-Key',
      'rate_limit_per_minute' => 60,
      'log_api_requests' => true,
      'return_detailed_errors' => false,
    ),
    'envato' => 
    array (
      'api_base_url' => 'https://api.envato.com',
      'personal_token' => '',
      'client_id' => '',
      'client_secret' => '',
      'redirect_uri' => '/auth/envato/callback',
      'timeout_seconds' => 30,
      'retry_attempts' => 3,
      'cache_user_data' => true,
      'cache_duration_minutes' => 60,
    ),
    'expiration' => 
    array (
      'default_license_duration_days' => 365,
      'support_duration_days' => 365,
      'renewal_reminder_days' => 30,
      'grace_period_after_expiration' => 7,
      'auto_suspend_expired_licenses' => true,
      'allow_expired_verification' => false,
    ),
    'logging' => 
    array (
      'log_verifications' => true,
      'log_failed_verifications' => true,
      'log_domain_changes' => true,
      'log_license_generation' => true,
      'log_api_calls' => true,
      'detailed_logging' => false,
      'log_retention_days' => 90,
    ),
    'security' => 
    array (
      'encrypt_license_data' => true,
      'use_secure_tokens' => true,
      'validate_request_signatures' => false,
      'prevent_license_sharing' => true,
      'detect_suspicious_activity' => true,
      'block_vpn_verification' => false,
      'require_https' => true,
    ),
    'notifications' => 
    array (
      'notify_on_verification' => false,
      'notify_on_expiration' => true,
      'notify_on_domain_change' => true,
      'notify_on_suspicious_activity' => true,
      'notification_email' => '',
      'use_slack_notifications' => false,
      'slack_webhook_url' => '',
    ),
    'performance' => 
    array (
      'enable_caching' => true,
      'cache_driver' => 'redis',
      'enable_query_optimization' => true,
      'batch_verification_size' => 100,
      'use_database_indexes' => true,
      'compress_api_responses' => true,
    ),
    'testing' => 
    array (
      'allow_test_licenses' => true,
      'test_license_prefix' => 'TEST-',
      'bypass_verification_in_testing' => false,
      'mock_envato_responses' => false,
      'generate_fake_data' => false,
    ),
  ),
  'license_server' => 
  array (
    'url' => 'http://localhost/my-logos/api',
    'domain' => 'localhost',
    'timeout' => 30,
    'cache_ttl' => 
    array (
      'updates' => 300,
      'history' => 600,
      'latest' => 300,
      'products' => 1800,
    ),
    'retry_attempts' => 3,
    'retry_delay' => 1000,
    'default_product' => 
    array (
      'slug' => 'the-ultimate-license-management-system',
      'name' => 'The Ultimate License Management System',
    ),
    'security' => 
    array (
      'verify_ssl' => true,
      'allowed_domains' => '',
      'rate_limit' => 
      array (
        'enabled' => true,
        'max_attempts' => 10,
        'decay_minutes' => 60,
      ),
    ),
    'logging' => 
    array (
      'enabled' => true,
      'level' => 'info',
      'log_failures' => true,
      'log_success' => false,
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'handler_with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'formatter' => NULL,
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'smtp',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'scheme' => NULL,
        'url' => NULL,
        'host' => 'mail.my-logos.com',
        'port' => '465',
        'username' => 'tech@my-logos.com',
        'password' => 'yXZ,[;b(rMfq',
        'timeout' => NULL,
        'local_domain' => 'localhost',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
        'retry_after' => 60,
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
        'retry_after' => 60,
      ),
    ),
    'from' => 
    array (
      'address' => 'tech@my-logos.com',
      'name' => 'License Management System',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'D:\\xampp1\\htdocs\\my-logos\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'roles',
      'permissions' => 'permissions',
      'model_has_permissions' => 'model_has_permissions',
      'model_has_roles' => 'model_has_roles',
      'role_has_permissions' => 'role_has_permissions',
    ),
    'column_names' => 
    array (
      'role_pivot_key' => NULL,
      'permission_pivot_key' => NULL,
      'model_morph_key' => 'model_id',
      'team_foreign_key' => 'team_id',
    ),
    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'events_enabled' => false,
    'teams' => false,
    'team_resolver' => 'Spatie\\Permission\\DefaultTeamResolver',
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'cache' => 
    array (
      'expiration_time' => 86400,
      'key' => 'spatie.permission.cache',
      'store' => 'default',
    ),
    'guard_name' => 'web',
    'permissions' => 
    array (
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'mysql',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'security' => 
  array (
    'validation' => 
    array (
      'max_string_length' => 10000,
      'sanitize_html' => true,
    ),
    'xss_protection' => 
    array (
      'allowed_tags' => '',
      'enabled' => true,
    ),
    'rate_limiting' => 
    array (
      'api_requests_per_minute' => 60,
      'login_attempts_per_minute' => 5,
      'password_reset_per_hour' => 3,
    ),
    'ip_control' => 
    array (
      'blacklist' => '',
      'whitelist' => '',
    ),
    'file_upload_security' => 
    array (
      'max_upload_size' => 10240,
      'allowed_extensions' => 
      array (
        'images' => 
        array (
          0 => 'jpg',
          1 => 'jpeg',
          2 => 'png',
          3 => 'gif',
          4 => 'webp',
        ),
        'documents' => 
        array (
          0 => 'pdf',
          1 => 'doc',
          2 => 'docx',
          3 => 'txt',
        ),
        'archives' => 
        array (
          0 => 'zip',
          1 => 'rar',
        ),
      ),
      'validate_file_content' => true,
    ),
    'headers' => 
    array (
      'x_frame_options' => 'DENY',
      'x_content_type_options' => 'nosniff',
      'x_xss_protection' => '1; mode=block',
      'content_security_policy' => 'default-src \'self\'',
    ),
    'session' => 
    array (
      'secure_cookie' => false,
      'http_only' => true,
      'same_site' => 'lax',
    ),
    'api' => 
    array (
      'token_expiration' => 3600,
      'rate_limit_per_minute' => 100,
    ),
    'encryption' => 
    array (
      'cipher' => 'AES-256-CBC',
      'key_length' => 32,
    ),
  ),
  'sensitive' => 
  array (
    'api' => 
    array (
      'license_token' => '',
      'envato_personal_token' => '',
      'envato_client_id' => '',
      'envato_client_secret' => '',
      'envato_username' => '',
    ),
    'payment' => 
    array (
      'stripe_public_key' => '',
      'stripe_secret_key' => '',
      'stripe_webhook_secret' => '',
      'paypal_client_id' => '',
      'paypal_client_secret' => '',
      'paypal_webhook_id' => '',
    ),
    'database' => 
    array (
      'encryption_key' => '',
      'backup_password' => '',
    ),
    'email' => 
    array (
      'smtp_password' => 'yXZ,[;b(rMfq',
      'smtp_username' => 'tech@my-logos.com',
      'encryption_key' => '',
    ),
    'security' => 
    array (
      'jwt_secret' => '',
      'encryption_key' => '',
      'session_secret' => '',
    ),
    'services' => 
    array (
      'google_analytics_id' => '',
      'facebook_app_id' => '',
      'facebook_app_secret' => '',
      'twitter_api_key' => '',
      'twitter_api_secret' => '',
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'envato' => 
    array (
      'client_id' => NULL,
      'client_secret' => NULL,
      'redirect' => 'http://localhost/my-logos/auth/envato/callback',
      'token' => NULL,
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'license-management-system-session',
    'path' => '/',
    'domain' => NULL,
    'secure' => true,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'D:\\xampp1\\htdocs\\my-logos\\resources\\views',
    ),
    'compiled' => 'D:\\xampp1\\htdocs\\my-logos\\storage\\framework/views',
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost',
      1 => '127.0.0.1',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
      'validate_csrf_token' => 'Illuminate\\Foundation\\Http\\Middleware\\ValidateCsrfToken',
    ),
  ),
  'scout' => 
  array (
    'driver' => 'algolia',
    'prefix' => '',
    'queue' => false,
    'after_commit' => false,
    'chunk' => 
    array (
      'searchable' => 500,
      'unsearchable' => 500,
    ),
    'soft_delete' => false,
    'identify' => false,
    'algolia' => 
    array (
      'id' => '',
      'secret' => '',
      'index-settings' => 
      array (
      ),
    ),
    'meilisearch' => 
    array (
      'host' => 'http://localhost:7700',
      'key' => NULL,
      'index-settings' => 
      array (
      ),
    ),
    'typesense' => 
    array (
      'client-settings' => 
      array (
        'api_key' => 'xyz',
        'nodes' => 
        array (
          0 => 
          array (
            'host' => 'localhost',
            'port' => '8108',
            'path' => '',
            'protocol' => 'http',
          ),
        ),
        'nearest_node' => 
        array (
          'host' => 'localhost',
          'port' => '8108',
          'path' => '',
          'protocol' => 'http',
        ),
        'connection_timeout_seconds' => 2,
        'healthcheck_interval_seconds' => 30,
        'num_retries' => 3,
        'retry_interval_seconds' => 1,
      ),
      'model-settings' => 
      array (
      ),
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
