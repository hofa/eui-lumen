<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function getOption(Request $request)
    {
        $output = [];
        $ins = $request->input('ins');
        $ins = explode(',', $ins);
        foreach ($ins as $in) {
            $function = 'getOption' . ucfirst($in);
            if (method_exists($this, $function)) {
                $output[$in] = $this->$function();
            }

            if (!isset($output[$in])) {
                $output[$in] = config('options.' . $in);
            }
        }
        return ['data' => $output];
    }

    public function getOptionRole()
    {
        $data = Role::where('status', 'Normal')->get()->toArray();
        $output = array_reduce($data, function (&$output, $v) {
            $output[$v['id']] = $v['name'];
            return $output;
        });
        return $output;
    }

    public function getOptionSettingCate()
    {
        $data = Setting::where('type', 'Cate')->orderBy('sorted', 'asc')->get()->toArray();
        $output = array_reduce($data, function (&$output, $v) {
            $output[$v['id']] = $v['mark'];
            return $output;
        });

        $output = array_merge([0 => '根节点'], $output);
        return $output;
    }

    // public function getOptionStatus()
    // {
    //     return [
    //         'Normal' => '正常',
    //         'Close' => '关闭',
    //     ];
    // }
}
