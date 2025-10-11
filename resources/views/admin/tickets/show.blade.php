@extends('layouts.admin')

@section('admin-content')
<div class="container-fluid products-form">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 text-dark">
                                <i class="fas fa-eye text-info me-2"></i>
                                {{ trans('app.View Ticket') }} #{{ $ticket->id }}
                            </h1>
                            <p class="text-muted mb-0">{{ $ticket->subject }}</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-primary me-2">
                                <i class="fas fa-edit me-1"></i>
                                {{ trans('app.Edit Ticket') }}
                            </a>
                            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ trans('app.Back to Tickets') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Overview -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>
                        {{ trans('app.Ticket Overview') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-dark mb-3">{{ $ticket->subject }}</h4>
                            <div class="mb-3">
                                <h6 class="text-muted">{{ trans('app.Ticket Description') }}</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ nl2br(e($ticket->content)) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-3">
                                    <span
                                        class="badge bg-{{ $ticket->priority == 'low' ? 'success' : ($ticket->priority == 'medium' ? 'warning' : 'danger') }} fs-6">
                                        {{ trans('app.' . ucfirst($ticket->priority)) }}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <span
                                        class="badge bg-{{ $ticket->status == 'open' ? 'success' : ($ticket->status == 'pending' ? 'warning' : ($ticket->status == 'resolved' ? 'info' : 'secondary')) }} fs-6">
                                        {{ trans('app.' . ucfirst($ticket->status)) }}
                                    </span>
                                </div>
                                @if($ticket->category)
                                <div class="mb-3">
                                    <span class="badge category-badge" data-color="{{ $ticket->category->color }}">
                                        {{ $ticket->category->name }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Knowledge Base Integration -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book me-2"></i>
                        {{ trans('app.Knowledge Base Integration') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('app.Insert Article') }}</label>
                            <select id="kb-article-select" class="form-select">
                                <option value="">{{ trans('app.Select an Article') }}</option>
                                @foreach(\App\Models\KbArticle::published()->get() as $article)
                                <option value="{{ $article->id }}" data-title="{{ $article->title }}"
                                    data-content="{{ $article->content }}">
                                    {{ $article->title }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" data-action="insert-kb-article" class="btn btn-info btn-sm mt-2">
                                <i class="fas fa-file-alt me-1"></i>{{ trans('app.Insert Article') }}
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ trans('app.Insert Category Link') }}</label>
                            <select id="kb-category-select" class="form-select">
                                <option value="">{{ trans('app.Select a Category') }}</option>
                                @foreach(\App\Models\KbCategory::all() as $category)
                                <option value="{{ route('kb.category', $category->slug) }}">
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" data-action="insert-kb-category-link"
                                class="btn btn-outline-info btn-sm mt-2">
                                <i class="fas fa-link me-1"></i>{{ trans('app.Insert Link') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Replies Section -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments me-2"></i>
                        {{ trans('app.Replies') }} ({{ $ticket->replies->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($ticket->replies as $reply)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div
                                class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 user-avatar-small">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ optional($reply->user)->name ?? trans('app.Admin') }}</h6>
                                <small class="text-muted">{{ $reply->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="ms-5">
                            {{ nl2br(e($reply->message)) }}
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-comments text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">{{ trans('app.No Replies Yet') }}</h5>
                        <p class="text-muted">{{ trans('app.Start the conversation by adding the first reply below') }}
                        </p>
                    </div>
                    @endforelse

                    <!-- Add Reply Form - Always Available for Admin -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-reply me-1"></i>
                            {{ trans('app.Add Reply') }}
                            @if($ticket->status === 'closed' || $ticket->status === 'resolved')
                            <span class="badge bg-warning ms-2">{{ trans('app.Ticket Closed') }}</span>
                            @endif
                        </h6>
                        <form method="post" action="{{ route('admin.tickets.reply', $ticket) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    {{ trans('app.Your Reply') }} <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('message') is-invalid @enderror" id="message"
                                    name="message" rows="6" data-summernote="true" data-toolbar="standard"
                                    data-placeholder="{{ trans('app.Type your reply here') }}"
                                    placeholder="{{ trans('app.Type your reply here') }}" required></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ trans('app.Use the rich text editor to format your reply') }}
                                    @if($ticket->status === 'closed' || $ticket->status === 'resolved')
                                    <br><i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                    {{ trans('app.Note: This ticket is closed, but you can still add a reply') }}
                                    @endif
                                </div>
                                @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i>{{ trans('app.Send Reply') }}
                                </button>
                                @if($ticket->status === 'closed' || $ticket->status === 'resolved')
                                <button type="button" class="btn btn-outline-primary" onclick="reopenTicket()">
                                    <i class="fas fa-unlock me-1"></i>{{ trans('app.Reopen Ticket') }}
                                </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        {{ trans('app.Quick Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($ticket->status !== 'resolved')
                        <form method="post" action="{{ route('admin.tickets.update-status', $ticket) }}"
                            class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="resolved">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check me-1"></i>{{ trans('app.Mark as Resolved') }}
                            </button>
                        </form>
                        @endif

                        @if($ticket->status !== 'pending')
                        <form method="post" action="{{ route('admin.tickets.update-status', $ticket) }}"
                            class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-clock me-1"></i>{{ trans('app.Mark as Pending') }}
                            </button>
                        </form>
                        @endif

                        @if($ticket->status !== 'closed')
                        <form method="post" action="{{ route('admin.tickets.update-status', $ticket) }}"
                            class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="closed">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-lock me-1"></i>{{ trans('app.Mark as Closed') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ticket Details -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ trans('app.Ticket Details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-primary">#{{ $ticket->id }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Ticket ID') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-success">{{ $ticket->replies->count() }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Replies') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-info">{{ $ticket->created_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Created') }}</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stats-card">
                                <h4 class="text-warning">{{ $ticket->updated_at->format('M Y') }}</h4>
                                <p class="text-muted small mb-0">{{ trans('app.Updated') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ trans('app.Customer Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div
                            class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 user-avatar-large">
                            <i class="fas fa-user fs-4"></i>
                        </div>
                        <h6>{{ $ticket->user->name ?? trans('app.Unknown User') }}</h6>
                        <p class="text-muted small mb-3">{{ $ticket->user->email ?? trans('app.No Email') }}</p>
                        @if($ticket->user)
                        <a href="{{ route('admin.users.show', $ticket->user) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>{{ trans('app.View User') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- License Information -->
            @if($ticket->user && $ticket->user->licenses->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        {{ trans('app.License Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($ticket->user->licenses as $license)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">{{ $license->product->name ?? trans('app.Unknown Product') }}</h6>
                            <span class="badge {{ $license->support_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $license->support_active ? trans('app.Active') : trans('app.Expired') }}
                            </span>
                        </div>
                        <div class="small text-muted">
                            <div class="mb-1">
                                <strong>{{ trans('app.Code') }}:</strong>
                                <code class="bg-light px-1 rounded">{{ $license->purchase_code }}</code>
                            </div>
                            <div class="mb-1">
                                <strong>{{ trans('app.Type') }}:</strong> {{ $license->license_type }}
                            </div>
                            <div>
                                <strong>{{ trans('app.Support') }}:</strong>
                                <span class="{{ $license->support_active ? 'text-success' : 'text-danger' }}">
                                    {{ $license->support_expires_at ? $license->support_expires_at->format('M d, Y') :
                                    'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Invoice Information -->
            @if($ticket->invoice)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        {{ trans('app.Invoice Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h6>{{ $ticket->invoice->invoice_number }}</h6>
                        <p class="text-muted small mb-2">${{ number_format($ticket->invoice->amount, 2) }} - {{
                            ucfirst($ticket->invoice->status) }}</p>
                        @if($ticket->invoice->product)
                        <p class="text-muted small mb-3">{{ trans('app.Product') }}: {{ $ticket->invoice->product->name
                            }}</p>
                        @endif
                        <a href="{{ route('admin.invoices.show', $ticket->invoice) }}"
                            class="btn btn-outline-info btn-sm">
                            <i class="fas fa-eye me-1"></i>{{ trans('app.View Invoice') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection