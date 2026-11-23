{{-- Read-only orientation reference, not a cutting/drilling coordinate map. --}}
@php($fitting = $ball->customerProfile?->latestFitting())
@php($middle = $fitting?->middle_finger_size ?? $source?->middle_span)
@php($ring = $fitting?->ring_finger_size ?? $source?->ring_span)
@php($thumb = $fitting?->thumb_size ?? $source?->thumb_size)
<div class="card drilldeck-grip-reference">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between flex-wrap gap-2 align-items-start">
            <div>
                <h3 class="h6 mb-1">Grip specification reference</h3>
                <p class="small text-secondary mb-0">Fingers at top · thumb at bottom</p>
            </div>
            @if($fitting)
                <a class="btn btn-sm btn-outline-secondary"
                   href="{{ route('shop.customers.fittings.show', [$ball->customerProfile, $fitting]) }}">
                    Fitting v{{ $fitting->revision }}
                </a>
            @endif
        </div>

        <svg viewBox="0 0 520 450" class="w-100 my-2"
            role="img" aria-label="Static grip reference: Middle finger top left, ring finger top right, thumb below">
            <line x1="260" y1="32" x2="260" y2="432" stroke="#c7d2e2" stroke-dasharray="5 7"/>
            <g stroke="#6f879f" stroke-width="2" fill="none">
                <path d="M154 135 L220 337 M366 135 L300 337"/>
                <path d="M215 97 H305"/>
            </g>
            <g fill="#e3f3fc" stroke="#1169bd" stroke-width="8">
                <circle cx="151" cy="104" r="59"/>
                <circle cx="369" cy="104" r="59"/>
                <circle cx="260" cy="361" r="69"/>
            </g>
            <g fill="#10263d" text-anchor="middle" font-family="system-ui, sans-serif">
                <text x="151" y="92" font-size="15">MIDDLE</text>
                <text x="151" y="115" font-size="19" font-weight="700">{{ \Illuminate\Support\Str::limit($middle ?: '—', 12) }}</text>
                <text x="369" y="92" font-size="15">RING</text>
                <text x="369" y="115" font-size="19" font-weight="700">{{ \Illuminate\Support\Str::limit($ring ?: '—', 12) }}</text>
                <text x="260" y="351" font-size="16">THUMB</text>
                <text x="260" y="377" font-size="21" font-weight="700">{{ \Illuminate\Support\Str::limit($thumb ?: '—', 12) }}</text>
            </g>
            <g fill="#344a60" font-size="15" font-family="system-ui, sans-serif" text-anchor="middle">
                <text x="147" y="215">Span {{ $fitting?->middle_span ?? $source?->middle_span ?? '—' }}</text>
                <text x="374" y="215">Span {{ $fitting?->ring_span ?? $source?->ring_span ?? '—' }}</text>
                <text x="260" y="29">Bridge {{ $fitting?->bridge ?? $source?->bridge ?? '—' }}</text>
                <text x="260" y="445">Hand: {{ ucfirst($fitting?->handedness ?? $source?->handedness ?? 'not recorded') }}</text>
            </g>
        </svg>

        <p class="small text-secondary mb-0">
            A static, not-to-scale view based on the customer's recorded fitting.
            Do not use the drawing to determine hole positions or drill angles.
        </p>
    </div>
</div>
