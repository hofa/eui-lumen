<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class IPBlackWhiteList extends Model
{
    protected $table = 'ip_black_white_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip', 'role_id', 'type',
    ];

    public function refreshCache()
    {
        $this->removeCacheByRoleId(0);
        foreach (Role::all() as $k => $v) {
            $this->removeCacheByRoleId($v->id);
        }

        $ips = self::where('role_id', 0)->where('type', 'Black')->get()->pluck('ip');
        if (!empty($ips)) {
            $ips = implode(' ', $ips->toArray());
            Redis::sadd('ip_black_set:' . '0', $ips);
        }

        foreach (Role::all() as $k => $v) {
            if ($v['ip_white_enabled'] == 'Normal') {
                $ips = self::where('role_id', $v->id)->where('type', 'White')->get()->pluck('ip');
                if (!empty($ips)) {
                    $ips = implode(' ', $ips->toArray());
                    Redis::sadd('ip_white_set:' . $v->id, $ips);
                }
            }

            $ips = self::where('role_id', $v->id)->where('type', 'White')->get()->pluck('ip');
            if (!empty($ips)) {
                $ips = implode(' ', $ips->toArray());
                Redis::sadd('ip_black_set:' . $v->id, $ips);
            }
        }
    }

    public function isBlack($roleIds, $ip)
    {
        $roleIds[] = 0;
        foreach ($roleIds as $roleId) {
            $t = Redis::sismember('ip_black_set:' . $roleId, $ip);
            if ($t) {
                return true;
            }
        }

        return false;
    }

    public function isWhite($roleIds, $ip)
    {
        foreach ($roleIds as $roleId) {
            if (!Redis::exists('ip_white_set:' . $roleId)) {
                continue;
            }
            $t = Redis::sismember('ip_white_set:' . $roleId, $ip);
            if (!$t) {
                return false;
            }
        }

        return true;
    }

    public function removeCacheByRoleId($roleId)
    {
        Redis::del('ip_black_set:' . $roleId);
        Redis::del('ip_white_set:' . $roleId);
    }
}
