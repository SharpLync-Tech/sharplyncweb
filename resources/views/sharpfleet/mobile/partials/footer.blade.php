@php
    $path = request()->path();

    $nav = [
        [
            'key' => 'home',
            'label' => 'Home',
            'icon' => 'home-outline',
            'url' => '/app/sharpfleet/mobile',
        ],
        [
            'key' => 'start',
            'label' => 'Start',
            'icon' => 'play-circle-outline',
            'url' => '#', // will open Start Trip sheet later
        ],
        [
            'key' => 'history',
            'label' => 'History',
            'icon' => 'time-outline',
            'url' => '/app/sharpfleet/mobile/history',
        ],
        [
            'key' => 'more',
            'label' => 'More',
            'icon' => 'menu-outline',
            'url' => '/app/sharpfleet/mobile/more',
        ],
    ];
@endphp

<nav class="sf-mobile-footer">
    <div class="sf-mobile-footer-blur">
        <div class="sf-mobile-footer-tint"></div>

        <div class="sf-mobile-footer-inner">
            @foreach ($nav as $item)
                @php
                    $active = str_starts_with($path, ltrim(parse_url($item['url'], PHP_URL_PATH), '/'));
                @endphp

                <a
                    href="{{ $item['url'] }}"
                    class="sf-mobile-footer-item {{ $active ? 'active' : '' }}"
                >
                    <span class="sf-footer-icon-wrap">
                        @if($active)
                            <span class="sf-footer-glow">
                                <ion-icon name="{{ $item['icon'] }}"></ion-icon>
                            </span>
                        @endif

                        <ion-icon
                            name="{{ $item['icon'] }}"
                            class="sf-footer-icon"
                        ></ion-icon>
                    </span>

                    <span class="sf-footer-label">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
