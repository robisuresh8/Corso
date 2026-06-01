<?php

namespace Config;  // ← exactly 'Config' hona chahiye, 'App\Config' nahi

use CodeIgniter\Config\BaseConfig;

class Razorpay extends BaseConfig
{
<<<<<<< HEAD
    public string $keyId = 'rzp_test_SwGZYK2qIAphav';
    public string $keySecret = 'MfVDBID1i7i3BOen8poq7XQ3';
    public int $amountPaise = 10000;
}
=======
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
>>>>>>> 185abceb9e75be1c2dac2c9386d394869a28b162
