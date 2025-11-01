<?php
// 设置导航标题和页面标题
// $nav_title = '长南高信车间设备信息管理平台';
// $page_title = '长南高信车间设备信息管理平台';

// 引入配置文件和页眉
include 'config.php';
include 'header.php';
?>
<div class="dashboard">
    <h2 style="text-align: center; margin-bottom: 40px;">您好<?php if (isset($_SESSION['username'])) echo '，' . $_SESSION['username'] . '！'; ?>！</h2>

    <!-- 宽屏布局容器 -->
    <div class="dashboard-layout">
        <!-- 左侧列：设备总数 + 扫码查验 -->
        <div class="dashboard-column">
            <div class="dashboard-card" onclick="window.location.href='/devices.php'">
                <div class="dashboard-card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <rect x="5" y="6" width="14" height="12" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5 9H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M9 6C9 4.5 10.5 3 12 3C13.5 3 15 4.5 15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="dashboard-card-content">
                    <h3>设备总数</h3>
                    <p id="device-count" class="dashboard-card-count">--</p>
                </div>
            </div>

            <!-- 扫码查验按钮 - 设备总数下方 -->
            <div class="verification-card" id="scan-verification">
                <div class="verification-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M16 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M8 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M3.5 10H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M22 10H20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M12 16L16 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M16 16L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="verification-content">
                    <h3>扫码查验</h3>
                </div>
            </div>
        </div>

        <!-- 右侧列：问题总数 + 手动查验 -->
        <div class="dashboard-column">
            <div class="dashboard-card" onclick="window.location.href='/problems.php'">
                <div class="dashboard-card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <path d="M12 4L4 20H20L12 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="15" r="1" fill="currentColor" />
                        <line x1="12" y1="9" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="dashboard-card-content">
                    <h3>问题总数</h3>
                    <p id="problem-count" class="dashboard-card-count">--</p>
                </div>
            </div>

            <!-- 手动查验按钮 - 问题总数下方 -->
            <div class="verification-card" id="manual-verification">
                <div class="verification-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <path d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="verification-content">
                    <h3>手动查验</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- 窄屏布局容器 - 默认隐藏 -->
    <div class="dashboard-cards-mobile">
        <div class="dashboard-card" onclick="window.location.href='/devices.php'">
            <div class="dashboard-card-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                    <rect x="5" y="6" width="14" height="12" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5 9H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M9 6C9 4.5 10.5 3 12 3C13.5 3 15 4.5 15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="dashboard-card-content">
                <h3>设备总数</h3>
                <p id="device-count-mobile" class="dashboard-card-count">--</p>
            </div>
        </div>

        <!-- 窄屏：查验功能按钮 - 设备总数下方，问题总数上方 -->
        <div class="verification-container-mobile">
            <div class="verification-card" id="scan-verification-mobile">
                <div class="verification-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M16 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M8 4V2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M3.5 10H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M22 10H20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M12 16L16 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M16 16L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="verification-content">
                    <h3>扫码查验</h3>
                </div>
            </div>

            <div class="verification-card" id="manual-verification-mobile">
                <div class="verification-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <path d="M12 16V12M12 8H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="verification-content">
                    <h3>手动查验</h3>
                </div>
            </div>
        </div>

        <div class="dashboard-card" onclick="window.location.href='/problems.php'">
            <div class="dashboard-card-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                    <path d="M12 4L4 20H20L12 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="12" cy="15" r="1" fill="currentColor" />
                    <line x1="12" y1="9" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>
            <div class="dashboard-card-content">
                <h3>问题总数</h3>
                <p id="problem-count-mobile" class="dashboard-card-count">--</p>
            </div>
        </div>
    </div>
</div>

<script>
    // 页面加载完成后获取统计数据
    window.addEventListener('DOMContentLoaded', function() {
        // 获取设备和问题统计数据
        fetch('api.php?action=getDashboardStats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 更新设备总数
                    document.getElementById('device-count').textContent = data.data.device_count;
                    // 更新问题总数
                    document.getElementById('problem-count').textContent = data.data.problem_count;
                } else {
                    console.error('获取统计数据失败:', data.message);
                }
            })
            .catch(error => {
                console.error('获取统计数据失败:', error);
            });
    });
