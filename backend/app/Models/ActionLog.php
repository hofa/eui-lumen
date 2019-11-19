<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    protected $table = 'action_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module_id', 'user_id', 'diff', 'action_user_id', 'ip', 'mark',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function actionUser()
    {
        return $this->belongsTo('App\Models\User', 'action_user_id', 'id');
    }
}
