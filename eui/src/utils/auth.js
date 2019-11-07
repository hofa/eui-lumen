import Cookies from 'js-cookie'

const TokenKey = '_token'

export function getToken() {
  return Cookies.get(TokenKey)
}

export function getPermission() {
  return Cookies.get(TokenKey + '_permission')
}

export function getExpiresIn() {
  return Cookies.get(TokenKey + '_expiresIn')
}

export function setToken(token) {
  Cookies.set(TokenKey, token)
}

export function setExpiresIn(expiresIn) {
  Cookies.set(TokenKey + '_expiresIn', expiresIn)
}

export function setName(name) {
  Cookies.set('name', name)
}
export function getName(name) {
  return Cookies.get('name')
}

export function setPermission(data) {
  Cookies.set(TokenKey + '_permission', data)
}

export function removeToken() {
  Cookies.remove(TokenKey)
  Cookies.remove(TokenKey + '_expiresIn')
  Cookies.remove(TokenKey + '_permission')
  Cookies.remove('name')
}
