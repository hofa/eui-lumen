<?php

namespace App\Http\Controllers;

use App\Http\Resources\Role as RoleResource;
use App\Models\ActionLog;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMenuController extends Controller
{
    public function patchPermission(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->menus()->detach();
        $ids = (array) $request->input('ids', []);
        $oldResource = new RoleResource($role);
        $collection = collect($oldResource->toArray($request));
        foreach ($ids as $id) {
            $menu = Menu::find($id);
            if ($menu) {
                $role->menus()->attach($menu);
            }
        }
        $resource = new RoleResource($role);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '授权',
                'ip' => $request->ip(),
            ]);
        }
        return $resource->additional(['meta' => ['message' => '授权成功']]);
    }

    public function deleteRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->menus()->detach();
        $role->delete();
        $resource = new RoleResource($role);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '删除角色',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '删除成功']]);
    }

    public function postRole(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
            'ip_white_enabled' => 'required',
        ]);

        $role = Role::create($request->only(['name', 'status', 'ip_white_enabled']));
        $resource = new RoleResource($role);
        ActionLog::create([
            'user_id' => 0,
            'action_user_id' => Auth::user()->id,
            'module_id' => $request['menu']['id'],
            'diff' => json_encode($resource->toArray($request), JSON_UNESCAPED_UNICODE),
            'mark' => '创建角色',
            'ip' => $request->ip(),
        ]);
        return $resource->additional(['meta' => ['message' => '创建成功']]);
    }

    public function putRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
        ]);
        $oldResource = new RoleResource($role);
        $collection = collect($oldResource->toArray($request));
        $role->name = $request->input('name');
        $role->status = $request->input('status');
        $role->ip_white_enabled = $request->input('ip_white_enabled');
        $role->save();
        $resource = new RoleResource($role);
        $diff = $collection->diff($resource->toArray($request));
        if (!empty($diff)) {
            ActionLog::create([
                'user_id' => 0,
                'action_user_id' => Auth::user()->id,
                'module_id' => $request['menu']['id'],
                'diff' => json_encode($diff, JSON_UNESCAPED_UNICODE),
                'mark' => '修改个人信息',
                'ip' => $request->ip(),
            ]);
        }
        return $resource->additional(['meta' => ['message' => '修改成功']]);
    }

    public function getRole(Request $request)
    {
        // $this->validate($request, [
        //     'name' => 'required'
        // ]);
        $role = new Role;
        $request->input('name') && $role = $role->where('name', $request->input('name'));
        $request->input('status') && $role = $role->where('status', $request->input('status'));
        $sort = $request->input('sort');
        if (!empty($sort)) {
            $sortField = trim(str_replace(['*', '-'], '', $sort));
            $sortBy = $sort[0] == '*' ? 'asc' : 'desc';
            $role = $role->orderBy($sortField, $sortBy);
        }
        $pageSize = $request->input('psize', 20);
        $data = $role->paginate($pageSize)->appends($request->query());
        return RoleResource::collection($data);
    }

    public function getMenu(Request $request)
    {
        // return '[{"name":"Dashboard","url":"/dashboard","icon":"icon-speedometer","badge":{"variant":"primary","text":"NEW"}},{"title":true,"name":"Theme","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Colors","url":"/theme/colors","icon":"icon-drop"},{"name":"Typography","url":"/theme/typography","icon":"icon-pencil"},{"title":true,"name":"Components","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Base","url":"/base","icon":"icon-puzzle","children":[{"name":"Breadcrumbs","url":"/base/breadcrumbs","icon":"icon-puzzle"},{"name":"Cards","url":"/base/cards","icon":"icon-puzzle"},{"name":"Carousels","url":"/base/carousels","icon":"icon-puzzle"},{"name":"Collapses","url":"/base/collapses","icon":"icon-puzzle"},{"name":"Forms","url":"/base/forms","icon":"icon-puzzle"},{"name":"Jumbotrons","url":"/base/jumbotrons","icon":"icon-puzzle"},{"name":"List Groups","url":"/base/list-groups","icon":"icon-puzzle"},{"name":"Navs","url":"/base/navs","icon":"icon-puzzle"},{"name":"Navbars","url":"/base/navbars","icon":"icon-puzzle"},{"name":"Paginations","url":"/base/paginations","icon":"icon-puzzle"},{"name":"Popovers","url":"/base/popovers","icon":"icon-puzzle"},{"name":"Progress Bars","url":"/base/progress-bars","icon":"icon-puzzle"},{"name":"Switches","url":"/base/switches","icon":"icon-puzzle"},{"name":"Tables","url":"/base/tables","icon":"icon-puzzle"},{"name":"Tabs","url":"/base/tabs","icon":"icon-puzzle"},{"name":"Tooltips","url":"/base/tooltips","icon":"icon-puzzle"}]},{"name":"Buttons","url":"/buttons","icon":"icon-cursor","children":[{"name":"Buttons","url":"/buttons/standard-buttons","icon":"icon-cursor"},{"name":"Button Dropdowns","url":"/buttons/dropdowns","icon":"icon-cursor"},{"name":"Button Groups","url":"/buttons/button-groups","icon":"icon-cursor"},{"name":"Brand Buttons","url":"/buttons/brand-buttons","icon":"icon-cursor"}]},{"name":"Charts","url":"/charts","icon":"icon-pie-chart"},{"name":"Icons","url":"/icons","icon":"icon-star","children":[{"name":"CoreUI Icons","url":"/icons/coreui-icons","icon":"icon-star","badge":{"variant":"info","text":"NEW"}},{"name":"Flags","url":"/icons/flags","icon":"icon-star"},{"name":"Font Awesome","url":"/icons/font-awesome","icon":"icon-star","badge":{"variant":"secondary","text":"4.7"}},{"name":"Simple Line Icons","url":"/icons/simple-line-icons","icon":"icon-star"}]},{"name":"Notifications","url":"/notifications","icon":"icon-bell","children":[{"name":"Alerts","url":"/notifications/alerts","icon":"icon-bell"},{"name":"Badges","url":"/notifications/badges","icon":"icon-bell"},{"name":"Modals","url":"/notifications/modals","icon":"icon-bell"}]},{"name":"Widgets","url":"/widgets","icon":"icon-calculator","badge":{"variant":"primary","text":"NEW"}},{"divider":true},{"title":true,"name":"Extras"},{"name":"Pages","url":"/pages","icon":"icon-star","children":[{"name":"Login","url":"/pages/login","icon":"icon-star"},{"name":"Register","url":"/pages/register","icon":"icon-star"},{"name":"Error 404","url":"/pages/404","icon":"icon-star"},{"name":"Error 500","url":"/pages/500","icon":"icon-star"}]},{"name":"Disabled","url":"/dashboard","icon":"icon-ban","badge":{"variant":"secondary","text":"NEW"},"attributes":{"disabled":true}},{"name":"Download CoreUI","url":"http://coreui.io/vue/","icon":"icon-cloud-download","class":"mt-auto","variant":"success","attributes":{"target":"_blank","rel":"noopener"}},{"name":"Try CoreUI PRO","url":"http://coreui.io/pro/vue/","icon":"icon-layers","variant":"danger","attributes":{"target":"_blank","rel":"noopener"}}]';
        $parents = Menu::where('status', 'Normal')->where('display', 'Normal')->where('type', 'Menu')->where('parent_id', 0)->orderBy('sorted', 'ASC')->get();
        $menus = [];
        foreach ($parents as $parent) {
            $temp = [
                'name' => $parent->title,
                'url' => $parent->path,
                'icon' => $parent->icon,
            ];
            $extends = !empty($parent->extends) ? json_decode($parent->extends, true) : [];
            $temp = array_merge($temp, $extends);
            $childrens = Menu::where('status', 'Normal')->where('display', 'Normal')->where('type', 'Menu')->where('parent_id', $parent->id)->orderBy('sorted', 'ASC')->get();
            $cmenus = [];
            if ($childrens) {
                foreach ($childrens as $children) {
                    $temp2 = [
                        'name' => $children->title,
                        'url' => $children->path,
                        'icon' => $children->icon,
                    ];
                    $extends = !empty($children->extends) ? json_decode($children->extends, true) : [];
                    $temp2 = array_merge($temp2, $extends);
                    $cmenus[] = $temp2;
                }
            }

            !empty($cmenus) && $temp['children'] = $cmenus;
            $menus[] = $temp;
        }
        return ['data' => $menus];
    }

    public function getMenu3(Request $request)
    {
        $menus = [];
        $role = new Role;
        $model = Auth::getUser();
        $rolesIds = $model->roles()->get()->pluck('id')->toArray();
        $data = $role->getCacheMenuByRoleId($rolesIds);
        $parents = $data->where('parent_id', 0)->sortBy('sorted')->toArray();
        foreach ($parents as $parent) {
            $parent = (object) $parent;
            $temp = [
                'id' => $parent->id,
                'label' => $parent->title,
            ];

            $childrens = $data->sortBy('sorted')->where('parent_id', $parent->id)->toArray();
            $cmenus = [];
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children = (object) $children;
                    $temp2 = [
                        'id' => $children->id,
                        'label' => $children->title,
                    ];

                    $subChildrens = $data->sortBy('sorted')->where('parent_id', $children->id)->toArray();
                    $subCmenus = [];
                    if ($subChildrens) {
                        foreach ($subChildrens as $subChildren) {
                            $subChildren = (object) $subChildren;
                            $temp3 = [
                                'id' => $subChildren->id,
                                'label' => $subChildren->title,
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

        // $icon = function ($type) {
        //     return $type == 'Node' ? '<i class="el-icon-key"></i>' : '';
        // };
        // return '[{"name":"Dashboard","url":"/dashboard","icon":"icon-speedometer","badge":{"variant":"primary","text":"NEW"}},{"title":true,"name":"Theme","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Colors","url":"/theme/colors","icon":"icon-drop"},{"name":"Typography","url":"/theme/typography","icon":"icon-pencil"},{"title":true,"name":"Components","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Base","url":"/base","icon":"icon-puzzle","children":[{"name":"Breadcrumbs","url":"/base/breadcrumbs","icon":"icon-puzzle"},{"name":"Cards","url":"/base/cards","icon":"icon-puzzle"},{"name":"Carousels","url":"/base/carousels","icon":"icon-puzzle"},{"name":"Collapses","url":"/base/collapses","icon":"icon-puzzle"},{"name":"Forms","url":"/base/forms","icon":"icon-puzzle"},{"name":"Jumbotrons","url":"/base/jumbotrons","icon":"icon-puzzle"},{"name":"List Groups","url":"/base/list-groups","icon":"icon-puzzle"},{"name":"Navs","url":"/base/navs","icon":"icon-puzzle"},{"name":"Navbars","url":"/base/navbars","icon":"icon-puzzle"},{"name":"Paginations","url":"/base/paginations","icon":"icon-puzzle"},{"name":"Popovers","url":"/base/popovers","icon":"icon-puzzle"},{"name":"Progress Bars","url":"/base/progress-bars","icon":"icon-puzzle"},{"name":"Switches","url":"/base/switches","icon":"icon-puzzle"},{"name":"Tables","url":"/base/tables","icon":"icon-puzzle"},{"name":"Tabs","url":"/base/tabs","icon":"icon-puzzle"},{"name":"Tooltips","url":"/base/tooltips","icon":"icon-puzzle"}]},{"name":"Buttons","url":"/buttons","icon":"icon-cursor","children":[{"name":"Buttons","url":"/buttons/standard-buttons","icon":"icon-cursor"},{"name":"Button Dropdowns","url":"/buttons/dropdowns","icon":"icon-cursor"},{"name":"Button Groups","url":"/buttons/button-groups","icon":"icon-cursor"},{"name":"Brand Buttons","url":"/buttons/brand-buttons","icon":"icon-cursor"}]},{"name":"Charts","url":"/charts","icon":"icon-pie-chart"},{"name":"Icons","url":"/icons","icon":"icon-star","children":[{"name":"CoreUI Icons","url":"/icons/coreui-icons","icon":"icon-star","badge":{"variant":"info","text":"NEW"}},{"name":"Flags","url":"/icons/flags","icon":"icon-star"},{"name":"Font Awesome","url":"/icons/font-awesome","icon":"icon-star","badge":{"variant":"secondary","text":"4.7"}},{"name":"Simple Line Icons","url":"/icons/simple-line-icons","icon":"icon-star"}]},{"name":"Notifications","url":"/notifications","icon":"icon-bell","children":[{"name":"Alerts","url":"/notifications/alerts","icon":"icon-bell"},{"name":"Badges","url":"/notifications/badges","icon":"icon-bell"},{"name":"Modals","url":"/notifications/modals","icon":"icon-bell"}]},{"name":"Widgets","url":"/widgets","icon":"icon-calculator","badge":{"variant":"primary","text":"NEW"}},{"divider":true},{"title":true,"name":"Extras"},{"name":"Pages","url":"/pages","icon":"icon-star","children":[{"name":"Login","url":"/pages/login","icon":"icon-star"},{"name":"Register","url":"/pages/register","icon":"icon-star"},{"name":"Error 404","url":"/pages/404","icon":"icon-star"},{"name":"Error 500","url":"/pages/500","icon":"icon-star"}]},{"name":"Disabled","url":"/dashboard","icon":"icon-ban","badge":{"variant":"secondary","text":"NEW"},"attributes":{"disabled":true}},{"name":"Download CoreUI","url":"http://coreui.io/vue/","icon":"icon-cloud-download","class":"mt-auto","variant":"success","attributes":{"target":"_blank","rel":"noopener"}},{"name":"Try CoreUI PRO","url":"http://coreui.io/pro/vue/","icon":"icon-layers","variant":"danger","attributes":{"target":"_blank","rel":"noopener"}}]';
        $parents = Menu::where('status', 'Normal')->where('parent_id', 0)->orderBy('sorted', 'ASC')->get();
        $menus = [];
        foreach ($parents as $parent) {
            $temp = [
                'id' => $parent->id,
                'label' => $parent->title,
            ];

            $childrens = Menu::where('status', 'Normal')->where('parent_id', $parent->id)->orderBy('sorted', 'ASC')->get();
            $cmenus = [];
            if ($childrens) {
                foreach ($childrens as $children) {
                    $temp2 = [
                        'id' => $children->id,
                        'label' => $children->title,
                    ];

                    $subChildrens = Menu::where('status', 'Normal')->where('parent_id', $children->id)->orderBy('sorted', 'ASC')->get();
                    $subCmenus = [];
                    if ($subChildrens) {
                        foreach ($subChildrens as $subChildren) {
                            $temp3 = [
                                'id' => $subChildren->id,
                                'label' => $subChildren->title,
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

    public function getRoleSidebar(Request $request)
    {
        $role = new Role;
        $model = Auth::getUser();
        $rolesIds = $model->roles()->get()->pluck('id')->toArray();
        $data = $role->getCacheMenuByRoleId($rolesIds);
        $parents = $data->where('type', 'Menu')->where('display', 'Normal')->where('parent_id', 0)->sortBy('sorted')->toArray();
        foreach ($parents as $parent) {
            $parent = (object) $parent;
            $temp = [
                'name' => $parent->title,
                'path' => $parent->path,
                'meta' => ['title' => $parent->title, 'icon' => $parent->icon],
            ];
            $childrens = $data->where('type', 'Menu')->where('display', 'Normal')->where('parent_id', $parent->id)->sortBy('sorted')->toArray();
            $cmenus = [];
            if ($childrens) {
                foreach ($childrens as $children) {
                    $children = (object) $children;
                    $temp2 = [
                        'name' => $children->title,
                        'path' => $children->path,
                        'meta' => ['title' => $children->title, 'icon' => $children->icon],
                    ];
                    $cmenus[] = $temp2;
                }
            }

            !empty($cmenus) && $temp['children'] = $cmenus;
            $menus[] = $temp;
        }
        return ['data' => $menus];

        $parents = Menu::where('type', 'Menu')->where('display', 'Normal')->where('status', 'Normal')->where('parent_id', 0)->orderBy('sorted', 'ASC')->get();
        $menus = [];
        // $menus[] = ['name' => '首页', 'path' => '/', 'meta' => ['title' => '首页', 'icon' => 'dashboard']];
        foreach ($parents as $parent) {
            $temp = [
                'name' => $parent->title,
                'path' => $parent->path,
                // 'icon' => $parent->icon,
                'meta' => ['title' => $parent->title, 'icon' => $parent->icon],
            ];
            // $extends = !empty($parent->extends) ? json_decode($parent->extends, true) : [];
            // $temp = array_merge($temp, $extends);
            $childrens = Menu::where('type', 'Menu')->where('display', 'Normal')->where('status', 'Normal')->where('parent_id', $parent->id)->orderBy('sorted', 'ASC')->get();
            $cmenus = [];
            if ($childrens) {
                foreach ($childrens as $children) {
                    $temp2 = [
                        'name' => $children->title,
                        'path' => $children->path,
                        // 'icon' => $children->icon,
                        'meta' => ['title' => $children->title, 'icon' => $children->icon],
                    ];
                    // $extends = !empty($children->extends) ? json_decode($children->extends, true) : [];
                    // $temp2 = array_merge($temp2, $extends);
                    $cmenus[] = $temp2;
                }
            }

            !empty($cmenus) && $temp['children'] = $cmenus;
            $menus[] = $temp;
        }
        return ['data' => $menus];
        // return $menus;
    }
}
