<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Altas extends Model
{
    protected $table = 'altas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'template', 'user_id', 'editor',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
