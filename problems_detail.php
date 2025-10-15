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
                                <span class="record-count" onclick="handleCountClickInline(event, 'photos')">
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
                                <label>时间：</label>
                                <span>${problem.resolution_time || '无'}</span>
                            </div>
                            <div class="record-item">
                                <label>人员：</label>
                                <span>${formatPersonNames(problem.resolver)}</span>
                            </div>
                            <div class="record-item">
                                <label>说明：</label>
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

        // 加载问题照片
        loadProblemPhotos();

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

    // 处理数量标签点击事件的内联函数
    function handleCountClickInline(e, type) {
        e.stopPropagation(); // 阻止事件冒泡到父元素
        if (type === 'photos') {
            loadProblemPhotos();
        }
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
    function loadProblemPhotos(page = 1, pageSize = 5) {
        return new Promise((resolve, reject) => {
            const content = document.getElementById('photos-content');

            let url = `api.php?action=getProblemPhotos&pid=${problemId}`;
            // 总是添加分页参数
            if (pageSize === 'all') {
                // 当选择全部时，传递pageSize=0表示查询所有记录
                url += `&page=1&pageSize=0`;
            } else {
                // 确保pageSize是有效的数字
                const numericPageSize = parseInt(pageSize) || 5;
                url += `&page=${page}&pageSize=${numericPageSize}`;
            }

            content.innerHTML = '<div class="loading">加载中...</div>';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // 检查数据是否包含total字段（分页模式）
                    const hasPagination = data.total !== undefined && data.data !== undefined;
                    const photos = hasPagination ? data.data : data;
                    const total = hasPagination ? data.total : data.length;

                    // 更新照片数量显示
                    const countElement = document.querySelector('.record-count-number');
                    if (countElement) {
                        countElement.textContent = total;
                    }

                    if (photos.length > 0) {
                        let html = '<table class="drawings-table">';
                        html += '<thead><tr><th>序号</th><th>照片名称</th><th>文件大小</th><th>操作</th></tr></thead>';
                        html += '<tbody>';

                        photos.forEach((photo, index) => {
                            // 确保root_dir以斜杠结尾
                            let rootDir = photo.root_dir || '';
                            if (rootDir && !rootDir.endsWith('/')) {
                                rootDir += '/';
                            }

                            // 构建完整的URL
                            const fullUrl = photo.url || (rootDir + (photo.link_name || ''));

                            // 格式化文件大小
                            const fileSize = formatFileSize(photo.file_size || 0);

                            // 确定文件类型
                            const fileExtension = (photo.original_name || photo.name || '').split('.').pop()?.toLowerCase() || '';
                            let fileType = '其他文件';
                            if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'].includes(fileExtension)) {
                                fileType = '图片';
                            }

                            // 计算正确的序号（考虑分页）
                            const serialNumber = pageSize === 'all' ? index + 1 : (page - 1) * pageSize + index + 1;

                            html += `<tr>`;
                            html += `<td>${serialNumber}</td>`;
                            html += `<td><a href="javascript:void(0)" onclick="previewPhoto('${fullUrl}', '${photo.original_name || photo.name || '问题照片'}')">${photo.original_name || photo.name || '问题照片'}</a></td>`;
                            html += `<td>${fileSize}</td>`;
                            html += `<td><a href="javascript:void(0)" class="download-btn" data-url="${fullUrl}" data-name="${photo.original_name || photo.name || '问题照片'}" data-type="${fileType}" data-size="${fileSize}">下载</a></td>`;
                            html += `</tr>`;
                        });

                        html += '</tbody></table>';
                        // 只有在数据加载完成后才更新内容，避免闪烁
                        content.innerHTML = html;

                        // 添加分页控件
                        addPaginationControls(total, page, pageSize, 'photos');
                    } else {
                        content.innerHTML = '<p class="no-result">没有查询到照片</p>';
                        // 移除分页控件
                        removePaginationControls('photos');
                    }

                    resolve(data);
                })
                .catch(error => {
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls('photos');

                    reject(error);
                });
        });
    }

    // 预览照片
    function previewPhoto(url, title) {
        // 创建预览模态框
        const modal = document.createElement('div');
        modal.className = 'preview-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            user-select: none;
            display: block;
            overflow: hidden;
        `;

        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';

        // 阻止模态框内的滑动事件传递到背景页面，但允许按钮点击
        modal.addEventListener('touchstart', function(e) {
            // 检查是否点击的是按钮元素或按钮内的元素
            if (!e.target.closest('button')) {
                e.preventDefault();
            }
        }, {
            passive: false
        });

        modal.addEventListener('touchmove', function(e) {
            // 检查是否点击的是按钮元素或按钮内的元素
            if (!e.target.closest('button')) {
                e.preventDefault();
            }
        }, {
            passive: false
        });

        const closeBtn = document.createElement('button');
        closeBtn.textContent = '×';
        closeBtn.style.cssText = `
            position: absolute;
            top: 20px;
            right: 30px;
            bottom: 20px;
            color: white;
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        closeBtn.onclick = () => {
            document.body.removeChild(modal);
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        };
        closeBtn.onmouseover = () => {
            closeBtn.style.color = '#ff6b6b';
        };
        closeBtn.onmouseout = () => {
            closeBtn.style.color = 'white';
        };

        // 创建标题
        const titleElement = document.createElement('div');
        titleElement.textContent = title;
        titleElement.style.cssText = `
            position: absolute;
            top: 20px;
            left: 30px;
            color: white;
            font-size: 16px;
            max-width: 60%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 20px;
            cursor: pointer;
        `;

        // 完整文件名气泡
        let filenameBubble = null;

        // 处理点击事件的函数
        function handleTitleClick(e) {
            e.stopPropagation(); // 阻止冒泡到模态框

            if (filenameBubble && document.body.contains(filenameBubble)) {
                document.body.removeChild(filenameBubble);
                filenameBubble = null;
            } else {
                // 创建气泡元素
                filenameBubble = document.createElement('div');
                filenameBubble.textContent = title;
                filenameBubble.style.cssText = `
                    position: fixed;
                    top: 40px;
                    left: 30px;
                    background-color: rgba(255, 255, 255, 0.95);
                    color: #333;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 14px;
                    z-index: 1001;
                    max-width: 80%;
                    word-wrap: break-word;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
                `;

                // 添加小三角形
                const triangle = document.createElement('div');
                triangle.style.cssText = `
                    position: absolute;
                    top: -6px;
                    left: 15px;
                    width: 0;
                    height: 0;
                    border-left: 6px solid transparent;
                    border-right: 6px solid transparent;
                    border-bottom: 6px solid rgba(255, 255, 255, 0.95);
                `;
                filenameBubble.appendChild(triangle);
                document.body.appendChild(filenameBubble);
            }
        }

        // 添加点击事件（鼠标）
        titleElement.addEventListener('click', handleTitleClick);

        // 添加触摸事件（移动设备）
        titleElement.addEventListener('touchstart', function(e) {
            e.preventDefault(); // 阻止默认行为
            handleTitleClick(e);
        }, {
            passive: false
        });

        // 点击气泡以外区域关闭气泡
        function closeFilenameBubble(e) {
            if (filenameBubble && document.body.contains(filenameBubble) &&
                !filenameBubble.contains(e.target) && e.target !== titleElement) {
                document.body.removeChild(filenameBubble);
                filenameBubble = null;
            }
        }

        // 添加全局点击事件监听
        document.addEventListener('click', closeFilenameBubble);

        // 移除模态框时也移除事件监听
        modal.addEventListener('remove', function() {
            document.removeEventListener('click', closeFilenameBubble);
            if (filenameBubble && document.body.contains(filenameBubble)) {
                document.body.removeChild(filenameBubble);
                filenameBubble = null;
            }
        });

        // 创建操作按钮容器
        const controlsContainer = document.createElement('div');
        controlsContainer.style.cssText = `
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        `;

        // 创建缩小按钮
        const zoomOutBtn = document.createElement('button');
        zoomOutBtn.textContent = '缩小';
        zoomOutBtn.style.cssText = `
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s;
        `;

        // 创建重置按钮
        const resetBtn = document.createElement('button');
        resetBtn.textContent = '重置';
        resetBtn.style.cssText = `
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s;
        `;

        // 创建放大按钮
        const zoomInBtn = document.createElement('button');
        zoomInBtn.textContent = '放大';
        zoomInBtn.style.cssText = `
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            transition: all 0.3s;
        `;

        // 按钮悬停效果
        [zoomOutBtn, resetBtn, zoomInBtn].forEach(btn => {
            btn.onmouseover = () => {
                btn.style.backgroundColor = 'rgba(255, 255, 255, 0.3)';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.5)';
            };
            btn.onmouseout = () => {
                btn.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
                btn.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            };
        });

        // 添加到控制容器
        controlsContainer.appendChild(zoomOutBtn);
        controlsContainer.appendChild(resetBtn);
        controlsContainer.appendChild(zoomInBtn);

        // 创建图片容器
        const viewport = document.createElement('div');
        viewport.style.cssText = `
            max-width: 100%;
            width: 100%;
            position: absolute;
            top: 45px;
            bottom: 50px;
            height: calc(100% - 95px);
            overflow: hidden;
            z-index: 1;
            background-color: rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        // 创建可拖动的内容容器
        const contentContainer = document.createElement('div');
        contentContainer.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform-origin: center;
            cursor: grab;
            transition: transform 0.1s ease;
        `;
        contentContainer.onmousedown = () => {
            contentContainer.style.cursor = 'grabbing';
        };
        contentContainer.onmouseup = () => {
            contentContainer.style.cursor = 'grab';
        };

        // 检查文件扩展名，决定如何预览
        const fileExtension = url.split('.').pop().toLowerCase();

        if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'].includes(fileExtension)) {
            // 图片文件直接预览
            const img = document.createElement('img');
            img.src = url;
            img.alt = title;
            img.style.cssText = `
                max-width: none;
                max-height: none;
                width: 100%;
                height: auto;
                display: block;
            `;
            contentContainer.appendChild(img);

            // 初始化一个空的图片占位符
            const placeholder = document.createElement('div');
            placeholder.className = 'image-placeholder';
            placeholder.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: #ccc;
                font-size: 16px;
                text-align: center;
                display: none;
            `;
            placeholder.textContent = '图片加载中...';
            viewport.appendChild(placeholder);

            // 显示加载中
            placeholder.style.display = 'block';

            // 等待图片加载完成后初始化
            img.onload = () => {
                placeholder.style.display = 'none';
                initZoomAndPan(img, contentContainer, viewport, zoomInBtn, resetBtn, zoomOutBtn);
            };

            // 处理图片加载失败的情况
            img.onerror = () => {
                placeholder.style.display = 'block';
                placeholder.textContent = '图片加载失败，请尝试下载查看';
                placeholder.style.color = '#ff6b6b';

                // 添加下载按钮
                const downloadBtn = document.createElement('a');
                downloadBtn.href = url;
                downloadBtn.download = title;
                downloadBtn.textContent = '下载图片';
                downloadBtn.style.cssText = `
                    display: inline-block;
                    margin-top: 10px;
                    padding: 8px 16px;
                    background-color: #3498db;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    font-size: 14px;
                    cursor: pointer;
                `;
                placeholder.appendChild(downloadBtn);
            };
        } else {
            // 非图片文件显示提示
            const placeholder = document.createElement('div');
            placeholder.className = 'image-placeholder';
            placeholder.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: #ccc;
                font-size: 16px;
                text-align: center;
                padding: 20px;
            `;
            placeholder.innerHTML = `
                <div style="margin-bottom: 10px;">无法直接预览此文件类型</div>
                <a href="${url}" download="${title}" style="
                    display: inline-block;
                    padding: 8px 16px;
                    background-color: #3498db;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    font-size: 14px;
                    cursor: pointer;
                ">下载文件</a>
            `;
            viewport.appendChild(placeholder);
        }

        // 组装模态框
        viewport.appendChild(contentContainer);
        modal.appendChild(titleElement);
        modal.appendChild(closeBtn);
        modal.appendChild(viewport);
        modal.appendChild(controlsContainer);
        document.body.appendChild(modal);

        // 点击模态框外部关闭
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
                document.body.style.overflow = '';
            }
        });

        // ESC键关闭
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(modal);
                document.removeEventListener('keydown', escListener);
                document.body.style.overflow = '';
            }
        });
    }

    // 初始化缩放和平移功能
    function initZoomAndPan(img, contentContainer, viewport, zoomInBtn, resetBtn, zoomOutBtn) {
        // 初始化缩放比例和位置
        let scale = 1;
        let lastX = 0;
        let lastY = 0;
        let isDragging = false;
        let offsetX = 0;
        let offsetY = 0;

        // 获取视口和图片尺寸
        const viewportWidth = viewport.offsetWidth;
        const viewportHeight = viewport.offsetHeight;
        const imgWidth = img.offsetWidth;
        const imgHeight = img.offsetHeight;

        // 计算初始缩放比例，横向占满屏幕
        const initialScale = viewportWidth / imgWidth;

        // 设置初始缩放和位置
        scale = initialScale;
        contentContainer.style.transform = `translate(-50%, -50%) scale(${scale})`;

        // 缩放函数
        function applyTransform() {
            contentContainer.style.transform = `translate(-50%, -50%) translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
        }

        // 放大按钮事件 - 取消最大缩放限制
        zoomInBtn.addEventListener('click', () => {
            scale *= 1.2;
            applyTransform();
        });

        // 缩小按钮事件 - 取消最小缩放限制
        zoomOutBtn.addEventListener('click', () => {
            scale /= 1.2;
            applyTransform();
        });

        // 重置按钮事件
        resetBtn.addEventListener('click', () => {
            scale = initialScale;
            offsetX = 0;
            offsetY = 0;
            applyTransform();
        });

        // 鼠标事件 - 开始拖动
        contentContainer.addEventListener('mousedown', (e) => {
            if (e.button === 0) { // 左键
                isDragging = true;
                lastX = e.clientX;
                lastY = e.clientY;
                e.preventDefault(); // 阻止默认行为
            }
        });

        // 鼠标事件 - 拖动
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const deltaX = (e.clientX - lastX) / (scale * 2);
            const deltaY = (e.clientY - lastY) / (scale * 2);

            offsetX += deltaX;
            offsetY += deltaY;

            applyTransform();

            lastX = e.clientX;
            lastY = e.clientY;
        });

        // 鼠标事件 - 结束拖动
        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        // 鼠标离开窗口时结束拖动
        document.addEventListener('mouseleave', () => {
            isDragging = false;
        });

        // 鼠标滚轮缩放
        contentContainer.addEventListener('wheel', (e) => {
            e.preventDefault();

            const zoomFactor = e.deltaY > 0 ? 0.8 : 1.2;
            const newScale = scale * zoomFactor;

            // 限制缩放范围
            if (newScale >= initialScale * 0.5 && newScale <= 5) {
                scale = newScale;
                applyTransform();
            }
        });

        // 触摸事件支持
        let touchStartDistance = null;
        let touchStartMidpoint = null;

        // 触摸开始
        contentContainer.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                // 单指拖动
                isDragging = true;
                lastX = e.touches[0].clientX;
                lastY = e.touches[0].clientY;
            } else if (e.touches.length === 2) {
                // 双指缩放
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                touchStartDistance = Math.sqrt(dx * dx + dy * dy);

                // 计算中点
                touchStartMidpoint = {
                    x: (e.touches[0].clientX + e.touches[1].clientX) / 2,
                    y: (e.touches[0].clientY + e.touches[1].clientY) / 2
                };

                // 存储当前偏移量
                lastX = touchStartMidpoint.x;
                lastY = touchStartMidpoint.y;
            }
            e.preventDefault(); // 阻止默认行为
        });

        // 触摸移动
        document.addEventListener('touchmove', (e) => {
            if (e.touches.length === 1 && isDragging) {
                // 单指拖动
                const deltaX = (e.touches[0].clientX - lastX) / (scale * 2);
                const deltaY = (e.touches[0].clientY - lastY) / (scale * 2);

                offsetX += deltaX;
                offsetY += deltaY;

                applyTransform();

                lastX = e.touches[0].clientX;
                lastY = e.touches[0].clientY;
                e.preventDefault();
            } else if (e.touches.length === 2 && touchStartDistance !== null) {
                // 双指缩放
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                const touchEndDistance = Math.sqrt(dx * dx + dy * dy);

                // 计算缩放比例变化
                const scaleChange = touchEndDistance / touchStartDistance;
                const newScale = scale * scaleChange;

                // 限制缩放范围
                if (newScale >= initialScale * 0.5 && newScale <= 5) {
                    // 计算新的中点
                    const touchEndMidpoint = {
                        x: (e.touches[0].clientX + e.touches[1].clientX) / 2,
                        y: (e.touches[0].clientY + e.touches[1].clientY) / 2
                    };

                    // 计算位置偏移变化
                    const deltaX = (touchEndMidpoint.x - touchStartMidpoint.x) / (newScale * 2);
                    const deltaY = (touchEndMidpoint.y - touchStartMidpoint.y) / (newScale * 2);

                    // 更新缩放和位置
                    scale = newScale;
                    offsetX += deltaX;
                    offsetY += deltaY;

                    applyTransform();

                    // 更新起点
                    touchStartDistance = touchEndDistance;
                    touchStartMidpoint = touchEndMidpoint;
                    lastX = touchEndMidpoint.x;
                    lastY = touchEndMidpoint.y;
                }
                e.preventDefault();
            }
        });

        // 触摸结束
        document.addEventListener('touchend', () => {
            isDragging = false;
            if (e.touches.length === 0) {
                touchStartDistance = null;
                touchStartMidpoint = null;
            }
        });
    }

    // 格式化文件大小
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // 添加分页控件
    function addPaginationControls(total, currentPage, pageSize, dataType) {
        // 确保内容容器存在
        const contentContainer = document.getElementById('photos-content');
        if (!contentContainer) return;

        // 如果已存在分页控件，先移除
        removePaginationControls(dataType);

        // 计算总页数
        const totalPages = Math.ceil(total / pageSize);

        // 如果只有一页或没有数据，不需要分页控件
        if (totalPages <= 1 || total === 0) return;

        // 创建分页控件容器
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-controls';
        paginationContainer.id = dataType + '-pagination';
        paginationContainer.style.cssText = `
            margin-top: 20px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        `;

        // 分页信息
        const infoSpan = document.createElement('span');
        infoSpan.textContent = `共 ${total} 条记录`;
        infoSpan.style.cssText = `
            margin-right: 15px;
            color: #666;
        `;
        paginationContainer.appendChild(infoSpan);

        // 每页显示数量选择器
        const pageSizeSelect = document.createElement('select');
        const pageSizeOptions = [5, 10, 20, 'all'];
        pageSizeOptions.forEach(optionValue => {
            const option = document.createElement('option');
            option.value = optionValue;
            option.textContent = optionValue === 'all' ? '全部' : optionValue;
            if (optionValue === pageSize || (pageSize === 0 && optionValue === 'all')) {
                option.selected = true;
            }
            pageSizeSelect.appendChild(option);
        });
        pageSizeSelect.style.cssText = `
            margin-right: 15px;
            padding: 5px 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: white;
        `;
        pageSizeSelect.onchange = () => {
            loadProblemPhotos(1, pageSizeSelect.value);
        };
        paginationContainer.appendChild(pageSizeSelect);

        // 上一页按钮
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '上一页';
        prevBtn.disabled = currentPage <= 1;
        prevBtn.style.cssText = `
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            ${currentPage <= 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}
        `;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                loadProblemPhotos(currentPage - 1, pageSize);
            }
        };
        paginationContainer.appendChild(prevBtn);

        // 页码按钮
        // 简化版页码显示，只显示当前页、首页、末页和相邻的几页
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        // 调整起始页，确保显示足够的页码
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // 首页按钮
        if (startPage > 1) {
            const firstPageBtn = document.createElement('button');
            firstPageBtn.textContent = '1';
            firstPageBtn.style.cssText = `
                padding: 5px 10px;
                background-color: #f8f9fa;
                color: #3498db;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                cursor: pointer;
            `;
            firstPageBtn.onclick = () => loadProblemPhotos(1, pageSize);
            paginationContainer.appendChild(firstPageBtn);

            // 如果首页和起始页之间有间隔，显示省略号
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.margin = '0 5px';
                paginationContainer.appendChild(ellipsis);
            }
        }

        // 中间页码按钮
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.style.cssText = `
                padding: 5px 10px;
                ${i === currentPage ? 
                    'background-color: #3498db; color: white; border: none;' : 
                    'background-color: #f8f9fa; color: #3498db; border: 1px solid #dee2e6;'}
                border-radius: 4px;
                cursor: pointer;
            `;
            pageBtn.onclick = () => loadProblemPhotos(i, pageSize);
            paginationContainer.appendChild(pageBtn);
        }

        // 末页按钮
        if (endPage < totalPages) {
            // 如果末页和结束页之间有间隔，显示省略号
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.style.margin = '0 5px';
                paginationContainer.appendChild(ellipsis);
            }

            const lastPageBtn = document.createElement('button');
            lastPageBtn.textContent = totalPages;
            lastPageBtn.style.cssText = `
                padding: 5px 10px;
                background-color: #f8f9fa;
                color: #3498db;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                cursor: pointer;
            `;
            lastPageBtn.onclick = () => loadProblemPhotos(totalPages, pageSize);
            paginationContainer.appendChild(lastPageBtn);
        }

        // 下一页按钮
        const nextBtn = document.createElement('button');
        nextBtn.textContent = '下一页';
        nextBtn.disabled = currentPage >= totalPages;
        nextBtn.style.cssText = `
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            ${currentPage >= totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''}
        `;
        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                loadProblemPhotos(currentPage + 1, pageSize);
            }
        };
        paginationContainer.appendChild(nextBtn);

        // 添加到内容容器
        contentContainer.appendChild(paginationContainer);
    }

    // 移除分页控件
    function removePaginationControls(dataType) {
        const paginationContainer = document.getElementById(dataType + '-pagination');
        if (paginationContainer) {
            paginationContainer.parentNode.removeChild(paginationContainer);
        }
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
                                                return `<img src="${photoUrl}" alt="处理照片" class="timeline-photo-thumbnail" data-url="${photoUrl}" data-name="${photo.name || '处理照片'}">`;
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
                    if (document.querySelector('#processing-count .record-count-number')) {
                        document.querySelector('#processing-count .record-count-number').textContent = result.data.length;
                    }

                    // 添加点击查看大图的事件
                    document.querySelectorAll('.timeline-photo-thumbnail').forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            const url = this.getAttribute('data-url');
                            const name = this.getAttribute('data-name') || '处理照片';
                            previewPhoto(url, name);
                        });
                    });
                } else {
                    processingContent.innerHTML = '<p class="no-result">暂无处理记录</p>';
                    if (document.querySelector('#processing-count .record-count-number')) {
                        document.querySelector('#processing-count .record-count-number').textContent = '0';
                    }
                }
            })
            .catch(error => {
                document.getElementById('processing-content').innerHTML = `<p class="error">加载处理记录失败: ${error.message}</p>`;
            });
    }

    // 为所有下载按钮添加点击事件
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('download-btn') && e.target.getAttribute('data-url')) {
                e.preventDefault();
                // 直接下载
                const url = e.target.getAttribute('data-url');
                const name = e.target.getAttribute('data-name') || 'download';
                const a = document.createElement('a');
                a.href = url;
                a.download = name;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        });
    });
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

    .record-count {
        background: #3498db;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: normal;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .record-count:hover {
        background-color: #2980b9;
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

    /* 图纸表格样式 */
    .drawings-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .drawings-table th,
    .drawings-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .drawings-table th {
        background-color: #f8f9fa;
        font-weight: bold;
        color: #495057;
    }

    .drawings-table tr:hover {
        background-color: #f8f9fa;
    }

    .drawings-table a {
        color: #3498db;
        text-decoration: none;
        cursor: pointer;
    }

    .drawings-table a:hover {
        text-decoration: underline;
    }

    .download-btn {
        color: #28a745;
        font-weight: bold;
    }

    .download-btn:hover {
        color: #218838;
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
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
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

    /* 保持.record-item为flex布局 */
    .record-item {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
    }

    /* 时间和人员标签不换行，调整间距 */
    .record-item label {
        flex: 0 0 60px;
        font-weight: bold;
        color: #666;
        margin-right: 5px;
    }

    /* 调整内容区域的样式 */
    .record-item span {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* 说明内容从新行开始 */
    .record-item:nth-child(3) {
        display: block;
    }
    .record-item:nth-child(3) label {
        width: 100%;
        margin-bottom: 5px;
        display: block;
    }

    .record-item span,
    .record-item div {
        flex: 1;
        word-break: break-word;
    }

    .resolution-content {
        background: #f8f9fa;
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

        /* 保持info-item为flex布局，不换行 */
        .info-item {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            white-space: nowrap;
            margin-bottom: 12px;
        }

        .info-item label {
            flex: 0 0 auto;
            margin-bottom: 0;
            font-weight: bold;
            color: #666;
        }

        .info-item span {
            flex: 1;
            margin-left: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .photos-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }

        .photo-thumbnail {
            height: 80px;
        }

        /* 窄屏时调整卡片样式 */
        .problem-description-section,
        .processing-records-section,
        .collapse-block,
        .timeline-content,
        .processing-record {
            background: transparent !important;
            border: none !important;
            padding: 10px 0 !important;
            margin-bottom: 10px !important;
        }
        
        /* 为描述和说明内容保持淡淡的底色 */
        .problem-description-content,
        .resolution-content {
            background: #f5f7fa !important;
            padding: 15px !important;
            border-radius: 6px !important;
            margin: 10px 0 !important;
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