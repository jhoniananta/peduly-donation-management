<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SubscriptionFeature;
use App\Models\User;
use App\Models\Company;
use App\Models\RoleUser;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Mail\ActivateAccount;
use App\Response\BaseResponse;
use App\Mail\ResetPasswordMail;
use App\Models\SubscriptionUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {

        if ($request->isMethod('get')) {
            return BaseResponse::unauthorizedMessage('Anda harus login terlebih dahulu');
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $company = Company::find($user->company_id);
            if ($company && in_array($company->status, ['menunggu', 'ditolak'])) {
                $message = $company->status == 'menunggu' ? 'Mohon menunggu, akun Anda sedang diverifikasi oleh Tim Peduly' : 'Mohon maaf, akun Anda ditolak. Hubungi Tim Peduly bila ingin mengonfirmasi.';
                return BaseResponse::unauthorizedMessage($message);
            }
            if (!$user->is_active) {
                return BaseResponse::unauthorizedMessage('Mohon maaf, akun Anda dinonaktifkan. Silakan hubungi Tim Peduly untuk mengaktifkan kembali.');
            }
            $feature = DB::table('role_features')
                ->join('roles', 'role_features.role_id', '=', 'roles.id')
                ->join('features', 'role_features.feature_id', '=', 'features.id')
                ->join('role_users', 'roles.id', '=', 'role_users.role_id')
                ->where('role_users.user_id', $user->id)
                ->pluck('features.name');

            $token = $user->createToken('API Token', $feature->toArray())->plainTextToken;
            return BaseResponse::successData(['token' => $token], 'Login successful');
        }
        return BaseResponse::unauthorizedMessage('E-mail atau kata sandi salah.');
    }

    public function googleLogin()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $th) {
            Log::error('Google login error: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal login menggunakan google');
        }
    }
    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();
            $company = Company::find($user->company_id);
            if ($company && in_array($company->status, ['menunggu', 'ditolak'])) {
                $message = $company->status == 'menunggu' ? 'Akun anda sedang berada dalam proses verifikasi' : 'Akun anda ditolak silahkan hubungi admin jika ini adalah sebuah kesalahan';
                return BaseResponse::unauthorizedMessage($message);
            }
            if (!$user->is_active) {
                return BaseResponse::unauthorizedMessage('Akun anda dinonaktifkan silahkan hubungi admin');
            }
            $feature = DB::table('role_features')
                ->join('roles', 'role_features.role_id', '=', 'roles.id')
                ->join('features', 'role_features.feature_id', '=', 'features.id')
                ->join('role_users', 'roles.id', '=', 'role_users.role_id')
                ->where('role_users.user_id', $user->id)
                ->pluck('features.name');

            $token = $user->createToken('API Token', $feature->toArray())->plainTextToken;
            // redirect ke fe dengan token

        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Google login error: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal login menggunakan google');
        }

        // return BaseResponse::successData(['token' => $token], 'Login successful');
    }
    public function register(Request $request)
    {
        try {
            //code...

            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string|max:255',
                'company_address' => 'required|string|max:255',
                'company_phone' => 'required|string|max:15',
                'company_email' => 'required|string|email|max:255|unique:companies,email',
                'user_email' => 'required|string|email|max:255|unique:users,email',
                'user_name' => 'required|string|max:255',
                'user_password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return BaseResponse::errorMessage($validator->errors()->first());
            }
            DB::beginTransaction();

            $company = new Company();
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
                $company->npwp = $path;
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
                $company->akta_pendirian = $path;
            }
            $company->name = $request->company_name;
            $company->address = $request->company_address;
            $company->phone = $request->company_phone;
            $company->email = $request->company_email;

            $company->save();

            $role = new Role();

            $role->name = 'admin';
            $role->company_id = $company->id;
            $role->save();

            $user = new User();
            $user->name = $request->user_name;
            $user->email = $request->user_email;
            $user->password = bcrypt($request->user_password);
            $user->company_id = $company->id;
            $user->save();

            $role_users = new RoleUser();
            $role_users->role_id = $role->id;
            $role_users->user_id = $user->id;
            $role_users->save();

            // tambahkan fitur dari basic plan ke role admin
            $basicPlan = Subscription::where('plan', 'free')->first();
            $subscriptionUser = SubscriptionUser::create([
                'user_id' => $user->id,
                'subscription_id' => $basicPlan->id,
                'end_date' => '2100-01-01',
            ]);
            $subscriptionFeature = SubscriptionFeature::where('subscription_id', $basicPlan->id)->get();
            foreach ($subscriptionFeature as $feature) {
                DB::table('role_features')->insert([
                    'role_id' => $role->id,
                    'feature_id' => $feature->feature_id,
                ]);
            }
            DB::commit();
            return BaseResponse::successMessage('Pendaftaran berhasil silahkan menunggu verifikasi admin');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error('Register error: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal melakukan pendaftaran');
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return BaseResponse::successMessage('Logged out');
    }

    public function user(Request $request)
    {
        $user = User::where('id', $request->user()->id)->with(['roles', 'company'])->first();
        if (!$user) {
            return BaseResponse::notFoundMessage('User not found');
        }
        return BaseResponse::successData($user->toArray(), 'Success');
    }
    public function sendActivationEmail(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->email_verified_at)
            return BaseResponse::successMessage('Your account is already active');
        $token = bin2hex(random_bytes(16)); // Generate a random token
        $user->token = $token;
        $user->token_expires_at = now()->addHours(3); // Set expiration time based on environment variable
        $user->save();

        Mail::to($user->email)->send(mailable: new ActivateAccount($token, $user->email));
        return BaseResponse::successMessage('Verification email sent');
    }
    public function activateUser(Request $request)
    {
        $token = $request->token;
        $email = $request->email;
        $user = User::where('email', $email)->where('token', $token)->first();
        if (!$user) {
            return BaseResponse::unauthorizedMessage(message: 'Invalid token');
        }
        if ($user->token_expires_at < now()) {
            return BaseResponse::unauthorizedMessage(message: 'Token expired');
        }
        $user->token = null;
        $user->token_expires_at = null;
        $user->email_verified_at = now();
        $user->save();
        return BaseResponse::successMessage('Account activated');
    }
    public function sendEmailResetPassword(Request $request)
    {
        try {
            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (!$user) {
                return BaseResponse::successMessage('Password reset email sent');
            }

            $token = bin2hex(random_bytes(16)); // Generate a random token
            $user->token = $token;
            $user->token_expires_at = now()->addHours(3); // Set expiration time
            $user->save();

            Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

            return BaseResponse::successMessage('Password reset email sent');
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Send email reset password error: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengirim email reset password');
        }
    }
    function resetPassword(Request $request)
    {
        $token = $request->token;
        $email = $request->email;
        $password = $request->password;
        $user = User::where('email', $email)->where('token', $token)->first();
        if (!$user) {
            return BaseResponse::unauthorizedMessage(message: 'Invalid token');
        }
        if ($user->token_expires_at < now()) {
            return BaseResponse::unauthorizedMessage(message: 'Token expired');
        }
        $user->password = bcrypt($password);
        $user->token = null;
        $user->token_expires_at = null;
        $user->save();
        return BaseResponse::successMessage('Password reset successful');
    }
}