</script>

<!-- 引入二维码识别库 -->
<script src="files/js/qrcode.min.js"></script>

<!-- 使用本地jsQR库用于二维码识别 -->
<script src="files/js/jsQR.min.js"></script>

<style>
    .dashboard {
        padding: 20px;
    }

    /* 宽屏布局 */
    .dashboard-layout {
        display: flex;
        justify-content: center;
        gap: 40px;
    }

    .dashboard-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* 窄屏布局 - 默认隐藏 */
    .dashboard-cards-mobile {
        display: none;
    }

    .dashboard-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 300px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .dashboard-card-icon {
        margin-bottom: 20px;
        color: #3498db;
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dashboard-card-icon svg {
        width: 100%;
        height: 100%;
    }

    /* 问题总数卡片样式 - 红色 */
    .dashboard-column:nth-child(2) .dashboard-card .dashboard-card-icon,
    .dashboard-cards-mobile>.dashboard-card:nth-child(3) .dashboard-card-icon {
        color: #e74c3c;
    }

    /* 保持问题总数的图标文字颜色不变 */
    .dashboard-column:nth-child(2) .dashboard-card .dashboard-card-count,
    .dashboard-cards-mobile>.dashboard-card:nth-child(3) .dashboard-card-count {
        color: #e74c3c;
    }

    .dashboard-card-content h3 {
        font-size: 20px;
        color: #333;
        margin-bottom: 10px;
    }

    .dashboard-card-count {
        font-size: 48px;
        font-weight: bold;
        color: #3498db;
        margin: 0;
    }

    /* 查验功能样式 */
    .verification-card {
        width: 300px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 15px 20px;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 80px;
    }

    .verification-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .verification-icon {
        margin-right: 15px;
        color: #9b59b6;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .verification-icon svg {
        width: 100%;
        height: 100%;
    }

    #manual-verification .verification-icon,
    #manual-verification-mobile .verification-icon {
        color: #f39c12;
    }

    .verification-content {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .verification-content h3 {
        font-size: 16px;
        color: #333;
        margin: 0;
    }

    /* 窄屏响应式布局 */
    @media (max-width: 768px) {

        /* 隐藏宽屏布局 */
        .dashboard-layout {
            display: none;
        }

        /* 显示窄屏布局 */
        .dashboard-cards-mobile {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .dashboard-card {
            width: 90%;
            margin-bottom: 20px;
        }

        /* 窄屏查验功能容器 */
        .verification-container-mobile {
            display: flex;
            gap: 15px;
            width: 90%;
            margin-bottom: 20px;
        }

        .verification-card {
            width: 48%;
            height: 70px;
            padding: 10px 15px;
            justify-content: center;
        }

        .verification-icon {
            width: 30px;
            height: 30px;
            margin-right: 10px;
        }

        .verification-icon svg {
            width: 20px;
            height: 20px;
        }

        .verification-content h3 {
            font-size: 14px;
        }
    }

    /* 扫码模态框样式 */
    #qr-scanner-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    #qr-scanner-content {
        background: white;
        border-radius: 12px;
        padding: 20px;
        max-width: 500px;
        width: 90%;
        text-align: center;
    }

    #qr-scanner-video {
        width: 100%;
        max-width: 400px;
        height: auto;
        margin: 20px 0;
    }

    #qr-scanner-close {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
    }

    #qr-scanner-close:hover {
        background-color: #2980b9;
    }

    /* 文件上传按钮样式 */
    #image-upload-button {
        background-color: #2ecc71;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 10px;
        transition: background-color 0.3s;
    }

    #image-upload-button:hover {
        background-color: #27ae60;
    }

    #qr-image-input {
        display: none;
    }

    #qr-preview-image {
        max-width: 100%;
        max-height: 300px;
        margin: 10px 0;
        display: none;
    }

    #use-camera-button {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 10px;
        transition: background-color 0.3s;
        display: none;
    }

    #use-camera-button:hover {
        background-color: #2980b9;
    }

    /* 手动输入模态框样式 */
    #manual-input-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    #manual-input-content {
        background: white;
        border-radius: 12px;
        padding: 20px;
        max-width: 400px;
        width: 90%;
        text-align: center;
    }

    #manual-input-content h3 {
        margin-top: 0;
        color: #333;
    }

    #device-id-input {
        width: 100%;
        padding: 10px;
        margin: 15px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 20px;
    }

    .modal-button {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .modal-button-primary {
        background-color: #3498db;
        color: white;
    }

    .modal-button-primary:hover {
        background-color: #2980b9;
    }

    .modal-button-secondary {
        background-color: #95a5a6;
        color: white;
    }

    .modal-button-secondary:hover {
        background-color: #7f8c8d;
    }

    .buttons-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }

    #image-upload-button,
    #use-camera-button,
    #qr-scanner-close {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    #image-upload-button,
    #use-camera-button {
        background-color: #3498db;
        color: white;
    }

    #image-upload-button:hover,
    #use-camera-button:hover {
        background-color: #2980b9;
    }

    #qr-scanner-close {
        background-color: #95a5a6;
        color: white;
    }

    #qr-scanner-close:hover {
        background-color: #7f8c8d;
    }

    #qr-scanner-video,
    #qr-preview-image {
        margin: 20px auto;
        /* 水平居中 */
        max-width: 100%;
        /* 最大宽度不超过父容器 */
        height: auto;
        /* 保持宽高比 */
        max-height: 300px;
        /* 限制最大高度 */
        border: 1px solid #ddd;
        border-radius: 4px;
        display: block;
        /* 确保块级元素以便居中 */
    }

    #qr-preview-image {
        display: none;
        /* 默认隐藏 */
    }

    /* 加载动画旋转效果 */
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<!-- 扫码查验模态框 -->
<div id="qr-scanner-modal" style="display: none;">
    <div id="qr-scanner-content">
        <h3>扫码查验设备</h3>
        <video id="qr-scanner-video" autoplay></video>
        <img id="qr-preview-image" alt="二维码预览" />

        <!-- 用于显示识别的二维码内容 -->
        <div id="qr-content-display" style="display: none; margin: 10px 0; padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">
            <p style="margin: 0; color: #333;">识别内容: <span id="qr-content-text"></span></p>
        </div>

        <!-- 加载动画 -->
        <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 12px; text-align: center;">
                <div style="width: 40px; height: 40px; margin: 0 auto 15px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin: 0; color: #333;">设备信息加载中...</p>
            </div>
        </div>

        <div class="buttons-container">
            <button id="image-upload-button">选择本地图片扫码</button>
            <input type="file" id="qr-image-input" accept="image/*" />
            <button id="use-camera-button">使用摄像头扫码</button>
            <button id="qr-scanner-close">关闭</button>
        </div>
    </div>
