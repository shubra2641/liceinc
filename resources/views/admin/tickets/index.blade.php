@extends('layouts.admin')

@section('admin-content')
<!-- Admin Tickets Page -->
<div class="admin-tickets-page">
    <div class="admin-page-header modern-header">
        <div class="admin-page-header-content">
            <div class="admin-page-title">
                <h1 class="gradient-text">{{ trans('app.Tickets Management') }}</h1>
                <p class="admin-page-subtitle">{{ trans('app.Handle Customer Support Tickets') }}</p>
            </div>
            <div class="admin-page-actions">
                <a href="{{ route('admin.tickets.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                    <i class="fas fa-plus me-2"></i>
                    {{ trans('app.Create Ticket for User') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-filter me-2"></i>{{ trans('app.Filters') }}</h2>
            <div class="admin-section-actions">
                <div class="admin-search-box">
                    <input type="text" class="admin-form-input" id="searchTickets" 
                           placeholder="{{ trans('app.Search Tickets') }}">
                    <i class="fas fa-search admin-search-icon"></i>
                </div>
            </div>
        </div>
        <div class="admin-section-content">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="category-filter">
                            <i class="fas fa-tag me-1"></i>{{ trans('app.Category') }}
                        </label>
                        <select id="category-filter" class="admin-form-input">
                            <option value="">{{ trans('app.All Categories') }}</option>
                            @foreach(\App\Models\TicketCategory::active()->ordered()->get() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="status-filter">
                            <i class="fas fa-info-circle me-1"></i>{{ trans('app.Status') }}
                        </label>
                        <select id="status-filter" class="admin-form-input">
                            <option value="">{{ trans('app.All Status') }}</option>
                            <option value="open">{{ trans('app.Open') }}</option>
                            <option value="pending">{{ trans('app.Pending') }}</option>
                            <option value="resolved">{{ trans('app.Resolved') }}</option>
                            <option value="closed">{{ trans('app.Closed') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="admin-form-group">
                        <label class="admin-form-label" for="priority-filter">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ trans('app.Priority') }}
                        </label>
                        <select id="priority-filter" class="admin-form-input">
                            <option value="">{{ trans('app.All Priorities') }}</option>
                            <option value="high">{{ trans('app.High') }}</option>
                            <option value="medium">{{ trans('app.Medium') }}</option>
                            <option value="low">{{ trans('app.Low') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2><i class="fas fa-ticket-alt me-2"></i>{{ trans('app.All Tickets') }}</h2>
            <span class="admin-badge admin-badge-info">{{ $tickets->total() }} {{ trans('app.Tickets') }}</span>
        </div>
        <div class="admin-section-content">
            @if($tickets->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 tickets-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">{{ trans('app.Subject') }}</th>
                            <th>{{ trans('app.User') }}</th>
                            <th class="text-center">{{ trans('app.Category') }}</th>
                            <th class="text-center">{{ trans('app.Priority') }}</th>
                            <th class="text-center">{{ trans('app.Status') }}</th>
                            <th class="text-center">{{ trans('app.Created') }}</th>
                            <th class="text-center">{{ trans('app.Invoice') }}</th>
                            <th class="text-center">{{ trans('app.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr class="ticket-row {{ $ticket->replies->count() === 0 ? 'new-ticket' : '' }}" 
                            data-subject="{{ strtolower($ticket->subject) }}" 
                            data-category="{{ $ticket->category_id ?? '' }}" 
                            data-status="{{ $ticket->status }}" 
                            data-priority="{{ $ticket->priority }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="fw-semibold text-dark">{{ $ticket->subject }}</div>
                                    @if($ticket->replies->count() === 0)
                                    <span class="badge bg-primary ms-2">{{ trans('app.New') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ optional($ticket->user)->name }}</div>
                                <small class="text-muted">{{ optional($ticket->user)->email }}</small>
                            </td>
                            <td class="text-center">
                                @if($ticket->category)
                                <span class="badge category-badge" data-category-color="{{ $ticket->category->color }}">
                                    {{ $ticket->category->name }}
                                </span>
                                @else
                                <span class="text-muted">{{ trans('app.No Category') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($ticket->priority == 'high')
                                <span class="badge bg-danger">{{ trans('app.High') }}</span>
                                @elseif($ticket->priority == 'medium')
                                <span class="badge bg-warning">{{ trans('app.Medium') }}</span>
                                @else
                                <span class="badge bg-secondary">{{ trans('app.Low') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($ticket->status == 'open')
                                <span class="badge bg-success">{{ trans('app.Open') }}</span>
                                @elseif($ticket->status == 'pending')
                                <span class="badge bg-warning">{{ trans('app.Pending') }}</span>
                                @elseif($ticket->status == 'resolved')
                                <span class="badge bg-info">{{ trans('app.Resolved') }}</span>
                                @else
                                <span class="badge bg-secondary">{{ trans('app.Closed') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold text-dark">{{ $ticket->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $ticket->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-center">
                                @if($ticket->invoice)
                                <div class="fw-semibold">
                                    <a href="{{ route('admin.invoices.show', $ticket->invoice) }}" class="text-primary">
                                        {{ $ticket->invoice->invoice_number }}
                                    </a>
                                </div>
                                <small class="text-muted">{{ $ticket->invoice->product->name ?? 'N/A' }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group-vertical btn-group-sm" role="group">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        {{ trans('app.View') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $tickets->links() }}
            </div>
        @else
        <!-- Enhanced Empty State -->
        <div class="admin-empty-state tickets-empty-state">
            <div class="admin-empty-state-content">
                <div class="admin-empty-state-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="admin-empty-state-text">
                    <h3 class="admin-empty-state-title">{{ trans('app.No Tickets Found') }}</h3>
                    <p class="admin-empty-state-description">
                        {{ trans('app.There are currently no support tickets. When customers submit tickets, they will appear here for you to manage.') }}
                    </p>
                </div>
                <div class="admin-empty-state-actions">
                    <a href="{{ route('admin.tickets.create') }}" class="admin-btn admin-btn-primary admin-btn-m">
                        <i class="fas fa-plus me-2"></i>
                        {{ trans('app.Create First Ticket') }}
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="admin-btn admin-btn-secondary admin-btn-m">
                        <i class="fas fa-arrow-left me-2"></i>
                        {{ trans('app.Back to Dashboard') }}
                    </a>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="admin-empty-state-stats">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="admin-stat-card">
                            <div class="admin-stat-icon">
                                <i class="fas fa-ticket-alt text-primary"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">0</div>
                                <div class="admin-stat-label">{{ trans('app.Total Tickets') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-stat-card">
                            <div class="admin-stat-icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">0</div>
                                <div class="admin-stat-label">{{ trans('app.Pending Tickets') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-stat-card">
                            <div class="admin-stat-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="admin-stat-content">
                                <div class="admin-stat-value">0</div>
                                <div class="admin-stat-label">{{ trans('app.Resolved Tickets') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Helpful Tips -->
            <div class="admin-empty-state-tips">
                <h4 class="admin-tips-title">
                    <i class="fas fa-lightbulb me-2"></i>
                    {{ trans('app.Getting Started Tips') }}
                </h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="admin-tip-card">
                            <div class="admin-tip-icon">
                                <i class="fas fa-users text-info"></i>
                            </div>
                            <div class="admin-tip-content">
                                <h5>{{ trans('app.Encourage Customer Support') }}</h5>
                                <p>{{ trans('app.Make it easy for customers to submit tickets by adding support links to your website.') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-tip-card">
                            <div class="admin-tip-icon">
                                <i class="fas fa-cog text-warning"></i>
                            </div>
                            <div class="admin-tip-content">
                                <h5>{{ trans('app.Set Up Categories') }}</h5>
                                <p>{{ trans('app.Organize tickets by creating categories for different types of support requests.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection