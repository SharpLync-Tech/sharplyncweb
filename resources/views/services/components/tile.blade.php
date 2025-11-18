{{-- resources/views/services/components/tile.blade.php --}}
<div class="service-tile" data-id="{{ $cat['id'] }}">
    <div class="tile-inner">
        <div class="tile-header">
            <img src="{{ asset($cat['icon']) }}" 
                 alt="{{ $cat['title'] }}" 
                 class="tile-icon">

            <h3>{{ $cat['title'] }}</h3>
            <p>{{ $cat['short'] }}</p>

            <button type="button" class="tile-toggle">
                Learn More
            </button>
        </div>

        <div class="tile-detail">
            <p>{{ $cat['long'] }}</p>

            @if (!empty($cat['subs']))
                <h4>Included Services</h4>
                <ul>
                    @foreach ($cat['subs'] as $sub)
                        <li>{{ $sub }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
