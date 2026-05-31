<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle preflight OPTIONS requests
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = service('response');
            $this->setCorsHeaders($response);
            return $response->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Inject CORS headers into normal responses (GET, POST, etc.)
        $this->setCorsHeaders($response);
        return $response;
    }

    private function setCorsHeaders(ResponseInterface $response)
    {
        $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', (string) env('cors.allowedOrigins', '')))));
        $requestOrigin = service('request')->getHeaderLine('Origin');

        if ($requestOrigin !== '' && in_array($requestOrigin, $allowedOrigins, true)) {
            $response->setHeader('Access-Control-Allow-Origin', $requestOrigin);
        } elseif ($allowedOrigins !== []) {
            $response->setHeader('Access-Control-Allow-Origin', $allowedOrigins[0]);
        }

        $response->setHeader('Access-Control-Allow-Methods', (string) env('cors.allowedMethods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH'));
        $response->setHeader('Access-Control-Allow-Headers', (string) env('cors.allowedHeaders', 'Content-Type, Authorization, X-Requested-With, Accept'));
        $response->setHeader('Access-Control-Allow-Credentials', filter_var(env('cors.supportsCredentials', true), FILTER_VALIDATE_BOOL) ? 'true' : 'false');
        $response->setHeader('Access-Control-Max-Age', '86400');
    }
}