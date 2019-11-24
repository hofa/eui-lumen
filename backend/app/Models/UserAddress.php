<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'country', 'province', 'city', 'area', 'street', 'recipient',
        'mobile', 'address', 'zip_code', 'is_default',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
