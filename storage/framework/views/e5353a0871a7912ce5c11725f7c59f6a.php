

<?php $__env->startSection('admin-content'); ?>
<!-- Admin Profile Edit Page -->
<div class="admin-profile-edit">
<div class="admin-page-header modern-header">
    <div class="admin-page-header-content">
        <div class="admin-page-title">
            <h1 class="gradient-text"><?php echo e(trans('app.My Profile')); ?></h1>
            <p class="admin-page-subtitle"><?php echo e(trans('app.Manage your account information and settings')); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                <?php echo e(trans('app.Back to Dashboard')); ?>

            </a>
        </div>
    </div>
</div>

<?php if($errors->any()): ?>
<div class="admin-alert admin-alert-error mb-6">
    <div class="admin-alert-content">
        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
        <div class="admin-alert-text">
            <h4><?php echo e(trans('app.Validation Errors')); ?></h4>
            <ul class="mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(session('success')): ?>
<div class="admin-alert admin-alert-success mb-6">
    <div class="admin-alert-content">
        <i class="fas fa-check-circle admin-alert-icon"></i>
        <div class="admin-alert-text">
            <h4><?php echo e(trans('app.Success')); ?></h4>
            <p><?php echo e(session('success')); ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="admin-alert admin-alert-error mb-6">
    <div class="admin-alert-content">
        <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
        <div class="admin-alert-text">
            <h4><?php echo e(trans('app.Error')); ?></h4>
            <p><?php echo e(session('error')); ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content Grid -->
