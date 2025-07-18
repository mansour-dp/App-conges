<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ApiDocController extends Controller
{
    public function index()
    {
        return view('api-docs');
    }

    public function openapi()
    {
        $openapi = file_get_contents(public_path('api/openapi.json'));
        return response($openapi)->header('Content-Type', 'application/json');
    }

    public function health()
    {
        return response()->json([
            'status' => 'OK',
            'timestamp' => now(),
            'version' => '1.0.0',
            'environment' => app()->environment(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ]);
    }

    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'disconnected';
        }
    }

    private function checkCache()
    {
        try {
            \Cache::put('health_check', 'ok', 60);
            return \Cache::get('health_check') === 'ok' ? 'working' : 'not working';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
