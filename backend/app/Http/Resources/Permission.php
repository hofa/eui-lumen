<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Permission extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'name' => $this->title,
            'type' => $this->type,
            'method' => $this->request_type,
        ];
        // return [
        //     'i' => $this->id,
        //     'p' => $this->path,
        //     'n' => $this->title,
        //     't' => $this->type,
        //     'm' => $this->request_type,
        // ];

        // return [
        //     $this->id,
        //     $this->path,
        //     $this->title,
        //     $this->type,
        //     $this->request_type,
        // ];
    }

    // public function with($request)
    // {
    //     return [
    //         'meta' => [
    //             'key' => 'value',
    //         ],
    //     ];
    // }
}