<div class="admin-content">
    <div class="row g-4">
        <!-- Main Content Area (2/3 width) -->
        <div class="col-lg-8">
            <!-- Profile Information Section -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-user text-primary me-2"></i>
                        <?php echo e(trans('app.Profile Information')); ?>

                    </h3>
                    <span class="admin-badge admin-badge-primary"><?php echo e(trans('app.Required')); ?></span>
                </div>
                <div class="admin-card-content">
                    <form method="POST" action="<?php echo e(route('admin.profile.update')); ?>" id="profile-form" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('patch'); ?>
                        
                        <!-- Personal Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i><?php echo e(trans('app.Personal Information')); ?>

                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label required" for="name">
                                        <i class="fas fa-user text-blue-500 me-1"></i><?php echo e(trans('app.Full Name')); ?>

                                    </label>
                                    <input type="text" id="name" name="name" class="admin-form-input"
                                           value="<?php echo e(old('name', $user->name)); ?>" required placeholder="<?php echo e(trans('app.Enter full name')); ?>">
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label required" for="email">
                                        <i class="fas fa-envelope text-green-500 me-1"></i><?php echo e(trans('app.Email Address')); ?>

                                    </label>
                                    <input type="email" id="email" name="email" class="admin-form-input"
                                           value="<?php echo e(old('email', $user->email)); ?>" required placeholder="<?php echo e(trans('app.Enter email address')); ?>">
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="firstname">
                                        <i class="fas fa-user text-blue-500 me-1"></i><?php echo e(trans('app.First Name')); ?>

                                    </label>
                                    <input type="text" id="firstname" name="firstname" class="admin-form-input"
                                           value="<?php echo e(old('firstname', $user->firstname)); ?>" placeholder="<?php echo e(trans('app.Enter first name')); ?>">
                                    <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="lastname">
                                        <i class="fas fa-user text-blue-500 me-1"></i><?php echo e(trans('app.Last Name')); ?>

                                    </label>
                                    <input type="text" id="lastname" name="lastname" class="admin-form-input"
                                           value="<?php echo e(old('lastname', $user->lastname)); ?>" placeholder="<?php echo e(trans('app.Enter last name')); ?>">
                                    <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="companyname">
                                        <i class="fas fa-building text-purple-500 me-1"></i><?php echo e(trans('app.Company Name')); ?>

                                    </label>
                                    <input type="text" id="companyname" name="companyname" class="admin-form-input"
                                           value="<?php echo e(old('companyname', $user->companyname)); ?>" placeholder="<?php echo e(trans('app.Enter company name')); ?>">
                                    <?php $__errorArgs = ['companyname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="phonenumber">
                                        <i class="fas fa-phone text-green-500 me-1"></i><?php echo e(trans('app.Phone Number')); ?>

                                    </label>
                                    <input type="text" id="phonenumber" name="phonenumber" class="admin-form-input"
                                           value="<?php echo e(old('phonenumber', $user->phonenumber)); ?>" placeholder="<?php echo e(trans('app.Enter phone number')); ?>">
                                    <?php $__errorArgs = ['phonenumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo e(trans('app.Contact Information')); ?>

                                </h5>
                            </div>
                            
                            <div class="col-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="address1">
                                        <i class="fas fa-map-marker-alt text-orange-500 me-1"></i><?php echo e(trans('app.Address Line 1')); ?>

                                    </label>
                                    <input type="text" id="address1" name="address1" class="admin-form-input"
                                           value="<?php echo e(old('address1', $user->address1)); ?>" placeholder="<?php echo e(trans('app.Enter address line 1')); ?>">
                                    <?php $__errorArgs = ['address1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="address2">
                                        <i class="fas fa-map-marker-alt text-orange-500 me-1"></i><?php echo e(trans('app.Address Line 2')); ?>

                                    </label>
                                    <input type="text" id="address2" name="address2" class="admin-form-input"
                                           value="<?php echo e(old('address2', $user->address2)); ?>" placeholder="<?php echo e(trans('app.Enter address line 2 (optional)')); ?>">
                                    <?php $__errorArgs = ['address2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="city">
                                        <i class="fas fa-city text-indigo-500 me-1"></i><?php echo e(trans('app.City')); ?>

                                    </label>
                                    <input type="text" id="city" name="city" class="admin-form-input"
                                           value="<?php echo e(old('city', $user->city)); ?>" placeholder="<?php echo e(trans('app.Enter city')); ?>">
                                    <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="state">
                                        <i class="fas fa-map text-indigo-500 me-1"></i><?php echo e(trans('app.State/Province')); ?>

                                    </label>
                                    <input type="text" id="state" name="state" class="admin-form-input"
                                           value="<?php echo e(old('state', $user->state)); ?>" placeholder="<?php echo e(trans('app.Enter state or province')); ?>">
                                    <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="postcode">
                                        <i class="fas fa-mailbox text-red-500 me-1"></i><?php echo e(trans('app.Postal Code')); ?>

                                    </label>
                                    <input type="text" id="postcode" name="postcode" class="admin-form-input"
                                           value="<?php echo e(old('postcode', $user->postcode)); ?>" placeholder="<?php echo e(trans('app.Enter postal code')); ?>">
                                    <?php $__errorArgs = ['postcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="country">
                                        <i class="fas fa-globe text-red-500 me-1"></i><?php echo e(trans('app.Country')); ?>

                                    </label>
                                    <input type="text" id="country" name="country" class="admin-form-input"
                                           value="<?php echo e(old('country', $user->country)); ?>" placeholder="<?php echo e(trans('app.Enter country')); ?>">
                                    <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                                <i class="fas fa-save me-2"></i><?php echo e(trans('app.Save Changes')); ?>

                            </button>
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                                <i class="fas fa-times me-2"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings Section -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-shield-alt text-red-500 me-2"></i>
                        <?php echo e(trans('app.Security Settings')); ?>

                    </h3>
                    <span class="admin-badge admin-badge-warning"><?php echo e(trans('app.Change Password')); ?></span>
                </div>
                <div class="admin-card-content">
                    <form method="POST" action="<?php echo e(route('admin.profile.update-password')); ?>" id="password-form" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('patch'); ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="current_password">
                                        <i class="fas fa-lock text-orange-500 me-1"></i><?php echo e(trans('app.Current Password')); ?>

                                    </label>
                                    <input type="password" id="current_password" name="current_password" class="admin-form-input"
                                           placeholder="<?php echo e(trans('app.Enter current password')); ?>" required>
                                    <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="password">
                                        <i class="fas fa-lock text-green-500 me-1"></i><?php echo e(trans('app.New Password')); ?>

                                    </label>
                                    <input type="password" id="password" name="password" class="admin-form-input"
                                           placeholder="<?php echo e(trans('app.Enter new password')); ?>" required>
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label" for="password_confirmation">
                                        <i class="fas fa-lock text-green-500 me-1"></i><?php echo e(trans('app.Confirm New Password')); ?>

                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="admin-form-input"
                                           placeholder="<?php echo e(trans('app.Confirm new password')); ?>" required>
                                    <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="admin-form-error"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-m">
                                <i class="fas fa-save me-2"></i><?php echo e(trans('app.Update Password')); ?>

                            </button>
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-secondary admin-btn-m">
                                <i class="fas fa-times me-2"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3 width) -->
        <div class="col-lg-4">
            <!-- Envato Integration Card -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bolt text-orange-500 me-2"></i>
                        <?php echo e(trans('app.Envato Integration')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">

                    <?php if($user->envato_username): ?>
                        <div class="admin-alert admin-alert-success">
                            <div class="admin-alert-content">
                                <i class="fas fa-check-circle admin-alert-icon"></i>
                                <div class="admin-alert-text">
                                    <h4><?php echo e(trans('app.Connected to Envato')); ?></h4>
                                    <p><strong><?php echo e(trans('app.Username')); ?>:</strong> <?php echo e($user->envato_username); ?></p>
                                    <?php if($user->envato_id): ?>
                                        <p><strong><?php echo e(trans('app.Envato ID')); ?>:</strong> <?php echo e($user->envato_id); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo e(route('admin.profile.disconnect-envato')); ?>" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-s w-100" 
                                    data-confirm="<?php echo e(trans('app.Are you sure you want to disconnect from Envato?')); ?>">
                                <i class="fas fa-unlink me-1"></i>
                                <?php echo e(trans('app.Disconnect Envato Account')); ?>

                            </button>
                        </form>
                    <?php elseif($hasApiConfig): ?>
                        <div class="admin-alert admin-alert-info">
                            <div class="admin-alert-content">
                                <i class="fas fa-info-circle admin-alert-icon"></i>
                                <div class="admin-alert-text">
                                    <h4><?php echo e(trans('app.Envato API Configured')); ?></h4>
                                    <p><?php echo e(trans('app.Envato API is configured and ready to connect your account.')); ?></p>
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo e(route('admin.profile.connect-envato')); ?>" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-s w-100">
                                <i class="fas fa-link me-1"></i>
                                <?php echo e(trans('app.Connect Envato Account')); ?>

                            </button>
                        </form>
                    <?php else: ?>
                        <div class="admin-alert admin-alert-warning">
                            <div class="admin-alert-content">
                                <i class="fas fa-exclamation-triangle admin-alert-icon"></i>
                                <div class="admin-alert-text">
                                    <h4><?php echo e(trans('app.Envato API Not Configured')); ?></h4>
                                    <p><?php echo e(trans('app.Please configure Envato API settings first to connect your account.')); ?></p>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo e(route('admin.settings.index')); ?>" class="admin-btn admin-btn-secondary admin-btn-s w-100">
                            <i class="fas fa-cog me-1"></i>
                            <?php echo e(trans('app.Configure Envato API')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Account Statistics Card -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-chart-bar text-blue-500 me-2"></i>
                        <?php echo e(trans('app.Account Statistics')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <div class="admin-stats-grid">
                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-key text-blue-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value"><?php echo e($user->licenses()->count()); ?></div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Total Licenses')); ?></div>
                            </div>
                        </div>

                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value"><?php echo e($user->licenses()->where('status', 'active')->count()); ?></div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Active Licenses')); ?></div>
                            </div>
                        </div>

                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-ticket-alt text-orange-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value"><?php echo e($user->tickets()->count()); ?></div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Support Tickets')); ?></div>
                            </div>
                        </div>

                        <div class="admin-stat-item">
                            <div class="admin-stat-icon">
                                <i class="fas fa-calendar text-purple-500"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value"><?php echo e($user->created_at->format('M d, Y')); ?></div>
                                <div class="admin-stat-label"><?php echo e(trans('app.Member Since')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="admin-card">
                <div class="admin-section-content">
                    <h3 class="admin-card-title">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        <?php echo e(trans('app.Quick Actions')); ?>

                    </h3>
                </div>
                <div class="admin-card-content">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn admin-btn-primary">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            <?php echo e(trans('app.Dashboard')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.settings.index')); ?>" class="admin-btn admin-btn-secondary">
                            <i class="fas fa-cog me-1"></i>
                            <?php echo e(trans('app.Settings')); ?>

                        </a>
                        <a href="<?php echo e(route('logout')); ?>" class="admin-btn admin-btn-danger logout-btn">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            <?php echo e(trans('app.Logout')); ?>

                        </a>
                    </div>
                    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                        <?php echo csrf_field(); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\profile\edit.blade.php ENDPATH**/ ?>