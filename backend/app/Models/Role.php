<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Role extends Model
{
    protected $table = 'role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'ip_white_enabled',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // 'status',
    ];

    public function menus()
    {
        return $this->belongsToMany('App\Models\Menu', 'role_menu', 'role_id', 'menu_id');
    }

    public function refreshCache()
    {
        $redis = Redis::connection();
        foreach (Role::where('status', 'Normal')->get() as $r) {
            if ($r->id == 1) {
                $menu = Menu::where('status', 'Normal')->orderBy('sorted', 'asc')->get();
            } else {
                $menu = $r->menus()->where('status', 'Normal')->orderBy('sorted', 'asc')->get();
            }
            !empty($menu) && $redis->set('role:menu:' . $r->id, $menu->toJson());
        }
    }

    public function getCacheMenuByRoleId($roleId)
    {
        $data = [];
        $redis = Redis::connection();
        $roleIds = (array) $roleId;
        if ($roleId === 0 || $roleId === 1 || in_array(1, $roleIds)) {
            $data = json_decode($redis->get('role:menu:1'), true);
        } else {
            foreach ($roleIds as $id) {
                $data = array_merge($data, json_decode($redis->get('role:menu:' . $id), true));
            }

            if (count($roleIds) > 2) {
                return collect($data)->unique('id');
            }
        }
        return collect($data);
    }

    public function removeCacheMenuByRoleId($roleId)
    {
        $redis->del('role:menu:' . $roleId);
    }
}
