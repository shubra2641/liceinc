<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LicenseLog>
 */
class LicenseLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model> */
    protected $model = LicenseLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action' => 'activated',
            'description' => 'License activated successfully',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Test Browser)',
            'metadata' => json_encode(['test' => 'data']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Set a specific action.
     */
    public function action(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $action,
            'description' => ucfirst($action).' action performed',
        ]);
    }

    /**
     * Associate with a license.
     */
    public function forLicense(License $license): static
    {
        return $this->state(fn (array $attributes) => [
            'license_id' => $license->id,
        ]);
    }

    /**
     * Associate with a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
