<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;

class Razorpay extends BaseConfig
{
    public string $keyId = '';
    public string $keySecret = '';
    public int $amountPaise = 0000;

    public function __construct()
    {
        parent::__construct();
        $this->keyId     = (string) (env('razorpay.keyId')     ?? $this->keyId);
        $this->keySecret = (string) (env('razorpay.keySecret') ?? $this->keySecret);
    }
}
