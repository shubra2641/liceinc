<?php $__env->startSection('admin-content'); ?>
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-edit text-primary me-2"></i>
                                <?php echo e(trans('app.Edit User')); ?>

                            </h1>
                            <p class="text-muted mb-0"><?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</p>
                        </div>
                        <div>
                            <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="btn btn-info me-2">
                                <i class="fas fa-eye me-1"></i>
                                <?php echo e(trans('app.View User')); ?>

                            </a>
                            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                <?php echo e(trans('app.Back to Users')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('admin.users.update', $user)); ?>" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- User Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            <?php echo e(trans('app.User Information')); ?>

                            <span class="badge bg-light text-primary ms-2"><?php echo e(trans('app.Required')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    <?php echo e(trans('app.Full Name')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="name" name="name" value="<?php echo e(old('name', $user->name)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter full name')); ?>" required>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-success me-1"></i>
                                    <?php echo e(trans('app.Email Address')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter email address')); ?>" required>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock text-warning me-1"></i>
                                    <?php echo e(trans('app.New Password')); ?>

                                </label>
                                <input type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="password" name="password" 
                                       placeholder="<?php echo e(trans('app.Leave empty to keep current password')); ?>">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo e(trans('app.Leave empty to keep current password')); ?>

                                </div>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock text-warning me-1"></i>
                                    <?php echo e(trans('app.Confirm New Password')); ?>

                                </label>
                                <input type="password" class="form-control <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="password_confirmation" name="password_confirmation" 
                                       placeholder="<?php echo e(trans('app.Confirm new password')); ?>">
                                <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-user-shield text-purple me-1"></i>
                                <?php echo e(trans('app.User Role')); ?> <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="role" id="role_user" value="user" 
                                               <?php echo e(old('role', $user->role) == 'user' ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="role_user">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.Regular User')); ?></strong>
                                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Can access basic features')); ?></p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check p-3 border rounded">
                                        <input class="form-check-input" type="radio" name="role" id="role_admin" value="admin" 
                                               <?php echo e(old('role', $user->role) == 'admin' ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="role_admin">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-shield text-danger me-2"></i>
                                                <div>
                                                    <strong><?php echo e(trans('app.Administrator')); ?></strong>
                                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Full system access')); ?></p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-danger small"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-address-card me-2"></i>
                            <?php echo e(trans('app.Client Information')); ?>

                            <span class="badge bg-light text-success ms-2"><?php echo e(trans('app.Optional')); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    <?php echo e(trans('app.First Name')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="firstname" name="firstname" value="<?php echo e(old('firstname', $user->firstname)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter first name')); ?>">
                                <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    <?php echo e(trans('app.Last Name')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="lastname" name="lastname" value="<?php echo e(old('lastname', $user->lastname)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter last name')); ?>">
                                <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="companyname" class="form-label">
                                    <i class="fas fa-building text-purple me-1"></i>
                                    <?php echo e(trans('app.Company Name')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['companyname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="companyname" name="companyname" value="<?php echo e(old('companyname', $user->companyname)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter company name')); ?>">
                                <?php $__errorArgs = ['companyname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phonenumber" class="form-label">
                                    <i class="fas fa-phone text-success me-1"></i>
                                    <?php echo e(trans('app.Phone Number')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['phonenumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="phonenumber" name="phonenumber" value="<?php echo e(old('phonenumber', $user->phonenumber)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter phone number')); ?>">
                                <?php $__errorArgs = ['phonenumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address1" class="form-label">
                                    <i class="fas fa-map-marker-alt text-warning me-1"></i>
                                    <?php echo e(trans('app.Address Line 1')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['address1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="address1" name="address1" value="<?php echo e(old('address1', $user->address1)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter address line 1')); ?>">
                                <?php $__errorArgs = ['address1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address2" class="form-label">
                                    <i class="fas fa-map-marker-alt text-warning me-1"></i>
                                    <?php echo e(trans('app.Address Line 2')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['address2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="address2" name="address2" value="<?php echo e(old('address2', $user->address2)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter address line 2 (optional)')); ?>">
                                <?php $__errorArgs = ['address2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city text-info me-1"></i>
                                    <?php echo e(trans('app.City')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="city" name="city" value="<?php echo e(old('city', $user->city)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter city')); ?>">
                                <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">
                                    <i class="fas fa-map text-info me-1"></i>
                                    <?php echo e(trans('app.State/Province')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="state" name="state" value="<?php echo e(old('state', $user->state)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter state or province')); ?>">
                                <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="postcode" class="form-label">
                                    <i class="fas fa-mailbox text-danger me-1"></i>
                                    <?php echo e(trans('app.Postal Code')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['postcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="postcode" name="postcode" value="<?php echo e(old('postcode', $user->postcode)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter postal code')); ?>">
                                <?php $__errorArgs = ['postcode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="country" class="form-label">
                                    <i class="fas fa-globe text-danger me-1"></i>
                                    <?php echo e(trans('app.Country')); ?>

                                </label>
                                <input type="text" class="form-control <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="country" name="country" value="<?php echo e(old('country', $user->country)); ?>" 
                                       placeholder="<?php echo e(trans('app.Enter country')); ?>">
                                <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- User Preview -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>
                            <?php echo e(trans('app.User Preview')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div id="user-preview" class="p-3 rounded border">
                                <i class="fas fa-user-circle fs-1 text-primary mb-2"></i>
                                <h5 id="preview-name"><?php echo e($user->name); ?></h5>
                                <p id="preview-email" class="text-muted small mb-0"><?php echo e($user->email); ?></p>
                                <span id="preview-role" class="badge bg-<?php echo e($user->role == 'admin' ? 'danger' : 'secondary'); ?> mt-2">
                                    <?php echo e($user->role == 'admin' ? trans('app.Administrator') : trans('app.User')); ?>

                                </span>
                            </div>
                            <p class="text-muted small mt-2"><?php echo e(trans('app.Live Preview')); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            <?php echo e(trans('app.Quick Stats')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-primary"><?php echo e($user->licenses_count ?? 0); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Licenses')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-success"><?php echo e($user->invoices_count ?? 0); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Invoices')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info"><?php echo e($user->tickets_count ?? 0); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Tickets')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning"><?php echo e($user->orders_count ?? 0); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Orders')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cog me-2"></i>
                            <?php echo e(trans('app.User Settings')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo e(old('is_active', $user->status === 'active') ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                <?php echo e(trans('app.Active User')); ?>

                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo e(trans('app.User can login and access the system')); ?>

                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_verified" name="email_verified" value="1"
                                   <?php echo e(old('email_verified', $user->email_verified_at ? true : false) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="email_verified">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <?php echo e(trans('app.Email Verified')); ?>

                            </label>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo e(trans('app.User email is verified')); ?>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php echo e(trans('app.Account Information')); ?>

                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-info"><?php echo e($user->created_at->format('M Y')); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Created')); ?></p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card">
                                    <h4 class="text-warning"><?php echo e($user->updated_at->format('M Y')); ?></h4>
                                    <p class="text-muted small mb-0"><?php echo e(trans('app.Updated')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i><?php echo e(trans('app.Cancel')); ?>

                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php echo e(trans('app.Save Changes')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Danger Zone -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e(trans('app.Danger Zone')); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3"><?php echo e(trans('app.Delete User Warning')); ?></p>
                    <form method="post" action="<?php echo e(route('admin.users.destroy', $user)); ?>" 
                          data-confirm="delete-user">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash me-1"></i><?php echo e(trans('app.Delete User')); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views\admin\users\edit.blade.php ENDPATH**/ ?>