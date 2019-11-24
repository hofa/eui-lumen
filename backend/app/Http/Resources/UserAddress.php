<?php
namespace App\Http\Resources;

use App\Helpers\Tools;
use Illuminate\Http\Resources\Json\Resource;

class UserAddress extends Resource
{
    public function toArray($request)
    {

        return [
            'id' => Tools::getHashId($this->id),
            'username' => $this->user()->first()->username,
            'address' => $this->address,
            'province' => $this->province,
            'city' => $this->city,
            'area' => $this->area,
            'zip_code' => $this->zip_code,
            'recipient' => $this->recipient,
            'mobile' => Tools::decrypt($this->mobile),
            'street' => $this->street,
            'is_default' => $this->is_default,
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
        ];

    }
}
