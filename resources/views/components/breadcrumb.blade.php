@props(['breadcrumbs' => []])

@if(count($breadcrumbs) > 1)
<div class="bg-white px-4 py-3 border-b border-gray-200 sm:px-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    @foreach($breadcrumbs as $index => $breadcrumb)
                        @if($index > 0)
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right flex-shrink-0 h-5 w-5 text-gray-400"></i>
                                    @if($breadcrumb['app.Active'])
                                        <span class="ml-2 text-sm font-medium text-gray-500" aria-current="page">{{ $breadcrumb['title'] }}</span>
                                    @else
                                        <a href="{{ $breadcrumb['url'] }}" class="ml-2 text-sm font-medium text-gray-700 hover:text-gray-900">{{ $breadcrumb['title'] }}</a>
                                    @endif
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        </div>
    </div>
</div>
@endif