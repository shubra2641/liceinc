@props(['siteName' => config('app.name')])

<!-- Enhanced User Footer with Need More Help Section -->
<footer class="user-footer-enhanced">
    <!-- Need More Help Section -->
    <div class="user-kb-premium-help">
        <div class="user-kb-help-background">
            <div class="user-kb-help-pattern"></div>
        </div>
        
        <div class="user-kb-help-container">
            <div class="user-kb-help-header">
                <div class="user-kb-help-icon-wrapper">
                    <div class="user-kb-help-icon">
                        <i class="fas fa-life-ring"></i>
                    </div>
                    <div class="user-kb-help-icon-glow"></div>
                </div>
                <div class="user-kb-help-content">
                    <h3 class="user-kb-help-title">
                        {{ trans('app.Need More Help?') }}
                    </h3>
                    <p class="user-kb-help-subtitle">
                        {{ trans('app.We\'re here to help you succeed') }}
                    </p>
                    <p class="user-kb-help-description">
                        {{ trans('app.Can\'t find what you\'re looking for? Our expert support team is standing by to provide personalized assistance and help you get the most out of our platform.') }}
                    </p>
                </div>
            </div>
            
            <div class="user-kb-help-features">
                <div class="user-kb-help-feature">
                    <div class="user-kb-feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="user-kb-feature-content">
                        <h4 class="user-kb-feature-title">{{ trans('app.24/7 Support') }}</h4>
                        <p class="user-kb-feature-description">{{ trans('app.Round-the-clock assistance') }}</p>
                    </div>
                </div>
                
                <div class="user-kb-help-feature">
                    <div class="user-kb-feature-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="user-kb-feature-content">
                        <h4 class="user-kb-feature-title">{{ trans('app.Expert Team') }}</h4>
                        <p class="user-kb-feature-description">{{ trans('app.Professional assistance') }}</p>
                    </div>
                </div>
                
                <div class="user-kb-help-feature">
                    <div class="user-kb-feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div class="user-kb-feature-content">
                        <h4 class="user-kb-feature-title">{{ trans('app.Fast Response') }}</h4>
                        <p class="user-kb-feature-description">{{ trans('app.Quick resolution times') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="user-kb-help-actions">
                <a href="{{ route('support.tickets.create') }}" class="user-kb-help-btn user-kb-help-btn-primary">
                    <div class="user-kb-btn-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="user-kb-btn-content">
                        <span class="user-kb-btn-title">{{ trans('app.Contact Support') }}</span>
                        <span class="user-kb-btn-subtitle">{{ trans('app.Get personalized help') }}</span>
                    </div>
                    <div class="user-kb-btn-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <a href="{{ route('kb.search') }}" class="user-kb-help-btn user-kb-help-btn-secondary">
                    <div class="user-kb-btn-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="user-kb-btn-content">
                        <span class="user-kb-btn-title">{{ trans('app.Search Again') }}</span>
                        <span class="user-kb-btn-subtitle">{{ trans('app.Find more articles') }}</span>
                    </div>
                    <div class="user-kb-btn-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
                
                <a href="{{ route('kb.index') }}" class="user-kb-help-btn user-kb-help-btn-tertiary">
                    <div class="user-kb-btn-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="user-kb-btn-content">
                        <span class="user-kb-btn-title">{{ trans('app.Browse All') }}</span>
                        <span class="user-kb-btn-subtitle">{{ trans('app.Explore knowledge base') }}</span>
                    </div>
                    <div class="user-kb-btn-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
            
            <div class="user-kb-help-footer">
                <div class="user-kb-help-stats">
                    <div class="user-kb-stat-item">
                        <span class="user-kb-stat-number">99%</span>
                        <span class="user-kb-stat-label">{{ trans('app.Satisfaction Rate') }}</span>
                    </div>
                    <div class="user-kb-stat-divider"></div>
                    <div class="user-kb-stat-item">
                        <span class="user-kb-stat-number">&lt;2h</span>
                        <span class="user-kb-stat-label">{{ trans('app.Avg Response') }}</span>
                    </div>
                    <div class="user-kb-stat-divider"></div>
                    <div class="user-kb-stat-item">
                        <span class="user-kb-stat-number">24/7</span>
                        <span class="user-kb-stat-label">{{ trans('app.Availability') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>


</footer>
