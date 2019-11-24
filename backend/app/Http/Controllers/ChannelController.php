<?php

namespace App\Http\Controllers;

use App\Http\Resources\Channel as ChannelResource;
use App\Models\ActionLog;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    public function deleteChannel(Request $request, $id)
    {
        $channel = Channel::findOrFail($id);
        $channel->delete();
        $resource = new ChannelResource($channel);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除渠道',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postChannel(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'flag' => 'required|integer|unique:channel',
        ]);

        $channel = Channel::create($request->only([
            'name', 'flag',
        ]));
        $resource = new ChannelResource($channel);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增渠道',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putChannel(Request $request, $id)
    {
        $channel = Channel::findOrFail($id);
        $this->validate($request, [
            'name' => 'required',
            'flag' => ['required', 'integer', Rule::unique('channel')->ignore($channel->flag)],
        ]);
        $oldResource = new ChannelResource($channel);
        $collection = collect($oldResource->toArray($request));
        $channel->fill($request->only([
            'name', 'flag',
        ]))->save();
        $resource = new ChannelResource($channel);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改渠道',
                'ip' => $request->ip(),
            ]);
        }
        return (new ChannelResource($channel))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getChannel(Request $request)
    {
        $channel = new Channel;
        $request->input('name') && $channel = $channel->where('name', $request->input('name'));
        $request->input('flag') && $channel = $channel->where('flag', $request->input('flag'));
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $channel = $channel->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $channel->paginate($pageSize)->appends($request->query());
        return ChannelResource::collection($data);
    }
}
