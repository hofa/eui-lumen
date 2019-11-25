<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class Nav extends Model
{
    protected $table = 'nav';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'parent_id', 'desc', 'extends', 'path', 'sorted', 'status', 'type', 'is_link', 'link_address', 'display',
    ];

    public function getNav($parentId = 0, $type = '', $display = '', $recursive = 1)
    {
        $nav = is_numeric($parentId) ? Nav::where('parent_id', $parentId) : Nav::where('flag', $parentId);
        $nav = $nav->orderBy('sorted', 'ASC');
        $type && $nav = $nav->whereIn('type', [$type, 'Normal']);
        $display && $nav = $nav->where('display', $display);
        $navs = $nav->get();
        $output = [];
        foreach ($navs as $nav) {
            $temp = [
                'id' => $nav->id,
                'label' => $nav->title,
                'status' => $nav->status,
                'display' => $nav->display,
                'type' => $nav->type,
                'title' => $nav->title,
                'path' => $nav->path,
                'sorted' => $nav->sorted,
                'extends' => $nav->extends,
                'parent_id' => $nav->parent_id,
                'is_link' => $nav->is_link,
                'link_address' => $nav->link_address,
                'desc' => $nav->desc,
            ];

            if ($recursive == 1) {
                $subChildrens = $this->getNav($nav->id, $type, $display, $recursive);
                if ($subChildrens) {
                    $temp['children'] = $subChildrens;
                }
            }
            $output[] = $temp;
        }
        return $output;
    }

    public function getNavByCache($parentId, $type, $display, $recursive)
    {
        $field = $parentId . '_' . $type . '_' . $display . '_' . $recursive;
        $data = Redis::hget('nav', $field);
        if (!empty($data)) {
            $data = json_decode($data, true);
        } else {
            $data = $this->getNav($parentId, $type, $display, $recursive);
            Redis::hset('nav', $field, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        return $data;
    }

    public function refreshCache()
    {
        Redis::del('nav');
    }

    public function articles()
    {
        return $this->belongsToMany('App\Models\Article', 'nav_article', 'article_id', 'nav_id');
    }
}
