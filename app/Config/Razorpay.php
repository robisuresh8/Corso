public function debug()
{
    return $this->response->setJSON([
        'keyId_env'     => env('razorpay.keyId'),
        'keySecret_env' => env('razorpay.keySecret') ? 'SET' : 'EMPTY',
        'keyId_cfg'     => config('Razorpay')->keyId,
        'CI_ENV'        => env('CI_ENVIRONMENT'),
    ]);
}
