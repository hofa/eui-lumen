import request from '@/utils/request'

export function getRefresh() {
  return request({
    url: '/user/permission/refresh',
    method: 'get'
  })
}
