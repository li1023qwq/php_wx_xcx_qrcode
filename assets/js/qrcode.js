class QrcodeLogin {
    constructor(options) {
        this.options = Object.assign({
            createUrl: '../api/create_qrcode.php',
            checkUrl: '../api/check_status.php',
            pollingInterval: 1500,
            maxPollingCount: 60,
            onSuccess: null,
            onError: null,
            onExpired: null,
            onCancel: null
        }, options);

        this.scene = '';
        this.pollingTimer = null;
        this.pollingCount = 0;
        this.isPolling = false;

        this.elements = {
            qrcodeContainer: document.querySelector('.qrcode-container'),
            qrcodeMask: document.querySelector('.qrcode-mask'),
            qrcodeImage: document.querySelector('#qrcodeImage'),
            createButton: document.querySelector('#createQrcode'),
            statusText: document.querySelector('#status')
        };

        this.init();
    }

    init() {
        if (this.elements.createButton) {
            this.elements.createButton.addEventListener('click', () => this.createQrcode());
        }
    }

    async createQrcode() {
        try {
            this.updateStatus('正在生成二维码...');
            this.elements.createButton.classList.add('disabled');

            const response = await fetch(this.options.createUrl);
            const result = await response.json();

            if (result.code === 200) {
                this.scene = result.data.scene;
                this.elements.qrcodeImage.src = `../assets/qrcodes/${result.data.qrcode}`;
                this.elements.qrcodeContainer.style.display = 'block';
                this.elements.createButton.style.display = 'none';
                
                this.startPolling();
            } else {
                throw new Error(result.msg);
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    startPolling() {
        if (this.isPolling) return;

        this.isPolling = true;
        this.pollingCount = 0;
        this.updateStatus('请使用微信扫码');

        this.pollingTimer = setInterval(() => {
            this.checkStatus();
        }, this.options.pollingInterval);
    }

    stopPolling() {
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
        }
        this.isPolling = false;
    }

    async checkStatus() {
        try {
            this.pollingCount++;

            if (this.pollingCount > this.options.maxPollingCount) {
                this.handleExpired();
                return;
            }

            const response = await fetch(`${this.options.checkUrl}?scene=${this.scene}`);
            const result = await response.json();

            switch (result.code) {
                case 200: // 登录成功
                    this.handleSuccess(result.data);
                    break;
                    
                case 201: // 等待扫码
                    this.updateStatus(result.msg);
                    break;
                    
                case 202: // 已扫码，等待确认
                    this.updateStatus(result.msg);
                    break;
                    
                case 203: // 已取消
                    this.handleCancel();
                    break;
                    
                case 408: // 已过期
                    this.handleExpired();
                    break;
                    
                default:
                    this.handleError(new Error(result.msg));
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    updateStatus(message) {
        if (this.elements.statusText) {
            this.elements.statusText.textContent = message;
        }
    }

    handleSuccess(data) {
        this.stopPolling();
        this.updateStatus('登录成功');
        this.showMask('登录成功');
        
        if (typeof this.options.onSuccess === 'function') {
            this.options.onSuccess(data);
        }
    }

    handleError(error) {
        this.stopPolling();
        this.updateStatus(error.message);
        this.showMask('出错了');
        
        if (typeof this.options.onError === 'function') {
            this.options.onError(error);
        }
    }

    handleExpired() {
        this.stopPolling();
        this.updateStatus('二维码已过期');
        this.showMask('二维码已过期');
        
        if (typeof this.options.onExpired === 'function') {
            this.options.onExpired();
        }
    }

    handleCancel() {
        this.stopPolling();
        this.updateStatus('已取消授权');
        this.showMask('已取消授权');
        
        if (typeof this.options.onCancel === 'function') {
            this.options.onCancel();
        }
    }

    showMask(text) {
        if (this.elements.qrcodeMask) {
            this.elements.qrcodeMask.textContent = text;
            this.elements.qrcodeMask.classList.add('show');
        }
    }

    hideMask() {
        if (this.elements.qrcodeMask) {
            this.elements.qrcodeMask.classList.remove('show');
        }
    }

    reset() {
        this.stopPolling();
        this.scene = '';
        this.pollingCount = 0;
        this.hideMask();
        
        if (this.elements.qrcodeImage) {
            this.elements.qrcodeImage.src = '';
        }
        
        if (this.elements.createButton) {
            this.elements.createButton.classList.remove('disabled');
            this.elements.createButton.style.display = 'inline-block';
        }
        
        if (this.elements.qrcodeContainer) {
            this.elements.qrcodeContainer.style.display = 'none';
        }
        
        this.updateStatus('');
    }
} 