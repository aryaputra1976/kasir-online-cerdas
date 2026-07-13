<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'movement_type' => ['nullable', Rule::in([
                StockMovement::TYPE_IN,
                StockMovement::TYPE_OUT,
                StockMovement::TYPE_ADJUSTMENT,
            ])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $categoryId = $validated['category_id'] ?? null;
        $movementType = $validated['movement_type'] ?? null;
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;

        $baseQuery = StockMovement::query()
            ->with(['product.category'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('product', function ($productQuery) use ($categoryId) {
                    $productQuery->where('category_id', $categoryId);
                });
            })
            ->when($movementType, function ($query) use ($movementType) {
                $query->where('movement_type', $movementType);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('movement_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('movement_date', '<=', $dateTo);
            });

        $movements = (clone $baseQuery)
            ->latest('movement_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $totalMovements = (clone $baseQuery)->count();
        $stockInMovements = (clone $baseQuery)->where('movement_type', StockMovement::TYPE_IN)->count();
        $stockOutMovements = (clone $baseQuery)->where('movement_type', StockMovement::TYPE_OUT)->count();
        $adjustmentMovements = (clone $baseQuery)->where('movement_type', StockMovement::TYPE_ADJUSTMENT)->count();

        return view('stock-movements', compact(
            'movements',
            'products',
            'categories',
            'search',
            'categoryId',
            'movementType',
            'dateFrom',
            'dateTo',
            'totalMovements',
            'stockInMovements',
            'stockOutMovements',
            'adjustmentMovements'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'movement_type' => [
                'required',
                Rule::in([
                    StockMovement::TYPE_IN,
                    StockMovement::TYPE_OUT,
                    StockMovement::TYPE_ADJUSTMENT,
                ]),
            ],
            'quantity' => ['required', 'integer', 'min:0'],
            'movement_date' => ['required', 'date', 'before_or_equal:today'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => [
                Rule::requiredIf($request->input('movement_type') === StockMovement::TYPE_ADJUSTMENT),
                'nullable',
                'string',
            ],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'movement_type.required' => 'Jenis mutasi wajib dipilih.',
            'quantity.required' => 'Jumlah mutasi wajib diisi.',
            'quantity.integer' => 'Jumlah mutasi harus berupa angka.',
            'movement_date.required' => 'Tanggal mutasi wajib diisi.',
            'movement_date.before_or_equal' => 'Tanggal mutasi tidak boleh setelah hari ini.',
            'note.required' => 'Catatan wajib diisi untuk penyesuaian stok.',
        ]);

        $movementType = $validated['movement_type'];
        $inputQuantity = (int) $validated['quantity'];

        if (
            in_array($movementType, [StockMovement::TYPE_IN, StockMovement::TYPE_OUT], true)
            && $inputQuantity < 1
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah stok masuk/keluar minimal 1.',
            ]);
        }

        DB::transaction(function () use ($validated, $movementType, $inputQuantity) {
            $product = Product::query()
                ->whereKey($validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = (int) $product->stock;

            if ($movementType === StockMovement::TYPE_IN) {
                $quantityChange = $inputQuantity;
                $stockAfter = $stockBefore + $inputQuantity;
            } elseif ($movementType === StockMovement::TYPE_OUT) {
                if ($inputQuantity > $stockBefore) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Stok keluar tidak boleh lebih besar dari stok saat ini.',
                    ]);
                }

                $quantityChange = $inputQuantity * -1;
                $stockAfter = $stockBefore - $inputQuantity;
            } else {
                $stockAfter = $inputQuantity;
                $quantityChange = $stockAfter - $stockBefore;
            }

            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => $movementType,
                'quantity_change' => $quantityChange,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'movement_date' => $validated['movement_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
                'source_type' => StockMovement::SOURCE_MANUAL,
            ]);

            $product->update([
                'stock' => $stockAfter,
            ]);
        });

        return redirect()
            ->route('stocks.movements')
            ->with('success', 'Mutasi stok berhasil dicatat dan stok produk sudah diperbarui.');
    }
}
