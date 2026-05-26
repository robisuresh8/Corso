<?php

namespace Config;  // ← exactly 'Config' hona chahiye, 'App\Config' nahi

use CodeIgniter\Config\BaseConfig;

class Razorpay extends BaseConfig
{
    public string $keyId = '';
    public string $keySecret = '';
    public int $amountPaise = 10000;

    public function __construct()
    {
        parent::__construct();
        $this->keyId      = (string) (env('razorpay.keyId')     ?? '');
        $this->keySecret  = (string) (env('razorpay.keySecret') ?? '');
        $this->amountPaise = (int)   (env('razorpay.amountPaise') ?? 10000);
    }
}
