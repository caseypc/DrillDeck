<form method="POST" action="{{ route('shop.customers.fittings.store',$customer) }}" class="row g-3">
    @csrf
    <div class="col-sm-6"><label class="form-label" for="fit-name">Spec sheet name</label>
        <input class="form-control" id="fit-name" name="name" maxlength="120" value="{{ old('name',$source?->name) }}" placeholder="e.g. First grip"></div>
    <div class="col-sm-3"><label class="form-label" for="fit-hand">Bowler hand</label>
        <select class="form-select" id="fit-hand" name="handedness"><option value="">Not recorded</option>
            @foreach(['right'=>'Right','left'=>'Left'] as $value=>$label)<option value="{{ $value }}" @selected(old('handedness',$source?->handedness)===$value)>{{ $label }}</option>@endforeach
        </select></div>
    <div class="col-sm-3"><label class="form-label" for="fit-grip">Grip type</label>
        <select class="form-select" id="fit-grip" name="grip_style"><option value="">Not recorded</option>
            @foreach(['fingertip'=>'Fingertip','conventional'=>'Conventional','no-thumb'=>'No thumb'] as $value=>$label)<option value="{{ $value }}" @selected(old('grip_style',$source?->grip_style)===$value)>{{ $label }}</option>@endforeach
        </select></div>

    @foreach([
        'Middle finger span'=>['middle_span'=>'Full','middle_span_cut'=>'Cut','middle_span_outer_cut'=>'Outer to cut'],
        'Ring finger span'=>['ring_span'=>'Full','ring_span_cut'=>'Cut','ring_span_outer_cut'=>'Outer to cut'],
        'Finger holes and bridge'=>['middle_finger_size'=>'Middle size','ring_finger_size'=>'Ring size','bridge'=>'Bridge','middle_insert_type'=>'Middle insert','ring_insert_type'=>'Ring insert'],
        'Middle finger pitch'=>['middle_pitch'=>'Original notes','middle_pitch_lateral'=>'Lateral','middle_pitch_forward'=>'Forward','middle_pitch_reverse'=>'Reverse'],
        'Ring finger pitch'=>['ring_pitch'=>'Original notes','ring_pitch_lateral'=>'Lateral','ring_pitch_forward'=>'Forward','ring_pitch_reverse'=>'Reverse'],
        'Thumb pitches and information'=>['thumb_size'=>'Thumb hole size','thumb_pitch'=>'Original notes','thumb_pitch_lateral'=>'Lateral','thumb_pitch_forward'=>'Forward','thumb_pitch_reverse'=>'Reverse','thumb_flexibility'=>'Flexibility','thumb_insert_type'=>'Thumb insert type'],
        'Thumb oval'=>['thumb_oval_type'=>'Type','thumb_start_bit'=>'Starting bit','thumb_oval_width'=>'Oval width','thumb_oval_degree'=>'Oval degree','thumb_insert_od'=>'Insert OD','thumb_taper_size'=>'Taper size'],
        'Bowler axis'=>['pap'=>'PAP']
    ] as $section=>$fields)
        <div class="col-12 mt-4"><h3 class="h6 text-uppercase text-primary border-bottom pb-2">{{ $section }}</h3></div>
        @foreach($fields as $key=>$label)
            <div class="col-sm-6">
                <label class="form-label" for="fit-{{ $key }}">{{ $label }}</label>
                <input class="form-control" id="fit-{{ $key }}" name="{{ $key }}"
                    maxlength="{{ $key==='notes'?4000:120 }}"
                    value="{{ old($key,$source?->$key) }}"
                    placeholder="Enter measured value">
            </div>
        @endforeach
    @endforeach
    <div class="col-12"><label class="form-label" for="fit-notes">Additional fitter notes</label>
        <textarea class="form-control" name="notes" id="fit-notes" rows="3" maxlength="4000">{{ old('notes',$source?->notes) }}</textarea></div>
    <div class="col-12">
        <div class="alert alert-warning small">Draft measurements only. The grip diagram is schematic, not to scale. No oval calculator or safe drill-depth recommendation has been implemented without professional validation.</div>
        <button class="btn btn-primary w-100">Save new fitting revision</button>
    </div>
</form>
