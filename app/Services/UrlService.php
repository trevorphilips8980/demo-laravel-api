<?php

namespace App\Services;

use Hashids\Hashids;

class UrlService
{
    protected $hashId;

    public function __construct()
    {
        $this->hashId = new Hashids('', 12);
    }

    public static function encodeId($id)
    {
        $obj = new self();
        return $obj->hashId->encode($id);
    }

    public static function decodeId($id)
    {
        $id = $id ?? '';
        $obj = new self();
        $decryptId = $obj->hashId->decode($id);
        return reset($decryptId);
    }
}
