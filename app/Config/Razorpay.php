<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Razorpay extends BaseConfig
{
    /** Razorpay Key ID (public), e.g. rzp_test_... */
    public string $keyId = '';

    /** Razorpay Key Secret — never expose to frontend */
    public string $keySecret = '';

    /** Amount in smallest currency unit (paise for INR), e.g. 10000 = ₹100 */
    public int $amountPaise = 10000;
}
