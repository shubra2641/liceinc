<?php $__env->startSection('admin-content'); ?>
<!-- Admin Envato Guide Page -->
<div class="admin-envato-guide">
<div class="admin-page-header modern-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1 class="gradient-text"><?php echo e(trans('app.envato_oauth_app_setup_guide')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.complete_guide_to_create_and_configure_your_envato_oauth_application')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.settings.index')); ?>" class="admin-btn admin-btn-info admin-btn-m">
                <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                <?php echo e(trans('app.back_to_settings')); ?>

            </a>
            <a href="https://build.envato.com/my-apps/" target="_blank" class="admin-btn admin-btn-secondary admin-btn-m">
                <i class="fas fa-external-link-alt w-4 h-4 mr-2"></i>
                <?php echo e(trans('app.create_envato_app')); ?>

            </a>
        </div>
    </div>
</div>

<div class="admin-content">
    <div class="row g-4">
        <!-- Step 1 -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <span class="admin-badge admin-badge-primary me-3">1</span>
                        <i class="fas fa-plus-circle text-blue-500 me-2"></i><?php echo e(trans('app.create_envato_app')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <p><?php echo e(trans('app.go_to')); ?> <a href="https://build.envato.com/my-apps/" target="_blank" class="text-primary"><?php echo e(trans('app.envato_my_apps')); ?></a> <?php echo e(trans('app.and_create_a_new_oauth_application')); ?></p>

                    <div class="admin-alert admin-alert-warning my-4">
                        <div class="admin-alert-content">
                            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <p>
                                    <strong><?php echo e(trans('app.Note')); ?>:</strong> <?php echo e(trans('app.you_need_an_active_envato_account_with_purchased_items_to_create_oauth_apps')); ?>

                                </p>
                            </div>
                        </div>
                    </div>

                    <h4><?php echo e(trans('app.app_information')); ?>:</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong><?php echo e(trans('app.app_name')); ?>:</strong> <?php echo e(trans('app.your_application_name_eg_my_license_manager')); ?></li>
                        <li class="mb-2"><strong><?php echo e(trans('app.app_website')); ?>:</strong> <?php echo e(trans('app.your_website_url')); ?></li>
                        <li class="mb-2"><strong><?php echo e(trans('app.app_description')); ?>:</strong> <?php echo e(trans('app.brief_description_of_your_application')); ?></li>
                        <li class="mb-2"><strong><?php echo e(trans('app.icon')); ?>:</strong> <?php echo e(trans('app.upload_your_app_icon_optional')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <span class="admin-badge admin-badge-primary me-3">2</span>
                        <i class="fas fa-link text-green-500 me-2"></i><?php echo e(trans('app.configure_redirect_uri')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <p><?php echo e(trans('app.in_your_envato_app_settings_set_the')); ?> <strong><?php echo e(trans('app.redirect_uri')); ?></strong> <?php echo e(trans('app.to')); ?>:</p>

                    <div class="admin-code-block my-4">
                        <?php echo e(config('app.url')); ?>/auth/envato/callback
                    </div>

                    <p><?php echo e(trans('app.this_uri_is_automatically_set_in_your_settings_above_make_sure_it_matches_exactly')); ?></p>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <span class="admin-badge admin-badge-primary me-3">3</span>
                        <i class="fas fa-shield-alt text-red-500 me-2"></i><?php echo e(trans('app.set_permissions')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <p><?php echo e(trans('app.configure_the_following_permissions_for_your_app')); ?>:</p>

                    <div class="admin-alert admin-alert-success my-4">
                        <div class="admin-alert-content">
                            <i class="fas fa-check-circle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <h4><?php echo e(trans('app.Required_permissions')); ?>:</h4>
                                <ul class="list-unstyled mt-3">
                                    <li class="mb-3">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-check text-success me-2 mt-1"></i>
                                            <div>
                                                <strong><?php echo e(trans('app.view_your_account_username')); ?></strong>
                                                <p class="text-muted mb-0"><?php echo e(trans('app.Required_for_user_authentication')); ?></p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-check text-success me-2 mt-1"></i>
                                            <div>
                                                <strong><?php echo e(trans('app.view_your_account_email_address')); ?></strong>
                                                <p class="text-muted mb-0"><?php echo e(trans('app.Required_for_user_identification')); ?></p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-check text-success me-2 mt-1"></i>
                                            <div>
                                                <strong><?php echo e(trans('app.verify_purchases')); ?></strong>
                                                <p class="text-muted mb-0"><?php echo e(trans('app.Required_for_license_verification')); ?></p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <span class="admin-badge admin-badge-primary me-3">4</span>
                        <i class="fas fa-code text-purple-500 me-2"></i><?php echo e(trans('app.add_credentials')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <p><?php echo e(trans('app.add_the_following_credentials_to_your_env_file')); ?>:</p>

                    <div class="admin-alert admin-alert-info my-4">
                        <div class="admin-alert-content">
                            <i class="fas fa-info-circle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <h4><?php echo e(trans('app.environment_variables')); ?>:</h4>
                                <div class="admin-code-block mt-3">
                                    <div class="admin-code-line">ENVATO_CLIENT_ID=<code class="text-warning">your_client_id_here</code></div>
                                    <div class="admin-code-line">ENVATO_CLIENT_SECRET=<code class="text-warning">your_client_secret_here</code></div>
                                    <div class="admin-code-line">ENVATO_REDIRECT_URI=<code class="text-warning"><?php echo e(url('/auth/envato/callback')); ?></code></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-alert admin-alert-warning my-4">
                        <div class="admin-alert-content">
                            <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                            <div class="admin-alert-text">
                                <h4><?php echo e(trans('app.important_notes')); ?>:</h4>
                                <ul class="list-unstyled mt-3">
                                    <li class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        <?php echo e(trans('app.make_sure_the_redirect_uri_matches_exactly')); ?>

                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        <?php echo e(trans('app.clear_config_cache_after_updating_env')); ?>

                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <span class="admin-badge admin-badge-primary me-3">5</span>
                        <i class="fas fa-cogs text-orange-500 me-2"></i><?php echo e(trans('app.configure_your_application')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <p><?php echo e(trans('app.go_back_to_your_settings_page_and')); ?>:</p>

                    <div class="admin-card admin-card-success my-4">
                        <div class="admin-section-content">
                            <h4 class="admin-card-title">
                                <i class="fas fa-list-check text-success me-2"></i>
                                <?php echo e(trans('app.configuration_steps')); ?>

                            </h4>
                        </div>
                        <div class="admin-card-content">
                            <ol class="list-unstyled">
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="admin-badge admin-badge-success me-3 mt-1">1</span>
                                        <div>
                                            <strong><?php echo e(trans('app.enter_your_client_id_and_client_secret')); ?></strong>
                                            <p class="text-muted mb-0"><?php echo e(trans('app.add_the_credentials_from_step_4')); ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="admin-badge admin-badge-success me-3 mt-1">2</span>
                                        <div>
                                            <strong><?php echo e(trans('app.verify_the_redirect_uri_is_correct')); ?></strong>
                                            <p class="text-muted mb-0"><?php echo e(trans('app.ensure_it_matches_your_envato_app_settings')); ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="admin-badge admin-badge-success me-3 mt-1">3</span>
                                        <div>
                                            <strong><?php echo e(trans('app.enable_envato_oauth_if_you_want_to_allow_user_login')); ?></strong>
                                            <p class="text-muted mb-0"><?php echo e(trans('app.Optional_for_purchase_verification_only')); ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="admin-badge admin-badge-success me-3 mt-1">4</span>
                                        <div>
                                            <strong><?php echo e(trans('app.test_your_api_connection_using_the_test_api_connection_button')); ?></strong>
                                            <p class="text-muted mb-0"><?php echo e(trans('app.verify_everything_is_working_correctly')); ?></p>
                                        </div>
                                    </div>
                                </li>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <span class="admin-badge admin-badge-success me-3 mt-1">5</span>
                                        <div>
                                            <strong><?php echo e(trans('app.save_your_settings')); ?></strong>
                                            <p class="text-muted mb-0"><?php echo e(trans('app.dont_forget_to_save_your_changes')); ?></p>
                                        </div>
                                    </div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API vs OAuth -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-info-circle text-blue-500 me-2"></i><?php echo e(trans('app.understanding_api_vs_oauth')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="admin-card admin-card-primary">
                                <div class="admin-section-content">
                                    <h4 class="admin-card-title">
                                        <i class="fas fa-key text-purple-500 me-2"></i><?php echo e(trans('app.personal_token_api')); ?>

                                    </h4>
                                </div>
                                <div class="admin-card-content">
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-shield-alt text-purple-500 me-3 mt-1"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.license_verification')); ?></strong>
                                                    <p class="text-muted mb-0"><?php echo e(trans('app.used_for_license_verification_and_purchase_validation')); ?></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-lock text-purple-500 me-3 mt-1"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.server_side_only')); ?></strong>
                                                    <p class="text-muted mb-0"><?php echo e(trans('app.server_side_only_never_exposed_to_users')); ?></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-star text-purple-500 me-3 mt-1"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.core_functionality')); ?></strong>
                                                    <p class="text-muted mb-0"><?php echo e(trans('app.Required_for_core_functionality')); ?></p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-card admin-card-success">
                                <div class="admin-section-content">
                                    <h4 class="admin-card-title">
                                        <i class="fas fa-user-circle text-green-500 me-2"></i><?php echo e(trans('app.oauth')); ?>

                                    </h4>
                                </div>
                                <div class="admin-card-content">
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-sign-in-alt text-green-500 me-3 mt-1"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.User_login')); ?></strong>
                                                    <p class="text-muted mb-0"><?php echo e(trans('app.allows_users_to_login_with_envato_account')); ?></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-toggle-on text-green-500 me-3 mt-1"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.Optional_feature')); ?></strong>
                                                    <p class="text-muted mb-0"><?php echo e(trans('app.Optional_feature_for_user_convenience')); ?></p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-cog text-green-500 me-3 mt-1"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.separate_configuration')); ?></strong>
                                                    <p class="text-muted mb-0"><?php echo e(trans('app.requires_separate_app_configuration')); ?></p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="col-12">
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-tools text-red-500 me-2"></i><?php echo e(trans('app.troubleshooting')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="admin-card admin-card-danger">
                                <div class="admin-section-content">
                                    <h4 class="admin-card-title">
                                        <i class="fas fa-exclamation-triangle text-red-500 me-2"></i><?php echo e(trans('app.api_test_failed')); ?>

                                    </h4>
                                </div>
                                <div class="admin-card-content">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-red-500 me-3"></i>
                                            <?php echo e(trans('app.check_that_your_personal_token_is_correct')); ?>

                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-red-500 me-3"></i>
                                            <?php echo e(trans('app.ensure_your_envato_account_has_purchased_items')); ?>

                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-red-500 me-3"></i>
                                            <?php echo e(trans('app.verify_the_token_has_the_required_permissions')); ?>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="admin-card admin-card-warning">
                                <div class="admin-section-content">
                                    <h4 class="admin-card-title">
                                        <i class="fas fa-user-times text-orange-500 me-2"></i><?php echo e(trans('app.oauth_login_not_working')); ?>

                                    </h4>
                                </div>
                                <div class="admin-card-content">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-orange-500 me-3"></i>
                                            <?php echo e(trans('app.verify_client_id_and_client_secret_are_correct')); ?>

                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-orange-500 me-3"></i>
                                            <?php echo e(trans('app.check_that_redirect_uri_matches_exactly')); ?>

                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-orange-500 me-3"></i>
                                            <?php echo e(trans('app.ensure_oauth_is_enabled_in_settings')); ?>

                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-orange-500 me-3"></i>
                                            <?php echo e(trans('app.confirm_app_permissions_are_set_correctly')); ?>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex flex-column flex-sm-row gap-3">
        <a href="<?php echo e(route('admin.settings.index')); ?>" class="admin-btn admin-btn-primary admin-btn-m">
            <i class="fas fa-arrow-left me-2"></i>
            <?php echo e(trans('app.back_to_settings')); ?>

        </a>
        <a href="https://build.envato.com/my-apps/" target="_blank" class="admin-btn admin-btn-secondary admin-btn-m">
            <?php echo e(trans('app.create_envato_app')); ?>

            <i class="fas fa-external-link-alt ms-2"></i>
        </a>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\settings\envato-guide.blade.php ENDPATH**/ ?>