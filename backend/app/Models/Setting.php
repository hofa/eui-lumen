<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Setting extends Model
{
    protected $table = 'setting';

    protected static $cache = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'field', 'val', 'mark', 'sorted', 'parent_id', 'options',
    ];

    public function refreshCache()
    {
        $front = self::whereIn('type', ['GKV', 'FKV'])->get()->toJson();
        Redis::set('setting:front', $front);

        $backend = self::whereIn('type', ['GKV', 'BKV'])->get()->toJson();
        Redis::set('setting:backend', $backend);
    }

    public function getFrontSettingCache()
    {
        return json_decode(Redis::get('setting:front'), true);
    }

    public function getBackendSettingCache()
    {
        return json_decode(Redis::get('setting:backend'), true);
    }

    public static function g($field, $default = null)
    {
        if (empty(self::$cache)) {
            self::$cache = (new Self)->getBackendSettingCache();
        }

        return self::$cache[$field] ?? $default;
    }
}
