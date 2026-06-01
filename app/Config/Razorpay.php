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

        // Try both cases — Render env vars are case-sensitive
        $this->keyId = (string) (
            env('Razorpay.keyId') ?:
            env('razorpay.keyId') ?:
            env('RAZORPAY_KEY_ID') ?:
            $this->keyId
        );

        $this->keySecret = (string) (
            env('Razorpay.keySecret') ?:
            env('razorpay.keySecret') ?:
            env('RAZORPAY_KEY_SECRET') ?:
            $this->keySecret
        );

        $this->amountPaise = (int) (
            env('Razorpay.amountPaise') ?:
            env('razorpay.amountPaise') ?:
            env('RAZORPAY_AMOUNT_PAISE') ?:
            $this->amountPaise
        );
    }
}