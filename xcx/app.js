// app.js
App({
  onLaunch() {
    // 展示本地存储能力
    const logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)

    // 登录
    wx.login({
      success: res => {
        // 发送 res.code 到后台换取 openId, sessionKey, unionId
      }
    })
  },
  globalData: {
    userInfo: null,
    qrcodeBaseUrl: 'http://192.168.0.3:9092' //php端的地址
  },

  // 通用请求方法
  request(options) {
    const token = wx.getStorageSync('token')
    // 判断哪些请求应该发送到qrcodeBaseUrl
    const qrcodeEndpoints = [
      '/api/check_status.php',
      '/api/handle_scan.php',
      '/api/handle_auth.php',
      '/api/check_scene.php'
    ];
    
    const baseUrl = qrcodeEndpoints.some(endpoint => options.url.includes(endpoint))
      ? this.globalData.qrcodeBaseUrl 
      : this.globalData.apiBaseUrl;

    return new Promise((resolve, reject) => {
      wx.request({
        url: `${baseUrl}${options.url}`,
        method: options.method || 'GET',
        data: options.data,
        header: {
          'Content-Type': 'application/json',
          'Authorization': token ? `Bearer ${token}` : '',
          ...options.header
        },
        success: (res) => {
          if (res.data.code === 200 || res.data.code === 201 || res.data.code === 202) {
            resolve(res.data)
          } else {
            reject(res.data)
            wx.showToast({
              title: res.data.msg || '请求失败',
              icon: 'none'
            })
          }
        },
        fail: (err) => {
          reject(err)
          wx.showToast({
            title: '网络错误',
            icon: 'none'
          })
        }
      })
    })
  },

  clearLoginInfo() {
    // 清除登录信息
    wx.removeStorageSync('token')
    wx.removeStorageSync('userInfo')
    this.globalData.token = null
    this.globalData.userInfo = null
  }
})
