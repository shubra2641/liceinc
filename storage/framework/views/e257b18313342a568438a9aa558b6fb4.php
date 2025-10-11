<?php $__env->startSection('title', trans('app.My Profile')); ?>
<?php $__env->startSection('page-title', trans('app.My Profile')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Manage your account information and settings')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-user-cog"></i>
                <?php echo e(trans('app.My Profile')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Manage your account information and settings')); ?>

            </p>
        </div>

        <div class="user-card-content">
                    <?php if(session('status')): ?>
                        <div class="user-alert user-alert-success mb-4">
                            <div class="user-alert-content">
                                <i class="fas fa-check-circle user-alert-icon"></i>
                                <div class="user-alert-text">
                                    <?php if(session('status') == 'profile-updated'): ?>
                                        <h4><?php echo e(trans('app.Profile Updated')); ?></h4>
                                        <p><?php echo e(trans('app.Your profile has been updated successfully.')); ?></p>
                                    <?php elseif(session('status') == 'envato-unlinked'): ?>
                                        <h4><?php echo e(trans('app.Envato Account Unlinked')); ?></h4>
                                        <p><?php echo e(trans('app.Your Envato account has been unlinked successfully.')); ?></p>
                                    <?php else: ?>
                                        <h4><?php echo e(trans('app.Success')); ?></h4>
                                        <p><?php echo e(session('status')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="user-alert user-alert-error mb-4">
                            <div class="user-alert-content">
                                <i class="fas fa-exclamation-triangle user-alert-icon"></i>
                                <div class="user-alert-text">
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

                    <!-- Profile Overview -->
                    <div class="profile-overview">
                        <div class="profile-avatar">
                            <div class="avatar-circle">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-info">
                                <h3><?php echo e(auth()->user()->name); ?></h3>
                                <p><?php echo e(auth()->user()->email); ?></p>
                                <span class="member-since"><?php echo e(trans('app.Member since')); ?> <?php echo e(auth()->user()->created_at->format('M Y')); ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo e(auth()->user()->licenses()->count()); ?></div>
                                <div class="stat-label"><?php echo e(trans('app.Licenses')); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo e(auth()->user()->tickets()->count()); ?></div>
                                <div class="stat-label"><?php echo e(trans('app.Tickets')); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo e(auth()->user()->invoices()->count()); ?></div>
                                <div class="stat-label"><?php echo e(trans('app.Invoices')); ?></div>
                            </div>
                        </div>
                    </div>

            <!-- Main Content Grid -->
            <div class="user-profile-main-grid">
                <!-- Forms Section -->
                <div class="user-profile-forms-section">
                    <!-- Personal Information -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-user"></i>
                                <?php echo e(trans('app.Personal Information')); ?>

                            </div>
                            <p class="user-card-subtitle"><?php echo e(trans('app.Update your personal details')); ?></p>
                        </div>
                        <div class="user-card-content">
                            <form action="<?php echo e(route('profile.update')); ?>" method="POST" class="user-profile-form">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>

                                <div class="user-form-grid">
                                    <div class="user-form-group">
                                        <label for="name" class="user-form-label"><?php echo e(trans('app.Full Name')); ?> *</label>
                                        <input type="text" id="name" name="name" value="<?php echo e(old('name', auth()->user()->name)); ?>" class="form-input form-input-error" required>
                                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="email" class="user-form-label"><?php echo e(trans('app.Email Address')); ?> *</label>
                                        <input type="email" id="email" name="email" value="<?php echo e(old('email', auth()->user()->email)); ?>" class="form-input form-input-error" required>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="firstname" class="user-form-label"><?php echo e(trans('app.First Name')); ?></label>
                                        <input type="text" id="firstname" name="firstname" value="<?php echo e(old('firstname', auth()->user()->firstname)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="lastname" class="user-form-label"><?php echo e(trans('app.Last Name')); ?></label>
                                        <input type="text" id="lastname" name="lastname" value="<?php echo e(old('lastname', auth()->user()->lastname)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="companyname" class="user-form-label"><?php echo e(trans('app.Company Name')); ?></label>
                                        <input type="text" id="companyname" name="companyname" value="<?php echo e(old('companyname', auth()->user()->companyname)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['companyname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="phonenumber" class="user-form-label"><?php echo e(trans('app.Phone Number')); ?></label>
                                        <input type="text" id="phonenumber" name="phonenumber" value="<?php echo e(old('phonenumber', auth()->user()->phonenumber)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['phonenumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="envato_username" class="user-form-label"><?php echo e(trans('app.Envato Username')); ?></label>
                                        <input type="text" id="envato_username" name="envato_username" value="<?php echo e(old('envato_username', auth()->user()->envato_username)); ?>" class="form-input form-input-error" placeholder="Enter your Envato username">
                                        <?php $__errorArgs = ['envato_username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="envato_id" class="user-form-label"><?php echo e(trans('app.Envato ID')); ?></label>
                                        <input type="text" id="envato_id" name="envato_id" value="<?php echo e(old('envato_id', auth()->user()->envato_id)); ?>" class="form-input form-input-error" placeholder="Enter your Envato ID" readonly>
                                        <p class="user-form-help"><?php echo e(trans('app.This field is automatically set when you connect your Envato account')); ?></p>
                                        <?php $__errorArgs = ['envato_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="user-form-actions">
                                    <button type="button" class="user-action-button user-action-button-outline"><?php echo e(trans('app.Cancel')); ?></button>
                                    <button type="submit" class="user-action-button user-action-button-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo e(trans('app.Save Changes')); ?>

                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo e(trans('app.Contact Information')); ?>

                            </div>
                            <p class="user-card-subtitle"><?php echo e(trans('app.Update your contact details')); ?></p>
                        </div>
                        <div class="user-card-content">
                            <form action="<?php echo e(route('profile.update')); ?>" method="POST" class="user-profile-form">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>

                                <div class="user-form-grid">
                                    <div class="user-form-group">
                                        <label for="address1" class="user-form-label"><?php echo e(trans('app.Address Line 1')); ?></label>
                                        <input type="text" id="address1" name="address1" value="<?php echo e(old('address1', auth()->user()->address1)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['address1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="address2" class="user-form-label"><?php echo e(trans('app.Address Line 2')); ?></label>
                                        <input type="text" id="address2" name="address2" value="<?php echo e(old('address2', auth()->user()->address2)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['address2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="city" class="user-form-label"><?php echo e(trans('app.City')); ?></label>
                                        <input type="text" id="city" name="city" value="<?php echo e(old('city', auth()->user()->city)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="state" class="user-form-label"><?php echo e(trans('app.State/Province')); ?></label>
                                        <input type="text" id="state" name="state" value="<?php echo e(old('state', auth()->user()->state)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="postcode" class="user-form-label"><?php echo e(trans('app.Postal Code')); ?></label>
                                        <input type="text" id="postcode" name="postcode" value="<?php echo e(old('postcode', auth()->user()->postcode)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['postcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="user-form-group">
                                        <label for="country" class="user-form-label"><?php echo e(trans('app.Country')); ?></label>
                                        <input type="text" id="country" name="country" value="<?php echo e(old('country', auth()->user()->country)); ?>" class="form-input form-input-error">
                                        <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="user-form-error"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>

                                <div class="user-form-actions">
                                    <button type="button" class="user-action-button user-action-button-outline"><?php echo e(trans('app.Cancel')); ?></button>
                                    <button type="submit" class="user-action-button user-action-button-primary">
                                        <i class="fas fa-save"></i>
                                        <?php echo e(trans('app.Save Changes')); ?>

                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="user-profile-sidebar">
                    <!-- Envato Integration -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-link"></i>
                                <?php echo e(trans('app.Envato Integration')); ?>

                            </div>
                            <p class="user-card-subtitle"><?php echo e(trans('app.Manage your Envato account connection')); ?></p>
                        </div>
                        <div class="user-card-content">
                            <?php if(auth()->user()->hasEnvatoAccount()): ?>
                                <div class="user-envato-connected">
                                    <div class="user-envato-status">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <span><?php echo e(trans('app.Connected to Envato')); ?></span>
                                    </div>
                                    <p class="user-envato-username"><?php echo e(auth()->user()->envato_username); ?></p>
                                    <button class="user-action-button user-action-button-outline" data-action="unlink-envato">
                                        <i class="fas fa-unlink"></i>
                                        <?php echo e(trans('app.Unlink Envato Account')); ?>

                                    </button>
                                    <noscript>
                                        <form method="POST" action="<?php echo e(route('profile.unlink-envato')); ?>" class="mt-2">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="user-action-button user-action-button-outline" data-confirm="<?php echo e(trans('app.are_you_sure_unlink_envato')); ?>">
                                                <i class="fas fa-unlink"></i>
                                                <?php echo e(trans('app.Unlink Envato Account')); ?>

                                            </button>
                                        </form>
                                    </noscript>
                                </div>
                            <?php else: ?>
                                <div class="user-envato-connect">
                                    <div class="user-envato-icon">
                                        <i class="fas fa-link"></i>
                                    </div>
                                    <h3><?php echo e(trans('app.Connect Your Envato Account')); ?></h3>
                                    <p><?php echo e(trans('app.Link Your Envato Account To Verify Purchases And Access Exclusive Features.')); ?></p>
                                    <a href="<?php echo e(route('envato.link')); ?>" class="user-action-button user-action-button-primary">
                                        <i class="fas fa-link"></i>
                                        <?php echo e(trans('app.Connect Envato Account')); ?>

                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Account Statistics -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-chart-bar"></i>
                                <?php echo e(trans('app.Account Statistics')); ?>

                            </div>
                            <p class="user-card-subtitle"><?php echo e(trans('app.Your account overview')); ?></p>
                        </div>
                        <div class="user-card-content">
                            <div class="user-stats-list">
                                <div class="user-stat-item">
                                    <div class="user-stat-icon blue">
                                        <i class="fas fa-key"></i>
                                    </div>
                                    <div class="user-stat-info">
                                        <div class="user-stat-number"><?php echo e(auth()->user()->licenses->count()); ?></div>
                                        <div class="user-stat-label"><?php echo e(trans('app.Total Licenses')); ?></div>
                                    </div>
                                </div>
                                <div class="user-stat-item">
                                    <div class="user-stat-icon green">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="user-stat-info">
                                        <div class="user-stat-number"><?php echo e(auth()->user()->licenses->where('status', 'active')->count()); ?></div>
                                        <div class="user-stat-label"><?php echo e(trans('app.Active Licenses')); ?></div>
                                    </div>
                                </div>
                                <div class="user-stat-item">
                                    <div class="user-stat-icon yellow">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="user-stat-info">
                                        <div class="user-stat-number"><?php echo e(auth()->user()->tickets->count()); ?></div>
                                        <div class="user-stat-label"><?php echo e(trans('app.Support Tickets')); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\profile\index.blade.php ENDPATH**/ ?>