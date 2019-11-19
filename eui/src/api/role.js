import request from '@/utils/request'

export function getRefresh() {
  return request({
    url: '/user/permission/refresh',
    method: 'get'
  })
}

export function getIPBlackWhiteListRefresh() {
  return request({
    url: '/IPBlackWhiteList/refresh',
    method: 'post'
  })
}

export function getSettingRefresh() {
  return request({
    url: '/setting/refresh',
    method: 'post'
  })
}
