<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function responseWithData(mixed $data, int $statusCode = 200): mixed
    {
        return  new JsonResponse(["data" => $data], $statusCode);
    }
}
