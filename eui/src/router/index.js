import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '@/layout'

/**
 * Note: sub-menu only appear when route children.length >= 1
 * Detail see: https://panjiachen.github.io/vue-element-admin-site/guide/essentials/router-and-nav.html
 *
 * hidden: true                   if set true, item will not show in the sidebar(default is false)
 * alwaysShow: true               if set true, will always show the root menu
 *                                if not set alwaysShow, when item has more than one children route,
 *                                it will becomes nested mode, otherwise not show the root menu
 * redirect: noRedirect           if set noRedirect will no redirect in the breadcrumb
 * name:'router-name'             the name is used by <keep-alive> (must set!!!)
 * meta : {
    roles: ['admin','editor']    control the page roles (you can set multiple roles)
    title: 'title'               the name show in sidebar and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    breadcrumb: false            if set false, the item will hidden in breadcrumb(default is true)
    activeMenu: '/example/list'  if set path, the sidebar will highlight the path you set
  }
 */

/**
 * constantRoutes
 * a base page that does not have permission requirements
 * all roles can be accessed
 */
export const constantRoutes = [
  {
    path: '/login',
    component: () => import('@/views/login/index')
  },
  {
    path: '/404',
    component: () => import('@/views/404')
  },
  {
    path: '/401',
    component: () => import('@/views/hofa/401')
  },
  {
    path: '/',
    component: () => import('@/views/hofa/home')
  },
  {
    path: '/message/server',
    component: () => import('@/views/message/server')
  },
  {
    path: '/message/customer',
    component: () => import('@/views/message/customer')
  },
  {
    path: '/user',
    component: Layout,
    redirect: '/user/user',
    meta: { title: '用户管理' },
    children: [
      {
        path: 'user',
        component: () => import('@/views/hofa/user'),
        meta: { title: '玩家' }
      },
      {
        path: 'level',
        component: () => import('@/views/hofa/level'),
        meta: { title: '玩家层级' }
      },
      {
        path: 'bank',
        component: () => import('@/views/hofa/bank'),
        meta: { title: '玩家银行卡' }
      },
      {
        path: 'address',
        component: () => import('@/views/hofa/address'),
        meta: { title: '玩家收货地址' }
      },
      {
        path: 'channel',
        component: () => import('@/views/hofa/channel'),
        meta: { title: '玩家渠道' }
      }
    ]
  },
  {
    path: '/log',
    component: Layout,
    redirect: '/log/login',
    meta: { title: '操作日志' },
    children: [
      {
        path: 'login',
        component: () => import('@/views/hofa/loginLog'),
        meta: { title: '登录日志' }
      },
      {
        path: 'action',
        component: () => import('@/views/hofa/actionLog'),
        meta: { title: '操作日志' }
      },
      {
        path: 'IPBlackWhiteList',
        component: () => import('@/views/hofa/IPBlackWhiteList'),
        meta: { title: 'IP黑白名单' }
      }
    ]
  },
  {
    path: '/contents',
    component: Layout,
    redirect: '/contents/article',
    meta: { title: '系统设置' },
    children: [
      {
        path: 'nav',
        component: () => import('@/views/hofa/nav'),
        meta: { title: '导航' }
      },
      {
        path: 'article',
        component: () => import('@/views/hofa/article'),
        meta: { title: '文章' }
      },
      {
        path: 'atlas',
        component: () => import('@/views/hofa/atlas'),
        meta: { title: '图集' }
      },
      {
        path: 'product',
        component: () => import('@/views/hofa/product'),
        meta: { title: '商品' }
      }
    ]
  },
  {
    path: '/setting',
    component: Layout,
    redirect: '/setting/role',
    meta: { title: '系统设置' },
    children: [
      {
        path: 'role',
        component: () => import('@/views/hofa/role'),
        meta: { title: '角色' }
      },
      {
        path: 'menu',
        component: () => import('@/views/hofa/menu'),
        meta: { title: '菜单' }
      },
      {
        path: 'setting',
        component: () => import('@/views/hofa/setting'),
        meta: { title: '设置' }
      }
    ]
  },
  // 404 page must be placed at the end !!!
  { path: '*', redirect: '/404' }
]

const createRouter = () => new Router({
  // mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRoutes
})

const router = createRouter()

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter()
  router.matcher = newRouter.matcher // reset router
}

export default router
