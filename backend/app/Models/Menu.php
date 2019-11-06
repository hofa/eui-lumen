<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'parent_id', 'icon', 'extends', 'path', 'sorted', 'status', 'type', 'request_type', 'display',
    ];

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_menu', 'role_id', 'menu_id');
    }
}
