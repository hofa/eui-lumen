import request from '@/utils/request'

export function fetchSidebar() {
  return request({
    url: '/roleSidebar',
    method: 'get'
  })
}
