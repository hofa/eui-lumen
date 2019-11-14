<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidatorException;
use App\Http\Resources\Setting as SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
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
            'val' => 'required|alpha_dash',
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
        return (new SettingResource($setting))->additional(['meta' => ['message' => '新增成功']]);
    }

    public function patchBatchValSetting(Request $request)
    {
        $params = $request->all();
        foreach ($params as $key => $val) {
            Setting::where('field', $key)->update(['val' => $val]);
        }
        return ['meta' => ['message' => '保存成功']];
    }

    public function putSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $this->validate($request, [
            'type' => 'required',
            'field' => 'required|alpha_dash',
            'val' => 'required|alpha_dash',
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

        $setting->fill($data)->save();
        return (new SettingResource($setting))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function deleteSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();
        return (new SettingResource($setting))->additional(['meta' => ['message' => '删除成功']]);
    }
}
