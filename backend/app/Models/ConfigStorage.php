<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigStorage extends Model
{
    protected $table = 'config_storage';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'flag',
    ];

    protected $hidden = [
        'salt',
    ];
}
