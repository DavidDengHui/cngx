<?php
// 检查是否被直接访问
if (basename($_SERVER['PHP_SELF']) == 'devices_detail.php') {
    header('Location: /devices.php');
    exit();
}

// 确保did参数存在
if (!isset($did)) {
    header('Location: /devices.php');
    exit();
}

// 设置默认页面标题
$page_title = '设备详情';

include 'header.php';

// 保存did到JS可以访问的变量
?>
<script>
    // 全局变量存储设备ID
    const deviceId = '<?php echo $did; ?>';
    
    // 页面加载完成后获取设备详情
    document.addEventListener('DOMContentLoaded', function() {
        loadDeviceDetail();
    });
    
    // 通过API获取设备详情
    function loadDeviceDetail() {
        fetch(`api.php?action=getDeviceDetail&did=${deviceId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data) {
                    displayDeviceDetail(result.data);
                } else if (!result.success && result.message) {
                    // 如果设备不存在，跳转到编辑页面
                    if (result.message === '设备不存在') {
                        window.location.href = `/devices.php?did=${deviceId}&mode=edit`;
                    } else {
                        document.querySelector('.device-detail').innerHTML = `<p class="error">获取设备详情失败: ${result.message}</p>`;
                    }
                }
            })
            .catch(error => {
                console.error('获取设备详情失败:', error);
                document.querySelector('.device-detail').innerHTML = `<p class="error">获取设备详情失败</p>`;
            });
    }
    
    // 全局设备数据变量
    let globalDeviceData = null;

    // 显示设备详情
    function displayDeviceDetail(device) {
        // 保存设备数据到全局变量
        globalDeviceData = device;
        
        // 设置页面标题
        document.title = device.device_name + ' - 个人设备信息管理平台';
        
        // 构建设备详情HTML
        const deviceDetailHTML = `
            <h2 style="text-align: center; margin-bottom: 30px;">${device.device_name}</h2>

            <div class="detail-layout">
                <!-- 左侧设备信息 -->
                <div class="detail-sidebar">
                    <div class="device-info">
                        <div class="info-item">
                            <label>设备类型：</label>
                            <span>${device.type_name}</span>
                        </div>
                        <div class="info-item">
                            <label>所属站场：</label>
                            <span>${device.station_name}</span>
                        </div>
                        <div class="info-item">
                            <label>包保部门：</label>
                            <span>${device.department_name}</span>
                        </div>
                        <div class="info-item">
                            <label>包保人：</label>
                            <span>
                                ${formatKeepers(device.keepers)}
                            </span>
                        </div>
                        <div class="info-item">
                            <label>备注：</label>
                            <span>${device.remark ? device.remark : '无'}</span>
                        </div>
                    </div>
                </div>
                <!-- 右侧内容 -->
                <div class="detail-content">
                    <!-- 图纸折叠块 -->
                    <div class="collapse-block">
                        <div class="collapse-header" onclick="toggleCollapse('drawings')">
                            <div class="header-title">
                                <span>设备图纸</span>
                                <span class="record-count"> (${device.drawing_count})</span>
                            </div>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div id="drawings" class="collapse-content" style="display: none;">
                            <div id="drawings-content">
                                <div class="loading">加载中...</div>
                            </div>
                        </div>
                    </div>

                    <!-- 巡视记录折叠块 -->
                    <div class="collapse-block">
                        <div class="collapse-header" onclick="toggleCollapse('inspection-records')">
                            <div class="header-title">
                                <span>巡视记录</span>
                                <span id="inspection-count" class="record-count">(0)</span>
                                <button class="add-btn" onclick="event.stopPropagation(); openAddRecordModal('inspection')">新增</button>
                            </div>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div id="inspection-records" class="collapse-content" style="display: none;">
                            <div id="inspection-content">
                                <div class="loading">加载中...</div>
                            </div>
                        </div>
                    </div>

                    <!-- 检修记录折叠块 -->
                    <div class="collapse-block">
                        <div class="collapse-header" onclick="toggleCollapse('maintenance-records')">
                            <div class="header-title">
                                <span>检修记录</span>
                                <span id="maintenance-count" class="record-count">(0)</span>
                                <button class="add-btn" onclick="event.stopPropagation(); openAddRecordModal('maintenance')">新增</button>
                            </div>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div id="maintenance-records" class="collapse-content" style="display: none;">
                            <div id="maintenance-content">
                                <div class="loading">加载中...</div>
                            </div>
                        </div>
                    </div>

                    <!-- 问题库记录折叠块 -->
                    <div class="collapse-block">
                        <div class="collapse-header" onclick="toggleCollapse('problem-records')">
                            <div class="header-title">
                                <span>问题记录</span>
                                <span id="problem-count" class="record-count">(0)</span>
                                <button class="add-btn" onclick="event.stopPropagation(); openAddProblemModal()">新增</button>
                            </div>
                            <span class="collapse-icon">▼</span>
                        </div>
                        <div id="problem-records" class="collapse-content" style="display: none;">
                            <div id="problem-content">
                                <div class="loading">加载中...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="edit-btn" onclick="window.location.href='/devices.php?did=${deviceId}&mode=edit'">修改</button>
            </div>
        `;
        
        // 更新设备详情内容
        document.querySelector('.device-detail').innerHTML = deviceDetailHTML;
        
        // 更新新增记录模态框中的部门信息
        const workDepartment = document.getElementById('work-department');
        const workDepartmentId = document.getElementById('work-department-id');
        if (workDepartment && workDepartmentId) {
            workDepartment.value = device.department_name;
            workDepartmentId.value = device.cid;
        }
    }
    
    // 格式化包保人显示
    function formatKeepers(keepers) {
        if (!keepers) {
            return '无';
        }
        
        const keeperArray = keepers.split('||');
        const formattedKeepers = [];
        
        keeperArray.forEach(keeper => {
            formattedKeepers.push('<span class="keeper-tag">' + keeper + '</span>');
        });
        
        return formattedKeepers.join('');
    }
</script>

<div class="device-detail">
    <div class="loading" style="text-align: center; padding: 50px 0;">加载设备详情中...</div>
</div>

<!-- 新增记录模态框 -->
<div id="add-record-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">新增记录</h3>
            <button type="button" class="close-btn" onclick="closeAddRecordModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="add-record-form">
                <input type="hidden" id="record-type">
                <input type="hidden" id="record-did" value="<?php echo $did; ?>">

                <div class="form-group">
                    <label for="workers">作业人员：</label>
                    <input type="text" id="workers" required>
                </div>

                <div class="form-group">
                    <label for="work-date">作业日期：</label>
                    <input type="date" id="work-date" required>
                </div>

                <div class="form-group">
                    <label for="work-department">作业部门：</label>
                    <div class="select-container">
                        <input type="text" id="work-department" readonly placeholder="请选择部门">
                        <input type="hidden" id="work-department-id">
                    </div>
                </div>

                <div class="form-group">
                    <label for="work-remark">备注：</label>
                    <textarea id="work-remark" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" onclick="closeAddRecordModal()">取消</button>
            <button type="button" class="confirm-btn" onclick="submitAddRecord()">确定</button>
        </div>
    </div>
</div>

<!-- 新增问题模态框 -->
<div id="add-problem-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>新增问题</h3>
            <button type="button" class="close-btn" onclick="closeAddProblemModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="add-problem-form">
                <input type="hidden" id="problem-did" value="<?php echo $did; ?>">
                <input type="hidden" id="problem-sid">

                <div class="form-group">
                    <label for="problem-description">问题描述：</label>
                    <textarea id="problem-description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="problem-photos">问题照片：</label>
                    <input type="text" id="problem-photos" placeholder="多个照片URL用||分隔">
                </div>

                <div class="form-group">
                    <label for="problem-creator">发现人：</label>
                    <input type="text" id="problem-creator" required>
                </div>

                <div class="form-group">
                    <label for="problem-date">发现时间：</label>
                    <input type="date" id="problem-date" required>
                </div>

                <div class="form-group">
                    <label for="problem-department">责任部门：</label>
                    <div class="select-container">
                        <input type="text" id="problem-department" readonly placeholder="请选择部门">
                        <input type="hidden" id="problem-department-id">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" onclick="closeAddProblemModal()">取消</button>
            <button type="button" class="confirm-btn" onclick="submitAddProblem()">确定</button>
        </div>
    </div>
</div>

<script>
    // 切换折叠块显示/隐藏
    function toggleCollapse(id) {
        const content = document.getElementById(id);
        const icon = content.previousElementSibling.querySelector('.collapse-icon');

        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.textContent = '▲';

            // 懒加载内容
            if (content.querySelector('.loading')) {
                if (id === 'drawings') {
                    // 使用支持分页的版本
                    loadDataWithPagination('drawings');
                } else if (id === 'inspection-records') {
                    // 使用支持分页的版本
                    loadDataWithPagination('inspection');
                } else if (id === 'maintenance-records') {
                    // 使用支持分页的版本
                    loadDataWithPagination('maintenance');
                } else if (id === 'problem-records') {
                    // 使用支持分页的版本
                    loadDataWithPagination('problems');
                }
            }
        } else {
            content.style.display = 'none';
            icon.textContent = '▼';
        }
    }

    // 原始的loadDrawings函数已被删除，替换为支持分页的版本

    // 格式化文件大小
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // 下载确认框
    function showDownloadConfirm(element) {
        // 获取设备名称（从页面标题中提取）
        const deviceName = document.title.split(' - ')[0];

        // 获取文件信息
        let url = element.getAttribute('data-url');
        const fileName = element.getAttribute('data-name');
        const fileType = element.getAttribute('data-type');
        const fileSize = element.getAttribute('data-size');

        // 确保URL包含域名（完整链接）
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            const baseUrl = window.location.origin;
            // 确保拼接正确（处理url开头的斜杠）
            if (url.startsWith('/')) {
                url = baseUrl + url;
            } else {
                url = baseUrl + '/' + url;
            }
        }

        // 创建模态框
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.style.display = 'none';

        // 创建模态框内容
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';

        // 创建头部
        const modalHeader = document.createElement('div');
        modalHeader.className = 'modal-header';

        const modalTitle = document.createElement('h3');
        modalTitle.textContent = '确认下载';

        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-btn';
        closeBtn.textContent = '×';
        closeBtn.onclick = function() {
            modal.remove();
        };

        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(closeBtn);

        // 创建主体
        const modalBody = document.createElement('div');
        modalBody.className = 'modal-body';

        const infoList = document.createElement('div');
        infoList.style.marginBottom = '20px';

        infoList.innerHTML = `
            <div style="margin-bottom: 10px;"><strong>设备名称:</strong> ${deviceName}</div>
            <div style="margin-bottom: 10px;"><strong>图纸名称:</strong> ${fileName}</div>
            <div style="margin-bottom: 10px;"><strong>文件类型:</strong> ${fileType}</div>
            <div style="margin-bottom: 10px;"><strong>文件大小:</strong> ${fileSize}</div>
        `;

        modalBody.appendChild(infoList);

        // 创建按钮区域
        const modalFooter = document.createElement('div');
        modalFooter.className = 'modal-footer';
        modalFooter.style.display = 'flex';
        modalFooter.style.justifyContent = 'flex-end';
        modalFooter.style.gap = '10px';
        modalFooter.style.padding = '15px';
        modalFooter.style.borderTop = '1px solid #ddd';

        // 复制链接按钮
        const copyLinkBtn = document.createElement('button');
        copyLinkBtn.className = 'edit-btn';
        copyLinkBtn.style.backgroundColor = '#95a5a6';
        copyLinkBtn.style.padding = '8px 20px';
        copyLinkBtn.style.fontSize = '14px';
        copyLinkBtn.textContent = '复制下载链接';
        copyLinkBtn.style.cursor = 'pointer'; // 确保显示为可点击
        copyLinkBtn.style.userSelect = 'none'; // 防止文本选中

        // 增强移动设备上的点击体验
        copyLinkBtn.style.touchAction = 'manipulation';
        copyLinkBtn.style.webkitTapHighlightColor = 'rgba(0, 0, 0, 0.1)';

        // 直接调用全局复制函数
        copyLinkBtn.onclick = function() {
            copyDownloadLink(url, copyLinkBtn);
        };

        // 取消按钮
        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'delete-btn';
        cancelBtn.style.backgroundColor = '#95a5a6';
        cancelBtn.style.padding = '8px 20px';
        cancelBtn.style.fontSize = '14px';
        cancelBtn.textContent = '取消';
        cancelBtn.onclick = function() {
            modal.remove();
        };

        // 确认按钮
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'download-btn';
        confirmBtn.style.padding = '8px 20px';
        confirmBtn.style.fontSize = '14px';
        confirmBtn.textContent = '确认';
        confirmBtn.onclick = function() {
            // 创建隐藏的a标签进行下载
            const a = document.createElement('a');
            a.href = url;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            modal.remove();
        };

        modalFooter.appendChild(copyLinkBtn);
        modalFooter.appendChild(cancelBtn);
        modalFooter.appendChild(confirmBtn);

        // 组装模态框
        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);
        modalContent.appendChild(modalFooter);
        modal.appendChild(modalContent);

        // 添加到文档
        document.body.appendChild(modal);

        // 显示模态框
        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';

        // 点击模态框背景关闭
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
                // 恢复背景页面滚动
                document.body.style.overflow = '';
            }
        };

        // 添加复制链接和确认下载的点击事件
        copyLinkBtn.onclick = function() {
            copyDownloadLink(url, copyLinkBtn);
        };

        cancelBtn.onclick = function() {
            modal.remove();
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        };

        confirmBtn.onclick = function() {
            // 创建一个隐藏的a标签用于下载
            const a = document.createElement('a');
            a.href = url;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            modal.remove();
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        };
    }

    // 为所有下载按钮添加点击事件
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('download-btn') && e.target.getAttribute('data-url')) {
                e.preventDefault();
                showDownloadConfirm(e.target);
            }
        });
    });

    // 预览图纸
    function previewDrawing(url, title) {


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

        if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(fileExtension)) {
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
                    display: block;
                    margin-top: 10px;
                    color: #3498db;
                    text-decoration: underline;
                    cursor: pointer;
                `;
                placeholder.appendChild(downloadBtn);
            };
        } else {
            // 非图片文件提供下载链接
            const downloadLink = document.createElement('a');
            downloadLink.href = url;
            downloadLink.download = title;
            downloadLink.textContent = '点击下载查看: ' + title;
            downloadLink.style.cssText = `
                color: white;
                font-size: 18px;
                padding: 20px;
                background-color: #3498db;
                border-radius: 4px;
                text-decoration: none;
                display: inline-block;
                transition: background-color 0.3s;
            `;
            downloadLink.onmouseover = () => {
                downloadLink.style.backgroundColor = '#2980b9';
            };
            downloadLink.onmouseout = () => {
                downloadLink.style.backgroundColor = '#3498db';
            };
            contentContainer.appendChild(downloadLink);

            // 居中显示
            contentContainer.style.left = '50%';
            contentContainer.style.top = '50%';
            contentContainer.style.transform = 'translate(-50%, -50%)';
        }

        // 添加到视口
        viewport.appendChild(contentContainer);

        // 组装模态框
        modal.appendChild(closeBtn);
        modal.appendChild(titleElement);
        modal.appendChild(viewport);
        modal.appendChild(controlsContainer);

        // 添加到文档

        document.body.appendChild(modal);


        // 点击空白处关闭
        modal.addEventListener('click', (e) => {
            // 只有当点击的是模态框本身（而不是其内部元素）时才关闭
            if (e.target === modal) {
                document.body.removeChild(modal);
                // 恢复背景页面滚动
                document.body.style.overflow = '';
            }
        });

        // 为标题元素、控制按钮和视口添加事件监听器，防止事件冒泡
        titleElement.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        controlsContainer.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // 为关闭按钮添加阻止冒泡，确保只有点击关闭按钮本身才关闭弹窗
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // 为视口添加阻止冒泡，确保点击图片区域不会关闭弹窗
        viewport.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // ESC键关闭
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(modal);
                document.removeEventListener('keydown', escListener);
                // 恢复背景页面滚动
                document.body.style.overflow = '';
            }
        });


    }

    // 初始化缩放和平移功能
    function initZoomAndPan(img, contentContainer, viewport, zoomInBtn, resetBtn, zoomOutBtn) {
        // 确保图片已经完全加载
        if (!img.complete || img.naturalWidth === 0) {

            img.onload = () => initZoomAndPan(img, contentContainer, viewport, zoomInBtn, resetBtn, zoomOutBtn);
            return;
        }

        // 确保视口元素已经添加到文档中再获取其尺寸
        setTimeout(() => {
            // 获取视口和图片的尺寸
            const viewportRect = viewport.getBoundingClientRect();



            // 确保内容容器的基本样式正确
            contentContainer.style.width = 'auto';
            contentContainer.style.height = 'auto';
            contentContainer.style.position = 'absolute';
            contentContainer.style.top = '50%';
            contentContainer.style.left = '50%';

            // 处理视口高度为0的情况
            let effectiveViewportHeight = viewportRect.height;
            if (effectiveViewportHeight <= 0) {
                // 使用屏幕高度减去标题和控制按钮的高度作为默认高度
                effectiveViewportHeight = window.innerHeight - 95; // 45px顶部 + 50px底部

            }

            // 计算初始缩放比例，使图片尽可能大但完全在视口内
            let scale = Math.min(
                viewportRect.width / img.width,
                effectiveViewportHeight / img.height
            );

            // 如果图片很小，设置一个合理的最小缩放比例
            // 例如，如果图片宽度小于视口的50%，我们可以放大一些
            const minScaleForSmallImages = 1; // 最小放大到1倍
            if (scale < minScaleForSmallImages && img.width < viewportRect.width * 0.5) {
                scale = Math.min(minScaleForSmallImages, scale * 2); // 尝试放大一些，但不超过视口限制
            }

            // 防止缩放比例过小
            if (isNaN(scale) || scale <= 0) {
                scale = 1;
            }

            // 存储当前位置和缩放比例
            let offsetX = 0;
            let offsetY = 0;

            // 是否正在拖动
            let isDragging = false;
            let startX, startY, startOffsetX, startOffsetY;

            // 更新内容容器的变换
            function updateTransform() {
                // 将容器定位到视口中心，然后应用偏移和缩放
                contentContainer.style.transform = `translate(-50%, -50%) translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
            }



            // 应用初始变换
            updateTransform();

            // 放大按钮事件
            zoomInBtn.onclick = () => {
                scale *= 1.2;
                updateTransform();
            };

            // 缩小按钮事件
            zoomOutBtn.onclick = () => {
                scale /= 1.2;
                // 不限制最小缩放比例，允许用户强制缩小图片
                updateTransform();
            };

            // 重置按钮事件
            resetBtn.onclick = () => {
                // 直接设置偏移量为0，确保图片在水平和垂直方向上居中
                offsetX = 0;
                offsetY = 0;

                // 计算重置后的缩放比例，使图片尽可能大但完全在视口内
                const currentViewportRect = viewport.getBoundingClientRect();
                let currentEffectiveViewportHeight = currentViewportRect.height;

                if (currentEffectiveViewportHeight <= 0) {
                    currentEffectiveViewportHeight = window.innerHeight - 95; // 45px顶部 + 50px底部
                }

                scale = Math.min(
                    currentViewportRect.width / img.width,
                    currentEffectiveViewportHeight / img.height
                );

                // 如果图片很小，保持最小缩放比例
                if (scale < minScaleForSmallImages && img.width < currentViewportRect.width * 0.5) {
                    scale = Math.min(minScaleForSmallImages, scale * 2);
                }

                updateTransform();
            };

            // 鼠标滚轮缩放事件
            viewport.addEventListener('wheel', (e) => {
                e.preventDefault();

                // 获取鼠标在视口中的位置
                const mouseX = e.clientX - viewportRect.left;
                const mouseY = e.clientY - viewportRect.top;

                // 计算鼠标相对于视口中心的位置
                const centerX = viewportRect.width / 2;
                const centerY = effectiveViewportHeight / 2;
                const relX = mouseX - centerX;
                const relY = mouseY - centerY;

                // 根据滚轮方向调整缩放比例
                const delta = e.deltaY > 0 ? 0.8 : 1.2;
                const newScale = scale * delta;

                // 不限制缩放范围，允许用户自由缩放图片
                offsetX += relX * (1 - delta);
                offsetY += relY * (1 - delta);
                scale = newScale;
                updateTransform();
            });

            // 鼠标按下事件（开始拖动）
            viewport.addEventListener('mousedown', (e) => {
                // 只有在图片上点击才触发拖动
                if (e.target === img) {
                    isDragging = true;
                    startX = e.clientX;
                    startY = e.clientY;
                    startOffsetX = offsetX;
                    startOffsetY = offsetY;
                    viewport.style.cursor = 'grabbing';

                    // 添加到文档的鼠标移动和释放事件，确保在鼠标移出视口时也能响应
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                }
            });

            // 鼠标移动事件（拖动中）
            function onMouseMove(e) {
                if (isDragging) {
                    offsetX = startOffsetX + (e.clientX - startX);
                    offsetY = startOffsetY + (e.clientY - startY);
                    updateTransform();
                }
            }

            // 鼠标释放事件（结束拖动）
            function onMouseUp() {
                isDragging = false;
                viewport.style.cursor = 'grab';

                // 移除事件监听器
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }

            // 触摸开始事件（对应鼠标按下）
            let touchStartX, touchStartY;
            let initialDistance = null; // 用于存储初始手指距离
            let initialScale = null; // 用于存储初始缩放比例

            viewport.addEventListener('touchstart', (e) => {
                // 只有在图片上点击才触发拖动
                if (e.target === img) {
                    e.preventDefault(); // 阻止页面滚动
                    isDragging = true;
                    const touch = e.touches[0];
                    startX = touch.clientX;
                    startY = touch.clientY;
                    startOffsetX = offsetX;
                    startOffsetY = offsetY;
                    touchStartX = touch.clientX;
                    touchStartY = touch.clientY;

                    // 检查是否有两个手指（捏合缩放）
                    if (e.touches.length === 2) {
                        const touch1 = e.touches[0];
                        const touch2 = e.touches[1];
                        // 计算两个手指之间的初始距离
                        initialDistance = Math.sqrt(
                            Math.pow(touch2.clientX - touch1.clientX, 2) +
                            Math.pow(touch2.clientY - touch1.clientY, 2)
                        );
                        initialScale = scale; // 保存当前缩放比例
                    }
                }
            });

            // 触摸移动事件（对应鼠标移动）
            viewport.addEventListener('touchmove', (e) => {
                if (isDragging) {
                    e.preventDefault(); // 阻止页面滚动

                    // 处理捏合缩放
                    if (e.touches.length === 2 && initialDistance) {
                        const touch1 = e.touches[0];
                        const touch2 = e.touches[1];

                        // 计算当前两个手指之间的距离
                        const currentDistance = Math.sqrt(
                            Math.pow(touch2.clientX - touch1.clientX, 2) +
                            Math.pow(touch2.clientY - touch1.clientY, 2)
                        );

                        // 计算缩放比例变化
                        const scaleFactor = currentDistance / initialDistance;
                        let newScale = initialScale * scaleFactor;

                        // 不限制缩放范围，允许用户自由缩放图片

                        if (newScale !== scale) {
                            // 计算两个手指的中点（缩放中心）
                            const midX = (touch1.clientX + touch2.clientX) / 2;
                            const midY = (touch1.clientY + touch2.clientY) / 2;

                            // 计算中点相对于视口中心的位置
                            const centerX = viewportRect.width / 2;
                            const centerY = effectiveViewportHeight / 2;
                            const relX = midX - centerX;
                            const relY = midY - centerY;

                            // 调整位置，使手指中点保持不变
                            offsetX += relX * (1 - newScale / scale);
                            offsetY += relY * (1 - newScale / scale);
                            scale = newScale;
                            updateTransform();
                        }
                    }
                    // 处理单指拖动
                    else if (e.touches.length === 1) {
                        const touch = e.touches[0];
                        offsetX = startOffsetX + (touch.clientX - startX);
                        offsetY = startOffsetY + (touch.clientY - startY);
                        updateTransform();
                    }
                }
            });

            // 触摸相关变量
            let lastTapTime = 0; // 上次点击时间，用于检测双击
            const DOUBLE_TAP_TIME_THRESHOLD = 300; // 双击时间阈值（毫秒）

            // 触摸结束事件（对应鼠标释放）
            viewport.addEventListener('touchend', (e) => {
                if (isDragging) {
                    isDragging = false;
                    // 重置捏合缩放相关变量
                    initialDistance = null;
                    initialScale = null;

                    // 检查是否是点击操作而不是拖动
                    const touch = e.changedTouches[0];
                    const dx = Math.abs(touch.clientX - touchStartX);
                    const dy = Math.abs(touch.clientY - touchStartY);

                    // 如果移动距离很小，视为点击
                    if (dx < 10 && dy < 10) {
                        const currentTime = new Date().getTime();
                        const tapTimeInterval = currentTime - lastTapTime;

                        // 检查是否是双击（两次点击时间间隔在阈值内）
                        if (tapTimeInterval < DOUBLE_TAP_TIME_THRESHOLD && tapTimeInterval > 0) {
                            e.preventDefault();

                            if (!isDoubleClickMode) {
                                // 第一次双击：缩放到图片的100%比例
                                scale = 1;
                                isDoubleClickMode = true;
                            } else {
                                // 第二次双击：复原到最合适比例
                                offsetX = 0;
                                offsetY = 0;
                                scale = originalOptimalScale;
                                isDoubleClickMode = false;
                            }

                            updateTransform();
                            // 重置双击检测
                            lastTapTime = 0;
                        } else {
                            // 不是双击，记录这次点击时间
                            lastTapTime = currentTime;
                        }
                    } else {
                        // 拖动操作，重置双击检测
                        lastTapTime = 0;
                    }
                }
            });

            // 存储原始最合适的缩放比例，用于双击复原
            const originalOptimalScale = scale;
            let isDoubleClickMode = false; // 标记是否处于双击模式（100%比例）

            // 添加双击事件处理
            viewport.addEventListener('dblclick', (e) => {
                if (e.target === img) {
                    e.preventDefault();

                    if (!isDoubleClickMode) {
                        // 第一次双击：缩放到图片的100%比例
                        scale = 1;
                        isDoubleClickMode = true;
                    } else {
                        // 第二次双击：复原到最合适比例（相当于重置按钮的操作）
                        offsetX = 0;
                        offsetY = 0;
                        scale = originalOptimalScale;
                        isDoubleClickMode = false;
                    }

                    updateTransform();
                }
            });
        }, 10); // 短暂延迟确保元素已渲染到DOM中
    }

    // 加载记录数量（页面加载时自动执行）
    function loadRecordCounts() {
        // 加载巡视记录数量
        fetch(`api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=1`)
            .then(response => response.json())
            .then(data => {
                const countElement = document.getElementById('inspection-count');
                // 检查返回格式，如果有total字段则使用，否则回退到data.length
                const count = data.total !== undefined ? data.total : (Array.isArray(data) ? data.length : 0);
                countElement.textContent = `(${count})`;
            })
            .catch(error => {
                const countElement = document.getElementById('inspection-count');
                countElement.textContent = `(0)`;
            });

        // 加载检修记录数量
        fetch(`api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=2`)
            .then(response => response.json())
            .then(data => {
                const countElement = document.getElementById('maintenance-count');
                // 检查返回格式，如果有total字段则使用，否则回退到data.length
                const count = data.total !== undefined ? data.total : (Array.isArray(data) ? data.length : 0);
                countElement.textContent = `(${count})`;
            })
            .catch(error => {
                const countElement = document.getElementById('maintenance-count');
                countElement.textContent = `(0)`;
            });

        // 加载问题记录数量
        fetch(`api.php?action=getProblems&did=<?php echo $did; ?>`)
            .then(response => response.json())
            .then(data => {
                const countElement = document.getElementById('problem-count');
                // 检查返回格式，如果有total字段则使用，否则回退到data.length
                const count = data.total !== undefined ? data.total : (Array.isArray(data) ? data.length : 0);
                countElement.textContent = `(${count})`;
            })
            .catch(error => {
                const countElement = document.getElementById('problem-count');
                countElement.textContent = `(0)`;
            });
    }

    // 在页面加载完成后自动加载记录数量
    document.addEventListener('DOMContentLoaded', loadRecordCounts);

    // 原始的loadInspectionRecords函数已被删除，替换为支持分页的版本

    // 原始的loadMaintenanceRecords函数已被删除，替换为支持分页的版本

    // 显示记录详情模态框
    function showRecordDetailModal(record, type) {
        // 创建模态框容器
        const modal = document.createElement('div');
        modal.className = 'detail-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        // 模态框内容
        const modalContent = document.createElement('div');
        modalContent.style.cssText = `
            background: white;
            border-radius: 8px;
            padding: 25px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        `;

        // 关闭按钮
        const closeBtn = document.createElement('button');
        closeBtn.textContent = '×';
        closeBtn.style.cssText = `
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        `;
        closeBtn.onclick = () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        };

        // 模态框标题
        const title = document.createElement('h3');
        title.textContent = type === 'inspection' ? '巡视记录详情' : '检修记录详情';
        title.style.cssText = `
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
        `;

        // 详情内容
        const detailContent = document.createElement('div');
        detailContent.className = 'record-detail';
        detailContent.style.cssText = `
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        `;

        // 添加记录详情字段
        // 设备名称从页面标题获取
        const deviceName = document.title.split(' - ')[0];
        
        const fields = [
            { label: '设备名称:', value: deviceName },
            { label: '作业时间:', value: type === 'inspection' ? (record.inspection_time || record.work_date) : (record.maintenance_time || record.work_date) },
            { label: '作业人员:', value: type === 'inspection' ? (record.inspector || record.workers) : (record.maintainer || record.workers) },
            { label: '作业说明:', value: record.content || '无' }
        ];
        
        // 处理作业人员姓名，无论是单人还是多人都添加keeper-tag样式
        const workerField = fields.find(field => field.label === '作业人员:');
        if (workerField && workerField.value) {
            // 无论是否包含||分隔符，都进行格式化处理
            const workerArray = workerField.value.split('||');
            const formattedArray = [];
            workerArray.forEach(worker => {
                formattedArray.push('<span class="keeper-tag">' + worker + '</span>');
            });
            workerField.value = formattedArray.join('');
        }

        fields.forEach(field => {
            const fieldDiv = document.createElement('div');
            fieldDiv.style.cssText = 'display: flex;';
            
            const label = document.createElement('span');
            label.textContent = field.label;
            label.style.cssText = 'font-weight: bold; width: 100px; flex-shrink: 0;';
            
            const value = document.createElement('span');
            // 对于包含HTML的字段（如作业人员）使用innerHTML
            if (field.label === '作业人员:' && field.value.includes('<span')) {
                value.innerHTML = field.value;
            } else {
                value.textContent = field.value;
            }
            value.style.cssText = 'flex: 1; word-break: break-word;';
            
            fieldDiv.appendChild(label);
            fieldDiv.appendChild(value);
            detailContent.appendChild(fieldDiv);
        });

        // 按钮容器
        const buttonsContainer = document.createElement('div');
        buttonsContainer.style.cssText = `
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        `;

        // 删除按钮
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = '删除';
        deleteBtn.style.cssText = `
            padding: 8px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        `;
        deleteBtn.onclick = () => {
            // 不再移除记录详情模态框，直接显示删除确认对话框
            showDeleteConfirmDialog(record.wid, type);
        };

        // 关闭按钮
        const closeModalBtn = document.createElement('button');
        closeModalBtn.textContent = '关闭';
        closeModalBtn.style.cssText = `
            padding: 8px 20px;
            background-color: #95a5a6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        `;
        closeModalBtn.onclick = () => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        };

        // 组装模态框
        buttonsContainer.appendChild(closeModalBtn);
        buttonsContainer.appendChild(deleteBtn);
        modalContent.appendChild(closeBtn);
        modalContent.appendChild(title);
        modalContent.appendChild(detailContent);
        modalContent.appendChild(buttonsContainer);
        modal.appendChild(modalContent);

        // 阻止背景滚动
        document.body.style.overflow = 'hidden';

        // 添加到文档
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

    // 显示删除确认对话框
    function showDeleteConfirmDialog(wid, type) {
        // 创建确认对话框
        const confirmModal = document.createElement('div');
        confirmModal.className = 'confirm-modal';
        confirmModal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        // 对话框内容
        const confirmContent = document.createElement('div');
        confirmContent.style.cssText = `
            background: white;
            border-radius: 8px;
            padding: 25px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        `;

        // 标题
        const confirmTitle = document.createElement('h4');
        confirmTitle.textContent = '确认删除';
        confirmTitle.style.cssText = 'margin-top: 0; margin-bottom: 15px; color: #333;';

        // 消息
        const confirmMessage = document.createElement('p');
        confirmMessage.textContent = '确定要删除这条记录吗？此操作不可撤销。';
        confirmMessage.style.cssText = 'margin-bottom: 20px; color: #666;';

        // 按钮容器
        const confirmButtons = document.createElement('div');
        confirmButtons.style.cssText = `
            display: flex;
            justify-content: center;
            gap: 15px;
        `;

        // 取消按钮
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = '取消';
        cancelBtn.style.cssText = `
            padding: 8px 20px;
            background-color: #95a5a6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        `;
        cancelBtn.onclick = () => {
            document.body.removeChild(confirmModal);
            // 取消删除时不移除body的overflow样式，保持记录详情模态框的显示
        };

        // 确认删除按钮
        const confirmDeleteBtn = document.createElement('button');
        confirmDeleteBtn.textContent = '确认删除';
        confirmDeleteBtn.style.cssText = `
            padding: 8px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        `;
        confirmDeleteBtn.onclick = () => {
            // 执行删除操作
            deleteRecord(wid, type);
            document.body.removeChild(confirmModal);
            // 同时移除记录详情模态框
            const detailModal = document.querySelector('.detail-modal');
            if (detailModal) {
                document.body.removeChild(detailModal);
            }
            document.body.style.overflow = '';
        };

        // 组装对话框
        confirmButtons.appendChild(cancelBtn);
        confirmButtons.appendChild(confirmDeleteBtn);
        confirmContent.appendChild(confirmTitle);
        confirmContent.appendChild(confirmMessage);
        confirmContent.appendChild(confirmButtons);
        confirmModal.appendChild(confirmContent);

        // 阻止背景滚动
        document.body.style.overflow = 'hidden';

        // 添加到文档
        document.body.appendChild(confirmModal);

        // 点击对话框外部关闭
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                document.body.removeChild(confirmModal);
                // 点击外部取消时不移除body的overflow样式，保持记录详情模态框的显示
            }
        });

        // ESC键关闭
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(confirmModal);
                document.removeEventListener('keydown', escListener);
                // ESC键取消时不移除body的overflow样式，保持记录详情模态框的显示
            }
        });
    }

    // 原始的loadProblemRecords函数已被删除，替换为支持分页的版本

    // 打开新增记录模态框
    function openAddRecordModal(type) {
        const modal = document.getElementById('add-record-modal');
        const title = document.getElementById('modal-title');
        const recordType = document.getElementById('record-type');

        if (type === 'inspection') {
            title.textContent = '新增巡视记录';
            recordType.value = '1';
        } else if (type === 'maintenance') {
            title.textContent = '新增检修记录';
            recordType.value = '2';
        }

        // 设置当前日期为默认值
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('work-date').value = today;

        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';
    }

    // 关闭新增记录模态框
    function closeAddRecordModal() {
        document.getElementById('add-record-modal').style.display = 'none';
        // 恢复背景页面滚动
        document.body.style.overflow = '';
    }

    // 提交新增记录
    function submitAddRecord() {
        const recordType = document.getElementById('record-type').value;
        const did = document.getElementById('record-did').value;
        const workers = document.getElementById('workers').value;
        const workDate = document.getElementById('work-date').value;
        const departmentId = document.getElementById('work-department-id').value;
        const remark = document.getElementById('work-remark').value;

        fetch('api.php?action=addWorkLog', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: recordType,
                    did: did,
                    workers: workers,
                    workDate: workDate,
                    departmentId: departmentId,
                    remark: remark
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddRecordModal();

                    // 重新加载对应记录
                    if (recordType === '1') {
                        document.getElementById('inspection-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadDataWithPagination('inspection');
                    } else if (recordType === '2') {
                        document.getElementById('maintenance-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadDataWithPagination('maintenance');
                    }

                    alert('添加成功');
                } else {
                    alert('添加失败: ' + data.message);
                }
            })
            .catch(error => {
                alert('添加失败: ' + error.message);
            });
    }

    // 打开新增问题模态框
    function openAddProblemModal() {
        const modal = document.getElementById('add-problem-modal');

        // 设置当前日期为默认值
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('problem-date').value = today;

        // 如果有全局设备数据，设置部门信息
        if (globalDeviceData) {
            document.getElementById('problem-sid').value = globalDeviceData.sid || '';
            document.getElementById('problem-department').value = globalDeviceData.department_name || '';
            document.getElementById('problem-department-id').value = globalDeviceData.cid || '';
        }

        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';
    }

    // 关闭新增问题模态框
    function closeAddProblemModal() {
        document.getElementById('add-problem-modal').style.display = 'none';
        // 恢复背景页面滚动
        document.body.style.overflow = '';
    }

    // 提交新增问题
    function submitAddProblem() {
        const did = document.getElementById('problem-did').value;
        const sid = document.getElementById('problem-sid').value;
        const description = document.getElementById('problem-description').value;
        const photos = document.getElementById('problem-photos').value;
        const creator = document.getElementById('problem-creator').value;
        const createTime = document.getElementById('problem-date').value;
        const departmentId = document.getElementById('problem-department-id').value;

        fetch('api.php?action=addProblem', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    did: did,
                    sid: sid,
                    description: description,
                    photos: photos,
                    creator: creator,
                    createTime: createTime,
                    departmentId: departmentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeAddProblemModal();

                    // 重新加载问题记录
                    document.getElementById('problem-content').innerHTML = '<div class="loading">加载中...</div>';
                    loadDataWithPagination('problems');

                    alert('添加成功');
                } else {
                    alert('添加失败: ' + data.message);
                }
            })
            .catch(error => {
                alert('添加失败: ' + error.message);
            });
    }

    // 删除记录 - 将记录的status设置为0
    function deleteRecord(wid, type) {
        // 根据记录类型选择正确的API端点
        let apiAction = '';
        
        switch(type) {
            case 'inspection':
                apiAction = 'deleteInspection';
                break;
            case 'maintenance':
                apiAction = 'deleteMaintenance';
                break;
            case 'problem':
                apiAction = 'deleteProblemRecord';
                break;
            default:
                showNotification('不支持的记录类型', 'error');
                return;
        }
        
        fetch(`api.php?action=${apiAction}&id=${wid}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 显示删除成功提示
                    showNotification('已删除1条记录！', 'success');
                    
                    // 重新加载对应记录
                    if (type === 'inspection') {
                        document.getElementById('inspection-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadInspectionRecords(1);
                    } else if (type === 'maintenance') {
                        document.getElementById('maintenance-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadMaintenanceRecords(1);
                    } else if (type === 'problem') {
                        document.getElementById('problem-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadProblemRecords(1);
                    }
                } else {
                    showNotification('删除失败: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('删除失败: ' + error.message, 'error');
            });
    }
    
    // 显示通知提示
    function showNotification(message, type = 'info') {
        // 移除已存在的通知
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            document.body.removeChild(existingNotification);
        }
        
        // 创建通知元素
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        // 设置样式 - 从右侧滑入且低于header导航栏
        // 使用内联样式确保背景色和文本可见
        notification.style.position = 'fixed';
        notification.style.top = '80px';  // 确保低于header导航栏的高度
        notification.style.right = '-400px';  // 初始位置在右侧屏幕外
        notification.style.backgroundColor = '#e74c3c';
        notification.style.color = 'white';
        notification.style.padding = '12px 24px';
        notification.style.borderRadius = '4px';
        notification.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.2)';
        notification.style.zIndex = '10000';
        notification.style.opacity = '0';
        notification.style.fontSize = '14px';
        notification.style.fontWeight = 'bold';
        notification.style.minWidth = '200px';
        notification.style.textAlign = 'center';
        notification.style.transition = 'opacity 0.3s ease, right 0.5s ease';
        
        // 设置消息内容
        notification.textContent = message;
        
        // 添加到页面
        document.body.appendChild(notification);
        
        // 显示通知 - 从右侧滑入
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.right = '20px';  // 最终位置
        }, 10);
        
        // 3秒后隐藏通知 - 滑回右侧
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.right = '-400px';  // 回到初始位置
            
            // 动画结束后移除元素
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 500);
        }, 3000);
    }
</script>

<style>
    .device-detail {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .device-info {
        margin-bottom: 30px;
    }

    .info-item {
        margin-bottom: 15px;
        display: flex;
        align-items: flex-start;
    }

    .info-item label {
        font-weight: bold;
        color: #555;
        width: 120px;
        flex-shrink: 0;
    }

    .info-item span {
        flex: 1;
    }

    /* 作业时间单元格 - 确保在一行显示 */
    .records-table td:nth-child(2) {
        white-space: nowrap;
    }
    
    /* 作业人员单元格 - 允许换行并设置间距 */
    .records-table td:nth-child(3) {
        line-height: 1.6;
    }
    
    .keeper-tag {
        background-color: rgba(52, 152, 219, 0.2);
        /* 半透明蓝色 */
        color: #2c3e50;
        padding: 4px 10px;
        margin-right: 8px;
        margin-bottom: 4px;
        border-radius: 4px;
        display: inline-block;
    }

    .collapse-block {
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
    }

    .collapse-header {
        background-color: #f5f5f5;
        padding: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.3s;
    }

    .collapse-header:hover {
        background-color: #e9e9e9;
    }

    .collapse-header span:first-child {
        font-weight: bold;
    }

    .collapse-icon {
        transition: transform 0.3s;
    }

    .add-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .add-btn:hover {
        background-color: #2980b9;
    }

    .collapse-content {
        padding: 20px;
    }

    .action-buttons {
        text-align: center;
        margin-top: 30px;
    }

    .edit-btn {
        background-color: #f39c12;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .edit-btn:hover {
        background-color: #e67e22;
    }

    .drawings-table,
    .records-table,
    .problems-table {
        width: 100%;
        border-collapse: collapse;
    }

    /* 默认表格样式 */
    .drawings-table th,
    .drawings-table td,
    .records-table th,
    .records-table td,
    .problems-table th,
    .problems-table td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }
    
    /* 所有表头居中显示 */
    .drawings-table th,
    .records-table th,
    .problems-table th {
        text-align: center;
    }
    
    /* 所有单元格默认居中显示 */
    .drawings-table td,
    .records-table td,
    .problems-table td {
        text-align: center;
    }
    
    /* 图纸名称内容居左显示 */
    .drawings-table td:first-child,
    .drawings-table td:nth-child(2) {
        text-align: left;
    }
    
    /* 问题描述内容居左显示 */
    .problems-table td:nth-child(2) {
        text-align: left;
    }

    .drawings-table th,
    .records-table th,
    .problems-table th {
        background-color: #f5f5f5;
        font-weight: bold;
    }

    .drawings-table tr:hover,
    .records-table tr:hover,
    .problems-table tr:hover {
        background-color: #f9f9f9;
    }

    .drawings-table a,
    .problems-table a {
        color: #3498db;
        text-decoration: none;
    }

    .drawings-table a:hover,
    .problems-table a:hover,
    .download-btn:hover {
        text-decoration: underline;
    }

    .drawings-table .download-btn,
    .problems-table .download-btn,
    .download-btn {
        background-color: #27ae60;
        color: white !important;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        display: inline-block;
        text-decoration: none !important;
    }

    .download-btn:hover {
        background-color: #229954;
        color: white;
    }

    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .delete-btn:hover {
        background-color: #c0392b;
    }

    .status-red {
        color: #e74c3c;
        font-weight: bold;
    }

    .status-green {
        color: #27ae60;
        font-weight: bold;
    }

    .no-result {
        text-align: center;
        color: #999;
        padding: 20px 0;
    }

    .error {
        text-align: center;
        color: #e74c3c;
        padding: 20px 0;
    }

    .loading {
        text-align: center;
        color: #999;
        padding: 20px 0;
    }

    /* 模态框样式 */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }

    .modal-header h3 {
        margin: 0;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-btn:hover {
        color: #333;
    }

    .modal-body {
        padding: 15px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        padding: 15px;
        border-top: 1px solid #ddd;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }

    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .form-group textarea {
        resize: vertical;
    }

    .cancel-btn {
        background-color: #95a5a6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }

    .confirm-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
    }

    .cancel-btn:hover,
    .confirm-btn:hover {
        opacity: 0.9;
    }

    @media (max-width: 768px) {

        /* 窄屏时调整全局容器宽度 */
        .container {
            max-width: none;
            width: 100%;
            padding: 0;
            margin: 0;
        }

        /* 窄屏时让主体内容宽度铺满屏幕 */
        .device-detail {
            background: white;
            border-radius: 0;
            box-shadow: none;
            padding: 20px 15px;
            width: 100%;
        }

        /* 窄屏时表格内容自动换行 */
        .drawings-table td:nth-child(2),
        .records-table td:nth-child(2),
        .problems-table td:nth-child(2) {
            word-wrap: break-word;
            word-break: break-all;
        }

        /* 窄屏时隐藏文件大小列，优化显示空间 */
        .drawings-table th:nth-child(3),
        .drawings-table td:nth-child(3) {
            display: none;
        }

        /* 确保下载按钮里的文字保持一行显示 */
        .download-btn {
            white-space: nowrap;
            min-width: 60px;
            text-align: center;
        }

        .info-item {
            flex-direction: column;
        }

        .info-item label {
            width: auto;
            margin-bottom: 5px;
        }

        .modal-content {
            width: 95%;
            max-height: 90vh;
        }
    }

    /* 新增的样式：记录数量和标题布局 */
    .collapse-header .header-title {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    /* 统一标题样式 */
    .collapse-header .header-title span:first-child,
    .collapse-header>span:not(.record-count):not(.collapse-icon) {
        font-size: 16px;
        font-weight: bold;
    }

    /* 统一数量显示样式 */
    .record-count {
        font-size: 16px;
        color: #666;
        font-weight: bold;
        margin-left: 5px;
    }

    .collapse-header .header-title .add-btn {
        margin-left: auto;
        padding: 6px 16px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .collapse-header .header-title .add-btn:hover {
        background-color: #45a049;
    }

    /* 宽屏模式下的两列布局 */
    @media (min-width: 769px) {

        /* 容器自适应窗口宽度，当窗口宽度缩小时跟随一起缩小 */
        .container {
            max-width: 1800px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .device-detail {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: none;
        }

        .detail-layout {
            display: flex;
            gap: 40px;
            margin-bottom: 20px;
            /* 允许容器内元素在空间不足时缩小 */
            min-width: 0;
        }

        /* 左侧设备信息宽度基本保持不变，但在窗口宽度非常小时允许适当缩小 */
        .detail-sidebar {
            flex: 1 1 600px;
            min-width: 250px;
        }

        /* 右侧内容区域 - 展开后调整为更适合展示列表的宽度 */
        .detail-content {
            flex: 2 1 800px;
            min-width: 400px;
        }

        .device-info {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .info-item label {
            font-weight: bold;
            color: #555;
            width: 90px;
            flex-shrink: 0;
        }

        .info-item span {
            color: #333;
            flex: 1;
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        /* 优化右侧内容区域的折叠块样式 */
        .collapse-block {
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .collapse-header {
            padding: 20px 25px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }

        .collapse-content {
            padding: 25px;
        }
    }

    /* 窄屏模式下恢复单列布局 - 与原有的窄屏样式统一使用768px断点 */
    @media (max-width: 768px) {
        .detail-layout {
            display: block;
        }

        .device-info {
            margin-bottom: 20px;
        }
    }
    /* 分页控制样式 */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding: 15px 0;
        border-top: 1px solid #eee;
    }

    .pagination-info {
        font-size: 14px;
        color: #666;
    }

    .pagination-navigation {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .pagination-btn {
        padding: 8px 12px;
        border: 1px solid #ddd;
        background-color: white;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }

    .pagination-btn:hover:not(:disabled) {
        background-color: #f5f5f5;
        border-color: #3498db;
    }

    .pagination-btn.active {
        background-color: #3498db;
        color: white;
        border-color: #3498db;
    }

    .pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .pagination-ellipsis {
        padding: 0 10px;
        color: #999;
    }

    .pagination-pageSize {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .pagination-pageSize select {
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        background-color: white;
    }

    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }

        .pagination-info,
        .pagination-pageSize {
            order: 1;
        }

        .pagination-navigation {
            order: 2;
            flex-wrap: wrap;
            justify-content: center;
        }
    }
</style>
<script>
    // 分页状态变量
    const paginationStates = {
        drawings: { currentPage: 1, pageSize: 5 },
        inspection: { currentPage: 1, pageSize: 5 },
        maintenance: { currentPage: 1, pageSize: 5 },
        problems: { currentPage: 1, pageSize: 5 }
    };

    // 添加分页控件
    function addPaginationControls(total, currentPage, pageSize, type) {
        // 先移除已存在的分页控件
        removePaginationControls(type);
        
        // 只有在总数小于5的时候才不显示控件
        if (total < 5) {
            return;
        }

        // 创建分页容器
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container';
        paginationContainer.dataset.type = type;

        // 计算总页数
        // 当pageSize为'all'时，总页数为1
        const totalPages = pageSize === 'all' ? 1 : Math.ceil(total / pageSize);

        // 添加分页信息
        const paginationInfo = document.createElement('div');
        paginationInfo.className = 'pagination-info';
        paginationInfo.textContent = `共 ${total} 条记录，第 ${currentPage} / ${totalPages} 页`;
        paginationContainer.appendChild(paginationInfo);

        // 添加页码导航
        const paginationNavigation = document.createElement('div');
        paginationNavigation.className = 'pagination-navigation';

        // 上一页按钮
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.textContent = '上一页';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                paginationStates[type].currentPage = currentPage - 1;
                loadDataWithPagination(type);
            }
        });
        paginationNavigation.appendChild(prevBtn);

        // 页码按钮
        const maxButtonsToShow = 5; // 最多显示的页码按钮数
        let startPage = Math.max(1, currentPage - Math.floor(maxButtonsToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxButtonsToShow - 1);

        // 调整起始页码，确保显示足够的按钮
        if (endPage - startPage < maxButtonsToShow - 1) {
            startPage = Math.max(1, endPage - maxButtonsToShow + 1);
        }

        // 第一页和省略号
        if (startPage > 1) {
            addPageButton(paginationNavigation, 1, currentPage, type);
            if (startPage > 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pagination-ellipsis';
                ellipsis.textContent = '...';
                paginationNavigation.appendChild(ellipsis);
            }
        }

        // 添加页码按钮
        for (let i = startPage; i <= endPage; i++) {
            addPageButton(paginationNavigation, i, currentPage, type);
        }

        // 最后一页和省略号
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'pagination-ellipsis';
                ellipsis.textContent = '...';
                paginationNavigation.appendChild(ellipsis);
            }
            addPageButton(paginationNavigation, totalPages, currentPage, type);
        }

        // 下一页按钮
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.textContent = '下一页';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
                paginationStates[type].currentPage = currentPage + 1;
                loadDataWithPagination(type);
            }
        });
        paginationNavigation.appendChild(nextBtn);

        paginationContainer.appendChild(paginationNavigation);

        // 添加每页显示数量选择器
        const pageSizeDiv = document.createElement('div');
        pageSizeDiv.className = 'pagination-pageSize';

        const pageSizeLabel = document.createElement('span');
        pageSizeLabel.textContent = '每页显示：';
        pageSizeDiv.appendChild(pageSizeLabel);

        const pageSizeSelect = document.createElement('select');
        // 定义可选的每页显示数量选项
        const pageSizeOptions = [5, 10, 20, 50, 100];
        
        // 先添加"全部"选项
        const allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = '全部';
        if (pageSize === 'all') {
            allOption.selected = true;
        }
        pageSizeSelect.appendChild(allOption);
        
        // 添加其他选项
        pageSizeOptions.forEach(size => {
            const option = document.createElement('option');
            option.value = size;
            option.textContent = size;
            if (pageSize === size) {
                option.selected = true;
            }
            pageSizeSelect.appendChild(option);
        });

        pageSizeSelect.addEventListener('change', function() {
            const newPageSize = this.value === 'all' ? 'all' : parseInt(this.value);
            paginationStates[type].pageSize = newPageSize;
            paginationStates[type].currentPage = 1; // 重置为第一页
            loadDataWithPagination(type);
        });
        
        pageSizeDiv.appendChild(pageSizeSelect);
        paginationContainer.appendChild(pageSizeDiv);

        // 添加到对应内容区域
        const contentId = type + '-content';
        const contentDiv = document.getElementById(contentId);
        if (contentDiv) {
            contentDiv.appendChild(paginationContainer);
        }
    }

    // 添加页码按钮
    function addPageButton(container, pageNum, currentPage, type) {
        const button = document.createElement('button');
        button.className = 'pagination-btn' + (pageNum === currentPage ? ' active' : '');
        button.textContent = pageNum;

        if (pageNum !== currentPage) {
            button.addEventListener('click', function() {
                paginationStates[type].currentPage = pageNum;
                loadDataWithPagination(type);
            });
        }

        container.appendChild(button);
    }

    // 移除分页控件
    function removePaginationControls(type) {
        // 查找并移除指定类型的分页容器
        const existingPaginationElements = document.querySelectorAll(`.pagination-container[data-type="${type}"]`);
        existingPaginationElements.forEach(element => {
            if (element && element.parentNode) {
                element.parentNode.removeChild(element);
            }
        });
    }

    // 根据类型加载数据并应用分页
    function loadDataWithPagination(type) {
        const currentState = paginationStates[type];
        const page = currentState.currentPage;
        const pageSize = currentState.pageSize;
        
        switch (type) {
            case 'drawings':
                loadDrawings(page, pageSize);
                break;
            case 'inspection':
                loadInspectionRecords(page, pageSize);
                break;
            case 'maintenance':
                loadMaintenanceRecords(page, pageSize);
                break;
            case 'problems':
                loadProblemRecords(page, pageSize);
                break;
        }
    }

    // 加载图纸（支持分页） - 平滑加载版本
    function loadDrawings(page = 1, pageSize = 5) {
        const content = document.getElementById('drawings-content');
        
        let url = `api.php?action=getDrawings&did=<?php echo $did; ?>`;
        // 总是添加分页参数
        if (pageSize === 'all') {
            // 当选择全部时，传递pageSize=0表示查询所有记录
            url += `&page=1&pageSize=0`;
        } else {
            // 确保pageSize是有效的数字
            const numericPageSize = parseInt(pageSize) || 5;
            url += `&page=${page}&pageSize=${numericPageSize}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // 检查数据是否包含total字段（分页模式）
                const hasPagination = data.total !== undefined && data.data !== undefined;
                const drawings = hasPagination ? data.data : data;
                const total = hasPagination ? data.total : data.length;

                if (drawings.length > 0) {
                    let html = '<table class="drawings-table">';
                    html += '<thead><tr><th>序号</th><th>图纸名称</th><th>文件大小</th><th>操作</th></tr></thead>';
                    html += '<tbody>';

                    drawings.forEach((drawing, index) => {
                        // 确保root_dir以斜杠结尾
                        let rootDir = drawing.root_dir || '';
                        if (rootDir && !rootDir.endsWith('/')) {
                            rootDir += '/';
                        }

                        // 构建完整的URL
                        const fullUrl = rootDir + drawing.link_name;

                        // 格式化文件大小
                        const fileSize = formatFileSize(drawing.file_size);

                        // 确定文件类型
                        const fileExtension = drawing.original_name.split('.').pop()?.toLowerCase() || '';
                        let fileType = '其他文件';
                        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(fileExtension)) {
                            fileType = '图片';
                        } else if (['dwg', 'dxf', 'dgn', 'rvt'].includes(fileExtension)) {
                            fileType = 'CAD';
                        } else if (['doc', 'docx', 'pdf', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'].includes(fileExtension)) {
                            fileType = '文档';
                        }

                        // 计算正确的序号（考虑分页）
                        const serialNumber = pageSize === 'all' ? index + 1 : (page - 1) * pageSize + index + 1;

                        html += `<tr>`;
                        html += `<td>${serialNumber}</td>`;
                        html += `<td><a href="javascript:void(0)" onclick="previewDrawing('${fullUrl}', '${drawing.original_name}')">${drawing.original_name}</a></td>`;
                        html += `<td>${fileSize}</td>`;
                        html += `<td><a href="javascript:void(0)" class="download-btn" data-url="${fullUrl}" data-name="${drawing.original_name}" data-type="${fileType}" data-size="${fileSize}">下载</a></td>`;
                        html += `</tr>`;
                    });

                    html += '</tbody></table>';
                    // 只有在数据加载完成后才更新内容，避免闪烁
                    content.innerHTML = html;

                    // 添加分页控件
                    addPaginationControls(total, page, pageSize, 'drawings');
                } else {
                    content.innerHTML = '<p class="no-result">没有查询到图纸</p>';
                    // 移除分页控件
                    removePaginationControls('drawings');
                }
            })
            .catch(error => {
                const content = document.getElementById('drawings-content');
                content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                // 移除分页控件
                removePaginationControls('drawings');
            });
    }

    // 加载巡视记录（支持分页） - 平滑加载版本
    function loadInspectionRecords(page = 1, pageSize = 5) {
        const content = document.getElementById('inspection-content');
        
        let url = `api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=1`;
        // 总是添加分页参数
        if (pageSize === 'all') {
            // 当选择全部时，传递pageSize=0表示查询所有记录
            url += `&page=1&pageSize=0`;
        } else {
            // 确保pageSize是有效的数字
            const numericPageSize = parseInt(pageSize) || 5;
            url += `&page=${page}&pageSize=${numericPageSize}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // 检查数据是否包含total字段（分页模式）
                const hasPagination = data.total !== undefined && data.data !== undefined;
                const records = hasPagination ? data.data : data;
                const total = hasPagination ? data.total : data.length;
                
                // 更新标题计数
                const countElement = document.getElementById('inspection-count');
                if (countElement) {
                    countElement.textContent = `(${total})`;
                }

                if (records.length > 0) {
                    let html = '<table class="records-table">';
                    html += '<thead><tr><th>序号</th><th>作业时间</th><th>作业人员</th></tr></thead>';
                    html += '<tbody>';

                    records.forEach((record, index) => {
                        // 处理作业人员姓名，所有人员都添加keeper-tag样式
                        let formattedWorkers = record.workers;
                        if (formattedWorkers) {
                            // 无论是否包含||分隔符，都为每个作业人员添加keeper-tag样式
                            const workerArray = formattedWorkers.split('||');
                            const formattedArray = [];
                            workerArray.forEach(worker => {
                                formattedArray.push('<span class="keeper-tag">' + worker + '</span>');
                            });
                            // 使用换行符分隔多个作业人员
                            formattedWorkers = formattedArray.join('<br>');
                        }
                        
                        // 计算正确的序号（考虑分页）
                        const serialNumber = pageSize === 'all' ? index + 1 : (page - 1) * pageSize + index + 1;
                        
                        html += `<tr class="record-row" data-id="${record.wid}" data-type="inspection" data-record='${JSON.stringify(record).replace(/'/g, '\'')}'><td>${serialNumber}</td><td>${record.work_date}</td><td>${formattedWorkers}</td></tr>`;
                    });

                    html += '</tbody></table>';
                    // 只有在数据加载完成后才更新内容，避免闪烁
                    content.innerHTML = html;

                    // 添加行点击事件
                    document.querySelectorAll('.record-row[data-type="inspection"]').forEach(row => {
                        row.addEventListener('click', function() {
                            const recordData = JSON.parse(this.getAttribute('data-record'));
                            showRecordDetailModal(recordData, 'inspection');
                        });
                    });

                    // 添加分页控件
                    addPaginationControls(total, page, pageSize, 'inspection');
                } else {
                    content.innerHTML = '<p class="no-result">没有查询到巡视记录</p>';
                    // 移除分页控件
                    removePaginationControls('inspection');
                }
            })
            .catch(error => {
                const content = document.getElementById('inspection-content');
                content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                // 移除分页控件
                removePaginationControls('inspection');
            });
    }

    // 加载检修记录（支持分页） - 平滑加载版本
    function loadMaintenanceRecords(page = 1, pageSize = 5) {
        const content = document.getElementById('maintenance-content');
        
        let url = `api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=2`;
        // 总是添加分页参数
        if (pageSize === 'all') {
            // 当选择全部时，传递pageSize=0表示查询所有记录
            url += `&page=1&pageSize=0`;
        } else {
            // 确保pageSize是有效的数字
            const numericPageSize = parseInt(pageSize) || 5;
            url += `&page=${page}&pageSize=${numericPageSize}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // 检查数据是否包含total字段（分页模式）
                const hasPagination = data.total !== undefined && data.data !== undefined;
                const records = hasPagination ? data.data : data;
                const total = hasPagination ? data.total : data.length;
                
                // 更新标题计数
                const countElement = document.getElementById('maintenance-count');
                if (countElement) {
                    countElement.textContent = `(${total})`;
                }

                if (records.length > 0) {
                    let html = '<table class="records-table">';
                    html += '<thead><tr><th>序号</th><th>作业时间</th><th>作业人员</th></tr></thead>';
                    html += '<tbody>';

                    records.forEach((record, index) => {
                        // 处理作业人员姓名，所有人员都添加keeper-tag样式
                        let formattedWorkers = record.workers;
                        if (formattedWorkers) {
                            // 无论是否包含||分隔符，都为每个作业人员添加keeper-tag样式
                            const workerArray = formattedWorkers.split('||');
                            const formattedArray = [];
                            workerArray.forEach(worker => {
                                formattedArray.push('<span class="keeper-tag">' + worker + '</span>');
                            });
                            // 使用换行符分隔多个作业人员
                            formattedWorkers = formattedArray.join('<br>');
                        }
                        
                        // 计算正确的序号（考虑分页）
                        const serialNumber = pageSize === 'all' ? index + 1 : (page - 1) * pageSize + index + 1;
                        
                        html += `<tr class="record-row" data-id="${record.wid}" data-type="maintenance" data-record='${JSON.stringify(record).replace(/'/g, '\'')}'><td>${serialNumber}</td><td>${record.work_date}</td><td>${formattedWorkers}</td></tr>`;
                    });

                    html += '</tbody></table>';
                    // 只有在数据加载完成后才更新内容，避免闪烁
                    content.innerHTML = html;

                    // 添加行点击事件
                    document.querySelectorAll('.record-row[data-type="maintenance"]').forEach(row => {
                        row.addEventListener('click', function() {
                            const recordData = JSON.parse(this.getAttribute('data-record'));
                            showRecordDetailModal(recordData, 'maintenance');
                        });
                    });

                    // 添加分页控件
                    addPaginationControls(total, page, pageSize, 'maintenance');
                } else {
                    content.innerHTML = '<p class="no-result">没有查询到检修记录</p>';
                    // 移除分页控件
                    removePaginationControls('maintenance');
                }
            })
            .catch(error => {
                const content = document.getElementById('maintenance-content');
                content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                // 移除分页控件
                removePaginationControls('maintenance');
            });
    }

    // 加载问题记录（支持分页） - 平滑加载版本
    function loadProblemRecords(page = 1, pageSize = 5) {
        const content = document.getElementById('problem-content');
        
        let url = `api.php?action=getProblems&did=<?php echo $did; ?>`;
        // 总是添加分页参数
        if (pageSize === 'all') {
            // 当选择全部时，传递pageSize=0表示查询所有记录
            url += `&page=1&pageSize=0`;
        } else {
            // 确保pageSize是有效的数字
            const numericPageSize = parseInt(pageSize) || 5;
            url += `&page=${page}&pageSize=${numericPageSize}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // 检查数据是否包含total字段（分页模式）
                const hasPagination = data.total !== undefined && data.data !== undefined;
                const problems = hasPagination ? data.data : data;
                const total = hasPagination ? data.total : data.length;
                
                // 更新标题计数
                const countElement = document.getElementById('problem-count');
                if (countElement) {
                    countElement.textContent = `(${total})`;
                }

                if (problems.length > 0) {
                    let html = '<table class="problems-table">';
                    html += '<thead><tr><th>序号</th><th>问题描述</th><th>发现时间</th><th>当前状态</th></tr></thead>';
                    html += '<tbody>';

                    problems.forEach((problem, index) => {
                        const statusClass = problem.flow === 0 ? 'status-red' : 'status-green';
                        const statusText = problem.flow === 0 ? '已创建' : '已解决';
                        
                        // 计算正确的序号（考虑分页）
                        const serialNumber = pageSize === 'all' ? index + 1 : (page - 1) * pageSize + index + 1;
                        
                        html += `<tr><td>${serialNumber}</td><td><a href="problems.php?pid=${problem.pid}">${problem.description}</a></td><td>${problem.create_time}</td><td class="${statusClass}">${statusText}</td></tr>`;
                    });

                    html += '</tbody></table>';
                    // 只有在数据加载完成后才更新内容，避免闪烁
                    content.innerHTML = html;

                    // 添加分页控件
                    addPaginationControls(total, page, pageSize, 'problems');
                } else {
                    content.innerHTML = '<p class="no-result">没有查询到问题记录</p>';
                    // 移除分页控件
                    removePaginationControls('problems');
                }
            })
            .catch(error => {
                const content = document.getElementById('problem-content');
                content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                // 移除分页控件
                removePaginationControls('problems');
            });
    }

    // 修复原有代码中的问题 - 确保原有函数调用兼容新的分页功能
    // 注意：这部分应该被添加到原有的<script>标签内，替换原有的函数定义

    // 全局复制下载链接函数
    function copyDownloadLink(url, button) {
        // 优先使用现代的Clipboard API
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function() {
                showCopySuccess(button);
            }).catch(function() {
                // 降级方案
                fallbackCopyTextToClipboard(url, button);
            });
        } else {
            // 降级方案
            fallbackCopyTextToClipboard(url, button);
        }
    }

    // 复制成功提示 - 在按钮上显示
    function showCopySuccess(button) {
        const originalText = button.textContent;
        const originalBg = button.style.backgroundColor;

        button.textContent = '已复制！';
        button.style.backgroundColor = '#27ae60';

        setTimeout(function() {
            button.textContent = originalText;
            button.style.backgroundColor = originalBg;
        }, 2000);
    }

    // 降级复制方案，兼容不支持Clipboard API的设备
    function fallbackCopyTextToClipboard(text, button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;

        // 避免界面闪烁
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        textArea.style.opacity = '0';

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button);
            } else {
                alert('复制失败，请手动复制');
            }
        } catch (err) {
            alert('复制失败，请手动复制');
        }

        document.body.removeChild(textArea);
    }
</script>