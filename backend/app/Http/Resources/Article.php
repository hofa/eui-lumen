<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Article extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'template' => $this->template,
            'status' => $this->status,
            'username' => $this->user()->first()->username,
            'navs_id' => $this->navs()->get()->pluck('id'),
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
        ];
    }
}
