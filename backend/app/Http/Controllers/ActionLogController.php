<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActionLog as ActionLogResource;
use App\Models\ActionLog;
use Illuminate\Http\Request;

class ActionLogController extends Controller
{
    public function getActionLog(Request $request)
    {
        $actionLog = new ActionLog;
        $createdAt = (array) $request->input('created_at');
        $username = $request->input('username');
        $actionUsername = $request->input('action_username');
        if (!empty($username)) {
            $actionLog = $actionLog->whereHas('user', function ($r) use ($username) {
                return $r->where('username', $username);
            });
        }

        if (!empty($actionUsername)) {
            $actionLog = $actionLog->whereHas('actionUser', function ($r) use ($actionUsername) {
                return $r->where('username', $actionUsername);
            });
        }

        $request->input('ip') && $actionLog = $actionLog->where('ip', $request->input('ip'));
        $request->input('module_id') && $actionLog = $actionLog->where('module_id', $request->input('module_id'));
        isset($createdAt[0]) && !empty($createdAt[0]) && $actionLog = $actionLog->where('created_at', '>=', $createdAt[0]);
        isset($createdAt[1]) && !empty($createdAt[1]) && $actionLog = $actionLog->where('created_at', '<=', $createdAt[1]);
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $actionLog = $actionLog->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $actionLog->paginate($pageSize)->appends($request->query());
        return ActionLogResource::collection($data);
    }
}
