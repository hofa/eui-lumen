<?php

namespace App\Http\Controllers;

use App\Http\Resources\Article as ArticleResource;
use App\Models\ActionLog;
use App\Models\Article;
use App\Models\Nav;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function deleteArticle(Request $request, $id)
    {
        // $id = Tools::getIdByHash($id);
        $article = Article::findOrFail($id);
        $article->delete();
        $resource = new ArticleResource($article);
        ActionLog::create([
            'user_id' => $article->user_id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除文章',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postArticle(Request $request)
    {
        $navIds = (array) $request->input('nav_ids');
        $status = array_keys(config('options.status'));
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'status' => Rule::in($status),
        ]);

        $data = $request->only([
            'title',
            'content',
            'template',
            'status',
        ]);
        $data['user_id'] = Auth::user()->id;
        $article = Article::create($data);
        foreach ($navIds as $id) {
            $nav = Nav::find($id);
            if (!empty($nav)) {
                $article->navs()->attach($nav);
            }
        }
        $resource = new ArticleResource($article);
        ActionLog::create([
            'user_id' => Auth::user()->id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增文章',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putArticle(Request $request, $id)
    {
        // $id = Tools::getIdByHash($id);
        $article = Article::findOrFail($id);
        $navIds = (array) $request->input('nav_ids');
        $status = array_keys(config('options.status'));
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'status' => Rule::in($status),
        ]);

        $data = $request->only([
            'title',
            'content',
            'template',
            'status',
        ]);
        $article->navs()->detach();
        foreach ($navIds as $id) {
            $nav = Nav::find($id);
            if (!empty($nav)) {
                $article->navs()->attach($nav);
            }
        }
        $oldResource = new ArticleResource($article);
        $collection = collect($oldResource->toArray($request));
        $article->fill($data)->save();
        $resource = new ArticleResource($article);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => $article->user_id,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改文章',
                'ip' => $request->ip(),
            ]);
        }
        return (new ArticleResource($article))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getArticle(Request $request)
    {
        $article = new Article;
        $id = $request->input('id');
        $title = $request->input('title');
        $username = $request->input('username');
        $navIds = $request->input('nav_ids');
        $id && $article = $article->where('id', $id);
        $title && $article = $article->where('title', $title);

        if (!empty($navIds)) {
            $article = $article->wherehas('navs', function ($r) use ($navIds) {
                return $r->whereIn('nav_id', $navIds);
            });
        }

        if (!empty($username)) {
            $article = $article->wherehas('user', function ($r) use ($username) {
                return $r->where('username', $username);
            });
        }
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $article = $article->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $article->paginate($pageSize)->appends($request->query());

        return ArticleResource::collection($data);
    }

    public function getArticleNav(Request $request)
    {
        $nav = new Nav;

        return ['data' => $nav->getNavByCache(0, 'Article', 'Normal', 1)];
    }
}
