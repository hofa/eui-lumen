<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoginLog as LoginLogResource;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function getLoginLog(Request $request)
    {
        $loginLog = new LoginLog;
        $createdAt = (array) $request->input('created_at');
        $request->input('username') && $loginLog = $loginLog->where('username', $request->input('username'));
        $request->input('ip') && $loginLog = $loginLog->where('ip', $request->input('ip'));
        $request->input('type') && $loginLog = $loginLog->where('type', $request->input('type'));
        $request->input('status') && $loginLog = $loginLog->where('status', $request->input('status'));
        isset($createdAt[0]) && !empty($createdAt[0]) && $loginLog = $loginLog->where('created_at', '>=', $createdAt[0]);
        isset($createdAt[1]) && !empty($createdAt[1]) && $loginLog = $loginLog->where('created_at', '<=', $createdAt[1]);
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $loginLog = $loginLog->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $loginLog->paginate($pageSize)->appends($request->query());
        return LoginLogResource::collection($data);
    }

    public function postUnlock(request $request)
    {
        $this->validate($request, [
            'value' => 'required|alpha_dash',
        ]);
        (new LoginLog)->delError($request->input('value'));
        return ['meta' => ['message' => '解封成功']];
    }
}
