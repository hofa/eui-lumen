<?php

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Menu::truncate();
        $this->user();
        $this->log();
        $this->setting();
    }

    public function user()
    {
        $struct = [
            'title' => '用户管理',
            'icon' => 'guide',
            'path' => '/user',
            'parent_id' => 0,
            'extends' => '',
            'sorted' => 1,
        ];
        $r = Menu::create($struct);

        $struct = [
            'title' => '玩家',
            'icon' => 'user',
            'path' => '/user/user',
            'parent_id' => $r->id,
            'extends' => '',
            'sorted' => 0,
        ];
        $user = Menu::create($struct);

        $struct = [
            'title' => '玩家查询',
            'icon' => 'user',
            'path' => '/user',
            'parent_id' => $user->id,
            'extends' => '',
            'sorted' => 0,
            'display' => 'Close',
            'type' => 'Node',
            'request_type' => 'Get',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '玩家修改',
            'icon' => 'user',
            'path' => '/user/{id}',
            'parent_id' => $user->id,
            'extends' => '',
            'sorted' => 0,
            'display' => 'Close',
            'type' => 'Node',
            'request_type' => 'Put',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '玩家新增',
            'icon' => 'user',
            'path' => '/user',
            'parent_id' => $user->id,
            'extends' => '',
            'sorted' => 0,
            'display' => 'Close',
            'type' => 'Node',
            'request_type' => 'Post',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '玩家层级',
            'icon' => 'tree',
            'path' => '/user/level',
            'parent_id' => $r->id,
            'extends' => '',
            'sorted' => 0,
        ];
        Menu::create($struct);
    }

    public function log()
    {
        $struct = [
            'title' => '操作记录',
            'icon' => 'tab',
            'path' => '/log',
            'parent_id' => 0,
            'extends' => '',
            'sorted' => 2,
        ];

        $log = Menu::create($struct);
        $struct = [
            'title' => '登录日志',
            'icon' => 'tree',
            'path' => '/log/login',
            'parent_id' => $log->id,
            'extends' => '',
            'sorted' => 0,
        ];
        Menu::create($struct);

        $struct = [
            'title' => '操作日志',
            'icon' => 'tree',
            'path' => '/log/action',
            'parent_id' => $log->id,
            'extends' => '',
            'sorted' => 0,
        ];
        Menu::create($struct);
    }

    public function setting()
    {
        $struct = [
            'title' => '系统设置',
            'icon' => 'tree',
            'path' => '/setting',
            'parent_id' => 0,
            'extends' => '',
            'sorted' => 99,
        ];
        $st = Menu::create($struct);

        $struct = [
            'title' => '角色',
            'icon' => 'table',
            'path' => '/setting/role',
            'parent_id' => $st->id,
            'extends' => '',
            'sorted' => 0,
        ];
        $role = Menu::create($struct);

        $struct = [
            'title' => '角色查询',
            'icon' => 'table',
            'path' => '/role',
            'parent_id' => $role->id,
            'extends' => '',
            'sorted' => 0,
            'display' => 'Close',
            'type' => 'Node',
            'request_type' => 'Get',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '角色新增',
            'icon' => 'table',
            'path' => '/role',
            'parent_id' => $role->id,
            'extends' => '',
            'sorted' => 0,
            'display' => 'Close',
            'type' => 'Node',
            'request_type' => 'Post',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '角色编辑',
            'icon' => 'table',
            'path' => '/role/{id}',
            'parent_id' => $role->id,
            'extends' => '',
            'sorted' => 0,
            'display' => 'Close',
            'type' => 'Node',
            'request_type' => 'Put',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '角色删除',
            'icon' => 'table',
            'path' => '/role/{id}',
            'parent_id' => $role->id,
            'extends' => '',
            'sorted' => 0,
            'type' => 'Node',
            'request_type' => 'Delete',
        ];
        Menu::create($struct);

        $struct = [
            'title' => '菜单',
            'icon' => 'tree',
            'path' => '/setting/menu',
            'parent_id' => $st->id,
            'extends' => '',
            'sorted' => 0,
        ];
        Menu::create($struct);

        $struct = [
            'title' => '渠道',
            'icon' => 'tree',
            'path' => '/setting/channel',
            'parent_id' => $st->id,
            'extends' => '',
            'sorted' => 0,
        ];
        Menu::create($struct);
    }

    public function test()
    {
        $menus = '[{"name":"Dashboard","url":"/dashboard","icon":"icon-speedometer","badge":{"variant":"primary","text":"NEW"}},{"title":true,"name":"Theme","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Colors","url":"/theme/colors","icon":"icon-drop"},{"name":"Typography","url":"/theme/typography","icon":"icon-pencil"},{"title":true,"name":"Components","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Base","url":"/base","icon":"table","children":[{"name":"Breadcrumbs","url":"/base/breadcrumbs","icon":"icon-puzzle"},{"name":"Cards","url":"/base/cards","icon":"icon-puzzle"},{"name":"Carousels","url":"/base/carousels","icon":"icon-puzzle"},{"name":"Collapses","url":"/base/collapses","icon":"icon-puzzle"},{"name":"Forms","url":"/base/forms","icon":"icon-puzzle"},{"name":"Jumbotrons","url":"/base/jumbotrons","icon":"icon-puzzle"},{"name":"List Groups","url":"/base/list-groups","icon":"icon-puzzle"},{"name":"Navs","url":"/base/navs","icon":"icon-puzzle"},{"name":"Navbars","url":"/base/navbars","icon":"icon-puzzle"},{"name":"Paginations","url":"/base/paginations","icon":"icon-puzzle"},{"name":"Popovers","url":"/base/popovers","icon":"icon-puzzle"},{"name":"Progress Bars","url":"/base/progress-bars","icon":"icon-puzzle"},{"name":"Switches","url":"/base/switches","icon":"icon-puzzle"},{"name":"Tables","url":"/base/tables","icon":"icon-puzzle"},{"name":"Tabs","url":"/base/tabs","icon":"icon-puzzle"},{"name":"Tooltips","url":"/base/tooltips","icon":"icon-puzzle"}]},{"name":"Buttons","url":"/buttons","icon":"icon-cursor","children":[{"name":"Buttons","url":"/buttons/standard-buttons","icon":"icon-cursor"},{"name":"Button Dropdowns","url":"/buttons/dropdowns","icon":"icon-cursor"},{"name":"Button Groups","url":"/buttons/button-groups","icon":"icon-cursor"},{"name":"Brand Buttons","url":"/buttons/brand-buttons","icon":"icon-cursor"}]},{"name":"Charts","url":"/charts","icon":"icon-pie-chart"},{"name":"Icons","url":"/icons","icon":"icon-star","children":[{"name":"CoreUI Icons","url":"/icons/coreui-icons","icon":"icon-star","badge":{"variant":"info","text":"NEW"}},{"name":"Flags","url":"/icons/flags","icon":"icon-star"},{"name":"Font Awesome","url":"/icons/font-awesome","icon":"icon-star","badge":{"variant":"secondary","text":"4.7"}},{"name":"Simple Line Icons","url":"/icons/simple-line-icons","icon":"icon-star"}]},{"name":"Notifications","url":"/notifications","icon":"icon-bell","children":[{"name":"Alerts","url":"/notifications/alerts","icon":"icon-bell"},{"name":"Badges","url":"/notifications/badges","icon":"icon-bell"},{"name":"Modals","url":"/notifications/modals","icon":"icon-bell"}]},{"name":"Widgets","url":"/widgets","icon":"icon-calculator","badge":{"variant":"primary","text":"NEW"}},{"divider":true},{"title":true,"name":"Extras"},{"name":"Pages","url":"/pages","icon":"icon-star","children":[{"name":"Login","url":"/pages/login","icon":"icon-star"},{"name":"Register","url":"/pages/register","icon":"icon-star"},{"name":"Error 404","url":"/pages/404","icon":"icon-star"},{"name":"Error 500","url":"/pages/500","icon":"icon-star"}]},{"name":"Disabled","url":"/dashboard","icon":"icon-ban","badge":{"variant":"secondary","text":"NEW"},"attributes":{"disabled":true}},{"name":"Download CoreUI","url":"http://coreui.io/vue/","icon":"icon-cloud-download","class":"mt-auto","variant":"success","attributes":{"target":"_blank","rel":"noopener"}},{"name":"Try CoreUI PRO","url":"http://coreui.io/pro/vue/","icon":"icon-layers","variant":"danger","attributes":{"target":"_blank","rel":"noopener"}}]';
        $menus = '[{"name":"Dashboard","url":"/dashboard","icon":"icon-speedometer","badge":{"variant":"primary","text":"NEW"}},{"title":true,"name":"Theme","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Colors","url":"/theme/colors","icon":"icon-drop"},{"name":"Typography","url":"/theme/typography","icon":"icon-pencil"},{"title":true,"name":"Components","class":"","wrapper":{"element":"","attributes":{}}},{"name":"Base","url":"/base","icon":"icon-puzzle","children":[{"name":"Breadcrumbs","url":"/base/breadcrumbs","icon":"icon-puzzle"},{"name":"Cards","url":"/base/cards","icon":"icon-puzzle"},{"name":"Carousels","url":"/base/carousels","icon":"icon-puzzle"},{"name":"Collapses","url":"/base/collapses","icon":"icon-puzzle"},{"name":"Forms","url":"/base/forms","icon":"icon-puzzle"},{"name":"Jumbotrons","url":"/base/jumbotrons","icon":"icon-puzzle"},{"name":"List Groups","url":"/base/list-groups","icon":"icon-puzzle"},{"name":"Navs","url":"/base/navs","icon":"icon-puzzle"},{"name":"Navbars","url":"/base/navbars","icon":"icon-puzzle"},{"name":"Paginations","url":"/base/paginations","icon":"icon-puzzle"},{"name":"Popovers","url":"/base/popovers","icon":"icon-puzzle"},{"name":"Progress Bars","url":"/base/progress-bars","icon":"icon-puzzle"},{"name":"Switches","url":"/base/switches","icon":"icon-puzzle"},{"name":"Tables","url":"/base/tables","icon":"icon-puzzle"},{"name":"Tabs","url":"/base/tabs","icon":"icon-puzzle"},{"name":"Tooltips","url":"/base/tooltips","icon":"icon-puzzle"}]},{"name":"Buttons","url":"/buttons","icon":"icon-cursor","children":[{"name":"Buttons","url":"/buttons/standard-buttons","icon":"icon-cursor"},{"name":"Button Dropdowns","url":"/buttons/dropdowns","icon":"icon-cursor"},{"name":"Button Groups","url":"/buttons/button-groups","icon":"icon-cursor"},{"name":"Brand Buttons","url":"/buttons/brand-buttons","icon":"icon-cursor"}]},{"name":"Charts","url":"/charts","icon":"icon-pie-chart"},{"name":"Icons","url":"/icons","icon":"icon-star","children":[{"name":"CoreUI Icons","url":"/icons/coreui-icons","icon":"icon-star","badge":{"variant":"info","text":"NEW"}},{"name":"Flags","url":"/icons/flags","icon":"icon-star"},{"name":"Font Awesome","url":"/icons/font-awesome","icon":"icon-star","badge":{"variant":"secondary","text":"4.7"}},{"name":"Simple Line Icons","url":"/icons/simple-line-icons","icon":"icon-star"}]},{"name":"Notifications","url":"/notifications","icon":"icon-bell","children":[{"name":"Alerts","url":"/notifications/alerts","icon":"icon-bell"},{"name":"Badges","url":"/notifications/badges","icon":"icon-bell"},{"name":"Modals","url":"/notifications/modals","icon":"icon-bell"}]},{"name":"Widgets","url":"/widgets","icon":"icon-calculator","badge":{"variant":"primary","text":"NEW"}},{"divider":true},{"title":true,"name":"Extras"},{"name":"Pages","url":"/pages","icon":"icon-star","children":[{"name":"Login","url":"/pages/login","icon":"icon-star"},{"name":"Register","url":"/pages/register","icon":"icon-star"},{"name":"Error 404","url":"/pages/404","icon":"icon-star"},{"name":"Error 500","url":"/pages/500","icon":"icon-star"}]},{"name":"Disabled","url":"/dashboard","icon":"icon-ban","badge":{"variant":"secondary","text":"NEW"},"attributes":{"disabled":true}}]';

        $menus = json_decode($menus, true);
        $struct = [
            'name' => '',
            'icon' => '',
            'path' => '',
            'parent_id' => 0,
            'extends' => '',
        ];

        foreach ($menus as $key => $menu) {
            $struct = [
                'title' => $menu['name'] ?? '',
                'icon' => $menu['icon'] ?? '',
                'path' => $menu['url'] ?? '',
                'parent_id' => 0,
                'extends' => '',
                'sorted' => $key,
            ];
            $extends = $menu;
            unset($extends['name'], $extends['icon'], $extends['url']);
            if (isset($extends['children'])) {
                unset($extends['children']);
            }
            !empty($extends) && $struct['extends'] = json_encode($extends);
            $p = Menu::create($struct);
            if (isset($menu['children'])) {
                foreach ($menu['children'] as $k => $m) {
                    $struct = [
                        'title' => $m['name'] ?? '',
                        'icon' => $m['icon'] ?? '',
                        'path' => $m['url'] ?? '',
                        'parent_id' => $p->id,
                        'extends' => '',
                        'sorted' => $k,
                    ];
                    $extends = $m;
                    unset($extends['name'], $extends['icon'], $extends['url']);
                    if (isset($extends['children'])) {
                        unset($extends['children']);
                    }
                    !empty($extends) && $struct['extends'] = json_encode($extends);
                    Menu::create($struct);
                }
            }
        }
    }
}
