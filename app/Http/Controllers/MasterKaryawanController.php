<?php
// MasterKaryawanController.php - Optimized for PostgreSQL
namespace App\Http\Controllers;

use App\Models\MasterKaryawan;
use Illuminate\Http\Request;

class MasterKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterKaryawan::query();
        
        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $query->search($request->search);
        }
        
        // Sorting
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        if ($request->has('per_page')) {
            $karyawan = $query->paginate($request->get('per_page', 15));
        } else {
            $karyawan = $query->get();
        }

        return response()->json([
            'success' => true,
            'data' => $karyawan
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_npk' => 'required|string|max:5|unique:master_karyawan,no_npk',
            'nama' => 'required|string|max:50',
            'alamat' => 'required|string|max:50',
        ]);

        $karyawan = MasterKaryawan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil tambah data karyawan',
            'data' => $karyawan
        ], 201);
    }
}
