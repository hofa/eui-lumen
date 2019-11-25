import request from '@/utils/request'

export function upload(data) {
  return request({
    url: '/upload',
    method: 'post',
    headers: { 'Content-Type': 'multipart/form-data' },
    data
  })
}

export function removeUpload(data) {
  return request({
    url: '/upload',
    method: 'delete',
    data
  })
}

