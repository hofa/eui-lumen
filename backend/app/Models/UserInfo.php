<?php
namespace App\Models;

use App\Helpers\Tools;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    protected $table = 'user_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'qq', 'weixin', 'mobile', 'mobile_area', 'email', 'avatar', 'sex', 'nickname',
        'real_name', 'idcard', 'from_user_id', 'register_ip', 'last_login_ip', 'last_login_time',
        'wallet', 'level_id', 'channel_id', 'user_id',
    ];

    protected $encryptFields = [
        'qq', 'weixin', 'mobile', 'email', 'idcard',
    ];

    public function encrypt($data)
    {
        foreach ($data as $k => $v) {
            if (in_array($k, $this->encryptFields)) {
                $data[$k] = !empty($v) ? Tools::encrypt($v) : $v;
            }
        }
        return $data;
    }

    public function decrypt($data)
    {
        foreach ($data as $k => $v) {
            if (in_array($k, $this->encryptFields)) {
                $data[$k] = !empty($v) ? Tools::decrypt($v) : $v;
            }
        }
        return $data;
    }

    public function scopeEncryptWhere($query, $field, $val)
    {
        $val = in_array($field, $this->encryptFields) && !empty($val) ? Tools::encrypt($val) : $val;
        return $query->where($field, $val);
    }
}
