<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'channel';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'flag',
    ];
}
