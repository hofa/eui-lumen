<?php
namespace App\Http\Resources;

use App\Helpers\Tools;
use Illuminate\Http\Resources\Json\Resource;

class User extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => Tools::getHashId($this->id),
            'username' => $this->username,
            'status' => $this->status,
            'role' => $this->roles()->get()->pluck('id')->toArray(),
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
            // 'user_info' => $this->userInfo()->first(),
        ];
    }

}
