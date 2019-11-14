<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class LoginLog extends Model
{
    protected $table = 'login_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'user_id', 'ip', 'status', 'type',
    ];

    public function getIPError($ip)
    {
        $s = intval(Setting::g('singleIPLoginFailCountLimit', 10));
        if (Redis::incr('login:ip:' . $ip) > $s) {
            Redis::expire('login:ip:' . $ip, 48 * 3600);
            return true;
        }
        Redis::expire('login:ip:' . $ip, 48 * 3600);
        return false;
    }

    public function getPasswordError($username)
    {
        $s = intval(Setting::g('singleAccountLoginFailCountLimit', 5));
        if (Redis::incr('login:un:' . $username) > $s) {
            Redis::expire('login:un:' . $username, 48 * 3600);
            return true;
        }
        Redis::expire('login:un:' . $username, 48 * 3600);
        return false;
    }

    public function decrError($ip, $username)
    {
        Redis::decr('login:ip:' . $ip);
        Redis::decr('login:un:' . $username);
        Redis::expire('login:ip:' . $ip, 48 * 3600);
        Redis::expire('login:un:' . $username, 48 * 3600);
    }

    public function delError($value)
    {
        Redis::del('login:ip:' . $value);
        Redis::del('login:un:' . $value);
    }
}
