<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'article';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'template', 'user_id', 'editor', 'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function navs()
    {
        return $this->belongsToMany('App\Models\Nav', 'nav_article', 'article_id', 'nav_id');
    }
}
