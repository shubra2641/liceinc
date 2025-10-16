<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Request Helpers Trait.
 *
 * Provides common request functionality to eliminate code duplication
 * across different request classes.
 */
trait RequestHelpers
{
    /**
     * Handle checkbox values for update requests.
     *
     * @param array $additionalFields Additional checkbox fields to include
     *
     * @return void
     */
    protected function handleCheckboxValues(array $additionalFields = []): void
    {
        $defaultCheckboxes = [
            'include_changelog',
            'include_dependencies',
            'include_security_updates',
            'include_feature_updates',
            'include_bug_fixes',
            'check_beta',
            'check_prerelease',
            'auto_install',
            'notify_on_available',
            'compare_versions',
            'include_download_url',
            'include_checksums',
            'include_file_list',
            'include_installation_notes',
            'include_rollback_info',
        ];

        $allCheckboxes = array_merge($defaultCheckboxes, $additionalFields);
        $checkboxData = [];

        foreach ($allCheckboxes as $field) {
            $checkboxData[$field] = $this->has($field);
        }

        $this->merge($checkboxData);
    }

    /**
     * Handle filter and sort parameters.
     *
     * @param array $additionalFilters Additional filter fields to include
     *
     * @return void
     */
    protected function handleFilterAndSort(array $additionalFilters = []): void
    {
        $defaultFilters = [
            'current_version',
            'filter_version',
            'sort_order',
        ];

        $allFilters = array_merge($defaultFilters, $additionalFilters);
        $filterData = [];

        foreach ($allFilters as $field) {
            $filterData[$field] = $this->input($field)
                ? $this->sanitizeInput($this->input($field))
                : null;
        }

        $this->merge($filterData);
    }

    /**
     * Set default values for update requests.
     *
     * @param array $defaults Default values to set
     *
     * @return void
     */
    protected function setDefaultValues(array $defaults = []): void
    {
        $standardDefaults = [
            'include_changelog' => true,
            'include_dependencies' => false,
            'include_security_updates' => true,
            'include_feature_updates' => true,
            'include_bug_fixes' => true,
            'check_beta' => false,
            'check_prerelease' => false,
            'auto_install' => false,
            'notify_on_available' => true,
            'compare_versions' => true,
            'include_download_url' => true,
            'include_checksums' => false,
            'include_file_list' => false,
            'include_installation_notes' => false,
            'include_rollback_info' => false,
        ];

        $allDefaults = array_merge($standardDefaults, $defaults);
        $this->merge($allDefaults);
    }

    /**
     * Sanitize input data.
     *
     * @param mixed $input The input to sanitize
     *
     * @return string|null
     */
    protected function sanitizeInput($input): ?string
    {
        if (is_null($input)) {
            return null;
        }

        return trim(strip_tags($input));
    }
}
