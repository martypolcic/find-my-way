<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(version: '0.1.0', title: 'Find My Way API Documentation')]
abstract class Controller extends BaseController
{
    
}
