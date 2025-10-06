<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as AuthenticatableBase;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $companyname
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postcode
 * @property string|null $country
 * @property string|null $phonenumber
 * @property string $currency
 * @property string|null $notes
 * @property string|null $cardnum
 * @property string|null $startdate
 * @property string|null $expdate
 * @property \Illuminate\Support\Carbon|null $lastlogin
 * @property string $status
 * @property string $language
 * @property bool $allow_sso
 * @property bool $email_verified
 * @property array<array-key, mixed>|null $email_preferences
 * @property string|null $pwresetkey
 * @property \Illuminate\Support\Carbon|null $pwresetexpiry
 * @property numeric $credit
 * @property bool $taxexempt
 * @property bool $latefeeoveride
 * @property bool $overideduenotices
 * @property bool $separateinvoices
 * @property bool $disableautocc
 * @property bool $emailoptout
 * @property bool $marketing_emails_opt_in
 * @property bool $overrideautoclose
 * @property \Illuminate\Support\Carbon|null $datecreated
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $role
 * @property bool $is_admin
 * @property string|null $envato_username
 * @property string|null $envato_id
 * @property string|null $envato_token
 * @property string|null $envato_refresh_token
 * @property \Illuminate\Support\Carbon|null $envato_token_expires_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LicenseLog> $licenseLogs
 * @property-read int|null $license_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAllowSso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCardnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDatecreated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDisableautocc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailoptout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEnvatoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEnvatoRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEnvatoToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEnvatoTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEnvatoUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereExpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastlogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLatefeeoveride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMarketingEmailsOptIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOverideduenotices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOverrideautoclose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhonenumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePwresetexpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePwresetkey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSeparateinvoices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStartdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTaxexempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
class User extends AuthenticatableBase implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authorizable;
    use CanResetPassword;
    /**
     * @phpstan-ignore-next-line
     */
    use HasFactory;

    /**
     * @phpstan-ignore-next-line
     */
    protected static $factory = UserFactory::class;
    use HasRoles;
    use MustVerifyEmail;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'companyname',
        'email',
        'password',
        'email_verified_at',
        'status',
        'address1',
        'address2',
        'city',
        'state',
        'postcode',
        'country',
        'phonenumber',
        'currency',
        'notes',
        'cardnum',
        'startdate',
        'expdate',
        'lastlogin',
        'status',
        'language',
        'allow_sso',
        'email_verified',
        'email_preferences',
        'pwresetkey',
        'pwresetexpiry',
        'credit',
        'taxexempt',
        'latefeeoveride',
        'overideduenotices',
        'separateinvoices',
        'disableautocc',
        'emailoptout',
        'marketing_emails_opt_in',
        'overrideautoclose',
        'datecreated',
        'role',
        'is_admin',
        'envato_username',
        'envato_id',
        'envato_token',
        'envato_refresh_token',
        'envato_token_expires_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'envato_token',
        'envato_refresh_token',
        'pwresetkey',
        'pwresetexpiry',
        'cardnum',
        'startdate',
        'expdate',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'envato_token_expires_at' => 'datetime',
        'lastlogin' => 'datetime',
        'datecreated' => 'datetime',
        'pwresetexpiry' => 'datetime',
        'credit' => 'decimal:2',
        'taxexempt' => 'boolean',
        'latefeeoveride' => 'boolean',
        'overideduenotices' => 'boolean',
        'separateinvoices' => 'boolean',
        'disableautocc' => 'boolean',
        'emailoptout' => 'boolean',
        'marketing_emails_opt_in' => 'boolean',
        'overrideautoclose' => 'boolean',
        'allow_sso' => 'boolean',
        'email_verified' => 'boolean',
        'is_admin' => 'boolean',
        'status' => 'string',
        'email_preferences' => 'array',
    ];
    /**
     * @return HasMany<License, User>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
    /**
     * @return HasMany<Ticket, User>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
    /**
     * Invoices belonging to the user.
     */
    /**
     * @return HasMany<Invoice, User>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    public function hasEnvatoAccount(): bool
    {
        return ! empty($this->envato_username) && ! empty($this->envato_token);
    }
    /**
     * License logs that belong to the user through licenses.
     */
    /**
     * @return HasManyThrough<LicenseLog, License, User>
     */
    public function licenseLogs(): HasManyThrough
    {
        return $this->hasManyThrough(LicenseLog::class, License::class, 'user_id', 'license_id');
    }
}
