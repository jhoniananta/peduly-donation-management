<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Response\BaseResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Tampilkan 10 notifikasi terbaru untuk user saat ini
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('Mengambil notifikasi untuk user: ' . $user->id);
            $limit = $request->input('limit', 10);

            $notifications = Notification::where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
            Log::info('Isi notifikasi yang diambil: ' . $notifications->count());
            // konversi Collection ke array
            return BaseResponse::successData(
                $notifications->toArray(),
                'Daftar notifikasi berhasil diambil'
            );
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil notifikasi: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil notifikasi: ' . $th->getMessage());
        }
    }

    /**
     * Tampilkan semua notifikasi untuk user saat ini
     */
    public function getAll(Request $request)
    {
        try {
            $user = Auth::user();
            $notifications = Notification::where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // konversi Collection ke array
            return BaseResponse::successData(
                $notifications->toArray(),
                'Semua notifikasi berhasil diambil'
            );
        } catch (\Throwable $th) {
            Log::error('Gagal mengambil semua notifikasi: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal mengambil semua notifikasi: ' . $th->getMessage());
        }
    }

    /**
     * Tandai satu notifikasi sebagai terbaca
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $notification = Notification::where('id', $id)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            $notification->status = 'read';
            $notification->save();

            return BaseResponse::successMessage('Notifikasi berhasil ditandai terbaca');
        } catch (\Throwable $th) {
            Log::error('Gagal menandai notifikasi terbaca: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal menandai notifikasi: ' . $th->getMessage());
        }
    }

    /**
     * Tandai semua notifikasi sebagai terbaca
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();
            Notification::where('company_id', $user->company_id)
                ->where('status', 'unread')
                ->update(['status' => 'read']);

            return BaseResponse::successMessage('Semua notifikasi berhasil ditandai terbaca');
        } catch (\Throwable $th) {
            Log::error('Gagal menandai semua notifikasi terbaca: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal menandai semua notifikasi: ' . $th->getMessage());
        }
    }

    /**
     * Hapus semua notifikasi untuk user saat ini
     */
    public function clearAllNotifications(Request $request)
    {
        try {
            $user = Auth::user();
            Notification::where('company_id', $user->company_id)->delete();

            return BaseResponse::successMessage('Semua notifikasi berhasil dihapus');
        } catch (\Throwable $th) {
            Log::error('Gagal menghapus notifikasi: ' . $th->getMessage());
            return BaseResponse::errorMessage('Gagal menghapus notifikasi: ' . $th->getMessage());
        }
    }
}
