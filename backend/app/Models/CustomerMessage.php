<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMessage extends Model
{
    protected $table = 'customer_message';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'room_id', 'uuid', 'message', 'attach_id', 'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }
}
