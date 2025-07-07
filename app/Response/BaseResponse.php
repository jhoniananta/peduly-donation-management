<?php 
namespace App\Response;

class BaseResponse {
    
    public static function successData(array $data, string $message) {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], status: 200);
    }
    public static function successMessage(string $message) {
        return response()->json([
            'message' => $message,
        ], status: 200);
    }
    public static function errorMessage(string $message) {
        return response()->json([
            'message' => $message,
        ], status: 500);
    }

    public static function notFoundMessage(string $message) {
        return response()->json([
            'message' => $message,
        ], status: 404);
    }

    public static function unauthorizedMessage(string $message) {
        return response()->json([
            'message' => $message,
        ], status: 401);
    }

}

