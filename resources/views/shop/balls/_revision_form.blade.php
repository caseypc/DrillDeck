{{-- Save creates a new immutable layout revision for the selected physical ball. --}}
<form action="{{ route('shop.balls.specs.store',$ball) }}" method="POST" class="row g-3">
    @csrf
    @if(isset($order) && $order)<input type="hidden" name="order_id" value="{{ $order->id }}">@endif
    @if($ball->customerProfile)
    <div class="col-12"><label for="layout-fitting" class="form-label">Customer baseline hand fitting</label>
        <select id="layout-fitting" class="form-select" name="fitting_spec_id">
            <option value="">None — legacy measurements</option>
            @foreach($ball->customerProfile->fittings()->orderByDesc('revision')->get() as $fit)
                <option value="{{ $fit->id }}" @selected(old('fitting_spec_id',$source?->fitting_spec_id)==$fit->id)>
                    {{ $fit->name ?: 'Fitting revision '.$fit->revision }} · {{ $fit->created_at->format('M j, Y') }}
                </option>
            @endforeach
        </select>
        <div class="form-text">This links to a fixed revision of the customer's shared fitting measurements.</div>
    </div>
    @endif
    {{-- Restore the original enumerated grip controls in the primary form. --}}
    <div class="col-sm-6">
        <label class="form-label" for="drill-handedness">Bowling hand</label>
        <select class="form-select" id="drill-handedness" name="handedness">
            <option value="">Not recorded</option>
            @foreach(['right' => 'Right Hand', 'left' => 'Left Hand'] as $value => $label)
                <option value="{{ $value }}" @selected(old('handedness', $source?->handedness ?? $ball->customerProfile?->latestFitting()?->handedness) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-6">
        <label class="form-label" for="drill-grip-style">Grip type</label>
        <select class="form-select" id="drill-grip-style" name="grip_style">
            <option value="">Not recorded</option>
            @foreach(['fingertip' => 'Fingertip', 'conventional' => 'Conventional', 'no-thumb' => 'No Thumb'] as $value => $label)
                <option value="{{ $value }}" @selected(old('grip_style', $source?->grip_style ?? $ball->customerProfile?->latestFitting()?->grip_style) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12"><h4 class="h6 text-uppercase text-primary">Ball layout & surface</h4></div>
    @foreach(['drilling_angle'=>'Drilling angle (°)','pin_to_pap'=>'Pin to PAP (in)','val_angle'=>'VAL angle (°)','layout'=>'Layout notes / code','surface_finish'=>'Surface finish'] as $key=>$label)
        <div class="col-sm-6"><label class="form-label" for="drill-{{ $key }}">{{ $label }}</label>
            <input class="form-control" id="drill-{{ $key }}" name="{{ $key }}" maxlength="120"
                value="{{ old($key,$source?->$key) }}" autocomplete="on" placeholder="Not recorded">
        </div>
    @endforeach
    <div class="col-12"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#advanced-drill-fields" aria-expanded="false">Additional drill measurements</button></div>
    <div class="col-12 collapse" id="advanced-drill-fields"><div class="row g-3">
        @foreach(['middle_span'=>'Middle span','ring_span'=>'Ring span','thumb_size'=>'Thumb size','bridge'=>'Bridge','pap'=>'PAP'] as $key=>$label)
            <div class="col-sm-6"><label class="form-label" for="drill-{{ $key }}">{{ $label }}</label>
                <input class="form-control" id="drill-{{ $key }}" name="{{ $key }}" maxlength="100" value="{{ old($key,$source?->$key) }}">
            </div>
        @endforeach
        @foreach(['weight_hole_position'=>'Legacy weight-hole position','weight_hole_size'=>'Legacy weight-hole size','weight_hole_depth'=>'Legacy weight-hole depth'] as $key=>$label)
            <div class="col-sm-6"><label class="form-label" for="drill-{{ $key }}">{{ $label }}</label>
                <input class="form-control" id="drill-{{ $key }}" name="{{ $key }}" maxlength="50" value="{{ old($key,$source?->$key) }}">
            </div>
        @endforeach
        <p class="small text-warning mt-2 mb-0">Weight-hole fields are for historical documentation only; do not use them as drilling instructions.</p>
    </div></div>
    <div class="col-12"><label class="form-label" for="drill-notes">Driller notes</label>
        <textarea class="form-control" id="drill-notes" name="notes" rows="3" maxlength="4000">{{ old('notes',$source?->notes) }}</textarea>
    </div>
    <div class="col-12"><div class="alert alert-warning small">Illustrations are not calibrated; qualified staff must review all measurements before physical drilling.</div>
        <button class="btn btn-primary w-100">Save new layout revision</button>
    </div>
</form>
