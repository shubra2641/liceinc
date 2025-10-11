@extends('layouts.user')

@section('title', trans('app.Create Support Ticket'))
@section('page-title', trans('app.Create Support Ticket'))
@section('page-subtitle', trans('app.Get help with your products and licenses'))

@section('seo_title', $ticketsSeoTitle ?? $siteSeoTitle ?? trans('app.Create Support Ticket'))
@section('meta_description', $ticketsSeoDescription ?? $siteSeoDescription ?? trans('app.Get help with your products and licenses'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-plus"></i>
                {{ trans('app.Create Support Ticket') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Get help with your products and licenses') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Quick Help -->
            <div class="quick-help-section">
                <div class="quick-help-header">
                    <h3>{{ trans('app.Need Quick Help?') }}</h3>
                    <p>{{ trans('app.Before creating a ticket, check our knowledge base for instant answers') }}</p>
                </div>
                
                <div class="quick-help-actions">
                    <a href="{{ route('kb.index') }}" class="user-action-button">
                        <i class="fas fa-book"></i>
                        {{ trans('app.Browse Knowledge Base') }}
                    </a>
                    
                    <a href="{{ route('kb.index') }}?category=faq" class="user-action-button">
                        <i class="fas fa-question-circle"></i>
                        {{ trans('app.Frequently Asked Questions') }}
                    </a>
                </div>
            </div>

            <!-- Ticket Form -->
            <form action="{{ route('user.tickets.store') }}" method="POST" class="ticket-form">
                @csrf
                
                <div class="form-grid">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3>{{ trans('app.Basic Information') }}</h3>
                        
                        <div class="form-group">
                            <label for="subject">{{ trans('app.Subject') }} <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-input" placeholder="{{ trans('app.Brief description of your issue') }}" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">{{ trans('app.Category') }} <span class="required">*</span></label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">{{ trans('app.Select a category') }}</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        data-requires-purchase-code="{{ $category->requires_valid_purchase_code ? 'true' : 'false' }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} 
                                    @if($category->requires_valid_purchase_code) (Requires Purchase Code) @endif
                                </option>
                                @endforeach
                            </select>
                            
                            @error('category_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="priority">{{ trans('app.Priority') }} <span class="required">*</span></label>
                            <select id="priority" name="priority" class="form-select" required>
                                <option value="">{{ trans('app.Select priority') }}</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ trans('app.Low') }}</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>{{ trans('app.Medium') }}</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ trans('app.High') }}</option>
                            </select>
                            @error('priority')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Related Information -->
                    <div class="form-section">
                        <h3>{{ trans('app.Related Information') }}</h3>
                        
                        <div id="purchase-code-section" class="form-group hidden">
                            <label for="purchase_code">{{ trans('app.Purchase Code') }} <span class="required hidden" id="purchase-code-required">*</span></label>
                            <input type="text" id="purchase_code" name="purchase_code" class="form-input" placeholder="{{ trans('app.Enter your purchase code if applicable') }}" value="{{ old('purchase_code') }}">
                            <small class="form-help">{{ trans('app.Purchase code will be verified automatically') }}</small>
                            @error('purchase_code')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="product-slug-section" class="form-group hidden">
                            <label for="product_slug">{{ trans('app.Product Slug') }}</label>
                            <input type="text" id="product_slug" name="product_slug" class="form-input" placeholder="{{ trans('app.Product identifier from URL') }}" value="{{ old('product_slug') }}" readonly>
                            <small class="form-help">{{ trans('app.Product slug will be filled automatically from purchase code') }}</small>
                            @error('product_slug')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            
                            <div id="product-name-display" class="product-info hidden">
                                <strong>{{ trans('app.Product') }}:</strong> <span id="product-name"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="invoice_id">{{ trans('app.Related Invoice') }}</label>
                            <select id="invoice_id" name="invoice_id" class="form-select">
                                <option value="">{{ trans('app.Select invoice (optional)') }}</option>
                                @if(auth()->check())
                                    @foreach(auth()->user()->invoices ?? [] as $invoice)
                                    <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                        #{{ $invoice->id }} - {{ $invoice->total }} {{ $invoice->currency }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('invoice_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group hidden">
                            <label for="product_version">{{ trans('app.Product Version') }}</label>
                            <input type="text" id="product_version" name="product_version" class="form-input" placeholder="{{ trans('app.e.g., 1.0.0') }}" value="{{ old('product_version') }}">
                            @error('product_version')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group hidden">
                            <input type="hidden" id="browser_info" name="browser_info" value="{{ old('browser_info') }}">
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-section">
                    <h3>{{ trans('app.Description') }}</h3>
                    
                    <div class="form-group">
                        <label for="content">{{ trans('app.Detailed Description') }} <span class="required">*</span></label>
                        <textarea id="content" name="content" rows="8" class="form-textarea" placeholder="{{ trans('app.Please provide a detailed description of your issue, including steps to reproduce if applicable...') }}" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('user.tickets.index') }}" class="user-action-button secondary">
                        <i class="fas fa-arrow-left"></i>
                        {{ trans('app.Cancel') }}
                    </a>
                    
                    <button type="submit" class="user-action-button">
                        <i class="fas fa-paper-plane"></i>
                        {{ trans('app.Create Ticket') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

