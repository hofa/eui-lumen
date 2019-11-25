<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\Nav as NavResource;
use App\Models\ActionLog;
use App\Models\Nav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NavController extends Controller
{
    public function deleteNav(Request $request, $id)
    {
        $nav = Nav::findOrFail($id);
        $res = $nav->getNav($nav->id, '', '', 0);
        if (!empty($res)) {
            throw new ValidatorException('节点还有子节点，不能删除');
        }
        $nav->delete();

        $resource = new NavResource($nav);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除导航',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postNav(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            // 'icon' => 'required',
            'display' => 'required',
            'type' => 'required',
            // 'request_type' => 'required',
            'sorted' => 'required',
            'status' => 'required',
            // 'path' => 'required',
            // 'extends' => 'required',
            'parent_id' => 'required',
        ]);

        $nav = Nav::create($request->only([
            'title', 'display', 'type', 'is_link', 'link_address', 'sorted', 'status', 'path', 'parent_id', 'desc',
        ]));
        $resource = new NavResource($nav);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增导航',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putNav(Request $request, $id)
    {
        $nav = Nav::findOrFail($id);
        $this->validate($request, [
            'title' => 'required',
            // 'icon' => 'required',
            'display' => 'required',
            'type' => 'required',
            // 'request_type' => 'required',
            'sorted' => 'required',
            'status' => 'required',
            // 'path' => 'required',
            // 'extends' => 'required',
            'parent_id' => 'required',
        ]);
        $oldResource = new NavResource($nav);
        $collection = collect($oldResource->toArray($request));
        $nav->fill($request->only([
            'title', 'display', 'type', 'is_link', 'link_address', 'sorted', 'status', 'path', 'parent_id', 'desc',
        ]))->save();
        $resource = new NavResource($nav);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改导航',
                'ip' => $request->ip(),
            ]);
        }
        return (new NavResource($nav))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getNav(Request $request)
    {
        $nav = new Nav;

        return ['data' => $nav->getNav(0)];
    }
}
