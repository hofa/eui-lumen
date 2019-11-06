<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class User extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'status' => $this->status,
            'role' => $this->roles()->get()->pluck('id'),
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
        ];
    }

}
