<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function postUpload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:jpeg,png,zip',
        ]);
        $ext = $request->file('file')->getClientOriginalExtension();
        $d = date('YmdHis') . Str::random(12) . '.' . $ext;
        $destinationPath = "images/" . date('Ymd') . '/';
        $request->file('file')->move($destinationPath, $d);
        $data = [
            'url' => Setting::g('apiDomain', 'http://hofa.com') . '/' . $destinationPath . $d,
        ];
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'mark' => '上传文件',
            'ip' => $request->ip(),
        ]);
        return ['data' => $data];
    }

    public function deleteUpload(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|mimes:jpeg,png,zip',
        ]);

        $urls = (array) $request->input('url');
        foreach ($urls as $url) {
            $url = str_replace(['..', './', Setting::g('apiDomain', 'http://hofa.com')], '', $url);
            $data = [
                'url' => $url,
            ];
            if (is_file($url)) {
                @unlink($url);

                ActionLog::create([
                    'user_id' => 0,
                    'action_user_id' => Auth::user()->id,
                    'module_id' => $request['menu']['id'],
                    'diff' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    'mark' => '移除文件',
                    'ip' => $request->ip(),
                ]);
            }

        }

        return ['meta' => ['message' => '删除成功']];
    }
}
