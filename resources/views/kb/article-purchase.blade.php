@extends('layouts.user')

@section('title', $article->title)
@section('page-title', $article->title)
@section('page-subtitle', trans('app.Knowledge Base'))

@section('content')
<div class="user-dashboard-container">
    <!-- Article Purchase Header -->
    <div class="category-purchase-header">
        <div class="category-purchase-header-content">


            <div class="category-purchase-hero">
                <div class="category-purchase-hero-content">
                    <div class="category-purchase-hero-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="category-purchase-hero-text">
                        <h1 class="category-purchase-hero-title">{{ $article->title }}</h1>
                        @if($article->excerpt)
                        <p class="category-purchase-hero-subtitle">{{ $article->excerpt }}</p>
                        @endif
                    </div>
                    <div class="category-purchase-hero-badge">
                        <span class="user-badge user-badge-warning">
                            <i class="fas fa-shield-alt"></i>
                            {{ trans('app.license_code_protection') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Card -->
    <div class="license-verification-card">
        <div class="license-verification-header">
            <div class="license-verification-icon">
                <i class="fas fa-key"></i>
            </div>
            <div class="license-verification-title-section">
                <h2 class="license-verification-title">{{ trans('app.license_code_protection') }}</h2>
                <p class="license-verification-subtitle">
                    @if($article->requires_purchase_code && $article->purchase_message)
                    {{ $article->purchase_message }}
                    @elseif($article->category && $article->category->requires_purchase_code && $article->category->purchase_message)
                    {{ $article->category->purchase_message }}
                    @else
                    {{ trans('app.users_must_enter_license_to_access_content') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="license-verification-content">
            @if(!empty($error) || session('error'))
            <div class="license-error-message">
                <div class="license-error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="license-error-content">
                    <h4 class="license-error-title">{{ trans('app.Error') }}</h4>
                    <p class="license-error-text">{{ $error ?? session('error') }}</p>
                </div>
            </div>
            @endif

            <form method="GET" action="{{ route('kb.article', $article->slug) }}" class="license-verification-form">
                <div class="license-form-group">
                    <label for="raw_code" class="license-form-label">
                        <i class="fas fa-key"></i>
                        {{ trans('app.license_code') }}
                    </label>
                    <input 
                        id="raw_code" 
                        name="raw_code" 
                        type="text" 
                        required 
                        class="license-form-input"
                        placeholder="{{ trans('app.enter_license_code') }}"
                    >
                    <p class="license-form-help">
                        <i class="fas fa-info-circle"></i>
                        {{ trans('app.code_will_not_be_cleaned') }}
                    </p>
                </div>

                <button type="submit" class="license-verify-button">
                    <i class="fas fa-check"></i>
                    {{ trans('app.verify_license_code') }}
                </button>
            </form>
        </div>
    </div>


    <!-- Article Information -->
    <div class="user-card category-info-card">
        <div class="user-card-header">
            <div class="user-section-header">
                <div class="user-section-title">
                    <div class="user-section-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="user-section-title-text">{{ trans('app.Article Information') }}</h3>
                        <p class="user-section-subtitle">{{ trans('app.About this article') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-card-content">
            <div class="category-info-grid">
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Article Title') }}</div>
                        <div class="info-value">{{ $article->title }}</div>
                    </div>
                </div>

                @if($article->category)
                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Category') }}</div>
                        <div class="info-value">{{ $article->category->name }}</div>
                    </div>
                </div>
                @endif

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Created') }}</div>
                        <div class="info-value">{{ $article->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Views') }}</div>
                        <div class="info-value">{{ $article->views }}</div>
                    </div>
                </div>

                <div class="category-info-item">
                    <div class="info-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">{{ trans('app.Access Level') }}</div>
                        <div class="info-value">
                            <span class="user-badge user-badge-warning">
                                <i class="fas fa-lock"></i>
                                {{ trans('app.License Required') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection