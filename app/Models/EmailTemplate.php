<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * EmailTemplate Model.
 *
 * Manages email templates for the system with support for both user and admin notifications.
 * Templates are categorized and support variable substitution for dynamic content.
 *
 * @property int $id
 * @property string $name Template identifier (e.g., 'user_welcome', 'admin_license_created')
 * @property string $subject Email subject line
 * @property string $body Email body content with variable placeholders
 * @property string $type Template type: 'user' or 'admin'
 * @property string $category Template category: 'registration', 'license', 'ticket', 'invoice', etc.
 * @property array|null $variables Available variables for this template
 * @property bool $is_active Whether the template is active
 * @property string|null $description Template description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder<static>|EmailTemplate active()
 * @method static \Database\Factories\EmailTemplateFactory factory($count = null, $state = [])
 * @method static Builder<static>|EmailTemplate forCategory(string $category)
 * @method static Builder<static>|EmailTemplate forType(string $type)
 * @method static Builder<static>|EmailTemplate newModelQuery()
 * @method static Builder<static>|EmailTemplate newQuery()
 * @method static Builder<static>|EmailTemplate query()
 * @method static Builder<static>|EmailTemplate whereBody($value)
 * @method static Builder<static>|EmailTemplate whereCategory($value)
 * @method static Builder<static>|EmailTemplate whereCreatedAt($value)
 * @method static Builder<static>|EmailTemplate whereDescription($value)
 * @method static Builder<static>|EmailTemplate whereId($value)
 * @method static Builder<static>|EmailTemplate whereIsActive($value)
 * @method static Builder<static>|EmailTemplate whereName($value)
 * @method static Builder<static>|EmailTemplate whereSubject($value)
 * @method static Builder<static>|EmailTemplate whereType($value)
 * @method static Builder<static>|EmailTemplate whereUpdatedAt($value)
 * @method static Builder<static>|EmailTemplate whereVariables($value)
 * @mixin \Eloquent
 */
class EmailTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'subject',
        'body',
        'type',
        'category',
        'variables',
        'is_active',
        'description',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
    /**
     * Scope a query to only include templates for a specific type.
     */
    public function scopeForType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }
    /**
     * Scope a query to only include templates for a specific category.
     */
    public function scopeForCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }
    /**
     * Get template by name.
     */
    public static function getByName(string $name): ?self
    {
        return static::where('name', $name)->active()->first();
    }
    /**
     * Get all templates for a specific type and category.
     */
    public static function getByTypeAndCategory(
        string $type,
        string $category,
    ): \Illuminate\Database\Eloquent\Collection {
        return static::forType($type)->forCategory($category)->active()->get();
    }
    /**
     * Process template variables and return rendered content.
     */
    public function render(array $data = []): array
    {
        $subject = $this->replaceVariables($this->subject ?? '', $data);
        $body = $this->replaceVariables($this->body ?? '', $data);
        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
    /**
     * Replace variables in template content.
     */
    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        return $content;
    }
}
