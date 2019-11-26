<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigSMS extends Model
{
    protected $table = 'config_sms';

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
