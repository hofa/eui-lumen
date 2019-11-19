<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ActionLog extends Resource
{
    public function toArray($request)
    {
        $user = $this->user()->first();
        $actionUser = $this->actionUser()->first();
        return [
            'id' => $this->id,
            'username' => $user->username ?? null,
            'action_username' => $actionUser->username ?? null,
            'module_id' => $this->module_id,
            'mark' => $this->mark,
            'ip' => $this->ip,
            'diff' => $this->diff,
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
        ];
    }
}
