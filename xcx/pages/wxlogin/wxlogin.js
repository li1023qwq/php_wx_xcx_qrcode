// 获取应用实例
const app = getApp()

Page({
	data: {
		scanStatus: '', // 扫码状态
		scene: '', // 场景值
	},

	onLoad(options) {
		// 检查是否通过扫码进入
		if (options.scene) {
			// 如果是通过扫码进入，scene会被编码，需要解码
			const scene = decodeURIComponent(options.scene);
			console.log('Scanned scene:', scene);
			this.setData({ scene });
			
			// 验证scene是否有效
			this.checkScene();
		}
	},

	// 扫描二维码
	async scanQRCode() {
		try {
			const res = await wx.scanCode({
				onlyFromCamera: true,
				scanType: ['qrCode']
			});
			
			// 解析scene
			let scene;
			if (res.path) {
				scene = res.path.split('scene=')[1];
			} else if (res.result) {
				try {
					const url = new URL(res.result);
					scene = url.searchParams.get('scene');
				} catch(e) {
					// 如果不是URL格式，尝试直接获取数字
					scene = res.result.match(/\d+/)[0];
				}
			}
			
			if(scene) {
				this.setData({ scene });
				this.checkScene();
			} else {
				throw new Error('无效的二维码');
			}
		} catch (error) {
			console.error('扫码失败:', error);
			wx.showToast({
				title: '扫码失败',
				icon: 'none'
			});
		}
	},

	// 验证scene
	async checkScene() {
		try {
			const res = await app.request({
				url: '/api/check_status.php',
				method: 'GET',
				data: {
					scene: this.data.scene
				}
			});

			if (res.code === 201) { // 等待扫码状态
				// scene有效，获取openid
				this.login();
			} else if (res.code === 408) { // 二维码过期
				wx.showToast({
					title: '二维码已过期',
					icon: 'none'
				});
				// 重置scene
				this.setData({ scene: '' });
			} else {
				wx.showToast({
					title: res.msg || '二维码无效',
					icon: 'none'
				});
				// 重置scene
				this.setData({ scene: '' });
			}
		} catch (error) {
			console.error('验证scene失败:', error);
			wx.showToast({
				title: '二维码验证失败',
				icon: 'none'
			});
			// 重置scene
			this.setData({ scene: '' });
		}
	},

	// 获取openid并更新状态
	async login() {
		try {
			const loginRes = await wx.login();
			if (loginRes.code) {
				const res = await app.request({
					url: '/api/handle_scan.php',
					method: 'GET',
					data: {
						scene: this.data.scene,
						code: loginRes.code
					}
				});
				
				if (res.code === 200) {
					this.setData({
						scanStatus: '已扫码，请点击授权登录'
					});
				} else {
					this.setData({
						scanStatus: res.msg || '扫码失败'
					});
					wx.showToast({
						title: res.msg || '扫码失败',
						icon: 'none'
					});
				}
			}
		} catch (error) {
			console.error('Login failed:', error);
			wx.showToast({
				title: '登录失败',
				icon: 'none'
			});
		}
	},

	// 授权登录
	async loginAuth() {
		try {
			const res = await app.request({
				url: '/api/handle_auth.php',
				method: 'GET',
				data: {
					scene: this.data.scene,
					action: 'confirm'
				}
			});
			
			if (res.code === 200) {
				this.setData({
					scanStatus: '授权成功'
				});
				wx.showToast({
					title: '授权成功',
					icon: 'success',
					duration: 1500
				});
				
				// 延迟关闭页面
				setTimeout(() => {
					wx.navigateBack();
				}, 1500);
			}
		} catch (error) {
			console.error('Auth failed:', error);
			wx.showToast({
				title: '授权失败',
				icon: 'none'
			});
		}
	},

	// 取消授权
	async cancelAuth() {
		try {
			const res = await app.request({
				url: '/api/handle_auth.php',
				method: 'GET',
				data: {
					scene: this.data.scene,
					action: 'cancel'
				}
			});
			
			if (res.code === 200) {
				this.setData({
					scanStatus: '已取消授权'
				});
				wx.showToast({
					title: '已取消授权',
					icon: 'none',
					duration: 1500
				});
				
				// 延迟关闭页面
				setTimeout(() => {
					wx.navigateBack();
				}, 1500);
			}
		} catch (error) {
			console.error('Cancel auth failed:', error);
			wx.showToast({
				title: '操作失败',
				icon: 'none'
			});
		}
	},

	onUnload() {
		// 页面卸载时，如果未授权，自动取消授权
		if (this.data.scene && this.data.scanStatus === '已扫码，请点击授权登录') {
			this.cancelAuth();
		}
	}
})