<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Helpers\Tools;
use App\Http\Resources\Article as ArticleResource;
use App\Models\ActionLog;
use App\Models\Article;
use App\Models\User;
use App\rules\BankNum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function deleteArticle(Request $request, $id)
    {
        $id = Tools::getIdByHash($id);
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
        $status = array_keys(config('options.status'));
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
            'is_display' => Rule::in($status),
        ]);

        $user = User::where('username', $request->input('username'))->first();
        $data = $request->only([
            'title',
            'content',
            'template',
            'is_display',
        ]);
        $data['user_id'] = $user->id;
        $article = Article::create($data);
        $resource = new ArticleResource($article);
        ActionLog::create([
            'user_id' => $data['user_id'],
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
        $id = Tools::getIdByHash($id);
        $yn = array_keys(config('options.yn'));
        $article = Article::findOrFail($id);
        $this->validate($request, [
            // 'username' => 'required|exists:user',
            'bank_name' => 'required',
            'bank_branch' => 'required',
            // 'bank_card' => ['required', Rule::unique('user_bank')->ignore($id), new BankNum],
            'bank_card' => ['required', new BankNum],
            'real_name' => 'required',
            'is_default' => Rule::in($yn),
        ]);
        $data = $request->only([
            'bank_name',
            'bank_branch',
            'bank_card',
            'real_name',
            'is_default',
        ]);
        $data['bank_card'] = Tools::encrypt($data['bank_card']);
        $card = Article::where('bank_card', $data['bank_card'])->Where('id', '!=', $id)->first();
        if (!empty($card)) {
            ValidatorException::setError('bank_card', '银行卡号已存在');
        }
        $oldResource = new ArticleResource($article);
        $collection = collect($oldResource->toArray($request));
        $isDefault = $request->input('is_default');
        if ($isDefault == 'Yes') {
            Article::where('user_id', $article->user_id)->update(['is_default' => 'No']);
        }
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
        $id && $article = $article->where('id', $id);
        $title && $article = $article->where('title', $title);

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
}
