<?php
// TransaksiKoperasiController.php - Complete CRUD with Enhanced Search
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKoperasi;
use App\Models\MasterKaryawan;
use App\Models\MasterItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiKoperasiController extends Controller
{
    public function index(Request $request)
    {
        $query = TransaksiKoperasi::with(['karyawan', 'item']);
        
        // Enhanced search filters
        
        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }
        
        // Filter by specific date
        if ($request->has('tanggal') && $request->tanggal) {
            $query->whereDate('tanggal_transaksi', $request->tanggal);
        }
        
        // Filter by payment status (bayar)
        if ($request->has('bayar') && $request->bayar !== '') {
            $query->where('bayar', $request->boolean('bayar'));
        }
        
        // Filter by NPK
        if ($request->has('npk') && $request->npk !== '') {
            $query->where('npk', 'LIKE', '%' . $request->npk . '%');
        }
        
        // Filter by employee name
        if ($request->has('nama_karyawan') && $request->nama_karyawan !== '') {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama', 'ILIKE', '%' . $request->nama_karyawan . '%');
            });
        }
        
        // Filter by item code or name
        if ($request->has('item') && $request->item !== '') {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('kode', 'LIKE', '%' . $request->item . '%')
                  ->orWhere('nama_item', 'ILIKE', '%' . $request->item . '%');
            });
        }
        
        // Filter by amount range
        if ($request->has('min_harga') && $request->min_harga !== '') {
            $query->where('harga', '>=', $request->min_harga);
        }
        
        if ($request->has('max_harga') && $request->max_harga !== '') {
            $query->where('harga', '<=', $request->max_harga);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'tanggal_transaksi');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['tanggal_transaksi', 'npk', 'harga', 'qty', 'bayar', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('tanggal_transaksi', 'desc');
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $transaksi = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'data' => $transaksi,
            'filters' => $request->only([
                'start_date', 'end_date', 'tanggal', 'bayar', 'npk', 
                'nama_karyawan', 'item', 'min_harga', 'max_harga'
            ])
        ]);
    }

    public function show($id)
    {
        try {
            $transaksi = TransaksiKoperasi::with(['karyawan', 'item'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $transaksi
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.'
            ], 404);
        }
    }

    public function create()
    {
        $karyawan = MasterKaryawan::select('no_npk', 'nama', 'alamat')
                                 ->orderBy('nama')
                                 ->get();
        
        $items = MasterItem::select('id', 'kode', 'nama_item', 'harga', 'stok')
                          ->where('stok', '>', 0) // Only show items with stock
                          ->orderBy('nama_item')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'karyawan' => $karyawan,
                'items' => $items
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'npk' => 'required|string|max:5|exists:master_karyawan,no_npk',
            'tanggal_transaksi' => 'required|date',
            'kode' => 'required|string|max:4|exists:master_items,kode',
            'qty' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
            'bayar' => 'required|boolean',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Check item and lock row to prevent race condition
                $item = MasterItem::where('kode', $validated['kode'])
                                 ->lockForUpdate()
                                 ->first();
                
                if (!$item) {
                    throw ValidationException::withMessages([
                        'kode' => ['Item tidak ditemukan']
                    ]);
                }

                if ($item->stok < $validated['qty']) {
                    throw ValidationException::withMessages([
                        'qty' => ["Stok tidak mencukupi. Stok tersedia: {$item->stok}"]
                    ]);
                }

                // Create transaction
                $transaksi = TransaksiKoperasi::create($validated);

                // Update item stock
                $item->decrement('stok', $validated['qty']);

                // Load relationships
                $transaksi->load(['karyawan', 'item']);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil ditambahkan.',
                    'data' => $transaksi
                ], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $transaksi = TransaksiKoperasi::findOrFail($id);
            
            $validated = $request->validate([
                'npk' => 'required|string|max:5|exists:master_karyawan,no_npk',
                'tanggal_transaksi' => 'required|date',
                'kode' => 'required|string|max:4|exists:master_items,kode',
                'qty' => 'required|integer|min:1',
                'harga' => 'required|numeric|min:0',
                'bayar' => 'required|boolean',
            ]);

            return DB::transaction(function () use ($transaksi, $validated) {
                // Get old item and new item
                $oldItem = MasterItem::where('kode', $transaksi->kode)->lockForUpdate()->first();
                $newItem = MasterItem::where('kode', $validated['kode'])->lockForUpdate()->first();
                
                if (!$newItem) {
                    throw ValidationException::withMessages([
                        'kode' => ['Item tidak ditemukan']
                    ]);
                }

                // Calculate stock changes
                $oldQty = $transaksi->qty;
                $newQty = $validated['qty'];
                
                // If item changed or quantity changed, update stock
                if ($transaksi->kode !== $validated['kode']) {
                    // Return old stock
                    if ($oldItem) {
                        $oldItem->increment('stok', $oldQty);
                    }
                    
                    // Check new item stock
                    if ($newItem->stok < $newQty) {
                        throw ValidationException::withMessages([
                            'qty' => ["Stok tidak mencukupi. Stok tersedia: {$newItem->stok}"]
                        ]);
                    }
                    
                    // Deduct new stock
                    $newItem->decrement('stok', $newQty);
                } elseif ($oldQty !== $newQty) {
                    // Same item, different quantity
                    $stockDiff = $newQty - $oldQty;
                    
                    if ($stockDiff > 0) {
                        // Need more stock
                        if ($newItem->stok < $stockDiff) {
                            throw ValidationException::withMessages([
                                'qty' => ["Stok tidak mencukupi untuk penambahan. Stok tersedia: " . ($newItem->stok + $oldQty)]
                            ]);
                        }
                        $newItem->decrement('stok', $stockDiff);
                    } else {
                        // Return excess stock
                        $newItem->increment('stok', abs($stockDiff));
                    }
                }

                // Update transaction
                $transaksi->update($validated);
                
                // Load relationships
                $transaksi->load(['karyawan', 'item']);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil diperbarui.',
                    'data' => $transaksi
                ]);
            });
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $transaksi = TransaksiKoperasi::findOrFail($id);
                
                // Return stock to item
                $item = MasterItem::where('kode', $transaksi->kode)->lockForUpdate()->first();
                if ($item) {
                    $item->increment('stok', $transaksi->qty);
                }
                
                // Delete transaction
                $transaksi->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil dihapus dan stok telah dikembalikan.'
                ]);
            });
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 500);
        }
    }

    // Additional utility methods for search
    public function searchByDate(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date'
        ]);

        $transaksi = TransaksiKoperasi::with(['karyawan', 'item'])
                                    ->whereDate('tanggal_transaksi', $request->tanggal)
                                    ->orderBy('tanggal_transaksi', 'desc')
                                    ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    public function searchByNpk(Request $request)
    {
        $request->validate([
            'npk' => 'required|string'
        ]);

        $transaksi = TransaksiKoperasi::with(['karyawan', 'item'])
                                    ->where('npk', 'LIKE', '%' . $request->npk . '%')
                                    ->orderBy('tanggal_transaksi', 'desc')
                                    ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    public function searchByPaymentStatus(Request $request)
    {
        $request->validate([
            'bayar' => 'required|boolean'
        ]);

        $transaksi = TransaksiKoperasi::with(['karyawan', 'item'])
                                    ->where('bayar', $request->boolean('bayar'))
                                    ->orderBy('tanggal_transaksi', 'desc')
                                    ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Summary/Statistics method
    public function summary(Request $request)
    {
        $query = TransaksiKoperasi::query();
        
        // Apply date filter if provided
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('tanggal_transaksi', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('tanggal_transaksi', '<=', $request->end_date);
        }

        $summary = [
            'total_transaksi' => $query->count(),
            'total_terbayar' => $query->where('bayar', true)->count(),
            'total_belum_bayar' => $query->where('bayar', false)->count(),
            'total_nilai_transaksi' => $query->sum(DB::raw('harga * qty')),
            'total_nilai_terbayar' => $query->where('bayar', true)->sum(DB::raw('harga * qty')),
            'total_nilai_belum_bayar' => $query->where('bayar', false)->sum(DB::raw('harga * qty')),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}