<?php
namespace App\Controllers;
use CodeIgniter\Controller;

class CorsController extends Controller
{
    public function options()
    {
        $res = service('response');
        $res->setHeader('Access-Control-Allow-Origin', '*');
        $res->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $res->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        return $res->setStatusCode(204);
    }
}
