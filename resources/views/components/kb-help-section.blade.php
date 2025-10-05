@props([
'title' => 'Need More Help?',
'description' => 'Can\'t find what you\'re looking for? Contact our support team for personalized assistance.',
'primaryButtonText' => 'Contact Support',
'primaryButtonUrl' => 'support.tickets.create',
'secondaryButtonText' => 'Search Knowledge Base',
'secondaryButtonUrl' => 'kb.search',
'icon' => 'help'
])

<div class="kb-help-section">
    <div class="kb-help-content">
        <div class="kb-help-icon">
            @if($icon === 'help')
                <i class="fas fa-question-circle w-16 h-16"></i>
            @elseif($icon === 'search')
                <i class="fas fa-search w-16 h-16"></i>
            @elseif($icon === 'support')
                <i class="fas fa-headset w-16 h-16"></i>
            @elseif($icon === 'document')
                <i class="fas fa-file-alt w-16 h-16"></i>
            @endif
        </div>
        <h3 class="kb-help-title">
            {{ trans('app.' . $title) }}
        </h3>
        <p class="kb-help-description">
            {{ trans('app.' . $description) }}
        </p>
        <div class="kb-help-actions">
            <a href="{{ route($primaryButtonUrl) }}" class="kb-help-button kb-help-button-primary">
                <i class="fas fa-arrow-right w-5 h-5"></i>
                {{ trans('app.' . $primaryButtonText) }}
            </a>
            <a href="{{ route($secondaryButtonUrl) }}" class="kb-help-button kb-help-button-secondary">
                <i class="fas fa-search w-5 h-5"></i>
                {{ trans('app.' . $secondaryButtonText) }}
            </a>
        </div>
    </div>
</div>