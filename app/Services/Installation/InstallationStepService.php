<?php

declare(strict_types=1);

namespace App\Services\Installation;

/**
 * Installation Step Service.
 *
 * Handles installation steps configuration and status management.
 *
 * @version 1.0.0
 */
class InstallationStepService
{
    /**
     * Get installation steps configuration.
     *
     * @return array<int, array<string, string>> The installation steps configuration
     */
    public function getInstallationSteps(): array
    {
        return [
            ['name' => trans('install.step_welcome'), 'route' => 'install.welcome'],
            ['name' => 'License Verification', 'route' => 'install.license'],
            ['name' => trans('install.step_requirements'), 'route' => 'install.requirements'],
            ['name' => trans('install.step_database'), 'route' => 'install.database'],
            ['name' => trans('install.step_admin'), 'route' => 'install.admin'],
            ['name' => trans('install.step_settings'), 'route' => 'install.settings'],
            ['name' => trans('install.step_install'), 'route' => 'install.install'],
        ];
    }

    /**
     * Get installation steps with status information.
     *
     * @param int $currentStep The current step number
     *
     * @return array<int, array<string, mixed>> The installation steps with status information
     */
    public function getInstallationStepsWithStatus(int $currentStep = 1): array
    {
        $steps = $this->getInstallationSteps();

        return array_map(function ($index, $stepData) use ($currentStep) {
            $stepNumber = (int)$index + 1;
            $isCompleted = $stepNumber < $currentStep;
            $isCurrent = $stepNumber == $currentStep;
            $isPending = $stepNumber > $currentStep;

            return [
                'name' => $stepData['name'],
                'route' => $stepData['route'],
                'number' => $stepNumber,
                'isCompleted' => $isCompleted,
                'isCurrent' => $isCurrent,
                'isPending' => $isPending,
                'status' => $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending'),
            ];
        }, array_keys($steps), $steps);
    }

    /**
     * Get timezones configuration.
     *
     * @return array<string, mixed> The timezones configuration
     */
    public function getTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Dubai' => 'Dubai',
            'Asia/Riyadh' => 'Riyadh',
            'Asia/Kuwait' => 'Kuwait',
            'Asia/Qatar' => 'Qatar',
            'Asia/Bahrain' => 'Bahrain',
            'Africa/Cairo' => 'Cairo',
            'Asia/Tokyo' => 'Tokyo',
            'Australia/Sydney' => 'Sydney',
        ];
    }
}
