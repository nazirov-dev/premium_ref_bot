<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;


trait HttpResponse
{
  /**
   * Error message http response json.
   * 
   * @param \Exception|string $e
   * @return \Illuminate\Http\JsonResponse
   */
  protected function log($e)
  {
    if ($e instanceof \Exception) {
      Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
    } else {
      // Handle non-exception error messages
      Log::error($e);
    }

    return response()->json(['data' => '500 Error Server'], 500);
  }
}
