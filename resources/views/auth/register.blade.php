@extends('layouts.user')

@section('title', trans('app.Create Account'))
@section('page-title', trans('app.Join Us Today'))
@section('page-subtitle', trans('app.Create your account to get started'))
@section('app.Description', trans('app.Create a new account with email and password or sign up with Envato OAuth'))


@section('seo_title', $siteSeoTitle ?? trans('app.Create Account'))
@section('meta_description', $siteSeoDescription ?? trans('app.Create a new account with email and password or sign up with Envato OAuth'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-user-plus"></i>
                {{ trans('app.Join Us Today') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Create your account to get started with our premium services') }}
            </p>
        </div>

        <div class="user-card-content">
            <div class="register-grid">
                <!-- Main Registration Form -->
                <div class="register-form-section">
                    <!-- Envato OAuth Register -->
                    @if(\App\Helpers\EnvatoHelper::isConfigured())
                    <div class="envato-auth-section">
                        <a href="{{ route('auth.envato') }}" class="envato-auth-button">
                            <i class="fas fa-external-link-alt"></i>
                            {{ trans('app.Continue with Envato') }}
                        </a>
                        
                        <div class="auth-divider">
                            <div class="auth-divider-line"></div>
                            <span class="auth-divider-text">{{ trans('app.Or create an account') }}</span>
                            <div class="auth-divider-line"></div>
                        </div>
                    </div>
                    @endif

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('register') }}" class="register-form" novalidate>
                        @csrf

                        <div class="form-fields-grid">
                            <!-- First Name -->
                            <div class="form-field-group">
                                <label for="firstname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    {{ trans('app.First Name') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="firstname" name="firstname" type="text"
                                        class="form-input @error('firstname') form-input-error @enderror"
                                        value="{{ old('firstname') }}" required autofocus autocomplete="given-name"
                                        placeholder="{{ trans('app.Enter your first name') }}" />
                                </div>
                                @error('firstname')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="form-field-group">
                                <label for="lastname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    {{ trans('app.Last Name') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="lastname" name="lastname" type="text"
                                        class="form-input @error('lastname') form-input-error @enderror"
                                        value="{{ old('lastname') }}" required autocomplete="family-name"
                                        placeholder="{{ trans('app.Enter your last name') }}" />
                                </div>
                                @error('lastname')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Email Address -->
                            <div class="form-field-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    {{ trans('app.Email Address') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="email" name="email" type="email"
                                        class="form-input @error('email') form-input-error @enderror"
                                        value="{{ old('email') }}" required autocomplete="username"
                                        placeholder="{{ trans('app.Enter your email address') }}" />
                                </div>
                                @error('email')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-field-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    {{ trans('app.Password') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="register-password" name="password" type="password"
                                        class="form-input @error('password') form-input-error @enderror"
                                        required autocomplete="new-password" placeholder="{{ trans('app.Enter your password') }}" />
                                    <button type="button" id="toggle-password" class="form-input-toggle">
                                        <i class="fas fa-eye" id="password-show"></i>
                                        <i class="fas fa-eye-slash hidden" id="password-hide"></i>
                                    </button>
                                </div>
                                @error('password')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-field-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    {{ trans('app.Confirm Password') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="password_confirmation" name="password_confirmation" type="password"
                                        class="form-input @error('password_confirmation') form-input-error @enderror"
                                        required autocomplete="new-password" placeholder="{{ trans('app.Confirm your password') }}" />
                                </div>
                                @error('password_confirmation')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="form-field-group">
                                <label for="phonenumber" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    {{ trans('app.Phone Number') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="phonenumber" name="phonenumber" type="tel"
                                        class="form-input @error('phonenumber') form-input-error @enderror"
                                        value="{{ old('phonenumber') }}" autocomplete="tel"
                                        placeholder="{{ trans('app.Enter your phone number') }}" />
                                </div>
                                @error('phonenumber')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div class="form-field-group">
                                <label for="country" class="form-label">
                                    <i class="fas fa-globe"></i>
                                    {{ trans('app.Country') }}
                                </label>
                                <div class="form-input-wrapper">
                                    <select id="country" name="country"
                                        class="form-select @error('country') form-input-error @enderror">
                    <option value="">{{ trans('app.Select your country') }}</option>
                    <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                    <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                    <option value="GB" {{ old('country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                    <option value="AU" {{ old('country') == 'AU' ? 'selected' : '' }}>Australia</option>
                    <option value="DE" {{ old('country') == 'DE' ? 'selected' : '' }}>Germany</option>
                    <option value="FR" {{ old('country') == 'FR' ? 'selected' : '' }}>France</option>
                    <option value="IT" {{ old('country') == 'IT' ? 'selected' : '' }}>Italy</option>
                    <option value="ES" {{ old('country') == 'ES' ? 'selected' : '' }}>Spain</option>
                    <option value="NL" {{ old('country') == 'NL' ? 'selected' : '' }}>Netherlands</option>
                    <option value="SE" {{ old('country') == 'SE' ? 'selected' : '' }}>Sweden</option>
                    <option value="NO" {{ old('country') == 'NO' ? 'selected' : '' }}>Norway</option>
                    <option value="DK" {{ old('country') == 'DK' ? 'selected' : '' }}>Denmark</option>
                    <option value="FI" {{ old('country') == 'FI' ? 'selected' : '' }}>Finland</option>
                    <option value="CH" {{ old('country') == 'CH' ? 'selected' : '' }}>Switzerland</option>
                    <option value="AT" {{ old('country') == 'AT' ? 'selected' : '' }}>Austria</option>
                    <option value="BE" {{ old('country') == 'BE' ? 'selected' : '' }}>Belgium</option>
                    <option value="IE" {{ old('country') == 'IE' ? 'selected' : '' }}>Ireland</option>
                    <option value="PT" {{ old('country') == 'PT' ? 'selected' : '' }}>Portugal</option>
                    <option value="GR" {{ old('country') == 'GR' ? 'selected' : '' }}>Greece</option>
                    <option value="PL" {{ old('country') == 'PL' ? 'selected' : '' }}>Poland</option>
                    <option value="CZ" {{ old('country') == 'CZ' ? 'selected' : '' }}>Czech Republic</option>
                    <option value="HU" {{ old('country') == 'HU' ? 'selected' : '' }}>Hungary</option>
                    <option value="RO" {{ old('country') == 'RO' ? 'selected' : '' }}>Romania</option>
                    <option value="BG" {{ old('country') == 'BG' ? 'selected' : '' }}>Bulgaria</option>
                    <option value="HR" {{ old('country') == 'HR' ? 'selected' : '' }}>Croatia</option>
                    <option value="SI" {{ old('country') == 'SI' ? 'selected' : '' }}>Slovenia</option>
                    <option value="SK" {{ old('country') == 'SK' ? 'selected' : '' }}>Slovakia</option>
                    <option value="LT" {{ old('country') == 'LT' ? 'selected' : '' }}>Lithuania</option>
                    <option value="LV" {{ old('country') == 'LV' ? 'selected' : '' }}>Latvia</option>
                    <option value="EE" {{ old('country') == 'EE' ? 'selected' : '' }}>Estonia</option>
                    <option value="CY" {{ old('country') == 'CY' ? 'selected' : '' }}>Cyprus</option>
                    <option value="LU" {{ old('country') == 'LU' ? 'selected' : '' }}>Luxembourg</option>
                    <option value="MT" {{ old('country') == 'MT' ? 'selected' : '' }}>Malta</option>
                    <option value="JP" {{ old('country') == 'JP' ? 'selected' : '' }}>Japan</option>
                    <option value="KR" {{ old('country') == 'KR' ? 'selected' : '' }}>South Korea</option>
                    <option value="CN" {{ old('country') == 'CN' ? 'selected' : '' }}>China</option>
                    <option value="IN" {{ old('country') == 'IN' ? 'selected' : '' }}>India</option>
                    <option value="BR" {{ old('country') == 'BR' ? 'selected' : '' }}>Brazil</option>
                    <option value="MX" {{ old('country') == 'MX' ? 'selected' : '' }}>Mexico</option>
                    <option value="AR" {{ old('country') == 'AR' ? 'selected' : '' }}>Argentina</option>
                    <option value="CL" {{ old('country') == 'CL' ? 'selected' : '' }}>Chile</option>
                    <option value="CO" {{ old('country') == 'CO' ? 'selected' : '' }}>Colombia</option>
                    <option value="PE" {{ old('country') == 'PE' ? 'selected' : '' }}>Peru</option>
                    <option value="VE" {{ old('country') == 'VE' ? 'selected' : '' }}>Venezuela</option>
                    <option value="UY" {{ old('country') == 'UY' ? 'selected' : '' }}>Uruguay</option>
                    <option value="PY" {{ old('country') == 'PY' ? 'selected' : '' }}>Paraguay</option>
                    <option value="BO" {{ old('country') == 'BO' ? 'selected' : '' }}>Bolivia</option>
                    <option value="EC" {{ old('country') == 'EC' ? 'selected' : '' }}>Ecuador</option>
                    <option value="GY" {{ old('country') == 'GY' ? 'selected' : '' }}>Guyana</option>
                    <option value="SR" {{ old('country') == 'SR' ? 'selected' : '' }}>Suriname</option>
                    <option value="GF" {{ old('country') == 'GF' ? 'selected' : '' }}>French Guiana</option>
                    <option value="ZA" {{ old('country') == 'ZA' ? 'selected' : '' }}>South Africa</option>
                    <option value="EG" {{ old('country') == 'EG' ? 'selected' : '' }}>Egypt</option>
                    <option value="NG" {{ old('country') == 'NG' ? 'selected' : '' }}>Nigeria</option>
                    <option value="KE" {{ old('country') == 'KE' ? 'selected' : '' }}>Kenya</option>
                    <option value="GH" {{ old('country') == 'GH' ? 'selected' : '' }}>Ghana</option>
                    <option value="MA" {{ old('country') == 'MA' ? 'selected' : '' }}>Morocco</option>
                    <option value="TN" {{ old('country') == 'TN' ? 'selected' : '' }}>Tunisia</option>
                    <option value="DZ" {{ old('country') == 'DZ' ? 'selected' : '' }}>Algeria</option>
                    <option value="LY" {{ old('country') == 'LY' ? 'selected' : '' }}>Libya</option>
                    <option value="SD" {{ old('country') == 'SD' ? 'selected' : '' }}>Sudan</option>
                    <option value="ET" {{ old('country') == 'ET' ? 'selected' : '' }}>Ethiopia</option>
                    <option value="UG" {{ old('country') == 'UG' ? 'selected' : '' }}>Uganda</option>
                    <option value="TZ" {{ old('country') == 'TZ' ? 'selected' : '' }}>Tanzania</option>
                    <option value="ZW" {{ old('country') == 'ZW' ? 'selected' : '' }}>Zimbabwe</option>
                    <option value="BW" {{ old('country') == 'BW' ? 'selected' : '' }}>Botswana</option>
                    <option value="NA" {{ old('country') == 'NA' ? 'selected' : '' }}>Namibia</option>
                    <option value="ZM" {{ old('country') == 'ZM' ? 'selected' : '' }}>Zambia</option>
                    <option value="MW" {{ old('country') == 'MW' ? 'selected' : '' }}>Malawi</option>
                    <option value="MZ" {{ old('country') == 'MZ' ? 'selected' : '' }}>Mozambique</option>
                    <option value="MG" {{ old('country') == 'MG' ? 'selected' : '' }}>Madagascar</option>
                    <option value="MU" {{ old('country') == 'MU' ? 'selected' : '' }}>Mauritius</option>
                    <option value="SC" {{ old('country') == 'SC' ? 'selected' : '' }}>Seychelles</option>
                    <option value="RE" {{ old('country') == 'RE' ? 'selected' : '' }}>Réunion</option>
                    <option value="YT" {{ old('country') == 'YT' ? 'selected' : '' }}>Mayotte</option>
                    <option value="KM" {{ old('country') == 'KM' ? 'selected' : '' }}>Comoros</option>
                    <option value="DJ" {{ old('country') == 'DJ' ? 'selected' : '' }}>Djibouti</option>
                    <option value="SO" {{ old('country') == 'SO' ? 'selected' : '' }}>Somalia</option>
                    <option value="ER" {{ old('country') == 'ER' ? 'selected' : '' }}>Eritrea</option>
                    <option value="SS" {{ old('country') == 'SS' ? 'selected' : '' }}>South Sudan</option>
                    <option value="CF" {{ old('country') == 'CF' ? 'selected' : '' }}>Central African Republic</option>
                    <option value="TD" {{ old('country') == 'TD' ? 'selected' : '' }}>Chad</option>
                    <option value="NE" {{ old('country') == 'NE' ? 'selected' : '' }}>Niger</option>
                    <option value="ML" {{ old('country') == 'ML' ? 'selected' : '' }}>Mali</option>
                    <option value="BF" {{ old('country') == 'BF' ? 'selected' : '' }}>Burkina Faso</option>
                    <option value="CI" {{ old('country') == 'CI' ? 'selected' : '' }}>Côte d'Ivoire</option>
                    <option value="LR" {{ old('country') == 'LR' ? 'selected' : '' }}>Liberia</option>
                    <option value="SL" {{ old('country') == 'SL' ? 'selected' : '' }}>Sierra Leone</option>
                    <option value="GN" {{ old('country') == 'GN' ? 'selected' : '' }}>Guinea</option>
                    <option value="GW" {{ old('country') == 'GW' ? 'selected' : '' }}>Guinea-Bissau</option>
                    <option value="GM" {{ old('country') == 'GM' ? 'selected' : '' }}>Gambia</option>
                    <option value="SN" {{ old('country') == 'SN' ? 'selected' : '' }}>Senegal</option>
                    <option value="MR" {{ old('country') == 'MR' ? 'selected' : '' }}>Mauritania</option>
                    <option value="CV" {{ old('country') == 'CV' ? 'selected' : '' }}>Cape Verde</option>
                    <option value="ST" {{ old('country') == 'ST' ? 'selected' : '' }}>São Tomé and Príncipe</option>
                    <option value="GQ" {{ old('country') == 'GQ' ? 'selected' : '' }}>Equatorial Guinea</option>
                    <option value="GA" {{ old('country') == 'GA' ? 'selected' : '' }}>Gabon</option>
                    <option value="CG" {{ old('country') == 'CG' ? 'selected' : '' }}>Congo</option>
                    <option value="CD" {{ old('country') == 'CD' ? 'selected' : '' }}>Democratic Republic of the Congo</option>
                    <option value="AO" {{ old('country') == 'AO' ? 'selected' : '' }}>Angola</option>
                    <option value="CM" {{ old('country') == 'CM' ? 'selected' : '' }}>Cameroon</option>
                    <option value="CF" {{ old('country') == 'CF' ? 'selected' : '' }}>Central African Republic</option>
                    <option value="TD" {{ old('country') == 'TD' ? 'selected' : '' }}>Chad</option>
                    <option value="NE" {{ old('country') == 'NE' ? 'selected' : '' }}>Niger</option>
                    <option value="ML" {{ old('country') == 'ML' ? 'selected' : '' }}>Mali</option>
                    <option value="BF" {{ old('country') == 'BF' ? 'selected' : '' }}>Burkina Faso</option>
                    <option value="CI" {{ old('country') == 'CI' ? 'selected' : '' }}>Côte d'Ivoire</option>
                    <option value="LR" {{ old('country') == 'LR' ? 'selected' : '' }}>Liberia</option>
                    <option value="SL" {{ old('country') == 'SL' ? 'selected' : '' }}>Sierra Leone</option>
                    <option value="GN" {{ old('country') == 'GN' ? 'selected' : '' }}>Guinea</option>
                    <option value="GW" {{ old('country') == 'GW' ? 'selected' : '' }}>Guinea-Bissau</option>
                    <option value="GM" {{ old('country') == 'GM' ? 'selected' : '' }}>Gambia</option>
                    <option value="SN" {{ old('country') == 'SN' ? 'selected' : '' }}>Senegal</option>
                    <option value="MR" {{ old('country') == 'MR' ? 'selected' : '' }}>Mauritania</option>
                    <option value="CV" {{ old('country') == 'CV' ? 'selected' : '' }}>Cape Verde</option>
                    <option value="ST" {{ old('country') == 'ST' ? 'selected' : '' }}>São Tomé and Príncipe</option>
                    <option value="GQ" {{ old('country') == 'GQ' ? 'selected' : '' }}>Equatorial Guinea</option>
                    <option value="GA" {{ old('country') == 'GA' ? 'selected' : '' }}>Gabon</option>
                    <option value="CG" {{ old('country') == 'CG' ? 'selected' : '' }}>Congo</option>
                    <option value="CD" {{ old('country') == 'CD' ? 'selected' : '' }}>Democratic Republic of the Congo</option>
                    <option value="AO" {{ old('country') == 'AO' ? 'selected' : '' }}>Angola</option>
                    <option value="CM" {{ old('country') == 'CM' ? 'selected' : '' }}>Cameroon</option>
                    <option value="SA" {{ old('country') == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                    <option value="AE" {{ old('country') == 'AE' ? 'selected' : '' }}>United Arab Emirates</option>
                    <option value="QA" {{ old('country') == 'QA' ? 'selected' : '' }}>Qatar</option>
                    <option value="KW" {{ old('country') == 'KW' ? 'selected' : '' }}>Kuwait</option>
                    <option value="BH" {{ old('country') == 'BH' ? 'selected' : '' }}>Bahrain</option>
                    <option value="OM" {{ old('country') == 'OM' ? 'selected' : '' }}>Oman</option>
                    <option value="YE" {{ old('country') == 'YE' ? 'selected' : '' }}>Yemen</option>
                    <option value="IQ" {{ old('country') == 'IQ' ? 'selected' : '' }}>Iraq</option>
                    <option value="IR" {{ old('country') == 'IR' ? 'selected' : '' }}>Iran</option>
                    <option value="TR" {{ old('country') == 'TR' ? 'selected' : '' }}>Turkey</option>
                    <option value="IL" {{ old('country') == 'IL' ? 'selected' : '' }}>Israel</option>
                    <option value="PS" {{ old('country') == 'PS' ? 'selected' : '' }}>Palestine</option>
                    <option value="JO" {{ old('country') == 'JO' ? 'selected' : '' }}>Jordan</option>
                    <option value="LB" {{ old('country') == 'LB' ? 'selected' : '' }}>Lebanon</option>
                    <option value="SY" {{ old('country') == 'SY' ? 'selected' : '' }}>Syria</option>
                    <option value="CY" {{ old('country') == 'CY' ? 'selected' : '' }}>Cyprus</option>
                    <option value="RU" {{ old('country') == 'RU' ? 'selected' : '' }}>Russia</option>
                    <option value="UA" {{ old('country') == 'UA' ? 'selected' : '' }}>Ukraine</option>
                    <option value="BY" {{ old('country') == 'BY' ? 'selected' : '' }}>Belarus</option>
                    <option value="MD" {{ old('country') == 'MD' ? 'selected' : '' }}>Moldova</option>
                    <option value="GE" {{ old('country') == 'GE' ? 'selected' : '' }}>Georgia</option>
                    <option value="AM" {{ old('country') == 'AM' ? 'selected' : '' }}>Armenia</option>
                    <option value="AZ" {{ old('country') == 'AZ' ? 'selected' : '' }}>Azerbaijan</option>
                    <option value="KZ" {{ old('country') == 'KZ' ? 'selected' : '' }}>Kazakhstan</option>
                    <option value="UZ" {{ old('country') == 'UZ' ? 'selected' : '' }}>Uzbekistan</option>
                    <option value="TM" {{ old('country') == 'TM' ? 'selected' : '' }}>Turkmenistan</option>
                    <option value="TJ" {{ old('country') == 'TJ' ? 'selected' : '' }}>Tajikistan</option>
                    <option value="KG" {{ old('country') == 'KG' ? 'selected' : '' }}>Kyrgyzstan</option>
                    <option value="AF" {{ old('country') == 'AF' ? 'selected' : '' }}>Afghanistan</option>
                    <option value="PK" {{ old('country') == 'PK' ? 'selected' : '' }}>Pakistan</option>
                    <option value="BD" {{ old('country') == 'BD' ? 'selected' : '' }}>Bangladesh</option>
                    <option value="LK" {{ old('country') == 'LK' ? 'selected' : '' }}>Sri Lanka</option>
                    <option value="MV" {{ old('country') == 'MV' ? 'selected' : '' }}>Maldives</option>
                    <option value="BT" {{ old('country') == 'BT' ? 'selected' : '' }}>Bhutan</option>
                    <option value="NP" {{ old('country') == 'NP' ? 'selected' : '' }}>Nepal</option>
                    <option value="MM" {{ old('country') == 'MM' ? 'selected' : '' }}>Myanmar</option>
                    <option value="TH" {{ old('country') == 'TH' ? 'selected' : '' }}>Thailand</option>
                    <option value="LA" {{ old('country') == 'LA' ? 'selected' : '' }}>Laos</option>
                    <option value="KH" {{ old('country') == 'KH' ? 'selected' : '' }}>Cambodia</option>
                    <option value="VN" {{ old('country') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                    <option value="MY" {{ old('country') == 'MY' ? 'selected' : '' }}>Malaysia</option>
                    <option value="SG" {{ old('country') == 'SG' ? 'selected' : '' }}>Singapore</option>
                    <option value="ID" {{ old('country') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                    <option value="PH" {{ old('country') == 'PH' ? 'selected' : '' }}>Philippines</option>
                    <option value="TW" {{ old('country') == 'TW' ? 'selected' : '' }}>Taiwan</option>
                    <option value="HK" {{ old('country') == 'HK' ? 'selected' : '' }}>Hong Kong</option>
                    <option value="MO" {{ old('country') == 'MO' ? 'selected' : '' }}>Macau</option>
                    <option value="MN" {{ old('country') == 'MN' ? 'selected' : '' }}>Mongolia</option>
                    <option value="KP" {{ old('country') == 'KP' ? 'selected' : '' }}>North Korea</option>
                    <option value="FJ" {{ old('country') == 'FJ' ? 'selected' : '' }}>Fiji</option>
                    <option value="PG" {{ old('country') == 'PG' ? 'selected' : '' }}>Papua New Guinea</option>
                    <option value="SB" {{ old('country') == 'SB' ? 'selected' : '' }}>Solomon Islands</option>
                    <option value="VU" {{ old('country') == 'VU' ? 'selected' : '' }}>Vanuatu</option>
                    <option value="NC" {{ old('country') == 'NC' ? 'selected' : '' }}>New Caledonia</option>
                    <option value="PF" {{ old('country') == 'PF' ? 'selected' : '' }}>French Polynesia</option>
                    <option value="WS" {{ old('country') == 'WS' ? 'selected' : '' }}>Samoa</option>
                    <option value="TO" {{ old('country') == 'TO' ? 'selected' : '' }}>Tonga</option>
                    <option value="KI" {{ old('country') == 'KI' ? 'selected' : '' }}>Kiribati</option>
                    <option value="TV" {{ old('country') == 'TV' ? 'selected' : '' }}>Tuvalu</option>
                    <option value="NR" {{ old('country') == 'NR' ? 'selected' : '' }}>Nauru</option>
                    <option value="PW" {{ old('country') == 'PW' ? 'selected' : '' }}>Palau</option>
                    <option value="FM" {{ old('country') == 'FM' ? 'selected' : '' }}>Micronesia</option>
                    <option value="MH" {{ old('country') == 'MH' ? 'selected' : '' }}>Marshall Islands</option>
                    <option value="CK" {{ old('country') == 'CK' ? 'selected' : '' }}>Cook Islands</option>
                    <option value="NU" {{ old('country') == 'NU' ? 'selected' : '' }}>Niue</option>
                    <option value="TK" {{ old('country') == 'TK' ? 'selected' : '' }}>Tokelau</option>
                    <option value="WF" {{ old('country') == 'WF' ? 'selected' : '' }}>Wallis and Futuna</option>
                    <option value="AS" {{ old('country') == 'AS' ? 'selected' : '' }}>American Samoa</option>
                    <option value="GU" {{ old('country') == 'GU' ? 'selected' : '' }}>Guam</option>
                    <option value="MP" {{ old('country') == 'MP' ? 'selected' : '' }}>Northern Mariana Islands</option>
                    <option value="VI" {{ old('country') == 'VI' ? 'selected' : '' }}>U.S. Virgin Islands</option>
                    <option value="PR" {{ old('country') == 'PR' ? 'selected' : '' }}>Puerto Rico</option>
                    <option value="DO" {{ old('country') == 'DO' ? 'selected' : '' }}>Dominican Republic</option>
                    <option value="HT" {{ old('country') == 'HT' ? 'selected' : '' }}>Haiti</option>
                    <option value="JM" {{ old('country') == 'JM' ? 'selected' : '' }}>Jamaica</option>
                    <option value="TT" {{ old('country') == 'TT' ? 'selected' : '' }}>Trinidad and Tobago</option>
                    <option value="BB" {{ old('country') == 'BB' ? 'selected' : '' }}>Barbados</option>
                    <option value="LC" {{ old('country') == 'LC' ? 'selected' : '' }}>Saint Lucia</option>
                    <option value="VC" {{ old('country') == 'VC' ? 'selected' : '' }}>Saint Vincent and the Grenadines</option>
                    <option value="GD" {{ old('country') == 'GD' ? 'selected' : '' }}>Grenada</option>
                    <option value="AG" {{ old('country') == 'AG' ? 'selected' : '' }}>Antigua and Barbuda</option>
                    <option value="KN" {{ old('country') == 'KN' ? 'selected' : '' }}>Saint Kitts and Nevis</option>
                    <option value="DM" {{ old('country') == 'DM' ? 'selected' : '' }}>Dominica</option>
                    <option value="BZ" {{ old('country') == 'BZ' ? 'selected' : '' }}>Belize</option>
                    <option value="GT" {{ old('country') == 'GT' ? 'selected' : '' }}>Guatemala</option>
                    <option value="SV" {{ old('country') == 'SV' ? 'selected' : '' }}>El Salvador</option>
                    <option value="HN" {{ old('country') == 'HN' ? 'selected' : '' }}>Honduras</option>
                    <option value="NI" {{ old('country') == 'NI' ? 'selected' : '' }}>Nicaragua</option>
                    <option value="CR" {{ old('country') == 'CR' ? 'selected' : '' }}>Costa Rica</option>
                    <option value="PA" {{ old('country') == 'PA' ? 'selected' : '' }}>Panama</option>
                    <option value="CU" {{ old('country') == 'CU' ? 'selected' : '' }}>Cuba</option>
                    <option value="BS" {{ old('country') == 'BS' ? 'selected' : '' }}>Bahamas</option>
                    <option value="BM" {{ old('country') == 'BM' ? 'selected' : '' }}>Bermuda</option>
                    <option value="GL" {{ old('country') == 'GL' ? 'selected' : '' }}>Greenland</option>
                    <option value="IS" {{ old('country') == 'IS' ? 'selected' : '' }}>Iceland</option>
                    <option value="FO" {{ old('country') == 'FO' ? 'selected' : '' }}>Faroe Islands</option>
                    <option value="SJ" {{ old('country') == 'SJ' ? 'selected' : '' }}>Svalbard and Jan Mayen</option>
                    <option value="AX" {{ old('country') == 'AX' ? 'selected' : '' }}>Åland Islands</option>
                    <option value="GI" {{ old('country') == 'GI' ? 'selected' : '' }}>Gibraltar</option>
                    <option value="AD" {{ old('country') == 'AD' ? 'selected' : '' }}>Andorra</option>
                    <option value="MC" {{ old('country') == 'MC' ? 'selected' : '' }}>Monaco</option>
                    <option value="SM" {{ old('country') == 'SM' ? 'selected' : '' }}>San Marino</option>
                    <option value="VA" {{ old('country') == 'VA' ? 'selected' : '' }}>Vatican City</option>
                    <option value="LI" {{ old('country') == 'LI' ? 'selected' : '' }}>Liechtenstein</option>
                                        <option value="OTHER" {{ old('country') == 'OTHER' ? 'selected' : '' }}>{{ trans('app.Other') }}</option>
                                    </select>
                                </div>
                                @error('country')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-terms-section">
                            <div class="form-checkbox-wrapper">
                                <input id="terms" name="terms" type="checkbox" class="form-checkbox" required value="1">
                                <label for="terms" class="form-checkbox-label">
                                    {{ trans('app.I agree to the') }}
                                    <a href="#" class="form-link">{{ trans('app.Terms of Service') }}</a>
                                    {{ trans('app.and') }}
                                    <a href="#" class="form-link">{{ trans('app.Privacy Policy') }}</a>
                                </label>
                            </div>
                            @error('terms')
                            <div class="form-error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <!-- Anti-spam: either human question or Google reCAPTCHA -->

                        @if($registrationSettings['enableCaptcha'] && $registrationSettings['captchaSiteKey'])
                            <div class="form-field-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-input-wrapper">
                                    <div class="g-recaptcha" data-sitekey="{{ $registrationSettings['captchaSiteKey'] }}"></div>
                                </div>
                                @error('g-recaptcha-response')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        @endif


                        @if($registrationSettings['enableHumanQuestion'] && $registrationSettings['selectedQuestionText'])
                            <div class="form-field-group">
                                <label for="human_answer" class="form-label">
                                    <i class="fas fa-question"></i>
                                    {{ $registrationSettings['selectedQuestionText'] }}
                                </label>
                                <div class="form-input-wrapper">
                                    <input id="human_answer" name="human_answer" type="text"
                                        class="form-input @error('human_answer') form-input-error @enderror"
                                        value="{{ old('human_answer') }}" required placeholder="{{ trans('app.Answer here') }}" />
                                </div>
                                <input type="hidden" name="human_question_index" value="{{ $registrationSettings['selectedQuestionIndex'] }}" />
                                @error('human_answer')
                                <div class="form-error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        @endif

                        <button type="submit" class="form-submit-button">
                            <span class="button-text">{{ trans('app.Create Account') }}</span>
                            <i class="fas fa-spinner fa-spin button-loading hidden"></i>
                        </button>
                    </form>

                    <!-- Sign in link -->
                    <div class="form-signin-link">
                        <p class="signin-text">
                            {{ trans('app.Already have an account?') }}
                            <a href="{{ route('login') }}" class="signin-link">
                                {{ trans('app.Sign in now') }}
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
                                {{ trans('app.Account Benefits') }}
                            </div>
                        </div>
                        <div class="user-card-content">
                            <div class="benefits-list">
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title">{{ trans('app.Free Account') }}</h4>
                                        <p class="benefit-description">{{ trans('app.Create your account completely free') }}</p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title">{{ trans('app.Instant Access') }}</h4>
                                        <p class="benefit-description">{{ trans('app.Get immediate access to all features') }}</p>
                                    </div>
                                </div>
                                <div class="benefit-item">
                                    <div class="benefit-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="benefit-content">
                                        <h4 class="benefit-title">{{ trans('app.Secure & Private') }}</h4>
                                        <p class="benefit-description">{{ trans('app.Your data is protected and private') }}</p>
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
                                    {{ trans('app.Need Help?') }}
                                </h4>
                                <p class="help-description">
                                    {{ trans('app.Having trouble creating your account?') }}
                                </p>
                                <a href="{{ route('user.tickets.create') }}" class="help-button">
                                    <i class="fas fa-headset"></i>
                                    {{ trans('app.Contact Support') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($registrationSettings['enableCaptcha'] && $registrationSettings['captchaSiteKey'])
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif