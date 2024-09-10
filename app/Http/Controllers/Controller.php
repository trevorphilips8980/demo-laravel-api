<?php

namespace App\Http\Controllers;

/**
 *@OA\Info(
 *  title="Your Application API document",
 *  version="1.0.0",
 *),
 *@OA\SecurityScheme(
 *  securityScheme="bearerAuth",
 *  in="header",
 *  name="bearerAuth",
 *  type="http",
 *  scheme="bearer",
 *  bearerFormat="JWT",
 *),
 */
abstract class Controller
{
    //
}
