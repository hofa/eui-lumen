<?php
namespace App\Http\Resources;

use App\Helpers\Tools;
use Illuminate\Http\Resources\Json\Resource;

class UserBank extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => Tools::getHashId($this->id),
            'username' => $this->user()->first()->username,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'bank_card' => Tools::decrypt($this->bank_card),
            'real_name' => $this->real_name,
            'is_default' => $this->is_default,
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
        ];

    }
}
