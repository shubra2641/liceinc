

<?php $__env->startSection('title', trans('app.Create Account')); ?>
<?php $__env->startSection('page-title', trans('app.Join Us Today')); ?>
<?php $__env->startSection('page-subtitle', trans('app.Create your account to get started')); ?>
<?php $__env->startSection('app.Description', trans('app.Create a new account with email and password or sign up with Envato OAuth')); ?>


<?php $__env->startSection('seo_title', $siteSeoTitle ?? trans('app.Create Account')); ?>
<?php $__env->startSection('meta_description', $siteSeoDescription ?? trans('app.Create a new account with email and password or sign up with Envato OAuth')); ?>

<?php $__env->startSection('content'); ?>
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-user-plus"></i>
                <?php echo e(trans('app.Join Us Today')); ?>

            </div>
            <p class="user-card-subtitle">
                <?php echo e(trans('app.Create your account to get started with our premium services')); ?>

            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Registration Form -->
                <div class="register-form-section">
                    <!-- Envato OAuth Register -->
                    <?php if(\App\Helpers\EnvatoHelper::isConfigured()): ?>
                    <div class="envato-auth-section">
                        <a href="<?php echo e(route('auth.envato')); ?>" class="envato-auth-button">
                            <i class="fas fa-external-link-alt"></i>
                            <?php echo e(trans('app.Continue with Envato')); ?>

                        </a>
                        
                        <div class="auth-divider">
                            <div class="auth-divider-line"></div>
                            <span class="auth-divider-text"><?php echo e(trans('app.Or create an account')); ?></span>
                            <div class="auth-divider-line"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" action="<?php echo e(route('register')); ?>" class="register-form" novalidate>
                        <?php echo csrf_field(); ?>

                        <div class="form-fields-grid">
                            <!-- First Name -->
                            <div class="form-field-group">
                                <label for="firstname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    <?php echo e(trans('app.First Name')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="firstname" name="firstname" type="text"
                                        class="form-input <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        value="<?php echo e(old('firstname')); ?>" required autofocus autocomplete="given-name"
                                        placeholder="<?php echo e(trans('app.Enter your first name')); ?>" />
                                </div>
                                <?php $__errorArgs = ['firstname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Last Name -->
                            <div class="form-field-group">
                                <label for="lastname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    <?php echo e(trans('app.Last Name')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="lastname" name="lastname" type="text"
                                        class="form-input <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        value="<?php echo e(old('lastname')); ?>" required autocomplete="family-name"
                                        placeholder="<?php echo e(trans('app.Enter your last name')); ?>" />
                                </div>
                                <?php $__errorArgs = ['lastname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Email Address -->
                            <div class="form-field-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo e(trans('app.Email Address')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="email" name="email" type="email"
                                        class="form-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        value="<?php echo e(old('email')); ?>" required autocomplete="username"
                                        placeholder="<?php echo e(trans('app.Enter your email address')); ?>" />
                                </div>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Password -->
                            <div class="form-field-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    <?php echo e(trans('app.Password')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="register-password" name="password" type="password"
                                        class="form-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        required autocomplete="new-password" placeholder="<?php echo e(trans('app.Enter your password')); ?>" />
                                    <button type="button" id="toggle-password" class="form-input-toggle">
                                        <i class="fas fa-eye" id="password-show"></i>
                                        <i class="fas fa-eye-slash hidden" id="password-hide"></i>
                                    </button>
                                </div>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-field-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    <?php echo e(trans('app.Confirm Password')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        class="form-input <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        required autocomplete="new-password" placeholder="<?php echo e(trans('app.Confirm your password')); ?>" />
                                </div>
                                <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-field-group">
                                <label for="phonenumber" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    <?php echo e(trans('app.Phone Number')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="phonenumber" name="phonenumber" type="tel"
                                        class="form-input <?php $__errorArgs = ['phonenumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        value="<?php echo e(old('phonenumber')); ?>" autocomplete="tel"
                                        placeholder="<?php echo e(trans('app.Enter your phone number')); ?>" />
                                </div>
                                <?php $__errorArgs = ['phonenumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Country -->
                            <div class="form-field-group">
                                <label for="country" class="form-label">
                                    <i class="fas fa-globe"></i>
                                    <?php echo e(trans('app.Country')); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <select id="country" name="country"
                                        class="form-select <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value=""><?php echo e(trans('app.Select your country')); ?></option>
                    <option value="US" <?php echo e(old('country') == 'US' ? 'selected' : ''); ?>>United States</option>
                    <option value="CA" <?php echo e(old('country') == 'CA' ? 'selected' : ''); ?>>Canada</option>
                    <option value="GB" <?php echo e(old('country') == 'GB' ? 'selected' : ''); ?>>United Kingdom</option>
                    <option value="AU" <?php echo e(old('country') == 'AU' ? 'selected' : ''); ?>>Australia</option>
                    <option value="DE" <?php echo e(old('country') == 'DE' ? 'selected' : ''); ?>>Germany</option>
                    <option value="FR" <?php echo e(old('country') == 'FR' ? 'selected' : ''); ?>>France</option>
                    <option value="IT" <?php echo e(old('country') == 'IT' ? 'selected' : ''); ?>>Italy</option>
                    <option value="ES" <?php echo e(old('country') == 'ES' ? 'selected' : ''); ?>>Spain</option>
                    <option value="NL" <?php echo e(old('country') == 'NL' ? 'selected' : ''); ?>>Netherlands</option>
                    <option value="SE" <?php echo e(old('country') == 'SE' ? 'selected' : ''); ?>>Sweden</option>
                    <option value="NO" <?php echo e(old('country') == 'NO' ? 'selected' : ''); ?>>Norway</option>
                    <option value="DK" <?php echo e(old('country') == 'DK' ? 'selected' : ''); ?>>Denmark</option>
                    <option value="FI" <?php echo e(old('country') == 'FI' ? 'selected' : ''); ?>>Finland</option>
                    <option value="CH" <?php echo e(old('country') == 'CH' ? 'selected' : ''); ?>>Switzerland</option>
                    <option value="AT" <?php echo e(old('country') == 'AT' ? 'selected' : ''); ?>>Austria</option>
                    <option value="BE" <?php echo e(old('country') == 'BE' ? 'selected' : ''); ?>>Belgium</option>
                    <option value="IE" <?php echo e(old('country') == 'IE' ? 'selected' : ''); ?>>Ireland</option>
                    <option value="PT" <?php echo e(old('country') == 'PT' ? 'selected' : ''); ?>>Portugal</option>
                    <option value="GR" <?php echo e(old('country') == 'GR' ? 'selected' : ''); ?>>Greece</option>
                    <option value="PL" <?php echo e(old('country') == 'PL' ? 'selected' : ''); ?>>Poland</option>
                    <option value="CZ" <?php echo e(old('country') == 'CZ' ? 'selected' : ''); ?>>Czech Republic</option>
                    <option value="HU" <?php echo e(old('country') == 'HU' ? 'selected' : ''); ?>>Hungary</option>
                    <option value="RO" <?php echo e(old('country') == 'RO' ? 'selected' : ''); ?>>Romania</option>
                    <option value="BG" <?php echo e(old('country') == 'BG' ? 'selected' : ''); ?>>Bulgaria</option>
                    <option value="HR" <?php echo e(old('country') == 'HR' ? 'selected' : ''); ?>>Croatia</option>
                    <option value="SI" <?php echo e(old('country') == 'SI' ? 'selected' : ''); ?>>Slovenia</option>
                    <option value="SK" <?php echo e(old('country') == 'SK' ? 'selected' : ''); ?>>Slovakia</option>
                    <option value="LT" <?php echo e(old('country') == 'LT' ? 'selected' : ''); ?>>Lithuania</option>
                    <option value="LV" <?php echo e(old('country') == 'LV' ? 'selected' : ''); ?>>Latvia</option>
                    <option value="EE" <?php echo e(old('country') == 'EE' ? 'selected' : ''); ?>>Estonia</option>
                    <option value="CY" <?php echo e(old('country') == 'CY' ? 'selected' : ''); ?>>Cyprus</option>
                    <option value="LU" <?php echo e(old('country') == 'LU' ? 'selected' : ''); ?>>Luxembourg</option>
                    <option value="MT" <?php echo e(old('country') == 'MT' ? 'selected' : ''); ?>>Malta</option>
                    <option value="JP" <?php echo e(old('country') == 'JP' ? 'selected' : ''); ?>>Japan</option>
                    <option value="KR" <?php echo e(old('country') == 'KR' ? 'selected' : ''); ?>>South Korea</option>
                    <option value="CN" <?php echo e(old('country') == 'CN' ? 'selected' : ''); ?>>China</option>
                    <option value="IN" <?php echo e(old('country') == 'IN' ? 'selected' : ''); ?>>India</option>
                    <option value="BR" <?php echo e(old('country') == 'BR' ? 'selected' : ''); ?>>Brazil</option>
                    <option value="MX" <?php echo e(old('country') == 'MX' ? 'selected' : ''); ?>>Mexico</option>
                    <option value="AR" <?php echo e(old('country') == 'AR' ? 'selected' : ''); ?>>Argentina</option>
                    <option value="CL" <?php echo e(old('country') == 'CL' ? 'selected' : ''); ?>>Chile</option>
                    <option value="CO" <?php echo e(old('country') == 'CO' ? 'selected' : ''); ?>>Colombia</option>
                    <option value="PE" <?php echo e(old('country') == 'PE' ? 'selected' : ''); ?>>Peru</option>
                    <option value="VE" <?php echo e(old('country') == 'VE' ? 'selected' : ''); ?>>Venezuela</option>
                    <option value="UY" <?php echo e(old('country') == 'UY' ? 'selected' : ''); ?>>Uruguay</option>
                    <option value="PY" <?php echo e(old('country') == 'PY' ? 'selected' : ''); ?>>Paraguay</option>
                    <option value="BO" <?php echo e(old('country') == 'BO' ? 'selected' : ''); ?>>Bolivia</option>
                    <option value="EC" <?php echo e(old('country') == 'EC' ? 'selected' : ''); ?>>Ecuador</option>
                    <option value="GY" <?php echo e(old('country') == 'GY' ? 'selected' : ''); ?>>Guyana</option>
                    <option value="SR" <?php echo e(old('country') == 'SR' ? 'selected' : ''); ?>>Suriname</option>
                    <option value="GF" <?php echo e(old('country') == 'GF' ? 'selected' : ''); ?>>French Guiana</option>
                    <option value="ZA" <?php echo e(old('country') == 'ZA' ? 'selected' : ''); ?>>South Africa</option>
                    <option value="EG" <?php echo e(old('country') == 'EG' ? 'selected' : ''); ?>>Egypt</option>
                    <option value="NG" <?php echo e(old('country') == 'NG' ? 'selected' : ''); ?>>Nigeria</option>
                    <option value="KE" <?php echo e(old('country') == 'KE' ? 'selected' : ''); ?>>Kenya</option>
                    <option value="GH" <?php echo e(old('country') == 'GH' ? 'selected' : ''); ?>>Ghana</option>
                    <option value="MA" <?php echo e(old('country') == 'MA' ? 'selected' : ''); ?>>Morocco</option>
                    <option value="TN" <?php echo e(old('country') == 'TN' ? 'selected' : ''); ?>>Tunisia</option>
                    <option value="DZ" <?php echo e(old('country') == 'DZ' ? 'selected' : ''); ?>>Algeria</option>
                    <option value="LY" <?php echo e(old('country') == 'LY' ? 'selected' : ''); ?>>Libya</option>
                    <option value="SD" <?php echo e(old('country') == 'SD' ? 'selected' : ''); ?>>Sudan</option>
                    <option value="ET" <?php echo e(old('country') == 'ET' ? 'selected' : ''); ?>>Ethiopia</option>
                    <option value="UG" <?php echo e(old('country') == 'UG' ? 'selected' : ''); ?>>Uganda</option>
                    <option value="TZ" <?php echo e(old('country') == 'TZ' ? 'selected' : ''); ?>>Tanzania</option>
                    <option value="ZW" <?php echo e(old('country') == 'ZW' ? 'selected' : ''); ?>>Zimbabwe</option>
                    <option value="BW" <?php echo e(old('country') == 'BW' ? 'selected' : ''); ?>>Botswana</option>
                    <option value="NA" <?php echo e(old('country') == 'NA' ? 'selected' : ''); ?>>Namibia</option>
                    <option value="ZM" <?php echo e(old('country') == 'ZM' ? 'selected' : ''); ?>>Zambia</option>
                    <option value="MW" <?php echo e(old('country') == 'MW' ? 'selected' : ''); ?>>Malawi</option>
                    <option value="MZ" <?php echo e(old('country') == 'MZ' ? 'selected' : ''); ?>>Mozambique</option>
                    <option value="MG" <?php echo e(old('country') == 'MG' ? 'selected' : ''); ?>>Madagascar</option>
                    <option value="MU" <?php echo e(old('country') == 'MU' ? 'selected' : ''); ?>>Mauritius</option>
                    <option value="SC" <?php echo e(old('country') == 'SC' ? 'selected' : ''); ?>>Seychelles</option>
                    <option value="RE" <?php echo e(old('country') == 'RE' ? 'selected' : ''); ?>>Réunion</option>
                    <option value="YT" <?php echo e(old('country') == 'YT' ? 'selected' : ''); ?>>Mayotte</option>
                    <option value="KM" <?php echo e(old('country') == 'KM' ? 'selected' : ''); ?>>Comoros</option>
                    <option value="DJ" <?php echo e(old('country') == 'DJ' ? 'selected' : ''); ?>>Djibouti</option>
                    <option value="SO" <?php echo e(old('country') == 'SO' ? 'selected' : ''); ?>>Somalia</option>
                    <option value="ER" <?php echo e(old('country') == 'ER' ? 'selected' : ''); ?>>Eritrea</option>
                    <option value="SS" <?php echo e(old('country') == 'SS' ? 'selected' : ''); ?>>South Sudan</option>
                    <option value="CF" <?php echo e(old('country') == 'CF' ? 'selected' : ''); ?>>Central African Republic</option>
                    <option value="TD" <?php echo e(old('country') == 'TD' ? 'selected' : ''); ?>>Chad</option>
                    <option value="NE" <?php echo e(old('country') == 'NE' ? 'selected' : ''); ?>>Niger</option>
                    <option value="ML" <?php echo e(old('country') == 'ML' ? 'selected' : ''); ?>>Mali</option>
                    <option value="BF" <?php echo e(old('country') == 'BF' ? 'selected' : ''); ?>>Burkina Faso</option>
                    <option value="CI" <?php echo e(old('country') == 'CI' ? 'selected' : ''); ?>>Côte d'Ivoire</option>
                    <option value="LR" <?php echo e(old('country') == 'LR' ? 'selected' : ''); ?>>Liberia</option>
                    <option value="SL" <?php echo e(old('country') == 'SL' ? 'selected' : ''); ?>>Sierra Leone</option>
                    <option value="GN" <?php echo e(old('country') == 'GN' ? 'selected' : ''); ?>>Guinea</option>
                    <option value="GW" <?php echo e(old('country') == 'GW' ? 'selected' : ''); ?>>Guinea-Bissau</option>
                    <option value="GM" <?php echo e(old('country') == 'GM' ? 'selected' : ''); ?>>Gambia</option>
                    <option value="SN" <?php echo e(old('country') == 'SN' ? 'selected' : ''); ?>>Senegal</option>
                    <option value="MR" <?php echo e(old('country') == 'MR' ? 'selected' : ''); ?>>Mauritania</option>
                    <option value="CV" <?php echo e(old('country') == 'CV' ? 'selected' : ''); ?>>Cape Verde</option>
                    <option value="ST" <?php echo e(old('country') == 'ST' ? 'selected' : ''); ?>>São Tomé and Príncipe</option>
                    <option value="GQ" <?php echo e(old('country') == 'GQ' ? 'selected' : ''); ?>>Equatorial Guinea</option>
                    <option value="GA" <?php echo e(old('country') == 'GA' ? 'selected' : ''); ?>>Gabon</option>
                    <option value="CG" <?php echo e(old('country') == 'CG' ? 'selected' : ''); ?>>Congo</option>
                    <option value="CD" <?php echo e(old('country') == 'CD' ? 'selected' : ''); ?>>Democratic Republic of the Congo</option>
                    <option value="AO" <?php echo e(old('country') == 'AO' ? 'selected' : ''); ?>>Angola</option>
                    <option value="CM" <?php echo e(old('country') == 'CM' ? 'selected' : ''); ?>>Cameroon</option>
                    <option value="CF" <?php echo e(old('country') == 'CF' ? 'selected' : ''); ?>>Central African Republic</option>
                    <option value="TD" <?php echo e(old('country') == 'TD' ? 'selected' : ''); ?>>Chad</option>
                    <option value="NE" <?php echo e(old('country') == 'NE' ? 'selected' : ''); ?>>Niger</option>
                    <option value="ML" <?php echo e(old('country') == 'ML' ? 'selected' : ''); ?>>Mali</option>
                    <option value="BF" <?php echo e(old('country') == 'BF' ? 'selected' : ''); ?>>Burkina Faso</option>
                    <option value="CI" <?php echo e(old('country') == 'CI' ? 'selected' : ''); ?>>Côte d'Ivoire</option>
                    <option value="LR" <?php echo e(old('country') == 'LR' ? 'selected' : ''); ?>>Liberia</option>
                    <option value="SL" <?php echo e(old('country') == 'SL' ? 'selected' : ''); ?>>Sierra Leone</option>
                    <option value="GN" <?php echo e(old('country') == 'GN' ? 'selected' : ''); ?>>Guinea</option>
                    <option value="GW" <?php echo e(old('country') == 'GW' ? 'selected' : ''); ?>>Guinea-Bissau</option>
                    <option value="GM" <?php echo e(old('country') == 'GM' ? 'selected' : ''); ?>>Gambia</option>
                    <option value="SN" <?php echo e(old('country') == 'SN' ? 'selected' : ''); ?>>Senegal</option>
                    <option value="MR" <?php echo e(old('country') == 'MR' ? 'selected' : ''); ?>>Mauritania</option>
                    <option value="CV" <?php echo e(old('country') == 'CV' ? 'selected' : ''); ?>>Cape Verde</option>
                    <option value="ST" <?php echo e(old('country') == 'ST' ? 'selected' : ''); ?>>São Tomé and Príncipe</option>
                    <option value="GQ" <?php echo e(old('country') == 'GQ' ? 'selected' : ''); ?>>Equatorial Guinea</option>
                    <option value="GA" <?php echo e(old('country') == 'GA' ? 'selected' : ''); ?>>Gabon</option>
                    <option value="CG" <?php echo e(old('country') == 'CG' ? 'selected' : ''); ?>>Congo</option>
                    <option value="CD" <?php echo e(old('country') == 'CD' ? 'selected' : ''); ?>>Democratic Republic of the Congo</option>
                    <option value="AO" <?php echo e(old('country') == 'AO' ? 'selected' : ''); ?>>Angola</option>
                    <option value="CM" <?php echo e(old('country') == 'CM' ? 'selected' : ''); ?>>Cameroon</option>
                    <option value="SA" <?php echo e(old('country') == 'SA' ? 'selected' : ''); ?>>Saudi Arabia</option>
                    <option value="AE" <?php echo e(old('country') == 'AE' ? 'selected' : ''); ?>>United Arab Emirates</option>
                    <option value="QA" <?php echo e(old('country') == 'QA' ? 'selected' : ''); ?>>Qatar</option>
                    <option value="KW" <?php echo e(old('country') == 'KW' ? 'selected' : ''); ?>>Kuwait</option>
                    <option value="BH" <?php echo e(old('country') == 'BH' ? 'selected' : ''); ?>>Bahrain</option>
                    <option value="OM" <?php echo e(old('country') == 'OM' ? 'selected' : ''); ?>>Oman</option>
                    <option value="YE" <?php echo e(old('country') == 'YE' ? 'selected' : ''); ?>>Yemen</option>
                    <option value="IQ" <?php echo e(old('country') == 'IQ' ? 'selected' : ''); ?>>Iraq</option>
                    <option value="IR" <?php echo e(old('country') == 'IR' ? 'selected' : ''); ?>>Iran</option>
                    <option value="TR" <?php echo e(old('country') == 'TR' ? 'selected' : ''); ?>>Turkey</option>
                    <option value="IL" <?php echo e(old('country') == 'IL' ? 'selected' : ''); ?>>Israel</option>
                    <option value="PS" <?php echo e(old('country') == 'PS' ? 'selected' : ''); ?>>Palestine</option>
                    <option value="JO" <?php echo e(old('country') == 'JO' ? 'selected' : ''); ?>>Jordan</option>
                    <option value="LB" <?php echo e(old('country') == 'LB' ? 'selected' : ''); ?>>Lebanon</option>
                    <option value="SY" <?php echo e(old('country') == 'SY' ? 'selected' : ''); ?>>Syria</option>
                    <option value="CY" <?php echo e(old('country') == 'CY' ? 'selected' : ''); ?>>Cyprus</option>
                    <option value="RU" <?php echo e(old('country') == 'RU' ? 'selected' : ''); ?>>Russia</option>
                    <option value="UA" <?php echo e(old('country') == 'UA' ? 'selected' : ''); ?>>Ukraine</option>
                    <option value="BY" <?php echo e(old('country') == 'BY' ? 'selected' : ''); ?>>Belarus</option>
                    <option value="MD" <?php echo e(old('country') == 'MD' ? 'selected' : ''); ?>>Moldova</option>
                    <option value="GE" <?php echo e(old('country') == 'GE' ? 'selected' : ''); ?>>Georgia</option>
                    <option value="AM" <?php echo e(old('country') == 'AM' ? 'selected' : ''); ?>>Armenia</option>
                    <option value="AZ" <?php echo e(old('country') == 'AZ' ? 'selected' : ''); ?>>Azerbaijan</option>
                    <option value="KZ" <?php echo e(old('country') == 'KZ' ? 'selected' : ''); ?>>Kazakhstan</option>
                    <option value="UZ" <?php echo e(old('country') == 'UZ' ? 'selected' : ''); ?>>Uzbekistan</option>
                    <option value="TM" <?php echo e(old('country') == 'TM' ? 'selected' : ''); ?>>Turkmenistan</option>
                    <option value="TJ" <?php echo e(old('country') == 'TJ' ? 'selected' : ''); ?>>Tajikistan</option>
                    <option value="KG" <?php echo e(old('country') == 'KG' ? 'selected' : ''); ?>>Kyrgyzstan</option>
                    <option value="AF" <?php echo e(old('country') == 'AF' ? 'selected' : ''); ?>>Afghanistan</option>
                    <option value="PK" <?php echo e(old('country') == 'PK' ? 'selected' : ''); ?>>Pakistan</option>
                    <option value="BD" <?php echo e(old('country') == 'BD' ? 'selected' : ''); ?>>Bangladesh</option>
                    <option value="LK" <?php echo e(old('country') == 'LK' ? 'selected' : ''); ?>>Sri Lanka</option>
                    <option value="MV" <?php echo e(old('country') == 'MV' ? 'selected' : ''); ?>>Maldives</option>
                    <option value="BT" <?php echo e(old('country') == 'BT' ? 'selected' : ''); ?>>Bhutan</option>
                    <option value="NP" <?php echo e(old('country') == 'NP' ? 'selected' : ''); ?>>Nepal</option>
                    <option value="MM" <?php echo e(old('country') == 'MM' ? 'selected' : ''); ?>>Myanmar</option>
                    <option value="TH" <?php echo e(old('country') == 'TH' ? 'selected' : ''); ?>>Thailand</option>
                    <option value="LA" <?php echo e(old('country') == 'LA' ? 'selected' : ''); ?>>Laos</option>
                    <option value="KH" <?php echo e(old('country') == 'KH' ? 'selected' : ''); ?>>Cambodia</option>
                    <option value="VN" <?php echo e(old('country') == 'VN' ? 'selected' : ''); ?>>Vietnam</option>
                    <option value="MY" <?php echo e(old('country') == 'MY' ? 'selected' : ''); ?>>Malaysia</option>
                    <option value="SG" <?php echo e(old('country') == 'SG' ? 'selected' : ''); ?>>Singapore</option>
                    <option value="ID" <?php echo e(old('country') == 'ID' ? 'selected' : ''); ?>>Indonesia</option>
                    <option value="PH" <?php echo e(old('country') == 'PH' ? 'selected' : ''); ?>>Philippines</option>
                    <option value="TW" <?php echo e(old('country') == 'TW' ? 'selected' : ''); ?>>Taiwan</option>
                    <option value="HK" <?php echo e(old('country') == 'HK' ? 'selected' : ''); ?>>Hong Kong</option>
                    <option value="MO" <?php echo e(old('country') == 'MO' ? 'selected' : ''); ?>>Macau</option>
                    <option value="MN" <?php echo e(old('country') == 'MN' ? 'selected' : ''); ?>>Mongolia</option>
                    <option value="KP" <?php echo e(old('country') == 'KP' ? 'selected' : ''); ?>>North Korea</option>
                    <option value="FJ" <?php echo e(old('country') == 'FJ' ? 'selected' : ''); ?>>Fiji</option>
                    <option value="PG" <?php echo e(old('country') == 'PG' ? 'selected' : ''); ?>>Papua New Guinea</option>
                    <option value="SB" <?php echo e(old('country') == 'SB' ? 'selected' : ''); ?>>Solomon Islands</option>
                    <option value="VU" <?php echo e(old('country') == 'VU' ? 'selected' : ''); ?>>Vanuatu</option>
                    <option value="NC" <?php echo e(old('country') == 'NC' ? 'selected' : ''); ?>>New Caledonia</option>
                    <option value="PF" <?php echo e(old('country') == 'PF' ? 'selected' : ''); ?>>French Polynesia</option>
                    <option value="WS" <?php echo e(old('country') == 'WS' ? 'selected' : ''); ?>>Samoa</option>
                    <option value="TO" <?php echo e(old('country') == 'TO' ? 'selected' : ''); ?>>Tonga</option>
                    <option value="KI" <?php echo e(old('country') == 'KI' ? 'selected' : ''); ?>>Kiribati</option>
                    <option value="TV" <?php echo e(old('country') == 'TV' ? 'selected' : ''); ?>>Tuvalu</option>
                    <option value="NR" <?php echo e(old('country') == 'NR' ? 'selected' : ''); ?>>Nauru</option>
                    <option value="PW" <?php echo e(old('country') == 'PW' ? 'selected' : ''); ?>>Palau</option>
                    <option value="FM" <?php echo e(old('country') == 'FM' ? 'selected' : ''); ?>>Micronesia</option>
                    <option value="MH" <?php echo e(old('country') == 'MH' ? 'selected' : ''); ?>>Marshall Islands</option>
                    <option value="CK" <?php echo e(old('country') == 'CK' ? 'selected' : ''); ?>>Cook Islands</option>
                    <option value="NU" <?php echo e(old('country') == 'NU' ? 'selected' : ''); ?>>Niue</option>
                    <option value="TK" <?php echo e(old('country') == 'TK' ? 'selected' : ''); ?>>Tokelau</option>
                    <option value="WF" <?php echo e(old('country') == 'WF' ? 'selected' : ''); ?>>Wallis and Futuna</option>
                    <option value="AS" <?php echo e(old('country') == 'AS' ? 'selected' : ''); ?>>American Samoa</option>
                    <option value="GU" <?php echo e(old('country') == 'GU' ? 'selected' : ''); ?>>Guam</option>
                    <option value="MP" <?php echo e(old('country') == 'MP' ? 'selected' : ''); ?>>Northern Mariana Islands</option>
                    <option value="VI" <?php echo e(old('country') == 'VI' ? 'selected' : ''); ?>>U.S. Virgin Islands</option>
                    <option value="PR" <?php echo e(old('country') == 'PR' ? 'selected' : ''); ?>>Puerto Rico</option>
                    <option value="DO" <?php echo e(old('country') == 'DO' ? 'selected' : ''); ?>>Dominican Republic</option>
                    <option value="HT" <?php echo e(old('country') == 'HT' ? 'selected' : ''); ?>>Haiti</option>
                    <option value="JM" <?php echo e(old('country') == 'JM' ? 'selected' : ''); ?>>Jamaica</option>
                    <option value="TT" <?php echo e(old('country') == 'TT' ? 'selected' : ''); ?>>Trinidad and Tobago</option>
                    <option value="BB" <?php echo e(old('country') == 'BB' ? 'selected' : ''); ?>>Barbados</option>
                    <option value="LC" <?php echo e(old('country') == 'LC' ? 'selected' : ''); ?>>Saint Lucia</option>
                    <option value="VC" <?php echo e(old('country') == 'VC' ? 'selected' : ''); ?>>Saint Vincent and the Grenadines</option>
                    <option value="GD" <?php echo e(old('country') == 'GD' ? 'selected' : ''); ?>>Grenada</option>
                    <option value="AG" <?php echo e(old('country') == 'AG' ? 'selected' : ''); ?>>Antigua and Barbuda</option>
                    <option value="KN" <?php echo e(old('country') == 'KN' ? 'selected' : ''); ?>>Saint Kitts and Nevis</option>
                    <option value="DM" <?php echo e(old('country') == 'DM' ? 'selected' : ''); ?>>Dominica</option>
                    <option value="BZ" <?php echo e(old('country') == 'BZ' ? 'selected' : ''); ?>>Belize</option>
                    <option value="GT" <?php echo e(old('country') == 'GT' ? 'selected' : ''); ?>>Guatemala</option>
                    <option value="SV" <?php echo e(old('country') == 'SV' ? 'selected' : ''); ?>>El Salvador</option>
                    <option value="HN" <?php echo e(old('country') == 'HN' ? 'selected' : ''); ?>>Honduras</option>
                    <option value="NI" <?php echo e(old('country') == 'NI' ? 'selected' : ''); ?>>Nicaragua</option>
                    <option value="CR" <?php echo e(old('country') == 'CR' ? 'selected' : ''); ?>>Costa Rica</option>
                    <option value="PA" <?php echo e(old('country') == 'PA' ? 'selected' : ''); ?>>Panama</option>
                    <option value="CU" <?php echo e(old('country') == 'CU' ? 'selected' : ''); ?>>Cuba</option>
                    <option value="BS" <?php echo e(old('country') == 'BS' ? 'selected' : ''); ?>>Bahamas</option>
                    <option value="BM" <?php echo e(old('country') == 'BM' ? 'selected' : ''); ?>>Bermuda</option>
                    <option value="GL" <?php echo e(old('country') == 'GL' ? 'selected' : ''); ?>>Greenland</option>
                    <option value="IS" <?php echo e(old('country') == 'IS' ? 'selected' : ''); ?>>Iceland</option>
                    <option value="FO" <?php echo e(old('country') == 'FO' ? 'selected' : ''); ?>>Faroe Islands</option>
                    <option value="SJ" <?php echo e(old('country') == 'SJ' ? 'selected' : ''); ?>>Svalbard and Jan Mayen</option>
                    <option value="AX" <?php echo e(old('country') == 'AX' ? 'selected' : ''); ?>>Åland Islands</option>
                    <option value="GI" <?php echo e(old('country') == 'GI' ? 'selected' : ''); ?>>Gibraltar</option>
                    <option value="AD" <?php echo e(old('country') == 'AD' ? 'selected' : ''); ?>>Andorra</option>
                    <option value="MC" <?php echo e(old('country') == 'MC' ? 'selected' : ''); ?>>Monaco</option>
                    <option value="SM" <?php echo e(old('country') == 'SM' ? 'selected' : ''); ?>>San Marino</option>
                    <option value="VA" <?php echo e(old('country') == 'VA' ? 'selected' : ''); ?>>Vatican City</option>
                    <option value="LI" <?php echo e(old('country') == 'LI' ? 'selected' : ''); ?>>Liechtenstein</option>
                                        <option value="OTHER" <?php echo e(old('country') == 'OTHER' ? 'selected' : ''); ?>><?php echo e(trans('app.Other')); ?></option>
                                    </select>
                                </div>
                                <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-terms-section">
                            <div class="form-checkbox-wrapper">
                                <input id="terms" name="terms" type="checkbox" class="form-checkbox" required value="1">
                                <label for="terms" class="form-checkbox-label">
                                    <?php echo e(trans('app.I agree to the')); ?>

                                    <a href="#" class="form-link"><?php echo e(trans('app.Terms of Service')); ?></a>
                                    <?php echo e(trans('app.and')); ?>

                                    <a href="#" class="form-link"><?php echo e(trans('app.Privacy Policy')); ?></a>
                                </label>
                            </div>
                            <?php $__errorArgs = ['terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="form-error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo e($message); ?>

                            </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Submit Button -->
                        <!-- Anti-spam: either human question or Google reCAPTCHA -->

                        <?php if($registrationSettings['enableCaptcha'] && $registrationSettings['captchaSiteKey']): ?>
                            <div class="form-field-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-input-wrapper">
                                    <div class="g-recaptcha" data-sitekey="<?php echo e($registrationSettings['captchaSiteKey']); ?>"></div>
                                </div>
                                <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endif; ?>


                        <?php if($registrationSettings['enableHumanQuestion'] && $registrationSettings['selectedQuestionText']): ?>
                            <div class="form-field-group">
                                <label for="human_answer" class="form-label">
                                    <i class="fas fa-question"></i>
                                    <?php echo e($registrationSettings['selectedQuestionText']); ?>

                                </label>
                                <div class="form-input-wrapper">
                                    <input id="human_answer" name="human_answer" type="text"
                                        class="form-input <?php $__errorArgs = ['human_answer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        value="<?php echo e(old('human_answer')); ?>" required placeholder="<?php echo e(trans('app.Answer here')); ?>" />
                                </div>
                                <input type="hidden" name="human_question_index" value="<?php echo e($registrationSettings['selectedQuestionIndex']); ?>" />
                                <?php $__errorArgs = ['human_answer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?php echo e($message); ?>

                                </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="form-submit-button">
                            <span class="button-text"><?php echo e(trans('app.Create Account')); ?></span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>

                    <!-- Sign in link -->
                    <div class="form-signin-link">
                        <p class="signin-text">
                            <?php echo e(trans('app.Already have an account?')); ?>

                            <a href="<?php echo e(route('login')); ?>" class="signin-link">
                                <?php echo e(trans('app.Sign in now')); ?>

                            </a>
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="register-sidebar">
                    <!-- Benefits Info -->
                    <div class="user-card">
                        <div class="user-card-header">
                            <div class="user-card-title">
                                <i class="fas fa-check-circle"></i>
                                <?php echo e(trans('app.Account Benefits')); ?>

                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="benefits-list">
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title"><?php echo e(trans('app.Free Account')); ?></h4>
                                        <p class="benefit-description"><?php echo e(trans('app.Create your account completely free')); ?></p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title"><?php echo e(trans('app.Instant Access')); ?></h4>
                                        <p class="benefit-description"><?php echo e(trans('app.Get immediate access to all features')); ?></p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title"><?php echo e(trans('app.Secure & Private')); ?></h4>
                                        <p class="benefit-description"><?php echo e(trans('app.Your data is protected and private')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="user-card help-card">
                        <div class="user-card-content">
                            <div class="help-content">
                                <div class="help-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <h4 class="help-title">
                                    <?php echo e(trans('app.Need Help?')); ?>

                                </h4>
                                <p class="help-description">
                                    <?php echo e(trans('app.Having trouble creating your account?')); ?>

                                </p>
                                <a href="<?php echo e(route('user.tickets.create')); ?>" class="help-button">
                                    <i class="fas fa-headset"></i>
                                    <?php echo e(trans('app.Contact Support')); ?>

                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php if($registrationSettings['enableCaptcha'] && $registrationSettings['captchaSiteKey']): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp1\htdocs\my-logos\resources\views/auth/register.blade.php ENDPATH**/ ?>