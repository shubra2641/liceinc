<?php

namespace App\Http\Requests\Traits;

trait ProfileDataSanitization
{
    /**
     * Sanitize common profile fields
     */
    protected function sanitizeProfileFields(): void
    {
        $this->merge([
            'name' => $this->sanitizeInput($this->input('name')),
            'firstname' => $this->input('firstname') ? $this->sanitizeInput($this->input('firstname')) : null,
            'lastname' => $this->input('lastname') ? $this->sanitizeInput($this->input('lastname')) : null,
            'companyname' => $this->input('companyname') ? $this->sanitizeInput($this->input('companyname')) : null,
            'phonenumber' => $this->input('phonenumber') ? $this->sanitizeInput($this->input('phonenumber')) : null,
            'address1' => $this->input('address1') ? $this->sanitizeInput($this->input('address1')) : null,
            'address2' => $this->input('address2') ? $this->sanitizeInput($this->input('address2')) : null,
            'city' => $this->input('city') ? $this->sanitizeInput($this->input('city')) : null,
            'state' => $this->input('state') ? $this->sanitizeInput($this->input('state')) : null,
            'postcode' => $this->input('postcode') ? $this->sanitizeInput($this->input('postcode')) : null,
            'country' => $this->input('country') ? $this->sanitizeInput($this->input('country')) : null,
        ]);
    }

    /**
     * Sanitize additional profile fields
     */
    protected function sanitizeAdditionalFields(): void
    {
        $this->merge([
            'timezone' => $this->input('timezone') ? $this->sanitizeInput($this->input('timezone')) : null,
            'language' => $this->input('language') ? $this->sanitizeInput($this->input('language')) : null,
            'date_format' => $this->input('date_format') ? $this->sanitizeInput($this->input('date_format')) : null,
            'time_format' => $this->input('time_format') ? $this->sanitizeInput($this->input('time_format')) : null,
            'currency' => $this->input('currency') ? $this->sanitizeInput($this->input('currency')) : null,
            'bio' => $this->input('bio') ? $this->sanitizeInput($this->input('bio')) : null,
        ]);
    }

    /**
     * Sanitize Envato fields
     */
    protected function sanitizeEnvatoFields(): void
    {
        $this->merge([
            'envato_username' => $this->input('envato_username') ? $this->sanitizeInput($this->input('envato_username')) : null,
            'envato_id' => $this->input('envato_id') ? $this->sanitizeInput($this->input('envato_id')) : null,
        ]);
    }
}
