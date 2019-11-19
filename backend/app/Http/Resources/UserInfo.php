<?php
namespace App\Http\Resources;

use App\Helpers\Tools;
use Illuminate\Http\Resources\Json\Resource;

class UserInfo extends Resource
{
    public function toArray($request)
    {
        $data = [
            // 'id' => $this->id,
            'qq' => $this->qq,
            'weixin' => $this->weixin,
            'mobile' => $this->mobile,
            'mobile_area' => $this->mobile_area,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'sex' => $this->sex,
            'nickname' => $this->nickname,
            'real_name' => $this->real_name,
            'idcard' => $this->idcard,
            'from_user_id' => $this->from_user_id,
            'register_ip' => $this->register_ip,
            'last_login_ip' => $this->last_login_ip,
            'last_login_time' => $this->last_login_time,
            'wallet' => $this->wallet,
            'level_id' => $this->level_id,
            'channel_id' => $this->channel_id,
            'user_id' => Tools::getHashId($this->user_id),
        ];
        $data = $this->decrypt($data);
        return $data;
    }

}
