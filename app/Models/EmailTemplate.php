<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
 * @property array<string, mixed>|null $variables Available variables for this template
 * @property bool $is_active Whether the template is active
 * @property string|null $description Template description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder<static>|EmailTemplate active()
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
 *
 * @mixin \Eloquent
 */
class EmailTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * @var list<string>
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
     *
     * @param Builder<EmailTemplate> $query
     *
     * @return Builder<EmailTemplate>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include templates for a specific type.
     *
     * @param Builder<EmailTemplate> $query
     *
     * @return Builder<EmailTemplate>
     */
    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include templates for a specific category.
     *
     * @param Builder<EmailTemplate> $query
     *
     * @return Builder<EmailTemplate>
     */
    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
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
    /**
     * @return Collection<int, EmailTemplate>
     */
    public static function getByTypeAndCategory(
        string $type,
        string $category,
    ): Collection {
        return static::forType($type)->forCategory($category)->active()->get();
    }

    /**
     * Process template variables and return rendered content.
     */
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, string>
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
    /**
     * @param array<string, mixed> $data
     */
    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", is_string($value) ? $value : '', $content);
        }

        return $content;
    }
}
