<?php

namespace App\Http\Controllers;

use App\Http\Resources\Menu as MenuResource;
use App\Models\ActionLog;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function deleteMenu(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->roles()->detach();
        $menu->delete();

        $resource = new MenuResource($menu);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除菜单',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postMenu(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            // 'icon' => 'required',
            'display' => 'required',
            'type' => 'required',
            'request_type' => 'required',
            'sorted' => 'required',
            'status' => 'required',
            'path' => 'required',
            // 'extends' => 'required',
            'parent_id' => 'required',
        ]);

        $menu = Menu::create($request->only([
            'title', 'display', 'type', 'request_type', 'sorted', 'status', 'path', 'parent_id', 'icon',
        ]));
        $resource = new MenuResource($menu);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '新增菜单',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putMenu(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $this->validate($request, [
            'title' => 'required',
            // 'icon' => 'required',
            'display' => 'required',
            'type' => 'required',
            'request_type' => 'required',
            'sorted' => 'required',
            'status' => 'required',
            'path' => 'required',
            // 'extends' => 'required',
            'parent_id' => 'required',
        ]);
        $oldResource = new MenuResource($menu);
        $collection = collect($oldResource->toArray($request));
        $menu->fill($request->only([
            'title', 'display', 'type', 'request_type', 'sorted', 'status', 'path', 'parent_id', 'icon',
        ]))->save();
        $resource = new MenuResource($menu);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改菜单',
                'ip' => $request->ip(),
            ]);
        }
        return (new MenuResource($menu))->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getMenu(Request $request)
    {
        $parents = Menu::where('parent_id', 0)->orderBy('sorted', 'ASC')->get();
        $menus = [];
        foreach ($parents as $parent) {
            $temp = [
                'id' => $parent->id,
                'label' => $parent->title,
                'status' => $parent->status,
                'display' => $parent->display,
                'type' => $parent->type,
                'request_type' => $parent->request_type,
                'title' => $parent->title,
                'path' => $parent->path,
                'icon' => $parent->icon,
                'sorted' => $parent->sorted,
                'extends' => $parent->extends,
                'parent_id' => $parent->parent_id,

            ];

            $childrens = Menu::where('parent_id', $parent->id)->orderBy('sorted', 'ASC')->get();
            $cmenus = [];
            if ($childrens) {
                foreach ($childrens as $children) {
                    $temp2 = [
                        'id' => $children->id,
                        'label' => $children->title,
                        'status' => $children->status,
                        'display' => $children->display,
                        'type' => $children->type,
                        'request_type' => $children->request_type,
                        'title' => $children->title,
                        'path' => $children->path,
                        'icon' => $children->icon,
                        'sorted' => $children->sorted,
                        'extends' => $children->extends,
                        'parent_id' => $children->parent_id,
                    ];

                    $subChildrens = Menu::where('parent_id', $children->id)->orderBy('sorted', 'ASC')->get();
                    $subCmenus = [];
                    if ($subChildrens) {
                        foreach ($subChildrens as $subChildren) {
                            $temp3 = [
                                'id' => $subChildren->id,
                                'label' => $subChildren->title,
                                'status' => $subChildren->status,
                                'display' => $subChildren->display,
                                'type' => $subChildren->type,
                                'request_type' => $subChildren->request_type,
                                'title' => $subChildren->title,
                                'path' => $subChildren->path,
                                'icon' => $subChildren->icon,
                                'sorted' => $subChildren->sorted,
                                'extends' => $subChildren->extends,
                                'parent_id' => $subChildren->parent_id,
                            ];
                            $subCmenus[] = $temp3;
                        }
                        !empty($subCmenus) && $temp2['children'] = $subCmenus;
                    }

                    $cmenus[] = $temp2;
                }

            }

            !empty($cmenus) && $temp['children'] = $cmenus;
            $menus[] = $temp;
        }
        return ['data' => $menus];
    }
}
