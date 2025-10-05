<?php
namespace App\Models;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as AuthenticatableBase;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
class User extends AuthenticatableBase implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authorizable;
    use CanResetPassword;
    use HasFactory;
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
    public function licenses()
    {
        return $this->hasMany(License::class);
    }
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    /**
     * Invoices belonging to the user.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function hasEnvatoAccount()
    {
        return ! empty($this->envato_username) && ! empty($this->envato_token);
    }
    /**
     * License logs that belong to the user through licenses.
     */
    public function licenseLogs(): HasManyThrough
    {
        return $this->hasManyThrough(LicenseLog::class, License::class, 'user_id', 'license_id');
    }
}
