<?php
// 检查是否被直接访问
if (basename($_SERVER['PHP_SELF']) == 'problems_detail.php') {
    header('Location: /problems.php');
    exit();
}

// 确保pid参数存在且不为空
if (!isset($pid) || empty(trim($pid))) {
    header('Location: /problems.php');
    exit();
}

// 设置默认页面标题和导航标题
$page_title = '问题详情';
$nav_title = '问题详情';

include 'header.php';

?>

<script>
    // 全局变量存储问题ID
    const problemId = '<?php echo $pid; ?>';

    // 页面加载完成后获取问题详情
    document.addEventListener('DOMContentLoaded', function() {
        loadProblemDetail();
    });

    // 通过API获取问题详情
    function loadProblemDetail() {
        fetch(`api.php?action=getProblemDetail&pid=${problemId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data) {
                    displayProblemDetail(result.data);
                } else if (!result.success && result.message) {
                    // 如果问题不存在，跳转到问题搜索页面
                    if (result.message === '问题不存在') {
                        window.location.href = `/problems.php`;
                    } else {
                        document.querySelector('.problem-detail').innerHTML = `<p class="error">获取问题详情失败: ${result.message}</p>`;
                    }
                }
            })
            .catch(error => {
                console.error('获取问题详情失败:', error);
                document.querySelector('.problem-detail').innerHTML = `<p class="error">获取问题详情失败</p>`;
            });
    }

    // 全局问题数据变量
    let globalProblemData = null;

    // 显示问题详情
    function displayProblemDetail(problem) {
        // 保存问题数据到全局变量
        globalProblemData = problem;

        // 设置页面标题，格式为：问题详情 - [设备名称] - 个人设备信息管理平台
        const deviceName = problem.device || '未知设备';
        const siteSuffix = ' - 个人设备信息管理平台';
        document.title = '问题详情[' + deviceName + ']' + siteSuffix;

        // 构建问题详情HTML
        const problemDetailHTML = `
            <h2 style="text-align: center; margin-bottom: 30px;">${problem.device || '未知设备'}</h2>

            <div class="detail-layout">
                <!-- 左侧问题信息 -->
                <div class="detail-sidebar">
                    <div class="device-info">
            <div class="info-item">
                <label>设备类型：</label>
                <span>${problem.type_name || '无'}</span>
            </div>
            <div class="info-item">
                <label>所属站场：</label>
                <span>${problem.station_name || '无'}</span>
            </div>
            <div class="info-item">
                <label>包保部门：</label>
                <span>${problem.department_name || '无'}</span>
            </div>
            <div class="info-item">
                <label>责任部门：</label>
                <span>${problem.department || '无'}</span>
            </div>
            <div class="info-item">
                <label>发现人员：</label>
                <span>${formatPersonNames(problem.reporter)}</span>
            </div>
            <div class="info-item">
                <label>发现时间：</label>
                <span>${problem.report_time || '无'}</span>
            </div>
            <div class="info-item">
                <label>问题状态：</label>
                <span class="status-tag ${problem.process === 1 ? 'status-green' : 'status-red'}">${problem.process === 1 ? '已闭环' : '已创建'}</span>
            </div>
        </div>
                </div>
                <!-- 右侧内容 -->
                <div class="detail-content">
                    <!-- 问题描述（不使用折叠框） -->
                    <div class="problem-description-section">
                        <h3>问题描述</h3>
                        <div class="problem-description-content" style="height: auto;">${problem.description || '无问题描述'}</div>
                    </div>

                    <!-- 问题照片折叠块 -->
                    <div class="collapse-block">
                        <div class="collapse-header" onclick="toggleCollapse('problem-photos')">
                            <div class="collapse-header-title">
                                <h3>问题照片</h3>
                                <span class="record-count">
                                    <span class="record-count-number">0</span>
                                </span>
                            </div>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div id="problem-photos" class="collapse-content" style="display: none;">
                            <div id="photos-content">
                                <div class="loading">加载中...</div>
                            </div>
                        </div>
                    </div>

                    <!-- 处理记录（根据状态显示） -->
                    ${problem.process === 1 ? `
                    <div class="processing-records-section">
                        <h3>处理记录</h3>
                        <div class="processing-record">
                            <div class="record-item">
                                <label>克服时间：</label>
                                <span>${problem.resolution_time || '无'}</span>
                            </div>
                            <div class="record-item">
                                <label>克服人员：</label>
                                <span>${formatPersonNames(problem.resolver)}</span>
                            </div>
                            <div class="record-item">
                                <label>克服说明：</label>
                                <div class="resolution-content">${problem.resolution_content || '无'}</div>
                            </div>
                        </div>
                    </div>` : ''}
                </div>
            </div>

            <div class="action-buttons">
                <button class="back-btn" onclick="window.location.href='/problems.php'">返回问题列表</button>
                <button class="device-btn" onclick="window.location.href='/devices.php?did=${problem.did || ''}'">查看设备详情</button>
            </div>
        `;

        // 更新问题详情内容
        document.querySelector('.problem-detail').innerHTML = problemDetailHTML;

        // 尝试获取设备详情数据
        fetch(`api.php?action=getDeviceDetail&did=${problem.did}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data) {
                    const deviceData = result.data;
                    document.querySelector('.info-item:nth-child(1) span').textContent = deviceData.type_name || '无';
                    document.querySelector('.info-item:nth-child(2) span').textContent = deviceData.station_name || '无';
                    document.querySelector('.info-item:nth-child(3) span').textContent = deviceData.department_name || '无';
                }
            })
            .catch(error => {
                console.error('获取设备详情失败:', error);
            });
    }

    // 格式化发现人显示
    function formatCreators(creators) {
        if (!creators || creators === 'undefined') {
            return '无';
        }

        const creatorArray = creators.split('||');
        const formattedCreators = [];

        creatorArray.forEach(creator => {
            if (creator && creator !== 'undefined') {
                formattedCreators.push('<span class="keeper-tag">' + creator + '</span>');
            }
        });

        return formattedCreators.length > 0 ? formattedCreators.join('') : '无';
    }

    // 格式化人员姓名，处理多个名字的分隔显示
    function formatPersonNames(names) {
        if (!names || names === 'undefined') {
            return '无';
        }

        // 支持逗号、竖线等多种分隔符
        const nameArray = names.split(/[,|，]/).filter(name => name.trim() !== '');

        if (nameArray.length === 0) {
            return '无';
        }

        return nameArray.map(name => `<span class="blue-tag person-name">${name.trim()}</span>`).join('');
    }

    // 格式化日期时间
    function formatDateTime(dateTime) {
        if (!dateTime) {
            return '无';
        }
        // 假设dateTime格式为Y-m-d H:i:s
        const date = new Date(dateTime);
        return date.toLocaleString('zh-CN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // 获取状态文本
    function getStatusText(status) {
        if (!status || status === 'undefined') {
            return '未知状态';
        }

        const statusMap = {
            'pending': '待处理',
            'processing': '处理中',
            'resolved': '已解决',
            'closed': '已关闭'
        };
        return statusMap[status] || status;
    }

    // 切换折叠块显示/隐藏
    function toggleCollapse(id) {
        const content = document.getElementById(id);
        const icon = content.previousElementSibling.querySelector('.collapse-icon');

        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '▲';

            // 懒加载内容
            if (content.querySelector('.loading')) {
                if (id === 'problem-photos') {
                    loadProblemPhotos();
                } else if (id === 'processing-records') {
                    loadProcessingRecords();
                }
            }
        } else {
            content.style.display = 'none';
            icon.textContent = '▼';
        }
    }

    // 加载问题照片
    function loadProblemPhotos() {
        fetch(`api.php?action=getProblemPhotos&pid=${problemId}`)
            .then(response => response.json())
            .then(result => {
                const photosContent = document.getElementById('photos-content');

                if (result.success && result.data && result.data.length > 0) {
                    let html = '<div class="photos-grid">';

                    result.data.forEach(photo => {
                        const photoUrl = photo.url.startsWith('http') ? photo.url : `/${photo.url}`;
                        html += `
                            <div class="photo-item">
                                <img src="${photoUrl}" alt="问题照片" class="photo-thumbnail" data-url="${photoUrl}">
                                <div class="photo-name">${photo.name}</div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    photosContent.innerHTML = html;

                    // 添加点击查看大图的事件
                    document.querySelectorAll('.photo-thumbnail').forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            showPhotoPreview(this.getAttribute('data-url'));
                        });
                    });
                } else {
                    photosContent.innerHTML = '<p class="no-result">暂无问题照片</p>';
                }
            })
            .catch(error => {
                document.getElementById('photos-content').innerHTML = `<p class="error">加载照片失败: ${error.message}</p>`;
            });
    }

    // 加载处理记录
    function loadProcessingRecords() {
        fetch(`api.php?action=getProblemProcessingRecords&pid=${problemId}`)
            .then(response => response.json())
            .then(result => {
                const processingContent = document.getElementById('processing-content');

                if (result.success && result.data && result.data.length > 0) {
                    let html = '<div class="processing-timeline">';

                    result.data.forEach((record, index) => {
                        html += `
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-person">${record.processor}</span>
                                        <span class="timeline-time">${formatDateTime(record.process_time)}</span>
                                    </div>
                                    <div class="timeline-body">${record.process_content}</div>
                                    ${record.process_photos && record.process_photos.length > 0 ? `
                                        <div class="timeline-photos">
                                            ${record.process_photos.map(photo => {
                                                const photoUrl = photo.url.startsWith('http') ? photo.url : `/${photo.url}`;
                                                return `<img src="${photoUrl}" alt="处理照片" class="timeline-photo-thumbnail" data-url="${photoUrl}">`;
                                            }).join('')}
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    processingContent.innerHTML = html;

                    // 更新处理记录数量
                    document.querySelector('#processing-count .record-count-number').textContent = result.data.length;

                    // 添加点击查看大图的事件
                    document.querySelectorAll('.timeline-photo-thumbnail').forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            showPhotoPreview(this.getAttribute('data-url'));
                        });
                    });
                } else {
                    processingContent.innerHTML = '<p class="no-result">暂无处理记录</p>';
                    document.querySelector('#processing-count .record-count-number').textContent = '0';
                }
            })
            .catch(error => {
                document.getElementById('processing-content').innerHTML = `<p class="error">加载处理记录失败: ${error.message}</p>`;
            });
    }

    // 显示照片预览
    function showPhotoPreview(url) {
        // 创建预览模态框
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.onclick = function() {
            modal.remove();
        };

        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        modalContent.style.maxWidth = '90vw';
        modalContent.style.maxHeight = '90vh';
        modalContent.style.padding = '10px';
        modalContent.onclick = function(e) {
            e.stopPropagation();
        };

        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-btn';
        closeBtn.textContent = '×';
        closeBtn.style.position = 'absolute';
        closeBtn.style.top = '10px';
        closeBtn.style.right = '10px';
        closeBtn.onclick = function() {
            modal.remove();
        };

        const img = document.createElement('img');
        img.src = url;
        img.style.maxWidth = '100%';
        img.style.maxHeight = '80vh';
        img.style.display = 'block';

        modalContent.appendChild(closeBtn);
        modalContent.appendChild(img);
        modal.appendChild(modalContent);

        document.body.appendChild(modal);
    }
</script>

<div class="problem-detail">
    <div class="loading" style="text-align: center; padding: 50px 0;">加载问题详情中...</div>
</div>

<style>
    .problem-detail {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin: 20px 0;
    }

    .detail-layout {
        display: flex;
        gap: 30px;
        margin-top: 30px;
    }

    .detail-sidebar {
        flex: 0 0 320px;
    }

    .detail-content {
        flex: 1;
    }

    .device-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }

    .info-item {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
    }

    .info-item label {
        flex: 0 0 100px;
        font-weight: bold;
        color: #666;
    }

    .info-item span {
        flex: 1;
    }

    .keeper-tag {
        display: inline-block;
        background: #e9ecef;
        color: #495057;
        padding: 2px 8px;
        border-radius: 4px;
        margin-right: 5px;
        margin-bottom: 5px;
    }

    /* 状态标签样式 - 参考 problems.php */
    .status-tag {
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 14px;
        /* 增大字号 */
        font-weight: normal;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        min-width: auto;
        /* 不设置最小宽度，使背景与文字同样长度 */
    }

    .status-red {
        background-color: #fee;
        color: #e74c3c;
        transition: all 0.3s ease;
    }

    .status-red:hover {
        background-color: #e74c3c;
        color: white;
    }

    .status-green {
        background-color: #efe;
        color: #27ae60;
        transition: all 0.3s ease;
    }

    .status-green:hover {
        background-color: #27ae60;
        color: white;
    }

    /* 姓名标签样式 - 参考 devices_detail.php */
    .blue-tag {
        display: inline-block;
        background: #e3f2fd;
        /* 浅蓝色背景 */
        color: #1976d2;
        /* 深蓝色文字 */
        padding: 3px 8px;
        border-radius: 12px;
        margin-right: 5px;
        font-size: 14px;
        /* 增大字号 */
        font-weight: normal;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .blue-tag:hover,
    .blue-tag:active {
        background-color: #1976d2;
        /* 深蓝色背景 */
        color: white;
        /* 白色文字 */
    }

    .person-name {
        margin-right: 5px;
    }

    .person-name:last-child {
        margin-right: 0;
    }

    .problem-description-section {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 20px;
    }

    .problem-description-content {
        text-align: left;
        line-height: 1.6;
        word-break: break-word;
    }

    .problem-description-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #495057;
    }

    .processing-records-section {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 20px;
    }

    .processing-records-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #495057;
    }

    .processing-record {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
    }

    .record-item {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
    }

    .record-item:last-child {
        margin-bottom: 0;
    }

    .record-item label {
        flex: 0 0 100px;
        font-weight: bold;
        color: #666;
    }

    .record-item span,
    .record-item div {
        flex: 1;
        word-break: break-word;
    }

    .resolution-content {
        background: white;
        padding: 15px;
        border-radius: 4px;
        line-height: 1.6;
    }

    .collapse-block {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .collapse-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f8f9fa;
        cursor: pointer;
        user-select: none;
    }

    .collapse-header:hover {
        background: #e9ecef;
    }

    .collapse-header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: bold;
    }

    .collapse-header-title span:first-child {
        font-size: 16px;
        /* 设置与h3一致的字号 */
    }

    .record-count {
        background: #3498db;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: normal;
    }

    .collapse-icon {
        transition: transform 0.3s;
    }

    .collapse-content {
        padding: 20px;
    }

    .problem-description-content {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .photos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }

    .photo-item {
        text-align: center;
    }

    .photo-thumbnail {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .photo-thumbnail:hover {
        transform: scale(1.05);
    }

    .photo-name {
        margin-top: 8px;
        font-size: 12px;
        color: #666;
        word-break: break-all;
    }

    .processing-timeline {
        position: relative;
        padding-left: 30px;
    }

    .processing-timeline::before {
        content: '';
        position: absolute;
        left: 14px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-dot {
        position: absolute;
        left: -30px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #3498db;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e9ecef;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .timeline-person {
        font-weight: bold;
        color: #3498db;
    }

    .timeline-time {
        font-size: 12px;
        color: #666;
    }

    .timeline-body {
        line-height: 1.6;
        white-space: pre-wrap;
        margin-bottom: 10px;
    }

    .timeline-photos {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .timeline-photo-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .back-btn,
    .device-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .back-btn {
        background: #6c757d;
        color: white;
    }

    .back-btn:hover {
        background: #5a6268;
    }

    .device-btn {
        background: #3498db;
        color: white;
    }

    .device-btn:hover {
        background: #2980b9;
    }

    .loading {
        text-align: center;
        padding: 30px 0;
        color: #666;
    }

    .no-result {
        text-align: center;
        padding: 30px 0;
        color: #666;
    }

    .error {
        text-align: center;
        padding: 30px 0;
        color: #dc3545;
    }

    /* 响应式设计 */
    @media (max-width: 768px) {

        /* 窄屏时调整全局容器宽度 */
        .container {
            max-width: none;
            width: 100%;
            padding: 0;
            margin: 0;
        }

        /* 窄屏时让主体内容宽度铺满屏幕 */
        .problem-detail {
            background: white;
            border-radius: 0;
            box-shadow: none;
            padding: 20px 15px;
            width: 100%;
            margin: 0;
        }

        .detail-layout {
            flex-direction: column;
        }

        .detail-sidebar {
            flex: none;
            margin-bottom: 20px;
        }

        /* 窄屏时.info-item垂直排列 */
        .info-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .info-item label {
            flex: none;
            width: auto;
            margin-bottom: 5px;
        }

        .info-item span {
            flex: none;
        }

        .photos-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }

        .photo-thumbnail {
            height: 80px;
        }

        /* 窄屏时移除所有卡片样式，充分利用宽度空间 */
        .problem-description-section,
        .processing-records-section,
        .collapse-block,
        .timeline-content,
        .processing-record,
        .problem-description-content {
            background: transparent !important;
            border: none !important;
            padding: 10px 0 !important;
            margin-bottom: 10px !important;
        }

        .collapse-header {
            background: transparent !important;
            padding: 10px 0 !important;
            border-bottom: 1px solid #eee;
        }

        .collapse-content {
            padding: 10px 0 !important;
        }

        /* 窄屏时垂直排列操作按钮 */
        .action-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        /* 窄屏时处理时间线样式调整 */
        .processing-timeline {
            padding-left: 20px;
        }

        .processing-timeline::before {
            left: 9px;
        }

        .timeline-dot {
            left: -20px;
            width: 10px;
            height: 10px;
        }
    }
</style>

<?php include 'footer.php'; ?>