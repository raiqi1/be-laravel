<?php
// MasterItemController.php - Optimized for PostgreSQL
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterItem;

class MasterItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterItem::query();
        
        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $query->search($request->search);
        }
        
        // Filter by stock
        if ($request->has('with_stock') && $request->boolean('with_stock')) {
            $query->withStock();
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'nama_item');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        if ($request->has('per_page')) {
            $items = $query->paginate($request->get('per_page', 15));
        } else {
            $items = $query->get();
        }

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:4|unique:master_items,kode',
            'nama_item' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $item = MasterItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil tambah data item',
            'data' => $item
        ], 201);
    }
}