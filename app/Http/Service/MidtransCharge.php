<?php

namespace App\Http\Service;

use App\Response\BaseResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MidtransCharge
{
    private $server_key;
    private $api_link;
    private $payment_method;
    private $order_id;
    private $gross_amount; 
    private $bank_name; 
    public function __construct($payment_method, $order_id, $gross_amount,$bank_name = null)
    {
        $this->order_id = $order_id;
        $this->payment_method = $payment_method;
        $this->gross_amount = $gross_amount;
        $this->bank_name = $bank_name;
        $this->server_key = config('midtrans.MIDTRANS_SERVER_KEY');
        $this->api_link = config('midtrans.MIDTRANS_API_LINK');
    }

    public function setBankName($bank_name)
    {
        $this->bank_name = $bank_name;
    }
    
    public function charge()
    {
        try {
            //code...

            $data = [
                'payment_type' => $this->payment_method,
                'transaction_details' => [
                    'order_id' => $this->order_id,
                    'gross_amount' => $this->gross_amount,
                ],
            ];
            if ($this->payment_method == 'bank_transfer') {
                $data['bank_transfer'] = [
                    'bank' => $this->bank_name,
                ];
            }
            // dd($data);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->server_key),
            ])->post($this->api_link . '/v2/charge', $data);

            return $response->json();
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("Gagal melakukan charge " . $th->getMessage());
            return BaseResponse::errorMessage('Gagal melakukan charge ' . $th->getMessage());
        }
    }
}
