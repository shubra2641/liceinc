

<?php $__env->startSection('title', trans('install.settings_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="install-card">
    <div class="install-card-header">
        <div class="install-card-icon">
            <i class="fas fa-sliders-h"></i>
        </div>
        <h1 class="install-card-title"><?php echo e(trans('install.settings_title')); ?></h1>
        <p class="install-card-subtitle"><?php echo e(trans('install.settings_subtitle')); ?></p>
    </div>

    <form method="POST" action="<?php echo e(route('install.settings.store')); ?>" class="install-form" id="settings-form">
        <?php echo csrf_field(); ?>
        
        <div class="install-card-body">
            <div class="form-group">
                <label for="site_name" class="form-label">
                    <i class="fas fa-globe"></i>
                    <?php echo e(trans('install.site_name')); ?>

                </label>
                <input type="text" 
                       id="site_name" 
                       name="site_name" 
                       class="form-input" 
                       value="<?php echo e(old('site_name', 'License Management System')); ?>" 
                       required>
                <?php $__errorArgs = ['site_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="site_description" class="form-label">
                    <i class="fas fa-info-circle"></i>
                    <?php echo e(trans('install.site_description')); ?>

                </label>
                <textarea id="site_description" 
                          name="site_description" 
                          class="form-textarea" 
                          rows="3"><?php echo e(old('site_description', 'Professional license management and verification system')); ?></textarea>
                <?php $__errorArgs = ['site_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="admin_email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    <?php echo e(trans('install.admin_email')); ?>

                </label>
                <input type="email" 
                       id="admin_email" 
                       name="admin_email" 
                       class="form-input" 
                       value="<?php echo e(old('admin_email')); ?>">
                <?php $__errorArgs = ['admin_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-hint"><?php echo e(trans('install.admin_email_hint')); ?></div>
            </div>

            <div class="form-group">
                <label for="timezone" class="form-label">
                    <i class="fas fa-clock"></i>
                    <?php echo e(trans('install.timezone')); ?>

                </label>
                <select id="timezone" name="timezone" class="form-select" required>
                    <?php $__currentLoopData = $timezones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php echo e(old('timezone', 'UTC') == $value ? 'selected' : ''); ?>>
                            <?php echo e($label); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['timezone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label for="locale" class="form-label">
                    <i class="fas fa-language"></i>
                    <?php echo e(trans('install.default_language')); ?>

                </label>
                <select id="locale" name="locale" class="form-select" required>
                    <option value="en" <?php echo e(old('locale', app()->getLocale()) == 'en' ? 'selected' : ''); ?>>
                        English
                    </option>
                    <option value="ar" <?php echo e(old('locale', app()->getLocale()) == 'ar' ? 'selected' : ''); ?>>
                        العربية
                    </option>
                </select>
                <?php $__errorArgs = ['locale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="form-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Email Configuration Section -->
            <div class="install-section">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i>
                    <?php echo e(trans('install.email_configuration')); ?>

                </h3>
                <p class="section-subtitle"><?php echo e(trans('install.email_configuration_subtitle')); ?></p>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" id="enable_email" name="enable_email" value="1" <?php echo e(old('enable_email') ? 'checked' : ''); ?>>
                        <span class="checkmark"></span>
                        <?php echo e(trans('install.enable_email_notifications')); ?>

                    </label>
                    <div class="form-hint"><?php echo e(trans('install.enable_email_hint')); ?></div>
                    <noscript>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <?php echo e(trans('install.javascript_required_for_email_settings')); ?>

                        </div>
                    </noscript>
                </div>

                <div id="email-settings" class="email-settings <?php echo e(old('enable_email') ? 'd-block' : 'd-none'); ?>">
                    <div class="form-group">
                        <label for="mail_mailer" class="form-label">
                            <i class="fas fa-server"></i>
                            <?php echo e(trans('install.mail_mailer')); ?>

                        </label>
                        <select id="mail_mailer" name="mail_mailer" class="form-select">
                            <option value="smtp" <?php echo e(old('mail_mailer', 'smtp') == 'smtp' ? 'selected' : ''); ?>>SMTP</option>
                            <option value="mailgun" <?php echo e(old('mail_mailer') == 'mailgun' ? 'selected' : ''); ?>>Mailgun</option>
                            <option value="ses" <?php echo e(old('mail_mailer') == 'ses' ? 'selected' : ''); ?>>Amazon SES</option>
                            <option value="postmark" <?php echo e(old('mail_mailer') == 'postmark' ? 'selected' : ''); ?>>Postmark</option>
                        </select>
                        <?php $__errorArgs = ['mail_mailer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="mail_host" class="form-label">
                            <i class="fas fa-globe"></i>
                            <?php echo e(trans('install.mail_host')); ?>

                        </label>
                        <input type="text" 
                               id="mail_host" 
                               name="mail_host" 
                               class="form-input" 
                               value="<?php echo e(old('mail_host', 'smtp.gmail.com')); ?>" 
                               placeholder="smtp.gmail.com">
                        <?php $__errorArgs = ['mail_host'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="mail_port" class="form-label">
                                <i class="fas fa-plug"></i>
                                <?php echo e(trans('install.mail_port')); ?>

                            </label>
                            <input type="number" 
                                   id="mail_port" 
                                   name="mail_port" 
                                   class="form-input" 
                                   value="<?php echo e(old('mail_port', '587')); ?>" 
                                   placeholder="587">
                            <?php $__errorArgs = ['mail_port'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="mail_encryption" class="form-label">
                                <i class="fas fa-lock"></i>
                                <?php echo e(trans('install.mail_encryption')); ?>

                            </label>
                            <select id="mail_encryption" name="mail_encryption" class="form-select">
                                <option value="tls" <?php echo e(old('mail_encryption', 'tls') == 'tls' ? 'selected' : ''); ?>>TLS</option>
                                <option value="ssl" <?php echo e(old('mail_encryption') == 'ssl' ? 'selected' : ''); ?>>SSL</option>
                                <option value="" <?php echo e(old('mail_encryption') == '' ? 'selected' : ''); ?>>None</option>
                            </select>
                            <?php $__errorArgs = ['mail_encryption'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mail_username" class="form-label">
                            <i class="fas fa-user"></i>
                            <?php echo e(trans('install.mail_username')); ?>

                        </label>
                        <input type="text" 
                               id="mail_username" 
                               name="mail_username" 
                               class="form-input" 
                               value="<?php echo e(old('mail_username')); ?>" 
                               placeholder="your-email@gmail.com">
                        <?php $__errorArgs = ['mail_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="mail_password" class="form-label">
                            <i class="fas fa-key"></i>
                            <?php echo e(trans('install.mail_password')); ?>

                        </label>
                        <input type="password" 
                               id="mail_password" 
                               name="mail_password" 
                               class="form-input" 
                               value="<?php echo e(old('mail_password')); ?>" 
                               placeholder="<?php echo e(trans('install.mail_password_placeholder')); ?>">
                        <?php $__errorArgs = ['mail_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <div class="form-hint"><?php echo e(trans('install.mail_password_hint')); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="mail_from_address" class="form-label">
                            <i class="fas fa-at"></i>
                            <?php echo e(trans('install.mail_from_address')); ?>

                        </label>
                        <input type="email" 
                               id="mail_from_address" 
                               name="mail_from_address" 
                               class="form-input" 
                               value="<?php echo e(old('mail_from_address')); ?>" 
                               placeholder="noreply@yourdomain.com">
                        <?php $__errorArgs = ['mail_from_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label for="mail_from_name" class="form-label">
                            <i class="fas fa-signature"></i>
                            <?php echo e(trans('install.mail_from_name')); ?>

                        </label>
                        <input type="text" 
                               id="mail_from_name" 
                               name="mail_from_name" 
                               class="form-input" 
                               value="<?php echo e(old('mail_from_name', 'License Management System')); ?>" 
                               placeholder="<?php echo e(trans('install.mail_from_name_placeholder')); ?>">
                        <?php $__errorArgs = ['mail_from_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="install-actions">
            <a href="<?php echo e(route('install.admin')); ?>" class="install-btn install-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span><?php echo e(trans('install.back')); ?></span>
            </a>
            
            <button type="submit" class="install-btn install-btn-primary">
                <i class="fas fa-arrow-right"></i>
                <span><?php echo e(trans('install.continue')); ?></span>
            </button>
        </div>
    </form>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('install.layout', ['step' => 6], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\install\settings.blade.php ENDPATH**/ ?>