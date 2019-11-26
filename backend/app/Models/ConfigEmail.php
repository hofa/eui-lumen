<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigEmail extends Model
{
    protected $table = 'config_email';

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
