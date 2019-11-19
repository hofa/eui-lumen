<?php

namespace App\Http\Controllers;

use App\Http\Resources\IPBlackWhiteList as IPBlackWhiteListResource;
use App\Models\ActionLog;
use App\Models\IPBlackWhiteList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IPBlackWhiteListController extends Controller
{
    public function getIPBlackWhiteList(Request $request)
    {
        $IPBlackWhiteList = new IPBlackWhiteList;
        $request->input('ip') && $IPBlackWhiteList = $IPBlackWhiteList->where('ip', $request->input('ip'));
        $request->input('type') && $IPBlackWhiteList = $IPBlackWhiteList->where('type', $request->input('type'));
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $IPBlackWhiteList = $IPBlackWhiteList->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $IPBlackWhiteList->paginate($pageSize)->appends($request->query());
        return IPBlackWhiteListResource::collection($data);
    }

    public function postIPBlackWhiteList(request $request)
    {
        $type = array_keys(config('options.IPBlackWhiteListType'));
        $this->validate($request, [
            'type' => ['required', Rule::in($type)],
            'ips' => ['required'],
            'role_id' => ['required'],
        ]);

        $ips = $request->input('ips');
        $ips = explode("\n", $ips);
        $rules = ['ip' => ['ip', 'unique:ip_black_white_list']];
        foreach ($ips as $ip) {
            $data = ['type' => $request->input('type'), 'ip' => trim($ip), 'role_id' => $request->input('role_id')];
            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                ValidatorException::setError('ips', 'IP存在或不是合法的IP:' . $ip);
            }
            IPBlackWhiteList::create($data);
        }

        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode(['ips' => $ips, 'type' => $request->input('type')], JSON_UNESCAPED_UNICODE),
            'mark' => 'IP ' . $this->input('type'),
            'ip' => $request->ip(),
        ]);
        return ['meta' => ['message' => '解封成功']];
    }

    public function deleteIPBlackWhiteList(request $request, $id)
    {
        $IPBlackWhiteList = IPBlackWhiteList::findOrFail($id);
        $IPBlackWhiteList->delete();
        ActionLog::create([
            'user_id' => $id,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => $IPBlackWhiteList->toJson(),
            'mark' => '删除Ip',
            'ip' => $request->ip(),
        ]);
        return ['meta' => ['message' => '解封成功']];
    }

    public function refreshIPBlackWhiteList(request $request)
    {
        $IPBlackWhiteList = new IPBlackWhiteList;
        $IPBlackWhiteList->refreshCache();
        return ['meta' => ['message' => '刷新成功']];
    }
}
