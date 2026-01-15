@php
    $path = '/' . ltrim(request()->path(), '/');

    $nav = [
        [
            'key' => 'home',
            'label' => 'Home',
            'icon' => 'home-outline',
            'url' => '/app/sharpfleet/mobile',
            'active_when' => ['/app/sharpfleet/mobile'],
            'match_children' => false,
        ],
        [
            'key' => 'start',
            'label' => 'Start',
            'icon' => 'play-circle-outline',
            'url' => '/app/sharpfleet/mobile/start',
            'data_sheet_open' => 'start-trip',
            'active_when' => ['/app/sharpfleet/mobile/start'],
            'match_children' => true,
        ],
        [
            'key' => 'history',
            'label' => 'History',
            'icon' => 'time-outline',
            'url' => '/app/sharpfleet/mobile/history',
            'active_when' => ['/app/sharpfleet/mobile/history'],
            'match_children' => true,
        ],
        [
            'key' => 'more',
            'label' => 'More',
            'icon' => 'menu-outline',
            'url' => '/app/sharpfleet/mobile/more',
            'active_when' => ['/app/sharpfleet/mobile/more'],
            'match_children' => true,
        ],
    ];

    $isActive = function(array $item) use ($path) {
        foreach (($item['active_when'] ?? []) as $match) {
            if ($path === $match) return true;
            if (($item['match_children'] ?? true) && str_starts_with($path, rtrim($match, '/') . '/')) {
                return true;
            }
        }
        return false;
    };
@endphp

<nav class="sf-mobile-footer" aria-label="SharpFleet Mobile Navigation">
    <div class="sf-mobile-footer-blur">
        <div class="sf-mobile-footer-tint"></div>

        <div class="sf-mobile-footer-inner">
            @foreach ($nav as $item)
                @php $active = $isActive($item); @endphp

                <a
                    href="{{ $item['url'] }}"
                    class="sf-mobile-footer-item {{ $active ? 'active' : '' }}"
                    {{ isset($item['data_sheet_open']) ? 'data-sheet-open=' . $item['data_sheet_open'] : '' }}
                >
                    <span class="sf-footer-icon-wrap">
                        @if($active)
                            <span class="sf-footer-glow">
                                <ion-icon name="{{ $item['icon'] }}"></ion-icon>
                            </span>
                        @endif

                        <ion-icon name="{{ $item['icon'] }}" class="sf-footer-icon"></ion-icon>
                    </span>

                    <span class="sf-footer-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
