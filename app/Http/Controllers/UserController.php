<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->user()->hasRole('superadmin')) {
            $users = User::select(['name', 'email', 'phone', 'avatar_url', 'company_id'])->with(['company'])->get();
        } else {
            $company_id = $request->user()->company_id;
            $user_id = $request->user()->id;
            $users = User::where('company_id', $company_id)->whereNot('id', $user_id)->with(['roles', 'roles.features'])->get();
        }
        return BaseResponse::successData($users->toArray(), 'Data user berhasil diambil');
    }
    public function profile(Request $request)
    {
        $user = User::select(['name', 'email', 'phone', 'avatar_url'])->where('id', $request->user()->id)->first();
        return BaseResponse::successData($user->toArray(), 'Data user berhasil diambil');
    }
    public function update(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user_id ?? $request->user()->id,
            'phone' => 'nullable|string|max:15',
            'avatar_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return BaseResponse::errorMessage($validator->errors()->first());
        }

        $validatedData = $validator->validated();
        $userId = $request->user_id ?? $request->user()->id;
        $user = User::find($userId);
        if ($request->password) {
            $validatedData['password'] = bcrypt(value: $request->password);
        }
        if ($request->hasFile('avatar_url')) {
            # code...
            $avatarName = time() . '_' . $request->file('avatar_url')->getClientOriginalName();
            $avatarPath = $request->file('avatar_url')->storeAs('avatars', $avatarName, 'public');
            $validatedData['avatar_url'] = $avatarPath;
        }
        $user->update($validatedData);

        return BaseResponse::successData($user->toArray(), 'User updated successfully');
    }
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|string|email|max:255|unique:users,email',
                'user_name' => 'required|string|max:255',
                'user_password' => 'required|string|min:8',
                'user_phone' => 'nullable|string|max:15',
                'avatar_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }
            DB::beginTransaction();
            $user = new User();
            $user->name = $request->user_name;
            $user->email = $request->user_email;
            $user->password = bcrypt($request->user_password);
            $user->phone = $request->user_phone;
            if ($request->hasFile('avatar_url')) {
                # code...
                $avatarName = time() . '_' . $request->file('avatar_url')->getClientOriginalName();
                $avatarPath = $request->file('avatar_url')->storeAs('avatars', $avatarName, 'public');
                $user->avatar_url = $avatarPath;
            }
            if ($request->user()->hasRole('superadmin')) {
                $validator = Validator::make($request->all(), [
                    'company_id' => 'required|integer|exists:companies,id',
                ]);

                if ($validator->fails()) {
                    return BaseResponse::errorMessage($validator->errors()->first());
                }
                $user->company_id = $request->company_id;
            } else {
                $user->company_id = $request->user()->company_id;
            }
            $user->save();
            DB::commit();
            return BaseResponse::successData($user->toArray(), 'Pendaftaran user berhasil dilakukan');
        } catch (\Exception $e) {
            DB::rollBack();

            return BaseResponse::errorMessage($e->getMessage());
        }
    }
}
