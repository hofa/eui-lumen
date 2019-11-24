<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'level';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}
