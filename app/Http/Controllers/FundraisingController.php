<?php

namespace App\Http\Controllers;

use App\Models\Fundraising;
use App\Models\FundraisingNews;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Traits\HasRoles;

class FundraisingController extends Controller
{
    //
    public function index(Request $request)
    {
        $user =  auth('sanctum')->user();
        $query = Fundraising::query();

        // Role-based filtering
        if ($user->role === "superadmin") {
            // Superadmin bisa melihat semua fundraising dengan informasi company
            $query->with('company');

            // Jika superadmin ingin filter berdasarkan company tertentu
            if ($request->has('company_name')) {
                $query->whereHas('company', function ($q) use ($request) {
                    $q->where('name', $request->input('company_name'));
                });
            }
        } else {
            // Admin company hanya bisa akses fundraise nya sendiri
            $query->where('company_id', $user->company_id)->with('company');
        }

        // Search by fundraising name
        if ($request->has('searchBy') && !empty($request->input('searchBy'))) {
            $searchTerm = $request->input('searchBy');
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        // Sort by status
        if ($request->has('sortByStatus') && !empty($request->input('sortByStatus'))) {
            $sortBy = $request->input('sortByStatus');
            if (in_array($sortBy, ['aktif', 'menunggu', 'selesai'])) {
                $query->where('status', $sortBy);
            }
        }

        // Pagination
        $perPage = $request->input('perPage', 10);
        $perPage = min($perPage, 100);

        $fundraising = $query->paginate($perPage);

        $pagination = [
            'currentPage' => $fundraising->currentPage(),
            'perPage' => $fundraising->perPage(),
            'total' => $fundraising->total(),
            'lastPage' => $fundraising->lastPage(),
            'from' => $fundraising->firstItem(),
            'to' => $fundraising->lastItem(),
        ];

        return BaseResponse::successPagination(
            $fundraising->items(),
            $pagination,
            'Data fundraising berhasil diambil'
        );
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'target' => 'required|integer|min:1',
                'banner' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'company_id' => 'required|exists:companies,id',
                'is_hide_target' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            $bannerName = time() . '_' . $request->file('banner')->getClientOriginalName();
            $bannerPath = $request->file('banner')->storeAs('banners', $bannerName, 'public');
            $request->merge(['banner' => $bannerPath]);

            $fundraising = Fundraising::create([
                'name' => $request->input('name'),
                'target' => $request->input('target'),
                'banner' => $request->input('banner'),
                'description' => $request->input('description'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => 'menunggu',
                'company_id' => $request->input('company_id'),
                'is_hide_target' => $request->input('is_hide_target', false),
            ]);

            return BaseResponse::successData($fundraising->toArray(), 'Data fundraising berhasil ditambahkan');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Gagal menambahkan data fundraising : ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal menambahkan data fundraising : ' . $th->getMessage());
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // dd($request->all());
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'target' => 'required|integer|min:1',
                'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'status' => 'required|string|in:menunggu,aktif,selesai',
                'end_date' => 'required|date|after_or_equal:start_date',
                'is_hide_target' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            $fundraising = Fundraising::findOrFail($id);
            if ($request->hasFile('banner')) {
                $bannerName = time() . '_' . $request->file('banner')->getClientOriginalName();
                $bannerPath = $request->file('banner')->storeAs('banners', $bannerName, 'public');
                $request->merge(['banner' => $bannerPath]);
            }

            $fundraising->update([
                'name' => $request->input('name'),
                'target' => $request->input('target'),
                'banner' => $request->input('banner') ? $request->input('banner') : $fundraising->banner,
                'description' => $request->input('description'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'is_hide_target' => $request->input('is_hide_target', false),
            ]);

            return BaseResponse::successData($fundraising->toArray(), 'Data fundraising berhasil diubah');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Gagal mengubah data fundraising : ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengubah data fundraising : ' . $th->getMessage());
        }
    }
    public function storeNews(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'news' => 'required',
                'fundraising_id' => 'required|exists:fundraisings,id',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            DB::beginTransaction();

            $fundraising = Fundraising::where('id', $request->input('fundraising_id'))->with(['donations', 'donations.donor'])->first();
            $fundraisingNews = FundraisingNews::create([
                'news' => $request->input('news'),
                'fundraising_id' => $fundraising->id,
            ]);
            $fundraisingNews->save();

            $emails = $fundraising->donations
                ->where('status', 'settlement')
                ->pluck('donor.email')
                ->filter()
                ->unique();
            foreach ($emails as $email) {
                if ($email) {
                    Mail::to($email)->send(new \App\Mail\FundraisingNews());
                }
            }
            DB::commit();
            return BaseResponse::successData($fundraising->toArray(), 'Status fundraising berhasil diubah');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error('Gagal mengubah status fundraising : ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengubah status fundraising : ' . $th->getMessage());
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $fundraising = Fundraising::with(['company', 'fundraising_news', 'donations', 'donations.donor'])->findOrFail($id);
            return BaseResponse::successData($fundraising->toArray(), 'Data fundraising berhasil diambil');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Gagal mengambil data fundraising : ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil data fundraising : ' . $th->getMessage());
        }
    }

    public function showAllNews(Request $request)
    {
        try {
            $query = FundraisingNews::with('fundraising');

            // Search by fundraising name OR news content with single parameter
            if ($request->has('searchBy') && !empty($request->input('searchBy'))) {
                $searchTerm = $request->input('searchBy');
                $query->where(function ($q) use ($searchTerm) {
                    // Search in news content
                    $q->where('news', 'like', '%' . $searchTerm . '%')
                        // OR search in fundraising name
                        ->orWhereHas('fundraising', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            // Sort by fundraising (filter by fundraising_id)
            if ($request->has('sortByFundraise') && !empty($request->input('sortByFundraise'))) {
                $fundraisingId = $request->input('sortByFundraise');
                $query->where('fundraising_id', $fundraisingId);
            }

            // Pagination
            $perPage = $request->input('perPage', 10);
            $perPage = min($perPage, 100);

            $news = $query->paginate($perPage);

            // Transform the data
            $transformedData = collect($news->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'news' => $item->news,
                    'fundraising_id' => $item->fundraising_id,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'fundraising_name' => $item->fundraising->name ?? null,
                ];
            });

            $pagination = [
                'currentPage' => $news->currentPage(),
                'perPage' => $news->perPage(),
                'total' => $news->total(),
                'lastPage' => $news->lastPage(),
                'from' => $news->firstItem(),
                'to' => $news->lastItem(),
            ];

            return BaseResponse::successPagination(
                $transformedData->toArray(),
                $pagination,
                'Data fundraising news berhasil diambil'
            );
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil data fundraising news : ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil data fundraising news : ' . $th->getMessage());
        }
    }
}
