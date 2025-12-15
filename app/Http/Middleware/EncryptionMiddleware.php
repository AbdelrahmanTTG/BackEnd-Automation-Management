<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\EncryptionHelper;

class EncryptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('data') && is_string($request->input('data'))) {
            try {
                $decryptedData = EncryptionHelper::decrypt($request->input('data'));
                $request->merge($decryptedData);
                $request->request->remove('data');
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Invalid encrypted data',
                    'error' => $e->getMessage()
                ], 400);
            }
        }

        $response = $next($request);

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $originalData = $response->getData(true);

            if (isset($originalData['data']) || isset($originalData['user']) || isset($originalData['token'])) {
                try {
                    $encryptedData = EncryptionHelper::encrypt($originalData);
                    $response->setData(['data' => $encryptedData]);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Encryption failed',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        }

        return $response;
    }
}
