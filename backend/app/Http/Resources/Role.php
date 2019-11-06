<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Role extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'menu' => $this->menus()->get()->pluck('id'),
            // 'role' => ["1"],
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
        ];
    }
}
