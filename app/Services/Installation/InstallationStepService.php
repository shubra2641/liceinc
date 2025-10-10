<?php

declare(strict_types=1);

namespace App\Services\Installation;

/**
 * Service for managing installation steps configuration.
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
     * @return array<int, array<string, mixed>> The installation steps with status information
     */
    public function getInstallationStepsWithStatus(int $currentStep = 1): array
    {
        $steps = $this->getInstallationSteps();
        return array_map(function ($index, $stepData) use ($currentStep) {
            $stepNumber = (int) $index + 1;
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
}
