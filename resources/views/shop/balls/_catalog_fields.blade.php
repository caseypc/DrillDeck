{{-- Shared shop-configurable ball catalog dropdowns. --}}
<div data-ball-form class="row g-3">
    <div class="col-md-6">
        <label for="manufacturer-{{ $formId }}" class="form-label">Brand / Manufacturer</label>
        <select id="manufacturer-{{ $formId }}" name="manufacturer" class="form-select" data-ball-brand>
            <option value="">Select a brand</option>
            @foreach($ballOptions->where('kind','brand') as $option)
                <option value="{{ $option->label }}" data-option-id="{{ $option->id }}">{{ $option->label }}</option>
            @endforeach
            <option value="__other__">Other / Not Listed</option>
        </select>
        <input name="manufacturer_custom" class="form-control mt-2 d-none" data-custom-for="manufacturer" maxlength="120" placeholder="Enter manufacturer">
    </div>
    <div class="col-md-6">
        <label for="ball-name-{{ $formId }}" class="form-label">Ball Name / Product</label>
        <select id="ball-name-{{ $formId }}" name="ball_name" class="form-select" data-ball-product required>
            <option value="">Select product</option>
            @foreach($ballOptions->where('kind','ball_name') as $option)
                <option data-parent="{{ $option->parent_id }}" value="{{ $option->label }}">{{ $option->label }}</option>
            @endforeach
            <option value="__other__">Other / Not Listed</option>
        </select>
        <input name="ball_name_custom" class="form-control mt-2 d-none" data-custom-for="ball_name" maxlength="160" placeholder="Enter ball product name">
    </div>
    <div class="col-12">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#extra-ball-{{ $formId }}" aria-expanded="false">
            <i class="fa-solid fa-sliders me-1"></i> More ball specifications
        </button>
    </div>
    <div class="col-12 collapse" id="extra-ball-{{ $formId }}">
        <div class="row g-3 border rounded p-2 mx-0">
            @foreach(['core_type'=>'Core Type','coverstock_type'=>'Coverstock Type','surface'=>'Surface / Finish'] as $kind=>$label)
            <div class="col-md-4">
                <label class="form-label" for="{{ $kind }}-{{ $formId }}">{{ $label }}</label>
                <select class="form-select" id="{{ $kind }}-{{ $formId }}" name="{{ $kind }}" data-custom-select="{{ $kind }}">
                    <option value="">Not recorded</option>
                    @foreach($ballOptions->where('kind',$kind) as $option)
                        <option value="{{ $option->label }}">{{ $option->label }}</option>
                    @endforeach
                    <option value="__other__">Other / Not Listed</option>
                </select>
                <input class="form-control mt-2 d-none" data-custom-for="{{ $kind }}" name="{{ $kind }}_custom" maxlength="120" placeholder="Custom {{ strtolower($label) }}">
            </div>
            @endforeach
            @foreach(['serial_number'=>'Serial number','pin_distance'=>'Pin distance','top_weight'=>'Top weight','color'=>'Color'] as $key=>$label)
            <div class="col-md-6"><label class="form-label" for="{{ $key }}-{{ $formId }}">{{ $label }}</label>
                <input class="form-control" id="{{ $key }}-{{ $formId }}" name="{{ $key }}" maxlength="120" autocomplete="on">
            </div>
            @endforeach
            <div class="col-12"><label class="form-label" for="ball-notes-{{ $formId }}">Ball notes</label>
                <textarea class="form-control" id="ball-notes-{{ $formId }}" name="notes" rows="2" maxlength="4000"></textarea>
            </div>
        </div>
    </div>
</div>
