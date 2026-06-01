<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Razorpay extends BaseConfig
{
    public string $keyId = '';
    public string $keySecret = '';
    public int $amountPaise = 10000;

    public function __construct()
    {
        parent::__construct();
        $this->keyId      = (string) (env('Razorpay.keyId')     ?? '');
        $this->keySecret  = (string) (env('Razorpay.keySecret') ?? '');
        $this->amountPaise = (int)   (env('Razorpay.amountPaise') ?? 10000);
    }
}