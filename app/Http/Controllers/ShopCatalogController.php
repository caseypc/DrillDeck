<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopBallOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShopCatalogController extends Controller
{
    private const KINDS = ['brand', 'ball_name', 'core_type', 'coverstock_type', 'surface'];

    private function shop(Request $request): Shop
    {
        $shop = $request->user()->shops()->wherePivotIn('role', ['owner', 'manager'])->first();
        abort_unless($shop, 403);
        return $shop;
    }

    public function index(Request $request)
    {
        $shop = $this->shop($request);
        $options = $shop->ballOptions()->with('parent')
            ->orderBy('kind')->orderBy('sort_order')->orderBy('id')->get();
        return view('shop.catalog', compact('shop', 'options'));
    }

    private function validateParent(Shop $shop, string $kind, ?int $parentId): ?int
    {
        if ($kind !== 'ball_name') return null;
        if ($parentId === null || ! $shop->ballOptions()
            ->whereKey($parentId)->where('kind', 'brand')->where('active', true)->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Select an active manufacturer belonging to this shop.',
            ]);
        }
        return $parentId;
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $this->shop($request);
        $data = $request->validate([
            'kind' => ['required', Rule::in(self::KINDS)],
            'label' => ['required', 'string', 'max:160'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($shop, $data) {
            $shop = Shop::whereKey($shop->id)->lockForUpdate()->firstOrFail();
            $kind = $data['kind'];
            $parentId = $this->validateParent($shop, $kind, $data['parent_id'] ?? null);
            $label = trim($data['label']);

            if ($shop->ballOptions()->where('kind', $kind)->where('parent_id', $parentId)
                ->where('label', $label)->exists()) {
                throw ValidationException::withMessages(['label' => 'This option already exists.']);
            }

            $shop->ballOptions()->create([
                'kind' => $kind, 'label' => $label, 'parent_id' => $parentId,
                'active' => true,
                'sort_order' => ((int) $shop->ballOptions()->where('kind', $kind)->max('sort_order')) + 1,
            ]);
        });

        return back()->with('status', 'Catalog option added.');
    }

    public function update(Request $request, ShopBallOption $option): RedirectResponse
    {
        $shop = $this->shop($request);
        abort_unless($option->shop_id === $shop->id, 404);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:160'],
            'active' => ['required', 'boolean'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($shop, $option, $data) {
            $option = $shop->ballOptions()->whereKey($option->id)->lockForUpdate()->firstOrFail();
            $parentId = $option->kind === 'ball_name'
                ? $this->validateParent($shop, $option->kind,
                    array_key_exists('parent_id', $data) && $data['parent_id'] !== null
                        ? (int) $data['parent_id']
                        : $option->parent_id)
                : null;

            if ($option->kind === 'brand' && ! (bool) $data['active']
                && $shop->ballOptions()->where('parent_id', $option->id)->where('active', true)->exists()) {
                throw ValidationException::withMessages([
                    'active' => 'Disable or delete this manufacturer’s products first.',
                ]);
            }

            $label = trim($data['label']);
            if ($shop->ballOptions()->where('kind', $option->kind)
                ->where('parent_id', $parentId)->where('label', $label)
                ->where('id', '!=', $option->id)->exists()) {
                throw ValidationException::withMessages(['label' => 'This option already exists.']);
            }

            $option->update([
                'label' => $label, 'active' => (bool) $data['active'], 'parent_id' => $parentId,
            ]);
        });

        return back()->with('status', 'Catalog option updated.');
    }

    public function destroy(Request $request, ShopBallOption $option): RedirectResponse
    {
        $shop = $this->shop($request);
        abort_unless($option->shop_id === $shop->id, 404);

        DB::transaction(function () use ($shop, $option) {
            $option = $shop->ballOptions()->whereKey($option->id)->lockForUpdate()->firstOrFail();
            if ($shop->ballOptions()->where('parent_id', $option->id)->exists()) {
                throw ValidationException::withMessages([
                    'option' => 'Delete this brand’s product options before deleting the brand.',
                ]);
            }
            // Customer ball snapshots retain their text values when a dropdown entry is deleted.
            $option->delete();
        });

        return back()->with('status', 'Catalog option deleted.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $data = $request->validate([
            'kind' => ['required', Rule::in(self::KINDS)],
            'option_ids' => ['required', 'array', 'min:1'],
            'option_ids.*' => ['required', 'integer', 'distinct',
                Rule::exists('shop_ball_options', 'id')
                    ->where('shop_id', $shop->id)->where('kind', $request->input('kind'))],
        ]);

        DB::transaction(function () use ($shop, $data) {
            Shop::whereKey($shop->id)->lockForUpdate()->firstOrFail();
            $ids = $shop->ballOptions()->where('kind', $data['kind'])
                ->pluck('id')->sort()->values()->all();
            $submitted = collect($data['option_ids'])->sort()->values()->all();

            if ($ids !== $submitted) {
                throw ValidationException::withMessages([
                    'option_ids' => 'The catalog changed. Refresh before reordering again.',
                ]);
            }

            foreach ($data['option_ids'] as $position => $id) {
                $shop->ballOptions()->whereKey($id)->update(['sort_order' => $position]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
