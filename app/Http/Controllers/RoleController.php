<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->user()->hasRole('superadmin')) {
                $role = Role::all();
            } else {
                $role = Role::where('company_id', $request->user()->company_id)->get();
            }
            return BaseResponse::successData($role->toArray(), 'Data role berhasil diambil');
        } catch (\Throwable $th) {
            Log::error('Role index error: ' . $th->getMessage());
            return BaseResponse::errorMessage('Role index error: ' . $th->getMessage());
        }
    }

    public function assign(Request $request)
    {
        try {
            $role_id = $request->role_id;
            $user_id = $request->user_id;

            $validator = Validator::make($request->all(), [
                'role_id' => 'required|string|exists:roles,id',
                'user_id' => 'required|string|exists:users,id',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            RoleUser::where('user_id', $user_id)->delete();
            if (RoleUser::where('role_id', $role_id)->where('user_id', $user_id)->exists()) {
                return BaseResponse::errorMessage('Role sudah diassign ke user');
            }

            if (Role::find($role_id)->name == 'superadmin' && !$request->user()->roles->contains('name', 'superadmin')) {
                return BaseResponse::unauthorizedMessage('Role superadmin tidak dapat diassign ke user');
            }
            $role_user = new RoleUser();
            $role_user->role_id = $role_id;
            $role_user->user_id = $user_id;
            $role_user->save();

            return BaseResponse::successMessage('Role berhasil diassign ke user');
        } catch (\Throwable $th) {
            Log::error('Role assign error: ' . $th->getMessage());
            return BaseResponse::errorMessage("Role assign error: " . $th->getMessage());
        }
    }

    public function unassign(Request $request)
    {
        try {
            $role_id = $request->role_id;
            $user_id = $request->user_id;

            $validator = Validator::make($request->all(), [
                'role_id' => 'required|string|exists:roles,id',
                'user_id' => 'required|string|exists:users,id',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            $role_user = RoleUser::where('role_id', $role_id)->where('user_id', $user_id)->first();

            if (!$role_user) {
                return BaseResponse::errorMessage('Role tidak ditemukan pada user');
            }

            $role_user->delete();

            return BaseResponse::successMessage('Role berhasil dihapus dari user');
        } catch (\Throwable $th) {
            Log::error('Role unassign error: ' . $th->getMessage());
            return BaseResponse::errorMessage("Role unassign error: " . $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|not_in:superadmin',
                'hex_color' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            $role = new Role();
            $role->name = $request->name;
            if ($request->user()->hasRole('superadmin')) {
                $role->company_id = $request->company_id;
                if (!$request->company_id) {
                    return BaseResponse::errorMessage('Company id tidak boleh kosong');
                }
            } else {
                $role->company_id = $request->user()->company_id;
            }
            $role->hex_color = $request->hex_color;
            $role->save();

            return BaseResponse::successMessage('Role berhasil ditambahkan');
        } catch (\Throwable $th) {
            Log::error('Role store error: ' . $th->getMessage());
            return BaseResponse::errorMessage("Role store error: " . $th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|not_in:superadmin|unique:roles,name,' . $id,
                'hex_color' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }

            $role = Role::find($id);
            $role->name = $request->name;
            $role->hex_color = $request->hex_color;
            if ($request->user()->hasRole('superadmin')) {
                $role->company_id = $request->company_id;
            }
            $role->save();

            return BaseResponse::successMessage('Role berhasil diupdate');
        } catch (\Throwable $th) {
            Log::error('Role update error: ' . $th->getMessage());
            return BaseResponse::errorMessage("Role update error: " . $th->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (RoleUser::where('role_id', $id)->exists()) {
                return BaseResponse::errorMessage('Role tidak dapat dihapus karena masih digunakan user');
            }

            if ($role->features()->exists()) {
                return BaseResponse::errorMessage('Hapus fitur terlebih dahulu');
            }

            $role->delete();

            return BaseResponse::successMessage('Role berhasil dihapus');
        } catch (\Throwable $th) {
            Log::error('Role destroy error: ' . $th->getMessage());
            return BaseResponse::errorMessage("Role destroy error: " . $th->getMessage());
        }
    }
}
