<?php

namespace App\Http\Controllers\Api;

use App\Helpers\File;
use App\Http\Controllers\Controller;

class ShowFileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($path)
    {
        return File::show($path);
    }
}
