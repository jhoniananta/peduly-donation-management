<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\RoleFeature;
use App\Response\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole('superadmin')) {
            $feature = Feature::all();
        } else {
            $assignedFeatureIds = RoleFeature::where('role_id', operator: $request->user()->roles->first()->id)
                ->pluck('feature_id')
                ->toArray();
            $feature = Feature::whereIn('id', $assignedFeatureIds)->get();
        }
        return BaseResponse::successData($feature->toArray(), 'Data feature berhasil diambil');
    }
    public function assign(Request $request)
    {
        $feature_id = $request->feature_id;
        $role_id = $request->role_id;
        $validator = Validator::make($request->all(), [
            'feature_id' => 'required|integer|exists:features,id',
            'role_id' => 'required|string|exists:roles,id',
        ]);
        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }
        if (RoleFeature::where('role_id', $role_id)->where('feature_id', $feature_id)->exists()) {
            return BaseResponse::errorMessage('Fitur sudah diassign ke role');
        }
        if (Feature::find($feature_id)->name == '*' && !$request->user()->roles->contains('name', 'superadmin')) {
            return BaseResponse::unauthorizedMessage('Unable to assign all features to role');
        }
        $role_feature = new RoleFeature();
        $role_feature->role_id = $role_id;
        $role_feature->feature_id = $feature_id;
        $role_feature->save();
        return BaseResponse::successMessage('Fitur berhasil diassign ke role');
    }
    public function unassign(Request $request)
    {
        $feature_id = $request->feature_id;
        $role_id = $request->role_id;
        $validator = Validator::make($request->all(), [
            'feature_id' => 'required|integer|exists:features,id',
            'role_id' => 'required|string|exists:roles,id',
        ]);
        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }
        $roleFeature = RoleFeature::where('role_id', $role_id)
            ->where('feature_id', $feature_id)
            ->first();
        if (!$roleFeature) {
            return BaseResponse::errorMessage('Fitur tidak ditemukan pada role');
        }
        $roleFeature->delete();
        return BaseResponse::successMessage('Fitur berhasil dihapus dari role');
    }
}
