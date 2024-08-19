<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Pegawai;
use App\Models\Devisi;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str; // Import untuk UUID
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    // Display a listing of employees
    public function index(Request $request)
    {
        // Validate and sanitize input
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'division_id' => 'sometimes|uuid|exists:tbl_devisi,id',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
            ], 422); 
        }

        // Retrieve validated data
        $validatedData = $validator->validated();

        // Log the received parameters for debugging
        Log::info('Received parameters:', $validatedData);

        // Build the query
        $query = Pegawai::with('division');

        if (isset($validatedData['name'])) {
            $query->where('name', 'like', '%' . $validatedData['name'] . '%');
        }

        if (isset($validatedData['division_id'])) {
            $query->where('division_id', $validatedData['division_id']);
        }

        // Log the generated SQL query and bindings for debugging
        Log::info('Query SQL:', [$query->toSql(), $query->getBindings()]);

        // Get the paginated results
        $employees = $query->paginate(10);

        // Check if any employees were found
        if ($employees->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada karyawan yang ditemukan',
            ], 404);
        }

        // Transform data
        $formattedEmployees = $employees->items(); // Use items() instead of getCollection()

        $formattedEmployees = collect($formattedEmployees)->map(function ($employee) {
            return [
                'id' => $employee->id,
                'image' => $employee->image ? asset($employee->image) : null,
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'division_id' => 'required|uuid|exists:tbl_devisi,id',
            'position' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
            ], 422); // 422 for validation errors
        }

        // Retrieve validated data
        $validatedData = $validator->validated();

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = md5(time() . $image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->move(public_path('profile_Pegawai'), $imageName);
            $imagePath = 'profile_Pegawai/' . $imageName;
        }

        // Create a new employee record
        Pegawai::create([
            'id' => (string) Str::uuid(),
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
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:15',
            'division_id' => 'sometimes|nullable|uuid|exists:tbl_devisi,id',
            'position' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input',
                'errors' => $validator->errors(),
            ], 422); // 422 for validation errors
        }

        // Retrieve validated data
        $validatedData = $validator->validated();

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
            if ($employee->image && file_exists(public_path($employee->image))) {
                unlink(public_path($employee->image));
            }

            $image = $request->file('image');
            $imageName = md5(time() . $image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->move(public_path('profile_Pegawai'), $imageName);
            $imagePath = 'profile_Pegawai/' . $imageName;
        } else {
            $imagePath = $employee->image; // Keep the old image if no new one is provided
        }

        // Update employee record
        $employee->update(array_filter([
            'name' => $validatedData['name'] ?? $employee->name,
            'phone' => $validatedData['phone'] ?? $employee->phone,
            'division_id' => $validatedData['division_id'] ?? $employee->division_id,
            'position' => $validatedData['position'] ?? $employee->position,
            'image' => $imagePath,
        ]));

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
        if ($employee->image && file_exists(public_path($employee->image))) {
            unlink(public_path($employee->image));
        }

        // Delete the employee record
        $employee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Karyawan berhasil dihapus',
        ]);
    }
}