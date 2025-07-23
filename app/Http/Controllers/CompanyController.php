<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Fundraising;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Company::query();

            // Role-based filtering
            if ($user->hasRole('superadmin')) {
                // Superadmin can see all companies
                // No additional filtering needed
            } else {
                // Non-superadmin can only see their own company
                $query->where('id', $user->company_id);
            }

            // Search by company name, email, address or phone
            if ($request->has('searchBy') && !empty($request->input('searchBy'))) {
                $searchTerm = $request->input('searchBy');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('address', 'like', '%' . $searchTerm . '%')
                        ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                });
            }

            // Filter by status
            if ($request->has('sortByStatus') && !empty($request->input('sortByStatus'))) {
                $sortBy = $request->input('sortByStatus');
                if (in_array($sortBy, ['pending', 'diterima', 'ditolak'])) {
                    $query->where('status', $sortBy);
                }
            }

            // Pagination
            $perPage = $request->input('perPage', 10); // default 10 items per page
            $perPage = min($perPage, 100); // limit max items per page to 100

            $companies = $query->paginate($perPage);

            return BaseResponse::successData([
                'data' => $companies->items(),
                'pagination' => [
                    'currentPage' => $companies->currentPage(),
                    'perPage' => $companies->perPage(),
                    'total' => $companies->total(),
                    'lastPage' => $companies->lastPage(),
                    'from' => $companies->firstItem(),
                    'to' => $companies->lastItem(),
                ]
            ], 'Data perusahaan berhasil diambil');
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil data perusahaan:' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil data perusahaan: ' . $th->getMessage());
        }
    }

    public function verification(Request $request)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'status' => 'required|in:diterima,ditolak',
        ]);

        $company = Company::find($validatedData['company_id']);
        $company->status = $validatedData['status'];
        $company->save();

        return BaseResponse::successMessage('Status perusahaan berhasil diperbarui');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:companies,email,' . $id,
            'hex_color' => 'nullable|string',
            'link_default' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'fundraising_id' => 'nullable|exists:fundraisings,id',
        ]);

        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        $validatedData = $validator->validated();
        $company = Company::find($id);
        // Handle file upload for logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('logo', $filename, 'public');
            $validatedData['logo'] = $path;
        }
        if ($request->hasFile('npwp')) {
            $validator = Validator::make($request->all(), [
                'npwp' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048',
            ]);
            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }
            $file = $request->file('npwp');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('npwp', $filename, 'public');
            $validatedData['npwp'] = $path;
        }
        if ($request->hasFile('akta_pendirian')) {
            $validator = Validator::make($request->all(), [
                'akta_pendirian' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048',
            ]);
            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            $file = $request->file('akta_pendirian');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('akta_pendirian', $filename, 'public');
            $validatedData['akta_pendirian'] = $path;
        }
        $company->update($validatedData);

        return BaseResponse::successData($company->toArray(), 'Company updated successfully');
    }
    public function findCompany(Request $request, $id)
    {


        $company = Company::where('id', $id)->first();

        if (!$company) {
            return BaseResponse::errorMessage('Company not found');
        }
        if ($company->fundraising_id == '') {
            $fundraising = Fundraising::where('company_id', $company->id)->orderBy('created_at', 'desc')->first();
            $company->fundraising_id = $fundraising->id;
            $company->save();
        }
        return BaseResponse::successData($company->toArray(), 'Company found successfully');
    }
}
