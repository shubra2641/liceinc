@extends('layouts.user')

@section('title', trans('app.Support Tickets'))
@section('page-title', trans('app.Support Tickets'))
@section('page-subtitle', trans('app.Get help and support for your products'))

@section('seo_title', $ticketsSeoTitle ?? $siteSeoTitle ?? trans('app.Support Tickets'))
@section('meta_description', $ticketsSeoDescription ?? $siteSeoDescription ?? trans('app.Get help and support for your products'))


@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-headset"></i>
                {{ trans('app.Support Tickets') }}
            </div>
            <p class="user-card-subtitle">
                {{ trans('app.Get help and support for your products and licenses') }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Ticket Statistics -->
            <div class="invoice-stats-grid">
                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Total Tickets') }}</div>
                        <div class="user-stat-icon purple">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $tickets->total() }}</div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Open Tickets') }}</div>
                        <div class="user-stat-icon yellow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $tickets->where('status', 'open')->count() }}</div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Closed Tickets') }}</div>
                        <div class="user-stat-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ $tickets->where('status', 'closed')->count() }}</div>
                </div>

                <div class="user-stat-card">
                    <div class="user-stat-header">
                        <div class="user-stat-title">{{ trans('app.Avg Response Time') }}</div>
                        <div class="user-stat-icon blue">
                            <i class="fas fa-stopwatch"></i>
                        </div>
                    </div>
                    <div class="user-stat-value">{{ \App\Models\Setting::get('avg_response_time', 24) }}h</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="ticket-quick-actions">
                <a href="{{ route('user.tickets.create') }}" class="user-action-button">
                    <i class="fas fa-plus"></i>
                    {{ trans('app.Create New Ticket') }}
                </a>
                
                <a href="{{ route('kb.index') }}" class="user-action-button">
                    <i class="fas fa-book"></i>
                    {{ trans('app.Knowledge Base') }}
                </a>
            </div>

            <!-- Filters and Search -->
            <div class="license-filters">
                <div class="filter-group">
                    <label for="status-filter">{{ trans('app.Filter by Status') }}:</label>
                    <select id="status-filter" class="filter-select">
                        <option value="">{{ trans('app.All Statuses') }}</option>
                        <option value="open">{{ trans('app.Open') }}</option>
                        <option value="closed">{{ trans('app.Closed') }}</option>
                        <option value="pending">{{ trans('app.Pending') }}</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="priority-filter">{{ trans('app.Filter by Priority') }}:</label>
                    <select id="priority-filter" class="filter-select">
                        <option value="">{{ trans('app.All Priorities') }}</option>
                        <option value="low">{{ trans('app.Low') }}</option>
                        <option value="medium">{{ trans('app.Medium') }}</option>
                        <option value="high">{{ trans('app.High') }}</option>
                        <option value="urgent">{{ trans('app.Urgent') }}</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search-input">{{ trans('app.Search') }}:</label>
                    <input type="text" id="search-input" class="filter-input" placeholder="{{ trans('app.Search by subject...') }}">
                </div>
            </div>

            @if($tickets->isEmpty())
            <div class="user-empty-state">
                <div class="user-empty-state-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="user-empty-state-title">
                    {{ trans('app.No tickets found') }}
                </h3>
                <p class="user-empty-state-description">
                    {{ trans('app.You haven\'t created any support tickets yet. Need help? Create your first ticket!') }}
                </p>
                <a href="{{ route('user.tickets.create') }}" class="user-action-button">
                    <i class="fas fa-plus"></i>
                    {{ trans('app.Create First Ticket') }}
                </a>
            </div>
            @else
            <!-- Tickets Table -->
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>{{ trans('app.Ticket') }}</th>
                            <th>{{ trans('app.Subject') }}</th>
                            <th>{{ trans('app.Category') }}</th>
                            <th>{{ trans('app.Priority') }}</th>
                            <th>{{ trans('app.Status') }}</th>
                            <th>{{ trans('app.Created') }}</th>
                            <th>{{ trans('app.Last Reply') }}</th>
                            <th>{{ trans('app.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <div class="ticket-number">#{{ $ticket->id }}</div>
                            </td>
                            <td>
                                <div class="ticket-subject">{{ $ticket->subject }}</div>
                                @if($ticket->description)
                                <div class="ticket-description">{{ Str::limit($ticket->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="ticket-category-badge">
                                    {{ $ticket->category?->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="ticket-priority-badge ticket-priority-{{ $ticket->priority }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="ticket-status-badge ticket-status-{{ $ticket->status }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                            <td>{{ optional($ticket->updated_at)->format('M d, Y') }}</td>
                            <td>
                                <div class="license-actions-cell">
                                    <a href="{{ route('user.tickets.show', $ticket) }}" class="license-action-link">
                                        <i class="fas fa-eye"></i>
                                        {{ trans('app.View') }}
                                    </a>
                                    @if($ticket->status === 'open')
                                    <a href="{{ route('user.tickets.show', $ticket) }}#reply" class="license-action-link">
                                        <i class="fas fa-reply"></i>
                                        {{ trans('app.Reply') }}
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="license-pagination">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection