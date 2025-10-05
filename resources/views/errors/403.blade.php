@extends('layouts.guest')

@section('title', 'Unauthorized - Error 403')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-24 w-24 text-red-500">
                <i class="fas fa-exclamation-triangle text-6xl text-red-500"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Error 403 - Unauthorized
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ $exception->getMessage() ?: 'You do not have permission to access this page' }}
            </p>
        </div>
        
        <div class="mt-8 space-y-4">
            <div class="text-center">
                <a href="{{ url()->previous() }}" 
                   class="error-btn-primary">
                    <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
                    Go Back
                </a>
            </div>
            
            <div class="text-center">
                <a href="{{ route('home') }}" 
                   class="error-btn-secondary">
                    <i class="fas fa-home w-4 h-4 mr-2"></i>
                    Home Page
                </a>
            </div>
            
            @auth
            <div class="text-center">
                <a href="{{ route('dashboard') }}" 
                   class="error-btn-secondary">
                    <i class="fas fa-chart-bar w-4 h-4 mr-2"></i>
                    Dashboard
                </a>
            </div>
            @endauth
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-500">
                If you believe this is an error, please contact technical support
            </p>
        </div>
    </div>
</div>
@endsection
