<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CustomerMessage extends Resource
{
    public function toArray($request)
    {
        $data = $this->customer()->first();
        return [
            'type' => 'message',
            'nickname' => $data->nickname,
            'uuid' => $data->uuid,
            'content' => $this->message,
            'time' => strtotime($this->created_at),
        ];
    }
}
