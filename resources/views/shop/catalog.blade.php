@extends('layouts.shop')
@section('title', 'Ball catalog options')
@section('heading', $shop->name.' · Ball Catalog')

@section('shop-content')
<div class="mb-4">
    <p class="text-secondary mb-1">Configure the values used when adding bowling balls. Drag a handle to reorder each dropdown, edit a label, or delete an option.</p>
    <p class="text-secondary small">Deleting an option preserves existing bowling ball records. A manufacturer with products cannot be deleted until those products are removed.</p>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        @foreach([
            'brand' => 'Brands / Manufacturers',
            'ball_name' => 'Bowling Ball Products',
            'core_type' => 'Core Types',
            'coverstock_type' => 'Coverstock Types',
            'surface' => 'Surfaces / Finishes',
        ] as $kind => $heading)
            @php($items = $options->where('kind', $kind)->values())
            <section class="card mb-4" aria-labelledby="catalog-title-{{ $kind }}">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h5 mb-0" id="catalog-title-{{ $kind }}">{{ $heading }}</h2>
                        <span class="small text-secondary" aria-live="polite" role="status" data-catalog-status="{{ $kind }}"></span>
                    </div>
                    <div class="d-grid gap-2" data-catalog-list data-catalog-kind="{{ $kind }}" data-reorder-url="{{ route('shop.catalog.reorder') }}">
                        @foreach($items as $option)
                            <div class="border rounded p-2 p-md-3 bg-body" data-catalog-option-id="{{ $option->id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="catalog-drag-handle fs-4 text-secondary" role="img"
                                        aria-label="Drag to reorder {{ $option->label }}" title="Drag to reorder">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </span>
                                    <div class="flex-grow-1">
                                        <form method="POST" action="{{ route('shop.catalog.update', $option) }}" class="row g-2 align-items-center">
                                            @csrf @method('PATCH')
                                            <div class="{{ $kind === 'ball_name' ? 'col-lg-4' : 'col-lg-6' }}">
                                                <label for="catalog-label-{{ $option->id }}" class="visually-hidden">Edit {{ $option->label }}</label>
                                                <input id="catalog-label-{{ $option->id }}" name="label" class="form-control form-control-sm"
                                                    value="{{ $option->label }}" maxlength="160" required>
                                            </div>
                                            @if($kind === 'ball_name')
                                                <div class="col-lg-3">
                                                    <label class="visually-hidden" for="catalog-parent-{{ $option->id }}">Manufacturer</label>
                                                    <select class="form-select form-select-sm" id="catalog-parent-{{ $option->id }}" name="parent_id" required>
                                                        @foreach($options->where('kind','brand')->where('active', true) as $brand)
                                                            <option value="{{ $brand->id }}" @selected($brand->id === $option->parent_id)>{{ $brand->label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="col-lg-3">
                                                <label for="catalog-active-{{ $option->id }}" class="visually-hidden">Availability</label>
                                                <select id="catalog-active-{{ $option->id }}" class="form-select form-select-sm" name="active">
                                                    <option value="1" @selected($option->active)>Active</option>
                                                    <option value="0" @selected(! $option->active)>Disabled</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                                                    <button type="submit" form="delete-option-{{ $option->id }}" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete</button>
                                                </div>
                                            </div>
                                        </form>
                                        <form id="delete-option-{{ $option->id }}" method="POST" action="{{ route('shop.catalog.destroy', $option) }}"
                                            class="text-end mt-2" onsubmit="return confirm('Delete this option? Existing customer ball records will be retained.')">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($items->isEmpty()) <p class="text-secondary small mb-0">No options in this group.</p> @endif
                </div>
            </section>
        @endforeach
    </div>

    <div class="col-xl-4">
        <div class="card"><div class="card-body p-4">
            <h2 class="h5">Add catalog option</h2>
            <form method="POST" action="{{ route('shop.catalog.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="catalog-kind">Field</label>
                    <select id="catalog-kind" name="kind" class="form-select" required>
                        @foreach([
                            'brand' => 'Brand / Manufacturer',
                            'ball_name' => 'Ball Name / Product',
                            'core_type' => 'Core Type',
                            'coverstock_type' => 'Coverstock Type',
                            'surface' => 'Surface / Finish',
                        ] as $kind => $label)
                            <option value="{{ $kind }}" @selected(old('kind') === $kind)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" data-catalog-new-parent>
                    <label class="form-label" for="catalog-parent-new">Manufacturer for new product</label>
                    <select id="catalog-parent-new" name="parent_id" class="form-select">
                        <option value="">Select manufacturer</option>
                        @foreach($options->where('kind','brand')->where('active', true) as $brand)
                            <option value="{{ $brand->id }}" @selected(old('parent_id') == $brand->id)>{{ $brand->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="catalog-new-label">Name</label>
                    <input id="catalog-new-label" class="form-control" name="label" maxlength="160" value="{{ old('label') }}" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Add option</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
