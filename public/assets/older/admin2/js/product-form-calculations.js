/**
 * Product Form Date Calculations
 * 
 * This script handles automatic date calculations for product forms
 * including support expiry and extended support expiry dates.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a product form page
    const supportDaysInput = document.getElementById('support_days');
    const supportedUntilInput = document.getElementById('supported_until');
    const renewalPeriodSelect = document.getElementById('renewal_period');
    const extendedSupportedUntilInput = document.getElementById('extended_supported_until');
    
    // If elements don't exist, exit early
    if (!supportDaysInput || !supportedUntilInput || !renewalPeriodSelect || !extendedSupportedUntilInput) {
        return;
    }
    
    /**
     * Calculate supported until date based on support days
     */
    function calculateSupportedUntil() {
        const supportDays = parseInt(supportDaysInput.value) || 0;
        if (supportDays > 0) {
            const today = new Date();
            const supportedUntil = new Date(today.getTime() + (supportDays * 24 * 60 * 60 * 1000));
            supportedUntilInput.value = supportedUntil.toISOString().split('T')[0];
        }
    }
    
    /**
     * Calculate extended supported until date based on renewal period
     */
    function calculateExtendedSupportedUntil() {
        const renewalPeriod = renewalPeriodSelect.value;
        if (renewalPeriod) {
            const today = new Date();
            let extendedSupportedUntil = new Date(today);
            
            switch (renewalPeriod) {
                case 'monthly':
                    extendedSupportedUntil.setMonth(extendedSupportedUntil.getMonth() + 1);
                    break;
                case 'quarterly':
                    extendedSupportedUntil.setMonth(extendedSupportedUntil.getMonth() + 3);
                    break;
                case 'semi-annual':
                    extendedSupportedUntil.setMonth(extendedSupportedUntil.getMonth() + 6);
                    break;
                case 'annual':
                    extendedSupportedUntil.setFullYear(extendedSupportedUntil.getFullYear() + 1);
                    break;
                case 'three-years':
                    extendedSupportedUntil.setFullYear(extendedSupportedUntil.getFullYear() + 3);
                    break;
                case 'lifetime':
                    // For lifetime, clear the field (will be null in database)
                    extendedSupportedUntilInput.value = '';
                    return; // Exit early to avoid setting the date
            }
            
            extendedSupportedUntilInput.value = extendedSupportedUntil.toISOString().split('T')[0];
        }
    }
    
    // Event listeners
    supportDaysInput.addEventListener('input', calculateSupportedUntil);
    renewalPeriodSelect.addEventListener('change', calculateExtendedSupportedUntil);
    
    // Calculate on page load
    calculateSupportedUntil();
    calculateExtendedSupportedUntil();
});
