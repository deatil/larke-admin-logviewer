import request from '@/utils/request'

export function getFiles(params) {
  return request({
    url: '/log-viewer/files',
    method: 'get',
    params
  })
}

export function getLogs(params) {
  return request({
    url: '/log-viewer/logs',
    method: 'get',
    params
  })
}

export function deleteLog(params) {
  return request({
    url: '/log-viewer/delete',
    method: 'delete',
    params
  })
}

export function getDownloadUrl(file) {
  const baseUrl = process.env.VUE_APP_BASE_API
  if (baseUrl.substring(baseUrl.length, baseUrl.length - 1) == '/') {
    return baseUrl.substring(0, baseUrl.length - 1) + '/log-viewer/download?file=' + file
  } else {
    return baseUrl + '/sign-cert/download?file=' + file
  }
}
