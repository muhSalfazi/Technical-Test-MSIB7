<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Tambahkan ini
use App\Models\Pegawai;
use App\Models\Devisi;
use Tymon\JWTAuth\Facades\JWTAuth;

class PegawaiController extends Controller
{
    // Display a listing of employees
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'division_id' => 'nullable|uuid|exists:tbl_devisi,id',
        ]);

        $query = Pegawai::with('division');

        if ($validatedData['name'] ?? false) {
            $query->where('name', 'like', '%' . $validatedData['name'] . '%');
        }

        if ($validatedData['division_id'] ?? false) {
            $query->where('division_id', $validatedData['division_id']);
        }

        $employees = $query->paginate(10);

        // Ubah item menjadi koleksi dan ubah datanya
        $formattedEmployees = $employees->items(); // Get the items collection
        $formattedEmployees = collect($formattedEmployees)->map(function ($employee) {
            return [
                'id' => $employee->id,
                'image' => $employee->image ? Storage::url($employee->image) : null,
                'name' => $employee->name,
                'phone' => $employee->phone,
                'division' => [
                    'id' => $employee->division ? $employee->division->id : null,
                    'name' => $employee->division ? $employee->division->name : null,
                ],
                'position' => $employee->position,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Karyawan berhasil diambil',
            'data' => [
                'employees' => $formattedEmployees,
            ],
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    // Store a newly created employee
    public function store(Request $request)
    {
        // Validate and sanitize input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'division_id' => 'nullable|uuid|exists:tbl_devisi,id',
            'position' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('profile_Pegawai', 'public');
        }

        // Create a new employee record
        Pegawai::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'division_id' => $validatedData['division_id'],
            'position' => $validatedData['position'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Karyawan berhasil dibuat',
        ]);
    }

    // Update an existing employee
    public function update(Request $request, $uuid_pegawai)
    {
        // Validate and sanitize input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'division_id' => 'nullable|uuid|exists:tbl_devisi,id',
            'position' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Log request data for debugging
        Log::info('Update request data:', $request->all());

        // Find the employee by UUID
        $employee = Pegawai::where('id', $uuid_pegawai)->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        // Handle image upload if present
        if ($request->hasFile('image')) {
            // Delete the old image if exists
            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }

            $imagePath = $request->file('image')->store('profile_Pegawai', 'public');
        } else {
            $imagePath = $employee->image; // Keep the old image if no new one is provided
        }

        // Update employee record
        $employee->update([
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'division_id' => $validatedData['division_id'],
            'position' => $validatedData['position'],
            'image' => $imagePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Karyawan berhasil diperbarui',
        ]);
    }

    // Delete an existing employee
    public function destroy($uuid_pegawai)
    {
        $employee = Pegawai::where('id', $uuid_pegawai)->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        // Delete the employee's image if exists
        if ($employee->image) {
            Storage::disk('public')->delete($employee->image);
        }

        // Delete the employee record
        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Karyawan berhasil dihapus',
        ]);
    }
}