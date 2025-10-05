@extends('layouts.user')

@section('title', trans('app.Ticket Details'))
@section('page-title', trans('app.Ticket') . ' #' . $ticket->id)
@section('page-subtitle', trans('app.View ticket details and replies'))

@section('seo_title', $ticketsSeoTitle ?? $siteSeoTitle ?? trans('app.Ticket Details'))
@section('meta_description', $ticketsSeoDescription ?? $siteSeoDescription ?? trans('app.View ticket details and
replies'))

@section('content')
<div class="user-dashboard-container">
    <!-- Header Section -->
    <div class="user-card">
        <div class="user-card-header">
            <div class="user-card-title">
                <i class="fas fa-ticket-alt"></i>
                {{ trans('app.Ticket') }} #{{ $ticket->id }}
            </div>
            <p class="user-card-subtitle">
                {{ $ticket->subject }}
            </p>
        </div>

        <div class="user-card-content">
            <!-- Ticket Status Banner -->
            <div class="quick-help-section">
                <div class="quick-help-header">
                    <h3>{{ trans('app.Ticket Status') }}</h3>
                    <p>
                        @if($ticket->status === 'open')
                        {{ trans('app.This ticket is open and awaiting response') }}
                        @elseif($ticket->status === 'closed')
                        {{ trans('app.This ticket has been closed') }}
                        @else
                        {{ trans('app.This ticket is pending') }}
                        @endif
                    </p>
                </div>

                <div class="quick-help-actions">
                    <div class="ticket-status-badge ticket-status-{{ $ticket->status }}">
                        <i class="fas fa-{{ $ticket->status === 'open' ? 'clock' : 'check-circle' }}"></i>
                        {{ ucfirst($ticket->status) }}
                    </div>

                    <div class="ticket-priority-badge ticket-priority-{{ $ticket->priority }}">
                        <i class="fas fa-flag"></i>
                        {{ ucfirst($ticket->priority) }}
                    </div>
                </div>
            </div>

            <!-- Ticket Information -->
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>{{ trans('app.Ticket Information') }}</h3>

                    <div class="form-group">
                        <label>{{ trans('app.Ticket ID') }}</label>
                        <div class="form-input bg-light-gray">#{{ $ticket->id }}</div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('app.Status') }}</label>
                        <div class="form-input bg-light-gray">
                            <span class="ticket-status-badge ticket-status-{{ $ticket->status }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('app.Priority') }}</label>
                        <div class="form-input bg-light-gray">
                            <span class="ticket-priority-badge ticket-priority-{{ $ticket->priority }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('app.Category') }}</label>
                        <div class="form-input bg-light-gray">{{ $ticket->category?->name
                            ?? '-' }}</div>
                    </div>
                </div>

                <!-- Related Information -->
                <div class="form-section">
                    <h3>{{ trans('app.Related Information') }}</h3>

                    <div class="form-group">
                        <label>{{ trans('app.Created') }}</label>
                        <div class="form-input bg-light-gray">{{
                            $ticket->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('app.Last Updated') }}</label>
                        <div class="form-input bg-light-gray">{{
                            $ticket->updated_at->format('M d, Y H:i') }}</div>
                    </div>

                    @if($ticket->license)
                    <div class="form-group">
                        <label>{{ trans('app.Related License') }}</label>
                        <a href="{{ route('user.licenses.show', $ticket->license) }}" class="user-action-button">
                            <i class="fas fa-key"></i>
                            {{ trans('app.View License') }}
                        </a>
                    </div>
                    @endif

                    @if($ticket->license && $ticket->license->product)
                    <div class="form-group">
                        <label>{{ trans('app.Product') }}</label>
                        <a href="{{ route('public.products.show', $ticket->license->product->slug) }}"
                            class="user-action-button">
                            <i class="fas fa-box"></i>
                            {{ $ticket->license->product->name }}
                        </a>
                    </div>
                    @endif

                    <div class="form-group">
                        <label>{{ trans('app.Replies') }}</label>
                        <div class="form-input bg-light-gray">{{
                            $ticket->replies->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Ticket Description -->
            <div class="form-section">
                <h3>{{ trans('app.Ticket Description') }}</h3>

                <div class="form-group">
                    <label>{{ trans('app.Initial Message') }}</label>
                    <div class="ticket-message">
                        <div class="message-header">
                            <div class="message-author">
                                <i class="fas fa-user"></i>
                                <span>{{ $ticket->user->name }}</span>
                            </div>
                            <div class="message-date">
                                {{ $ticket->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <div class="message-content">
                            {{ nl2br(e($ticket->content)) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Replies -->
            @if($ticket->replies->isNotEmpty())
            <div class="form-section">
                <h3>{{ trans('app.Replies') }} ({{ $ticket->replies->count() }})</h3>

                <div class="ticket-replies">
                    @foreach($ticket->replies as $reply)
                    <div
                        class="ticket-message {{ $reply->user_id === $ticket->user_id ? 'user-message' : 'admin-message' }}">
                        <div class="message-header">
                            <div class="message-author">
                                <i class="fas fa-{{ $reply->user_id === $ticket->user_id ? 'user' : 'headset' }}"></i>
                                <span>{{ $reply->user->name }}</span>
                                @if($reply->user_id !== $ticket->user_id)
                                <span class="admin-badge">{{ trans('app.Support') }}</span>
                                @endif
                            </div>
                            <div class="message-date">
                                {{ $reply->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <div class="message-content">
                            {{ nl2br(e($reply->message)) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Reply Form -->
            @if(in_array($ticket->status, ['open', 'pending']))
            <div class="form-section">
                <h3>{{ trans('app.Add Reply') }}</h3>

                <form action="{{ route('user.tickets.reply', $ticket) }}" method="POST" class="ticket-form">
                    @csrf
                    <div class="form-group">
                        <label for="message">{{ trans('app.Message') }} <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="6" class="form-textarea"
                            placeholder="{{ trans('app.Type your reply here...') }}" required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="user-action-button">
                            <i class="fas fa-paper-plane"></i>
                            {{ trans('app.Send Reply') }}
                        </button>
                    </div>
                </form>
            </div>
            @else
            <!-- Closed Ticket Message -->
            <div class="quick-help-section">
                <div class="quick-help-header">
                    <h3>{{ trans('app.Ticket Closed') }}</h3>
                    <p>{{ trans('app.This ticket has been closed and no further replies can be added') }}</p>
                    @if($ticket->status === 'resolved')
                    <p class="resolved-note">{{ trans('app.This ticket has been resolved') }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Ticket Actions -->
            <div class="form-actions">
                <a href="{{ route('user.tickets.index') }}" class="user-action-button secondary">
                    <i class="fas fa-arrow-left"></i>
                    {{ trans('app.Back to Tickets') }}
                </a>

                <a href="{{ route('kb.index') }}" class="user-action-button">
                    <i class="fas fa-book"></i>
                    {{ trans('app.Knowledge Base') }}
                </a>
            </div>
        </div>
    </div>
</div>


@endsection