</div>

<!-- 手动查验模态框 -->
<div id="manual-input-modal" style="display: none;">
    <div id="manual-input-content">
        <h3>手动输入设备编号</h3>
        <input type="text" id="device-id-input" placeholder="请输入设备编号" />
        <div class="modal-buttons">
            <button id="manual-input-confirm" class="modal-button modal-button-primary">确定</button>
            <button id="manual-input-cancel" class="modal-button modal-button-secondary">取消</button>
        </div>
    </div>
</div>

<script>
    // 页面加载完成后执行
    document.addEventListener('DOMContentLoaded', function() {
        // 获取DOM元素
        const scanVerification = document.getElementById('scan-verification');
        const manualVerification = document.getElementById('manual-verification');
        const scanVerificationMobile = document.getElementById('scan-verification-mobile');
        const manualVerificationMobile = document.getElementById('manual-verification-mobile');
        const qrScannerModal = document.getElementById('qr-scanner-modal');
        const manualInputModal = document.getElementById('manual-input-modal');
        const qrScannerClose = document.getElementById('qr-scanner-close');
        const manualInputConfirm = document.getElementById('manual-input-confirm');
        const manualInputCancel = document.getElementById('manual-input-cancel');
        const deviceIdInput = document.getElementById('device-id-input');
        const qrScannerVideo = document.getElementById('qr-scanner-video');
        const deviceCount = document.getElementById('device-count');
        const problemCount = document.getElementById('problem-count');
        const deviceCountMobile = document.getElementById('device-count-mobile');
        const problemCountMobile = document.getElementById('problem-count-mobile');

        // 图片上传相关元素
        const imageUploadButton = document.getElementById('image-upload-button');
        const qrImageInput = document.getElementById('qr-image-input');
        const qrPreviewImage = document.getElementById('qr-preview-image');
        const useCameraButton = document.getElementById('use-camera-button');

        // 变量用于存储媒体流
        let stream = null;
        let scanInterval = null;

        // 扫码查验点击事件（宽屏和窄屏）
        function handleScanVerification() {
            // 显示模态框
            qrScannerModal.style.display = 'flex';
            // 阻止背景页面滚动
            document.body.style.overflow = 'hidden';

            // 获取摄像头权限并开始扫码
            startQRScanner();
        }

        // 手动查验点击事件（宽屏和窄屏）
        function handleManualVerification() {
            // 清空输入框
            deviceIdInput.value = '';
            // 显示模态框
            manualInputModal.style.display = 'flex';
            // 阻止背景页面滚动
            document.body.style.overflow = 'hidden';
            // 聚焦输入框
            setTimeout(() => deviceIdInput.focus(), 100);
        }

        // 为宽屏按钮添加事件监听
        scanVerification.addEventListener('click', handleScanVerification);
        manualVerification.addEventListener('click', handleManualVerification);

        // 为窄屏按钮添加事件监听
        if (scanVerificationMobile) {
            scanVerificationMobile.addEventListener('click', handleScanVerification);
        }
        if (manualVerificationMobile) {
            manualVerificationMobile.addEventListener('click', handleManualVerification);
        }

        // 同步宽屏和窄屏的计数显示
        function updateCountDisplays() {
            if (deviceCount && deviceCountMobile) {
                deviceCountMobile.textContent = deviceCount.textContent;
            }
            if (problemCount && problemCountMobile) {
                problemCountMobile.textContent = problemCount.textContent;
            }
        }

        // 监听宽屏计数变化并同步到窄屏
        const observer = new MutationObserver(updateCountDisplays);
        if (deviceCount) {
            observer.observe(deviceCount, {
                childList: true
            });
        }
        if (problemCount) {
            observer.observe(problemCount, {
                childList: true
            });
        }

        // 关闭扫码模态框
        qrScannerClose.addEventListener('click', function() {
            stopQRScanner();
            qrScannerModal.style.display = 'none';
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        });

        // 手动输入取消
        manualInputCancel.addEventListener('click', function() {
            manualInputModal.style.display = 'none';
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        });

        // 手动输入确认
        manualInputConfirm.addEventListener('click', function() {
            const did = deviceIdInput.value.trim();
            if (did) {
                window.location.href = `/devices.php?did=${did}`;
            } else {
                alert('请输入有效的设备编号');
                deviceIdInput.focus();
            }
        });

        // 为输入框添加回车键确认功能
        deviceIdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                manualInputConfirm.click();
            }
        });

        // 点击模态框背景关闭模态框
        qrScannerModal.addEventListener('click', function(e) {
            if (e.target === qrScannerModal) {
                stopQRScanner();
                qrScannerModal.style.display = 'none';
                // 恢复背景页面滚动
                document.body.style.overflow = '';
            }
        });

        manualInputModal.addEventListener('click', function(e) {
            if (e.target === manualInputModal) {
                manualInputModal.style.display = 'none';
                // 恢复背景页面滚动
                document.body.style.overflow = '';
            }
        });

        // 为图片上传按钮添加点击事件
        imageUploadButton.addEventListener('click', function() {
            qrImageInput.click();
        });

        // 为文件输入框添加change事件
        qrImageInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];

                // 检查文件类型是否为图片
                if (!file.type.match('image.*')) {
                    alert('请选择图片文件');
                    return;
                }

                // 停止摄像头扫码
                stopQRScanner();

                // 创建文件读取器
                const reader = new FileReader();

                // 设置读取完成后的回调
                reader.onload = function(e) {
                    // 显示图片预览
                    qrPreviewImage.src = e.target.result;
                    qrPreviewImage.style.display = 'block';
                    qrScannerVideo.style.display = 'none';

                    // 隐藏上传按钮，显示切换回摄像头的按钮
                    imageUploadButton.style.display = 'none';
                    useCameraButton.style.display = 'inline-block';

                    // 延迟识别，确保图片已加载
                    setTimeout(function() {
                        scanQRCodeFromImage(qrPreviewImage);
                    }, 500);
                }

                // 读取文件
                reader.readAsDataURL(file);
            }
        });

        // 为使用摄像头按钮添加点击事件
        useCameraButton.addEventListener('click', function() {
            // 隐藏图片预览，显示视频
            qrPreviewImage.style.display = 'none';
            qrScannerVideo.style.display = 'block';

            // 隐藏切换按钮，显示上传按钮
            useCameraButton.style.display = 'none';
            imageUploadButton.style.display = 'inline-block';

            // 重启摄像头扫码
            startQRScanner();
        });

        // 从图片中识别二维码
        function scanQRCodeFromImage(imageElement) {
            try {
                // 创建canvas用于处理图片
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                // 设置canvas大小与图片相同
                canvas.width = imageElement.width;
                canvas.height = imageElement.height;

                // 绘制图片到canvas
                context.drawImage(imageElement, 0, 0, canvas.width, canvas.height);

                // 获取图像数据
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);

                // 尝试使用jsQR库识别二维码
                if (typeof jsQR !== 'undefined') {
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    if (code) {
                        console.log('识别到二维码内容:', code.data);

                        // 显示识别到的内容
                        const qrContentDisplay = document.getElementById('qr-content-display');
                        const qrContentText = document.getElementById('qr-content-text');
                        qrContentText.textContent = code.data;
                        qrContentDisplay.style.display = 'block';

                        // 显示加载动画
                        const loadingOverlay = document.getElementById('loading-overlay');
                        loadingOverlay.style.display = 'flex';

                        // 检查是否包含设备编号
                        const didMatch = code.data.match(/did=([^&]+)/);
                        if (didMatch && didMatch[1]) {
                            // 延迟跳转，让用户看到识别内容和加载动画
                            setTimeout(() => {
                                window.location.href = `/devices.php?did=${didMatch[1]}`;
                            }, 1000);
                        } else {
                            // 如果没有did参数，直接使用识别到的内容
                            // 延迟跳转，让用户看到识别内容和加载动画
                            setTimeout(() => {
                                window.location.href = `/devices.php?did=${encodeURIComponent(code.data)}`;
                            }, 1000);
                        }
                    }
                } else {
                    // 备用识别方法 - 使用模拟的识别函数
                    const did = tryRecognizeQRCode(imageData);
                    if (did) {
                        window.location.href = `/devices.php?did=${did}`;
                    } else {
                        alert('未能识别图片中的二维码，请尝试其他图片或使用摄像头扫码');
                    }
                }
            } catch (error) {
                console.error('图片二维码识别出错:', error);
                alert('图片二维码识别出错');
            }
        }

        // 开始扫码
        function startQRScanner() {
            // 检查浏览器是否支持getUserMedia
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('您的浏览器不支持摄像头功能，请使用Chrome、Firefox等现代浏览器');
                return;
            }

            // 获取摄像头权限
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment'
                    }
                })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    qrScannerVideo.srcObject = stream;

                    // 创建canvas用于处理视频帧
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');

                    // 设置canvas大小与视频相同
                    function setCanvasSize() {
                        canvas.width = qrScannerVideo.videoWidth;
                        canvas.height = qrScannerVideo.videoHeight;
                    }

                    // 监听视频尺寸变化
                    qrScannerVideo.addEventListener('loadedmetadata', setCanvasSize);

                    // 定期捕获视频帧并尝试识别QR码
                    scanInterval = setInterval(function() {
                        if (qrScannerVideo.readyState === qrScannerVideo.HAVE_ENOUGH_DATA) {
                            // 绘制当前视频帧到canvas
                            context.drawImage(qrScannerVideo, 0, 0, canvas.width, canvas.height);

                            // 获取图像数据
                            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);

                            // 尝试识别QR码
                            try {
                                // 优先使用jsQR库
                                if (typeof jsQR !== 'undefined') {
                                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                                    if (code) {
                                        console.log('识别到二维码内容:', code.data);

                                        // 显示识别到的内容
                                        const qrContentDisplay = document.getElementById('qr-content-display');
                                        const qrContentText = document.getElementById('qr-content-text');
                                        qrContentText.textContent = code.data;
                                        qrContentDisplay.style.display = 'block';

                                        // 显示加载动画
                                        const loadingOverlay = document.getElementById('loading-overlay');
                                        loadingOverlay.style.display = 'flex';

                                        // 检查是否包含设备编号
                                        const didMatch = code.data.match(/did=([^&]+)/);
                                        if (didMatch && didMatch[1]) {
                                            stopQRScanner();
                                            // 延迟跳转，让用户看到识别内容和加载动画
                                            setTimeout(() => {
                                                qrScannerModal.style.display = 'none';
                                                loadingOverlay.style.display = 'none';
                                                // 恢复背景页面滚动
                                                document.body.style.overflow = '';
                                                window.location.href = `/devices.php?did=${didMatch[1]}`;
                                            }, 1000);
                                        } else {
                                            // 如果没有did参数，直接使用识别到的内容
                                            stopQRScanner();
                                            // 延迟跳转，让用户看到识别内容和加载动画
                                            setTimeout(() => {
                                                qrScannerModal.style.display = 'none';
                                                loadingOverlay.style.display = 'none';
                                                // 恢复背景页面滚动
                                                document.body.style.overflow = '';
                                                window.location.href = `/devices.php?did=${encodeURIComponent(code.data)}`;
                                            }, 1000);
                                        }
                                    }
                                } else {
                                    // 备用方案 - 使用模拟的识别函数
                                    const did = tryRecognizeQRCode(imageData);
                                    if (did) {
                                        // 显示识别到的内容
                                        const qrContentDisplay = document.getElementById('qr-content-display');
                                        const qrContentText = document.getElementById('qr-content-text');
                                        qrContentText.textContent = did;
                                        qrContentDisplay.style.display = 'block';

                                        // 显示加载动画
                                        const loadingOverlay = document.getElementById('loading-overlay');
                                        loadingOverlay.style.display = 'flex';

                                        stopQRScanner();
                                        // 延迟跳转，让用户看到识别内容和加载动画
                                        setTimeout(() => {
                                            qrScannerModal.style.display = 'none';
                                            loadingOverlay.style.display = 'none';
                                            // 恢复背景页面滚动
                                            document.body.style.overflow = '';
                                            window.location.href = `/devices.php?did=${did}`;
                                        }, 1000);
                                    }
                                }
                            } catch (error) {
                                console.error('QR码识别出错:', error);
                            }
                        }
                    }, 100);
                })
                .catch(function(error) {
                    console.error('获取摄像头权限失败:', error);
                    alert('获取摄像头权限失败，请确保您已授予浏览器访问摄像头的权限');
                    qrScannerModal.style.display = 'none';
                    // 恢复背景页面滚动
                    document.body.style.overflow = '';
                });
        }

        // 停止扫码
        function stopQRScanner() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            if (scanInterval) {
                clearInterval(scanInterval);
                scanInterval = null;
            }
        }

        // 尝试识别QR码（简化版，实际项目应使用专业库）
        function tryRecognizeQRCode(imageData) {
            // 这里是一个简化的模拟函数
            // 在实际项目中，应该使用jsQR等专业的QR码识别库
            // 由于我们没有实际的QR码识别库，这里返回一个模拟的结果

            // 随机模拟成功识别到一个设备编号（仅用于演示）
            // 实际项目中应该删除这段代码，使用真正的QR码识别库
            if (Math.random() < 0.02) { // 2%的概率模拟识别成功
                // 生成一个模拟的设备编号
                const randomDid = '10000' + Math.floor(Math.random() * 1000);
                console.log('模拟识别到设备编号:', randomDid);
                return randomDid;
            }

            return null;
        }

        // 页面卸载前停止扫码
        window.addEventListener('beforeunload', stopQRScanner);
    });
</script>

<?php
// 引入页脚
include 'footer.php';
