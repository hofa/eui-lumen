<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    protected $table = 'user_bank';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'bank_name', 'bank_branch', 'bank_card', 'real_name', 'is_default',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
