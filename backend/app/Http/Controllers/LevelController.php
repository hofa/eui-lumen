<?php

namespace App\Http\Controllers;

use App\Http\Resources\Level as LevelResource;
use App\Models\ActionLog;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{
    public function deleteLevel(Request $request, $id)
    {
        $level = Level::findOrFail($id);
        $level->delete();
        $resource = new LevelResource($level);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除层级',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postLevel(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $level = Level::create($request->only([
            'name',
        ]));
        $resource = new LevelResource($level);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增层级',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putLevel(Request $request, $id)
    {
        $level = Level::findOrFail($id);
        $this->validate($request, [
            'name' => 'required',
        ]);
        $oldResource = new LevelResource($level);
        $collection = collect($oldResource->toArray($request));
        $level->fill($request->only([
            'name',
        ]))->save();
        $resource = new LevelResource($level);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改层级',
                'ip' => $request->ip(),
            ]);
        }
        return (new LevelResource($level))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getLevel(Request $request)
    {
        $level = new Level;
        $request->input('name') && $level = $level->where('name', $request->input('name'));
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $level = $level->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $level->paginate($pageSize)->appends($request->query());
        return LevelResource::collection($data);
    }
}
