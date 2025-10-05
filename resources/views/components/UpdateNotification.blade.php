@if(auth()->check() && auth()->user()->is_admin)
<div id="update-notification" class="update-notification">
    <div class="update-notification-content">
        <div class="update-notification-icon">
            <i class="fas fa-download"></i>
        </div>
        <div class="update-notification-text">
            <h6>{{ trans('app.Update Available') }}</h6>
            <p>{{ trans('app.A new version is available for your system') }}</p>
        </div>
        <div class="update-notification-actions">
            <a href="{{ route('admin.updates.index') }}" class="btn btn-sm btn-warning">
                {{ trans('app.Update Now') }}
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="dismissUpdateNotification()">
                {{ trans('app.Later') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="dismissUpdateNotificationPermanently()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

@endif
