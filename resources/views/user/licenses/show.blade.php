@extends('layouts.user')

@section('title', trans('app.License Details'))
@section('page-title', trans('app.License Details'))
@section('page-subtitle', trans('app.View license information and manage domains'))

@section('seo_title', $siteSeoTitle ?? trans('app.License Details'))
@section('meta_description', $siteSeoDescription ?? trans('app.View license information and manage domains'))


@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-key"></i>
                {{ trans('app.License Details') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Manage your license and registered domains') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- License Information -->
            <div class="license-details-grid">
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3>{{ trans('app.License Information') }}</h3>
                        <span class="license-status-badge license-status-{{ $license->status }}">
                            {{ ucfirst($license->status) }}
                        </span>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label>{{ trans('app.Product') }}:</label>
                            <span>{{ $license->product?->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.License Key') }}:</label>
                            <div class="license-key-display">
                                <code class="license-key-code">{{ $license->license_key }}</code>
                                <button class="copy-key-btn" data-key="{{ $license->license_key }}" title="{{ trans('app.Copy License Key') }}">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.License Type') }}:</label>
                            <span class="license-type-badge">{{ ucfirst($license->license_type ?? '-') }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Purchase Date') }}:</label>
                            <span>{{ $license->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Support Until') }}:</label>
                            <span>{{ optional($license->support_expires_at)->format('M d, Y') ?? '-' }}</span>
                        </div>
                        
                        @if($license->license_expires_at)
                        <div class="info-row">
                            <label>{{ trans('app.Expires On') }}:</label>
                            <span>{{ $license->license_expires_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Product Information -->
                @if($license->product)
                <div class="license-info-card">
                    <div class="license-info-header">
                        <h3>{{ trans('app.Product Information') }}</h3>
                    </div>
                    
                    <div class="license-info-content">
                        <div class="info-row">
                            <label>{{ trans('app.Name') }}:</label>
                            <span>{{ $license->product->name }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Version') }}:</label>
                            <span>v{{ $license->product->version ?? '-' }}</span>
                        </div>
                        
                        <div class="info-row">
                            <label>{{ trans('app.Category') }}:</label>
                            <span>{{ $license->product->category?->name ?? '-' }}</span>
                        </div>
                        
                        @if($license->product->description)
                        <div class="info-row">
                            <label>{{ trans('app.Description') }}:</label>
                            <span>{{ $license->product->description }}</span>
                        </div>
                        @endif
                        
                        <div class="info-row">
                            <label>{{ trans('app.Actions') }}:</label>
                            <a href="{{ route('public.products.show', $license->product->slug) }}" class="license-action-link">
                                <i class="fas fa-external-link-alt"></i>
                                {{ trans('app.View Product') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Registered Domains -->
            <div class="license-domains-section">
                <div class="section-header">
                    <h3>{{ trans('app.Registered Domains') }}</h3>
                    <span class="domain-count">{{ $license->domains->count() }} {{ trans('app.domains registered') }}</span>
                </div>

                @if($license->domains->isEmpty())
                <div class="user-empty-state">
                    <div class="user-empty-state-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="user-empty-state-title">
                        {{ trans('app.No domains registered') }}
                    </h3>
                    <p class="user-empty-state-description">
                        {{ trans('app.This license has no registered domains yet') }}
                    </p>
                </div>
                @else
                <div class="domains-table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>{{ trans('app.Domain') }}</th>
                                <th>{{ trans('app.Registered Date') }}</th>
                                <th>{{ trans('app.Status') }}</th>
                                <th>{{ trans('app.Last Check') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($license->domains as $domain)
                            <tr>
                                <td>
                                    <div class="domain-info">
                                        <i class="fas fa-globe"></i>
                                        <span>{{ $domain->domain }}</span>
                                    </div>
                                </td>
                                <td>{{ $domain->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="domain-status-badge domain-status-{{ $domain->status }}">
                                        {{ ucfirst($domain->status) }}
                                    </span>
                                </td>
                                <td>{{ optional($domain->last_checked_at)->format('M d, Y H:i') ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- License Actions -->
            <div class="license-actions-section">
                <div class="action-buttons">
                    <a href="{{ route('user.licenses.index') }}" class="user-action-button">
                        <i class="fas fa-arrow-left"></i>
                        {{ trans('app.Back to Licenses') }}
                    </a>
                    
                    @if($license->product)
                    <a href="{{ route('public.products.show', $license->product->slug) }}" class="user-action-button">
                        <i class="fas fa-download"></i>
                        {{ trans('app.Download Product') }}
                    </a>
                    @endif
                    
                    <a href="{{ route('user.tickets.create') }}" class="user-action-button">
                        <i class="fas fa-headset"></i>
                        {{ trans('app.Get Support') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
