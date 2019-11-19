<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\Setting as SettingResource;
use App\Models\ActionLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function postRefresh(Request $request)
    {
        $setting = new Setting;
        $setting->refreshCache();
        return ['meta' => ['message' => '刷新成功']];
    }

    public function getFront(Request $request)
    {
        $setting = new Setting;
        $front = $setting->getFrontSettingCache();
        return ['data' => $front];
    }

    public function getSetting(Request $request)
    {
        $cates = Setting::where('type', 'Cate')->orderBy('sorted', 'asc')->get();
        $output = [];
        foreach ($cates as $cate) {
            $output[] = $cate->toArray();
            $kvs = Setting::where('parent_id', $cate->id)->orderBy('sorted', 'asc')->get();
            foreach ($kvs as $kv) {
                $output[] = $kv;
            }
        }

        return ['data' => $output];
    }

    public function postSetting(Request $request)
    {
        $settingType = array_keys(config('options.settingType'));
        $this->validate($request, [
            'type' => ['required', Rule::in($settingType)],
            'field' => 'required|alpha_dash|unique:setting',
            'val' => 'required',
            'sorted' => 'required|integer',
            'mark' => 'required|alpha_dash',
            'parent_id' => 'required|integer',
            'options' => 'nullable|json',
        ]);

        if ($request->input('type') == 'Cate' && $request->input('parent_id') > 0) {
            $parent = Setting::findOrFail($request->input('parent_id'));
            if ($parent->type == 'Cate') {
                ValidatorException::setError('parent_id', '分类只能设置一个层级');
            }
        }

        if ($request->input('type') != 'Cate' && $request->input('parent_id') == 0) {
            ValidatorException::setError('parent_id', '必须选择一个非根节点');
        }

        $data = $request->only(['type', 'field', 'val', 'sorted', 'mark', 'options', 'parent_id']);
        if (empty($data['options'])) {
            unset($data['options']);
        }

        $setting = Setting::create($data);
        $resource = new SettingResource($setting);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '创建配置',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '新增成功']]);
    }

    public function patchBatchValSetting(Request $request)
    {
        $params = $request->all();
        $oldParams = [];
        foreach ($params as $key => $val) {
            $oldParams[$key] = Setting::where('field', $key)->first()->val;
            Setting::where('field', $key)->update(['val' => $val]);
        }

        $collection = collect($params);
        $diff = $collection->diff($oldParams);
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改配置值',
                'ip' => $request->ip(),
            ]);
        }
        return ['meta' => ['message' => '保存成功']];
    }

    public function putSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->validate($request, [
            'type' => 'required',
            'field' => 'required|alpha_dash',
            'val' => 'required',
            'sorted' => 'required|integer',
            'mark' => 'required|alpha_dash',
            'parent_id' => 'required|integer',
            'options' => 'nullable|json',
        ]);

        if ($request->input('type') == 'Cate' && $request->input('parent_id') > 0) {
            $parent = Setting::findOrFail($request->input('parent_id'));
            if ($parent->type == 'Cate') {
                ValidatorException::setError('parent_id', '分类只能设置一个层级');
            }
        }

        if ($request->input('type') != 'Cate' && $request->input('parent_id') == 0) {
            ValidatorException::setError('parent_id', '必须选择一个非根节点');
        }

        $data = $request->only(['type', 'field', 'val', 'sorted', 'mark', 'options', 'parent_id']);
        if (empty($data['options'])) {
            unset($data['options']);
        }
        $oldResource = new SettingResource($setting);
        $collection = collect($oldResource->toArray($request));
        $setting->fill($data)->save();
        $resource = new SettingResource($setting);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改配置字段',
                'ip' => $request->ip(),
            ]);
        }
        return $resource->additional(['meta' => ['message' => '修改成功']]);
    }

    public function deleteSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();
        $resource = new SettingResource($setting);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除配置字段',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }
}
