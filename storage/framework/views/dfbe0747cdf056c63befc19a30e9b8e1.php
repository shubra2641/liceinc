<?php $__env->startSection('title', trans('app.checking_license')); ?>
<?php $__env->startSection('page-title', trans('app.license_check')); ?>
<?php $__env->startSection('page-subtitle', trans('app.checking_license')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-shield-alt"></i>
                <?php echo e(trans('app.check_title')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.check_description')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <!-- Stats Cards -->
            <div class="user-stats-grid">
                <!-- License Check -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.license_check')); ?></div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="licenseStatusValue">
                        <?php if(isset($success) && $success && isset($licenseData)): ?>
                            <?php echo e($licenseData['status'] ?? '-'); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.current_status')); ?></p>
                </div>

                <!-- License Type -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.license_type')); ?></div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="licenseTypeValue">
                        <?php if(isset($success) && $success && isset($licenseData)): ?>
                            <?php echo e($licenseData['license_type'] ?? '-'); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.license_category')); ?></p>
                </div>

                <!-- Days Remaining -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.days_remaining')); ?></div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="daysRemainingValue">
                        <?php if(isset($success) && $success && isset($licenseData)): ?>
                            <?php echo e($licenseData['days_remaining'] ?? '-'); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.expiration_info')); ?></p>
                </div>

                <!-- Domains Used -->
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title"><?php echo e(trans('app.domains_used')); ?></div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <div class="user-stat-value" id="domainsUsedValue">
                        <?php if(isset($success) && $success && isset($licenseData)): ?>
                            <?php echo e($licenseData['used_domains'] ?? '0'); ?>/<?php echo e($licenseData['max_domains'] ?? '0'); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo e(trans('app.usage_info')); ?></p>
                </div>
            </div>

            <!-- License Check Form -->
            <?php if(!isset($success) || !$success): ?>
            <div id="licenseCheckFormCard" class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-search"></i>
                        <?php echo e(trans('app.check_license')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.enter_license_details')); ?></p>
                </div>
                <div class="user-card-content">
                    <form id="licenseCheckForm" class="register-form license-status-form" action="<?php echo e(route('license.status.show.results')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="form-fields-grid">
                            <!-- License Code -->
                            <div class="form-field-group">
                                <label for="license_key" class="form-label">
                                    <i class="fas fa-key"></i>
                                    <?php echo e(trans('app.license_code')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input type="text" id="license_key" name="license_key" required
                                        class="form-input"
                                        placeholder="<?php echo e(trans('app.license_code_placeholder')); ?>">
                                </div>
                                <p class="form-help-text">
                                    <?php echo e(trans('app.license_code_example')); ?>

                                </p>
                            </div>

                            <!-- Email -->
                            <div class="form-field-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo e(trans('app.email')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input type="email" id="email" name="email" required
                                        class="form-input"
                                        placeholder="<?php echo e(trans('app.email_placeholder')); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="checkButton" class="form-submit-button">
                            <span class="button-text"><?php echo e(trans('app.check_button')); ?></span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-clock"></i>
                        <?php echo e(trans('app.checking_license')); ?>

                    </div>
                </div>
                <div class="user-card-content">
                    <div class="user-loading-container">
                        <div class="user-loading-spinner"></div>
                        <p class="user-loading-text"><?php echo e(trans('app.checking_license')); ?></p>
                    </div>
                </div>
            </div>

            <!-- License Details -->
            <?php if(isset($success) && $success && isset($licenseData)): ?>
            <div id="licenseDetails" class="user-card">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-check-circle"></i>
                        <?php echo e(trans('app.license_found')); ?>

                    </div>
                    <p class="user-card-subtitle"><?php echo e(trans('app.license_details_info')); ?></p>
                </div>
                <div class="user-card-content">
                    <!-- Detailed Information -->
                    <div class="license-details-grid">
                        <!-- License Information -->
                        <div class="license-info-card">
                            <div class="license-info-header">
                                <div class="license-info-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="license-info-title">
                                    <h3><?php echo e(trans('app.license_information')); ?></h3>
                                    <p><?php echo e(trans('app.license_details_subtitle')); ?></p>
                                </div>
                            </div>
                            <div class="license-info-content">
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-key"></i>
                                        <?php echo e(trans('app.license_key')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="licenseKey" class="license-key-code"><?php echo e($licenseData['license_key'] ?? 'N/A'); ?></span>
                                        <button class="copy-btn" data-copy-target="licenseKey">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-tag"></i>
                                        <?php echo e(trans('app.license_type')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="licenseType" class="license-type-badge"><?php echo e($licenseData['license_type'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo e(trans('app.status')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="licenseStatus" class="license-status-badge <?php echo e(strtolower($licenseData['status'] ?? 'unknown')); ?>"><?php echo e($licenseData['status'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-calendar-plus"></i>
                                        <?php echo e(trans('app.created_at')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="createdAt" class="license-date"><?php echo e($licenseData['created_at'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-calendar-times"></i>
                                        <?php echo e(trans('app.expires_at')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="expiresAt" class="license-date"><?php echo e($licenseData['expires_at'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-clock"></i>
                                        <?php echo e(trans('app.days_remaining')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="daysRemaining" class="license-days-remaining"><?php echo e($licenseData['days_remaining'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="license-info-card">
                            <div class="license-info-header">
                                <div class="license-info-icon">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div class="license-info-title">
                                    <h3><?php echo e(trans('app.product_information')); ?></h3>
                                    <p><?php echo e(trans('app.product_details_subtitle')); ?></p>
                                </div>
                            </div>
                            <div class="license-info-content">
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-box"></i>
                                        <?php echo e(trans('app.product_name')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="productName" class="product-name"><?php echo e($licenseData['product_name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-globe"></i>
                                        <?php echo e(trans('app.max_domains')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="maxDomains" class="domain-limit"><?php echo e($licenseData['max_domains'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-check-circle"></i>
                                        <?php echo e(trans('app.used_domains')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="usedDomains" class="domain-used"><?php echo e($licenseData['used_domains'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Code -->
                        <?php if(isset($licenseData['purchase_code']) && $licenseData['purchase_code']): ?>
                        <div class="license-info-card">
                            <div class="license-info-header">
                                <div class="license-info-icon">
                                    <i class="fas fa-barcode"></i>
                                </div>
                                <div class="license-info-title">
                                    <h3><?php echo e(trans('app.purchase_code')); ?></h3>
                                    <p><?php echo e(trans('app.purchase_code_info')); ?></p>
                                </div>
                            </div>
                            <div class="license-info-content">
                                <div class="license-info-row">
                                    <div class="license-info-label">
                                        <i class="fas fa-barcode"></i>
                                        <?php echo e(trans('app.purchase_code')); ?>

                                    </div>
                                    <div class="license-info-value">
                                        <span id="purchaseCode" class="purchase-code"><?php echo e($licenseData['purchase_code']); ?></span>
                                        <button class="copy-btn" data-copy-target="purchaseCode">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if(isset($success) && !$success && isset($error)): ?>
            <div id="errorMessage" class="user-card user-card-error">
                <div class="user-card-header">
                    <div class="user-card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo e(trans('app.verification_error')); ?>

                    </div>
                </div>
                <div class="user-card-content">
                    <div class="user-error-container">
                        <div class="user-error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="user-error-content">
                            <h3 class="user-error-title">
                                <?php echo e(trans('app.verification_error')); ?>

                            </h3>
                            <p id="errorText" class="user-error-text"><?php echo e($error); ?></p>
                            <?php if(isset($validationErrors)): ?>
                                <div class="validation-errors">
                                    <h4><?php echo e(trans('app.validation_errors')); ?>:</h4>
                                    <ul>
                                        <?php $__currentLoopData = $validationErrors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($error); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="user-actions-grid">
                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon indigo">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="user-action-content">
                            <h3><?php echo e(trans('app.Get Support')); ?></h3>
                            <p><?php echo e(trans('app.Need help with your license?')); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('support.tickets.create')); ?>" class="user-action-button">
                        <i class="fas fa-ticket-alt"></i>
                        <?php echo e(trans('app.Contact Support')); ?>

                    </a>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon purple">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="user-action-content">
                            <h3><?php echo e(trans('app.Knowledge Base')); ?></h3>
                            <p><?php echo e(trans('app.Find guides and tutorials')); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('kb.index')); ?>" class="user-action-button">
                        <i class="fas fa-search"></i>
                        <?php echo e(trans('app.Explore KB')); ?>

                    </a>
                </div>

                <div class="user-action-card">
                    <div class="user-action-header">
                        <div class="user-action-icon blue">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="user-action-content">
                            <h3><?php echo e(trans('app.My Licenses')); ?></h3>
                            <p><?php echo e(trans('app.Manage your licenses')); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('user.licenses.index')); ?>" class="user-action-button">
                        <i class="fas fa-list"></i>
                        <?php echo e(trans('app.View Licenses')); ?>

                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- License History Modal -->
    <div id="historyModal" class="user-modal hidden">
        <div class="user-modal-content license-history-modal">
            <div class="user-modal-header">
                <div class="user-modal-title">
                    <i class="fas fa-history"></i>
                    <?php echo e(trans('app.license_history')); ?>

                </div>
                <button id="closeHistoryModal" class="user-modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="user-modal-body">
                <!-- License Summary -->
                <div class="license-history-summary">
                    <div class="history-summary-card">
                        <div class="history-summary-header">
                            <div class="history-summary-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="history-summary-content">
                                <h4 class="history-summary-title"><?php echo e(trans('app.License Summary')); ?></h4>
                                <p class="history-summary-subtitle"><?php echo e(trans('app.Overview of license activity')); ?></p>
                            </div>
                        </div>
                        <div class="history-summary-stats">
                            <div class="history-stat-item">
                                <div class="history-stat-value" id="totalChecks">0</div>
                                <div class="history-stat-label"><?php echo e(trans('app.Total Checks')); ?></div>
                            </div>
                            <div class="history-stat-item">
                                <div class="history-stat-value" id="lastCheck">-</div>
                                <div class="history-stat-label"><?php echo e(trans('app.Last Check')); ?></div>
                            </div>
                            <div class="history-stat-item">
                                <div class="history-stat-value" id="activeDomains">0</div>
                                <div class="history-stat-label"><?php echo e(trans('app.Active Domains')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Timeline -->
                <div class="license-history-timeline">
                    <div class="history-timeline-header">
                        <h4 class="history-timeline-title">
                            <i class="fas fa-clock"></i>
                            <?php echo e(trans('app.Activity Timeline')); ?>

                        </h4>
                    </div>
                    <div id="historyContent" class="user-history-content">
                        <!-- History content will be populated here -->
                    </div>
                </div>

                <!-- History Actions -->
                <div class="history-modal-actions">
                    <button class="user-btn user-btn-outline" id="exportHistoryBtn">
                        <i class="fas fa-download"></i>
                        <?php echo e(trans('app.Export History')); ?>

                    </button>
                    <button class="user-btn user-btn-primary" id="refreshHistoryBtn">
                        <i class="fas fa-sync-alt"></i>
                        <?php echo e(trans('app.Refresh')); ?>

                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/license-status.blade.php ENDPATH**/ ?>