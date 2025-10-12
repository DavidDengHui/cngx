<?php
// 检查是否被直接访问
if (basename($_SERVER['PHP_SELF']) == 'devices_detail.php') {
    header('Location: /devices.php');
    exit();
}

// 确保did参数存在且不为空
if (!isset($did) || empty(trim($did))) {
    header('Location: /devices.php');
    exit();
}

// 设置默认页面标题
$page_title = '设备详情';

include 'header.php';

?>

<!-- 引入QRCode.js库 -->
<script src="files/js/qrcode.min.js"></script>
<script>
    // 全局变量存储设备ID
    const deviceId = '<?php echo $did; ?>';

    // 页面加载完成后获取设备详情和记录数量
    document.addEventListener('DOMContentLoaded', function() {
        loadDeviceDetail();
        loadRecordCounts(); // 加载所有记录的数量
    });

    // 通过API获取设备详情
    function loadDeviceDetail() {
        fetch(`api.php?action=getDeviceDetail&did=${deviceId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data) {
                    displayDeviceDetail(result.data);
                } else if (!result.success && result.message) {
                    // 如果设备不存在，跳转到设备搜索页面
                    if (result.message === '设备不存在') {
                        window.location.href = `/devices.php`;
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
                                <span id="drawing-count" class="record-count">
                                    <span class="record-count-number" onclick="handleCountClickInline(event, 'drawing')" title="点击标签刷新该项数据">${device.drawing_count}</span>
                                </span>
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
                                <span id="inspection-count" class="record-count">
                                    <span class="record-count-number" onclick="handleCountClickInline(event, 'inspection')" title="点击标签刷新该项数据">?</span>
                                </span>
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
                                <span id="maintenance-count" class="record-count">
                                    <span class="record-count-number" onclick="handleCountClickInline(event, 'maintenance')" title="点击标签刷新该项数据">?</span>
                                </span>
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
                                <span id="problem-count" class="record-count">
                                    <span class="record-count-number" onclick="handleCountClickInline(event, 'problem')" title="点击标签刷新该项数据">?</span>
                                </span>
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

            <!-- 二维码图片显示区域 -->
            <div id="qrcode-display-area" style="text-align: center; margin-bottom: 20px; display: none;">
                <img id="device-qrcode-image" src="" alt="设备二维码" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div class="action-buttons">
                <button class="edit-btn" onclick="window.location.href='/devices.php?did=${deviceId}&mode=edit'">修改</button>
                <button id="qrcode-button" class="qrcode-btn" onclick="showDeviceQRCode()">设备码</button>
            </div>
        `;

        // 更新设备详情内容
        document.querySelector('.device-detail').innerHTML = deviceDetailHTML;

        // 检查设备二维码图片是否存在
        checkDeviceQRCodeExists(deviceId);

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

    // 检查设备二维码图片是否存在
    function checkDeviceQRCodeExists(did) {
        // 构建二维码图片的URL路径
        const qrcodeUrl = `/uploads/qrcode/qr_${did}.png`;

        // 检查图片是否存在的函数
        function imageExists(url, callback) {
            const img = new Image();
            img.onload = function() {
                callback(true);
            };
            img.onerror = function() {
                callback(false);
            };
            img.src = url + '?' + new Date().getTime(); // 添加时间戳避免缓存
        }

        // 检查图片是否存在
        imageExists(qrcodeUrl, function(exists) {
            const qrcodeDisplayArea = document.getElementById('qrcode-display-area');
            const deviceQrcodeImage = document.getElementById('device-qrcode-image');
            const qrcodeButton = document.getElementById('qrcode-button');

            if (exists && qrcodeDisplayArea && deviceQrcodeImage && qrcodeButton) {
                // 如果图片存在，显示图片并更改按钮文本
                deviceQrcodeImage.src = qrcodeUrl;
                qrcodeDisplayArea.style.display = 'block';
                qrcodeButton.textContent = '重新生成设备码';
            } else {
                // 如果图片不存在，隐藏图片并更改按钮文本
                qrcodeDisplayArea.style.display = 'none';
                qrcodeButton.textContent = '生成设备码';
            }
        });
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
                    <label for="workers-input">作业人员：<span class="required">*</span></label>
                    <div class="workers-input-wrapper">
                        <div id="workers-tags" class="workers-tags"></div>
                        <input type="text" id="workers-input" class="workers-input" placeholder="多个姓名请使用空格分隔">
                        <input type="hidden" id="workers">
                    </div>
                </div>

                <div class="form-group">
                    <label for="work-date">作业时间：<span class="required">*</span></label>
                    <input type="datetime-local" id="work-date" required>
                </div>

                <div class="form-group">
                    <label for="work-remark">作业说明：</label>
                    <textarea id="work-remark" rows="3" placeholder="没啥要说的就跳过吧 ;-)"></textarea>
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
                <input type="hidden" id="problem-photos">

                <div class="form-group">
                    <label for="problem-description">问题描述：<span class="required">*</span></label>
                    <textarea id="problem-description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="problem-creator-input">发现人：<span class="required">*</span></label>
                    <div class="workers-input-wrapper">
                        <div id="creator-tags" class="workers-tags"></div>
                        <input type="text" id="problem-creator-input" class="workers-input" placeholder="多个姓名请使用空格分隔">
                        <input type="hidden" id="problem-creator">
                    </div>
                </div>

                <div class="form-group">
                    <label for="problem-date">发现时间：<span class="required">*</span></label>
                    <input type="datetime-local" id="problem-date" required>
                </div>

                <div class="form-group">
                    <label>责任部门：<span class="required">*</span></label>
                    <div class="select-container">
                        <input type="text" id="problem-department" readonly placeholder="请选择部门">
                        <input type="hidden" id="problem-department-id">
                    </div>
                </div>

                <div class="form-group">
                    <label>问题照片：</label>
                    <input type="file" id="problem-photos-upload" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.svg">
                    <div id="uploaded-photos" class="uploaded-photos-container"></div>
                    <p class="upload-note">支持的图片格式：JPG、JPEG、PNG、WebP、GIF、BMP、SVG</p>
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

        // 重置模态框滚动位置到顶部
        if (modalContent) {
            modalContent.scrollTop = 0;
        }

        if (modalBody) {
            modalBody.scrollTop = 0;
        }

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

    // 数据缓存对象，用于跟踪每个类型的加载状态
    const dataCache = {
        drawings: {
            loaded: false
        },
        inspection: {
            loaded: false
        },
        maintenance: {
            loaded: false
        },
        problems: {
            loaded: false
        }
    };

    // 加载记录数量（页面加载时自动执行）
    function loadRecordCounts() {
        refreshCount('drawing');
        refreshCount('inspection');
        refreshCount('maintenance');
        refreshCount('problem');
    }

    // 刷新指定类型的记录数量
    function refreshCount(type) {
        // 确保使用全局deviceId而不是PHP变量
        const did = deviceId;

        // 清空对应类型的缓存状态
        if (dataCache[type]) {
            dataCache[type].loaded = false;
        }

        // 获取对应折叠块的ID
        const getContentId = (dataType) => {
            const idMap = {
                'drawing': 'drawings',
                'inspection': 'inspection-records',
                'maintenance': 'maintenance-records',
                'problem': 'problem-records'
            };
            return idMap[dataType] || '';
        };

        // 获取对应的数据类型（转换为loadDataWithPagination使用的类型）
        const getDataType = (type) => {
            const typeMap = {
                'drawing': 'drawings',
                'inspection': 'inspection',
                'maintenance': 'maintenance',
                'problem': 'problems'
            };
            return typeMap[type] || type;
        };

        // 检查折叠块是否处于展开状态
        const contentId = getContentId(type);
        const contentElement = document.getElementById(contentId);
        const isExpanded = contentElement && contentElement.style.display !== 'none';

        // 如果折叠块是展开状态，清空其内容并显示加载状态
        if (isExpanded && contentElement) {
            const contentContainerId = getDataType(type) + '-content';
            const contentContainer = document.getElementById(contentContainerId);
            if (contentContainer) {
                // 保存加载指示器
                const loadingElement = contentContainer.querySelector('.loading');
                if (loadingElement) {
                    // 清空内容，只保留加载指示器
                    contentContainer.innerHTML = '';
                    contentContainer.appendChild(loadingElement);
                    loadingElement.style.display = 'block';
                }
            }
        }

        // 添加加载状态指示器
        const getCountElement = (id) => {
            const element = document.getElementById(id);
            if (element) {
                const numberElement = element.querySelector('.record-count-number');
                if (numberElement) {
                    // 显示加载中状态
                    const originalContent = numberElement.textContent;
                    numberElement.textContent = '...';
                    return {
                        element,
                        numberElement,
                        originalContent
                    };
                }
            }
            return null;
        };

        let countInfo;
        let url;

        switch (type) {
            case 'drawing':
                countInfo = getCountElement('drawing-count');
                url = `api.php?action=getDeviceDetail&did=${did}`;
                break;
            case 'inspection':
                countInfo = getCountElement('inspection-count');
                url = `api.php?action=getWorkLogs&did=${did}&type=1`;
                break;
            case 'maintenance':
                countInfo = getCountElement('maintenance-count');
                url = `api.php?action=getWorkLogs&did=${did}&type=2`;
                break;
            case 'problem':
                countInfo = getCountElement('problem-count');
                url = `api.php?action=getProblems&did=${did}`;
                break;
        }

        // 即使没有找到元素，也要继续获取数据，稍后再更新UI
        if (!url) return;

        const updateCountElement = () => {
            // 尝试重新获取元素
            const elementId = type === 'drawing' ? 'drawing-count' :
                type === 'inspection' ? 'inspection-count' :
                type === 'maintenance' ? 'maintenance-count' : 'problem-count';

            const element = document.getElementById(elementId);
            if (element) {
                const numberElement = element.querySelector('.record-count-number');
                if (numberElement) {
                    return numberElement;
                }
            }
            return null;
        };

        fetch(url)
            .then(response => response.json())
            .then(data => {
                let count = 0;

                if (type === 'drawing') {
                    // 处理设备详情数据
                    count = data.success && data.data && data.data.drawing_count !== undefined ? data.data.drawing_count : 0;
                } else if (type === 'problem') {
                    // 处理问题记录数据（适配增强后的API）
                    if (data.success) {
                        count = data.total !== undefined ? data.total : ((Array.isArray(data.data)) ? data.data.length : 0);
                    } else {
                        console.warn('获取问题记录数量失败:', data.message || '未知错误');
                    }
                } else {
                    // 处理其他记录类型
                    count = data.total !== undefined ? data.total : (Array.isArray(data) ? data.length : 0);
                }

                // 更新数量显示
                let numberElement = countInfo ? countInfo.numberElement : null;
                if (!numberElement) {
                    // 如果初始时没有找到元素，尝试再次获取
                    numberElement = updateCountElement();
                }
                if (numberElement) {
                    numberElement.textContent = count;
                }

                // 如果折叠块是展开状态，重新加载数据
                if (isExpanded) {
                    const dataType = getDataType(type);
                    // 重置分页状态为第一页
                    if (paginationStates[dataType]) {
                        paginationStates[dataType].currentPage = 1;
                    }
                    // 重新加载数据
                    setTimeout(() => {
                        loadDataWithPagination(dataType);
                    }, 100); // 短暂延迟确保UI更新
                }
            })
            .catch(error => {
                // 出错时恢复原始内容或显示0
                let numberElement = countInfo ? countInfo.numberElement : null;
                if (!numberElement) {
                    // 如果初始时没有找到元素，尝试再次获取
                    numberElement = updateCountElement();
                }
                if (numberElement) {
                    numberElement.textContent = '0';
                }
                console.error(`刷新${type}记录数量失败:`, error);
            });
    }

    // 处理数量标签点击事件的内联函数
    function handleCountClickInline(e, type) {
        e.stopPropagation(); // 阻止事件冒泡到父元素
        refreshCount(type);
    }

    // 在DOM加载完成后初始化
    document.addEventListener('DOMContentLoaded', function() {
        loadRecordCounts();
    });

    // 原始的loadRecordCounts函数实现
    function originalLoadRecordCounts() {
        // 加载巡视记录数量
        fetch(`api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=1`)
            .then(response => response.json())
            .then(data => {
                const countElement = document.getElementById('inspection-count');
                // 检查返回格式，如果有total字段则使用，否则回退到data.length
                const count = data.total !== undefined ? data.total : (Array.isArray(data) ? data.length : 0);
                const numberElement = countElement.querySelector('.record-count-number');
                if (numberElement) {
                    numberElement.textContent = count;
                }
            })
            .catch(error => {
                const countElement = document.getElementById('inspection-count');
                const numberElement = countElement.querySelector('.record-count-number');
                if (numberElement) {
                    numberElement.textContent = '0';
                }
            });

        // 加载检修记录数量
        fetch(`api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=2`)
            .then(response => response.json())
            .then(data => {
                const countElement = document.getElementById('maintenance-count');
                // 检查返回格式，如果有total字段则使用，否则回退到data.length
                const count = data.total !== undefined ? data.total : (Array.isArray(data) ? data.length : 0);
                const numberElement = countElement.querySelector('.record-count-number');
                if (numberElement) {
                    numberElement.textContent = count;
                }
            })
            .catch(error => {
                const countElement = document.getElementById('maintenance-count');
                const numberElement = countElement.querySelector('.record-count-number');
                if (numberElement) {
                    numberElement.textContent = '0';
                }
            });

        // 加载问题记录数量（适配增强后的API）
        fetch(`api.php?action=getProblems&did=<?php echo $did; ?>&page=1&pageSize=1`)
            .then(response => response.json())
            .then(data => {
                const countElement = document.getElementById('problem-count');
                if (countElement) {
                    // 检查数据是否包含success字段
                    if (data.success) {
                        // 检查返回格式，如果有total字段则使用，否则回退到data.data.length
                        const count = data.total !== undefined ? data.total : ((Array.isArray(data.data)) ? data.data.length : 0);
                        const numberElement = countElement.querySelector('.record-count-number');
                        if (numberElement) {
                            numberElement.textContent = count;
                        }
                    } else {
                        console.warn('获取问题记录数量失败:', data.message || '未知错误');
                    }
                }
            })
            .catch(error => {
                const countElement = document.getElementById('problem-count');
                if (countElement) {
                    const numberElement = countElement.querySelector('.record-count-number');
                    if (numberElement) {
                        numberElement.textContent = '0';
                    }
                }
                console.error('获取问题记录数量失败:', error);
            });
    }

    // 保留原始函数调用以确保兼容性
    // document.addEventListener('DOMContentLoaded', originalLoadRecordCounts);

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

        const fields = [{
                label: '设备名称:',
                value: deviceName
            },
            {
                label: '作业时间:',
                value: type === 'inspection' ? (record.inspection_time || record.work_date) : (record.maintenance_time || record.work_date)
            },
            {
                label: '作业人员:',
                value: type === 'inspection' ? (record.inspector || record.workers) : (record.maintainer || record.workers)
            },
            {
                label: '作业说明:',
                value: record.content || '无'
            }
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

    // 全局变量存储当前选择类型和路径
    let currentSelectType = '';
    let currentSelectPath = [];

    // 打开选择模态框
    function openSelectModal(type, label) {
        // 创建模态框容器
        let modal = document.getElementById('select-modal');
        if (!modal) {
            // 如果模态框不存在，则创建
            modal = document.createElement('div');
            modal.id = 'select-modal';
            modal.className = 'modal';
            modal.style.display = 'none';
            modal.innerHTML = `
                <style>
                    /* 模态框特定样式 - 仅应用于#select-modal内部 */
                    
                    /* 模态框按钮样式 */
                    #select-modal .modal-btn {
                        padding: 6px 16px;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 14px;
                        margin: 0 5px;
                    }
                    
                    /* 重置按钮 - 灰色 */
                    #select-modal .reset-btn {
                        background-color: #95a5a6;
                        color: white;
                    }
                    
                    /* 默认按钮 - 蓝色 */
                    #select-modal .default-btn {
                        background-color: #3498db;
                        color: white;
                    }
                    
                    /* 取消按钮 */
                    #select-modal .cancel-btn {
                        background-color: #95a5a6;
                        color: white;
                    }
                    
                    /* 确认按钮 - 通用绿色 */
                    #select-modal .confirm-btn {
                        background-color: #27ae60;
                        color: white;
                    }
                    
                    /* 路径样式 */
                    #select-modal .select-path {
                        padding: 10px 15px;
                        background-color: #f8f9fa;
                        border-bottom: 2px solid #dee2e6;
                        margin-bottom: 10px;
                        font-size: 14px;
                    }
                    
                    /* 路径项 - 链接样式 */
                    #select-modal .path-item {
                        color: #3498db;
                        cursor: pointer;
                        text-decoration: underline;
                    }
                    
                    /* 路径项悬停效果 */
                    #select-modal .path-item:hover {
                        color: #2980b9;
                    }
                    
                    /* 选择项容器 */
                    #select-modal .select-items {
                        max-height: 400px;
                        overflow-y: auto;
                    }
                    
                    /* 选择块默认样式 - 灰底黑字 */
                    #select-modal .select-item {
                        padding: 12px 15px;
                        background-color: #f8f9fa;
                        color: #333;
                        cursor: pointer;
                        border: 1px solid transparent;
                    }
                    
                    /* 选择块悬停效果 */
                    #select-modal .select-item:hover {
                        background-color: #e9ecef;
                    }
                    
                    /* 选择块点击后效果 - 蓝底白字 */
                    #select-modal .select-item:active,
                    #select-modal .select-item.selected {
                        background-color: #3498db;
                        color: white;
                    }
                </style>
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-header-left">
                            <button type="button" class="modal-btn reset-btn">重置</button>
                            <button type="button" class="modal-btn default-btn" style="display: none;">默认</button>
                        </div>
                        <div class="modal-header-right">
                            <button type="button" class="modal-btn cancel-btn">取消</button>
                            <button type="button" class="modal-btn confirm-btn">确认</button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div id="select-content"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // 添加事件监听
            modal.querySelector('.reset-btn').addEventListener('click', resetSelect);
            modal.querySelector('.default-btn').addEventListener('click', selectDefaultDepartment);
            modal.querySelector('.cancel-btn').addEventListener('click', closeSelectModal);
            modal.querySelector('.confirm-btn').addEventListener('click', confirmSelect);
        }

        currentSelectType = type;

        // 根据类型决定是否显示默认按钮
        if (type === 'problem-department') {
            modal.querySelector('.default-btn').style.display = 'inline-block';
        } else {
            modal.querySelector('.default-btn').style.display = 'none';
        }

        // 重置选择路径
        currentSelectPath = [];

        // 获取当前选中的值
        let currentId = 0;
        if (type === 'problem-department') {
            currentId = document.getElementById('problem-department-id').value || 0;
        }

        // 加载第一级数据
        loadSelectData(currentId);

        // 显示模态框并确保居中
        modal.style.display = 'block';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';

        // 确保模态框内容居中
        const modalContentElement = modal.querySelector('.modal-content');
        modalContentElement.style.margin = 'auto';

        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';

        // ESC键关闭
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === 'Escape') {
                closeSelectModal();
                document.removeEventListener('keydown', escListener);
            }
        });

        // 点击模态框外部关闭
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeSelectModal();
            }
        });
    }

    // 关闭选择模态框
    function closeSelectModal() {
        const modal = document.getElementById('select-modal');
        if (modal) {
            modal.style.display = 'none';
        }
        // 恢复背景页面滚动
        document.body.style.overflow = '';
    }

    // 加载选择数据
    function loadSelectData(parentId) {
        const type = currentSelectType;
        const contentDiv = document.getElementById('select-content');

        // 清空内容
        contentDiv.innerHTML = '<div class="loading">加载中...</div>';

        // 根据类型获取API URL
        let apiUrl = '';
        if (type === 'problem-department') {
            apiUrl = `api.php?action=getDepartments&parentId=${parentId}`;
        }

        // 发送请求获取数据
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let html = '';

                    // 显示当前路径
                    if (currentSelectPath.length > 0) {
                        html += '<div class="select-path">';
                        currentSelectPath.forEach((item, index) => {
                            html += `<span class="path-item" data-id="${item.id}">${item.name}</span>`;
                            if (index < currentSelectPath.length - 1) {
                                html += ' / ';
                            }
                        });
                        html += '</div>';
                    }

                    // 显示选项列表
                    html += '<div class="select-items">';
                    data.forEach(item => {
                        html += `<div class="select-item" data-id="${item.id}" data-name="${item.name}" data-shortname="${item.shortname || item.name}">${item.name}</div>`;
                    });
                    html += '</div>';

                    // 更新内容
                    contentDiv.innerHTML = html;

                    // 添加路径点击事件
                    document.querySelectorAll('.path-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const id = parseInt(this.getAttribute('data-id'));
                            // 找到该路径项的索引
                            const index = currentSelectPath.findIndex(p => p.id === id);
                            if (index !== -1) {
                                // 移除该索引之后的所有路径项
                                currentSelectPath.splice(index + 1);
                                // 重新加载数据
                                loadSelectData(id);
                            }
                        });
                    });

                    // 添加选项点击事件
                    document.querySelectorAll('.select-item').forEach(item => {
                        item.addEventListener('click', function() {
                            // 移除所有选择项的选中状态
                            document.querySelectorAll('.select-item').forEach(selectItem => {
                                selectItem.classList.remove('selected');
                            });

                            // 添加当前项的选中状态
                            this.classList.add('selected');

                            const id = parseInt(this.getAttribute('data-id'));
                            const name = this.getAttribute('data-name');
                            const shortname = this.getAttribute('data-shortname');

                            // 添加到选择路径
                            currentSelectPath.push({
                                id: id,
                                name: name,
                                shortname: shortname
                            });

                            // 加载下一级数据
                            loadSelectData(id);
                        });
                    });
                } else {
                    // 如果没有子项，检查是否是打开模态框时的初始加载
                    // 如果是初始加载（currentSelectPath为空）且parentId > 0，表示当前选择的部门没有下级部门
                    // 这种情况下，我们应该显示与当前部门同级的所有部门
                    if (currentSelectPath.length === 0 && parentId > 0) {
                        // 为了获取同级部门，我们需要遍历部门树来找到当前部门的父部门ID
                        // 我们可以通过递归查询所有部门来实现这一点

                        // 先获取所有顶级部门
                        fetch(`api.php?action=getDepartments&parentId=0`)
                            .then(response => response.json())
                            .then(topDepartments => {
                                // 递归查找当前部门
                                let foundParentId = null;
                                let currentDeptInfo = null;

                                // 递归函数来查找当前部门ID
                                function findDepartmentWithId(departments, targetId) {
                                    for (let dept of departments) {
                                        if (dept.id === targetId) {
                                            // 找到当前部门，但我们还需要知道它的父部门ID
                                            // 由于我们是从顶级部门开始遍历，我们需要在外部记录找到的父部门ID
                                            return true;
                                        }

                                        // 对于每个部门，尝试获取其子部门
                                        return new Promise(resolve => {
                                            fetch(`api.php?action=getDepartments&parentId=${dept.id}`)
                                                .then(res => res.json())
                                                .then(children => {
                                                    if (children && children.length > 0) {
                                                        for (let child of children) {
                                                            if (child.id === targetId) {
                                                                // 找到当前部门，其父部门ID就是dept.id
                                                                foundParentId = dept.id;
                                                                currentDeptInfo = child;
                                                                resolve(true);
                                                                return;
                                                            }
                                                        }

                                                        // 递归查找每个子部门的子部门
                                                        for (let child of children) {
                                                            if (findDepartmentWithId([child], targetId)) {
                                                                resolve(true);
                                                                return;
                                                            }
                                                        }
                                                    }
                                                    resolve(false);
                                                })
                                                .catch(() => resolve(false));
                                        });
                                    }
                                    return false;
                                }

                                // 由于递归中包含异步调用，我们需要特殊处理
                                // 为了简化实现，我们采用一个备选方案：尝试获取所有可能的部门级别
                                // 直到找到当前部门或遍历完所有层级

                                // 备选方案：尝试从顶级部门开始，逐层获取部门，直到找到包含当前部门的父部门
                                let parentLevelFound = false;
                                let currentLevel = 0;
                                const maxLevels = 5; // 限制最大层级以避免无限循环

                                // 我们可以尝试另一种方法：先获取当前部门的可能父部门
                                // 由于我们知道getDepartments接口返回特定父部门的所有子部门
                                // 我们可以尝试获取所有顶级部门的子部门，看看哪个包含当前部门ID

                                // 这里我们采用一个更直接的方法：
                                // 1. 假设当前部门的父部门是某个存在的部门ID
                                // 2. 尝试获取所有可能的父部门的子部门列表
                                // 3. 找到包含当前部门ID的父部门

                                // 为了简化，我们这里直接获取顶级部门，然后查看当前部门是否是顶级部门
                                // 如果不是，我们可以尝试获取每个顶级部门的子部门，看看当前部门是否在其中
                                // 如果找到，那么该顶级部门就是当前部门的父部门

                                // 先检查当前部门是否是顶级部门
                                let isTopLevel = false;
                                topDepartments.forEach(dept => {
                                    if (dept.id === parentId) {
                                        isTopLevel = true;
                                        currentDeptInfo = dept;
                                    }
                                });

                                if (isTopLevel) {
                                    // 如果是顶级部门，显示所有顶级部门
                                    displayDepartmentsList(topDepartments, parentId);
                                } else {
                                    // 使用childId参数获取父部门信息，这是更直接可靠的方法
                                    fetch(`api.php?action=getDepartments&childId=${parentId}`)
                                        .then(res => res.json())
                                        .then(parentDept => {
                                            if (parentDept && parentDept.id) {
                                                // 获取当前部门的名称信息
                                                let currentDept = null;
                                                // 递归构建完整的部门路径
                                                function buildCompletePath(childId, path = []) {
                                                    return fetch(`api.php?action=getDepartments&childId=${childId}`)
                                                        .then(res => res.json())
                                                        .then(parent => {
                                                            if (parent && parent.id) {
                                                                // 将当前父部门添加到路径前面
                                                                path.unshift({
                                                                    id: parent.id,
                                                                    name: parent.name,
                                                                    shortname: parent.shortname || parent.name
                                                                });
                                                                // 继续向上查找
                                                                return buildCompletePath(parent.id, path);
                                                            } else {
                                                                // 已到达一级部门，返回完整路径
                                                                return path;
                                                            }
                                                        })
                                                        .catch(() => {
                                                            // 出错时返回已构建的路径
                                                            return path;
                                                        });
                                                }

                                                // 先找到当前部门信息
                                                return fetch(`api.php?action=getDepartments&parentId=${parentDept.id}`)
                                                    .then(res => res.json())
                                                    .then(siblingDepartments => {
                                                        // 找到当前部门
                                                        siblingDepartments.forEach(dept => {
                                                            if (dept.id === parentId) {
                                                                currentDept = dept;
                                                            }
                                                        });

                                                        // 构建完整路径
                                                        return buildCompletePath(parentId).then(completePath => {
                                                            // 更新当前选择路径
                                                            currentSelectPath = completePath;
                                                            // 添加当前部门到路径末尾
                                                            if (currentDept) {
                                                                currentSelectPath.push({
                                                                    id: currentDept.id,
                                                                    name: currentDept.name,
                                                                    shortname: currentDept.shortname || currentDept.name
                                                                });
                                                            }

                                                            // 显示同级部门列表和完整路径
                                                            let html = '';

                                                            // 显示完整路径
                                                            if (currentSelectPath.length > 0) {
                                                                html += '<div class="select-path">';
                                                                currentSelectPath.forEach((item, index) => {
                                                                    html += `<span class="path-item" data-id="${item.id}">${item.name}</span>`;
                                                                    if (index < currentSelectPath.length - 1) {
                                                                        html += ' / ';
                                                                    }
                                                                });
                                                                html += '</div>';
                                                            }

                                                            // 显示同级部门列表
                                                            html += '<div class="select-items">';
                                                            siblingDepartments.forEach(item => {
                                                                const isSelected = item.id === parentId;
                                                                html += `<div class="select-item ${isSelected ? 'selected' : ''}" data-id="${item.id}" data-name="${item.name}" data-shortname="${item.shortname || item.name}">${item.name}</div>`;
                                                            });
                                                            html += '</div>';

                                                            // 更新内容
                                                            contentDiv.innerHTML = html;

                                                            // 添加路径点击事件
                                                            document.querySelectorAll('.path-item').forEach(item => {
                                                                item.addEventListener('click', function() {
                                                                    const id = parseInt(this.getAttribute('data-id'));
                                                                    // 找到该路径项的索引
                                                                    const index = currentSelectPath.findIndex(p => p.id === id);
                                                                    if (index !== -1) {
                                                                        // 移除该索引之后的所有路径项
                                                                        currentSelectPath.splice(index + 1);
                                                                        // 重新加载数据
                                                                        loadSelectData(id);
                                                                    }
                                                                });
                                                            });

                                                            // 添加选项点击事件
                                                            document.querySelectorAll('.select-item').forEach(item => {
                                                                item.addEventListener('click', function() {
                                                                    // 移除所有选择项的选中状态
                                                                    document.querySelectorAll('.select-item').forEach(selectItem => {
                                                                        selectItem.classList.remove('selected');
                                                                    });

                                                                    // 添加当前项的选中状态
                                                                    this.classList.add('selected');

                                                                    const id = parseInt(this.getAttribute('data-id'));
                                                                    const name = this.getAttribute('data-name');
                                                                    const shortname = this.getAttribute('data-shortname');

                                                                    // 添加到选择路径
                                                                    currentSelectPath.push({
                                                                        id: id,
                                                                        name: name,
                                                                        shortname: shortname
                                                                    });

                                                                    // 加载下一级数据
                                                                    loadSelectData(id);
                                                                });
                                                            });
                                                        });
                                                    })
                                                    .catch(() => {
                                                        contentDiv.innerHTML = `<div class="error">加载同级部门失败</div>`;
                                                    });
                                            } else {
                                                contentDiv.innerHTML = `<div class="error">无法找到部门的同级部门</div>`;
                                            }
                                        })
                                        .catch(() => {
                                            contentDiv.innerHTML = `<div class="error">获取父部门信息失败</div>`;
                                        });
                                }

                                // 显示部门列表的函数
                                function displayDepartmentsList(departments, selectedId) {
                                    if (departments && departments.length > 0) {
                                        let html = '';

                                        // 显示部门列表
                                        html += '<div class="select-items">';
                                        departments.forEach(item => {
                                            const isSelected = item.id === selectedId;
                                            html += `<div class="select-item ${isSelected ? 'selected' : ''}" data-id="${item.id}" data-name="${item.name}" data-shortname="${item.shortname || item.name}">${item.name}</div>`;
                                        });
                                        html += '</div>';

                                        // 更新内容
                                        contentDiv.innerHTML = html;

                                        // 添加选项点击事件
                                        document.querySelectorAll('.select-item').forEach(item => {
                                            item.addEventListener('click', function() {
                                                // 移除所有选择项的选中状态
                                                document.querySelectorAll('.select-item').forEach(selectItem => {
                                                    selectItem.classList.remove('selected');
                                                });

                                                // 添加当前项的选中状态
                                                this.classList.add('selected');

                                                const id = parseInt(this.getAttribute('data-id'));
                                                const name = this.getAttribute('data-name');
                                                const shortname = this.getAttribute('data-shortname');

                                                // 添加到选择路径
                                                currentSelectPath.push({
                                                    id: id,
                                                    name: name,
                                                    shortname: shortname
                                                });

                                                // 加载下一级数据
                                                loadSelectData(id);
                                            });
                                        });
                                    } else {
                                        // 如果没有同级部门，显示当前部门
                                        contentDiv.innerHTML = `<div class="select-items"><div class="select-item selected" data-id="${selectedId}" data-name="当前部门">当前部门</div></div>`;
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('加载部门数据失败:', error);
                                contentDiv.innerHTML = `<div class="error">加载部门列表失败</div>`;
                            });
                    } else if (currentSelectPath.length > 0) {
                        // 如果不是初始加载，且已到达叶子节点，自动确认选择
                        const lastItem = currentSelectPath[currentSelectPath.length - 1];

                        let html = '';

                        // 显示当前路径
                        html += '<div class="select-path">';
                        currentSelectPath.forEach((item, index) => {
                            html += `<span class="path-item" data-id="${item.id}">${item.name}</span>`;
                            if (index < currentSelectPath.length - 1) {
                                html += ' / ';
                            }
                        });
                        html += '</div>';

                        // 显示已选择的叶子节点
                        html += '<div class="select-items">';
                        html += `<div class="select-item selected" data-id="${lastItem.id}" data-name="${lastItem.name}" data-shortname="${lastItem.shortname}">${lastItem.name}</div>`;
                        html += '</div>';

                        // 更新内容
                        contentDiv.innerHTML = html;

                        // 自动确认选择
                        setTimeout(() => {
                            confirmSelect();
                        }, 300);
                    }
                }
            })
            .catch(error => {
                contentDiv.innerHTML = `<div class="error">加载失败: ${error.message}</div>`;
            });
    }

    // 选择项
    function selectItem(id, name, shortname) {
        if (currentSelectType === 'problem-department') {
            // 更新输入框的值
            const pathStr = currentSelectPath.map(item => item.shortname || item.name).join('/');
            document.getElementById('problem-department').value = pathStr;
            document.getElementById('problem-department-id').value = id;

            // 关闭模态框
            closeSelectModal();
        }
    }

    // 重置选择
    function resetSelect() {
        currentSelectPath = [];
        loadSelectData(0);
    }

    // 确认选择
    function confirmSelect() {
        if (currentSelectPath.length > 0) {
            const lastItem = currentSelectPath[currentSelectPath.length - 1];
            selectItem(lastItem.id, lastItem.name, lastItem.shortname);
        }
    }

    // 选择默认部门（设备包保部门）
    function selectDefaultDepartment() {
        if (globalDeviceData && globalDeviceData.department_name && globalDeviceData.cid) {
            document.getElementById('problem-department').value = globalDeviceData.department_name;
            document.getElementById('problem-department-id').value = globalDeviceData.cid;
            closeSelectModal();
        }
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
            // 清空对DOM元素的引用，防止后续代码访问不存在的元素
            try {
                if (window._tempCloseBtn) {
                    window._tempCloseBtn = null;
                }
            } catch (e) {}
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

    // 初始化作业人员标签功能
    function initWorkerTags(inputId, tagsContainerId, hiddenInputId) {
        const input = document.getElementById(inputId);
        const tagsContainer = document.getElementById(tagsContainerId);
        const hiddenInput = document.getElementById(hiddenInputId);
        let tags = [];

        // 默认隐藏标签容器
        tagsContainer.style.display = 'none';

        // 加载已保存的标签（如果有）
        if (hiddenInput.value) {
            tags = hiddenInput.value.split('||');
            renderTags();
        }

        // 为输入框添加input事件监听器，用于移除错误样式
        input.addEventListener('input', function(e) {
            const inputWrapper = tagsContainer.parentElement;
            const inputLabel = document.querySelector('label[for="' + inputId + '"]');
            inputWrapper.classList.remove('error');
            if (inputLabel) {
                inputLabel.classList.remove('error');
            }
        }, {
            once: false
        });

        // 监听输入事件（用于标签功能）
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            // 检查是否输入了分隔符（包括所有中文和英文的分隔符）
            const separators = [' ', '、', ',', '，', ';', '；', '\uff0c', '\uff1b'];

            for (const separator of separators) {
                if (value.includes(separator)) {
                    const parts = value.split(separator);
                    const name = parts[0].trim();
                    if (name) {
                        addTag(name);
                        // 清空输入框，不保留任何分隔符
                        e.target.value = '';
                    }
                    break;
                }
            }
        });

        // 处理退格键事件
        input.addEventListener('keydown', function(e) {
            // 当输入框为空且按下退格键时，触发标签区域晃动提示
            if (e.key === 'Backspace' && input.value === '') {
                e.preventDefault();
                // 添加晃动动画类
                tagsContainer.classList.add('shake-animation');
                // 动画结束后移除动画类，以便下次可以再次触发
                setTimeout(() => {
                    tagsContainer.classList.remove('shake-animation');
                }, 500);
            }
        });

        // 添加标签
        function addTag(name) {
            if (!tags.includes(name)) {
                tags.push(name);
                renderTags();
                updateHiddenInput();
            }
        }

        // 移除标签
        function removeTag(index) {
            // 保存要删除的标签内容
            const removedTag = tags[index];
            // 从标签数组中移除
            tags.splice(index, 1);
            // 将删除的标签内容放回输入框
            input.value = removedTag;
            renderTags();
            updateHiddenInput();
            // 聚焦到输入框
            input.focus();
        }

        // 渲染标签
        function renderTags() {
            tagsContainer.innerHTML = '';
            tags.forEach((tag, index) => {
                const tagElement = document.createElement('span');
                tagElement.className = 'keeper-tag';
                tagElement.textContent = tag;
                // 只有点击标签本身才能删除
                tagElement.addEventListener('click', () => removeTag(index));
                tagsContainer.appendChild(tagElement);
            });

            // 当没有标签时隐藏标签容器
            if (tags.length === 0) {
                tagsContainer.style.display = 'none';
            } else {
                tagsContainer.style.display = 'flex';
            }
        }

        // 更新隐藏输入框
        function updateHiddenInput() {
            hiddenInput.value = tags.join('||');
        }

        // 清空所有标签
        function clearTags() {
            tags = [];
            renderTags();
            updateHiddenInput();
        }

        return {
            clearTags
        };
    }

    // 初始化日期时间输入框 - 使用分开的日期和时间选择器
    function initDateTimeInput(inputId) {
        // 获取原始输入框并隐藏它
        const originalInput = document.getElementById(inputId);
        originalInput.style.display = 'none';

        // 保存原始输入框的required属性和name
        const isRequired = originalInput.required;
        const originalName = originalInput.name || inputId;

        // 检查是否已经存在之前创建的选择器，如果有则先删除它们
        const existingPicker = document.getElementById(inputId + '-picker');
        const existingDateInput = document.getElementById(inputId + '-date');
        const existingTimeInput = document.getElementById(inputId + '-time');
        const existingDateTimeContainer = document.getElementById(inputId + '-container');

        // 清理可能存在的旧元素
        if (existingDateTimeContainer) {
            existingDateTimeContainer.parentNode.removeChild(existingDateTimeContainer);
        } else {
            if (existingPicker) existingPicker.parentNode.removeChild(existingPicker);
            if (existingDateInput) existingDateInput.parentNode.removeChild(existingDateInput);
            if (existingTimeInput) existingTimeInput.parentNode.removeChild(existingTimeInput);
        }

        // 设置当前时间为默认值（东八区 UTC+8）
        const now = new Date();
        const beijingTime = new Date(now.getTime() + 8 * 60 * 60 * 1000);

        // 格式化时间
        const year = beijingTime.getUTCFullYear();
        const month = String(beijingTime.getUTCMonth() + 1).padStart(2, '0');
        const day = String(beijingTime.getUTCDate()).padStart(2, '0');
        const hours = String(beijingTime.getUTCHours()).padStart(2, '0');
        const minutes = String(beijingTime.getUTCMinutes()).padStart(2, '0');
        const seconds = String(beijingTime.getUTCSeconds()).padStart(2, '0');

        const dateValue = `${year}-${month}-${day}`;
        const timeValue = `${hours}:${minutes}:${seconds}`;
        const datetimeLocalValue = `${dateValue}T${timeValue}`;

        // 创建容器来放置日期和时间选择器
        const container = document.createElement('div');
        container.id = inputId + '-container';
        container.style.display = 'flex';
        container.style.alignItems = 'center';
        container.style.gap = '10px';
        container.style.height = '44px';

        // 创建日期选择器
        const dateInput = document.createElement('input');
        dateInput.type = 'date';
        dateInput.id = inputId + '-date';
        dateInput.value = dateValue;
        dateInput.required = isRequired;

        // 添加样式
        dateInput.style.padding = '8px 10px';
        dateInput.style.border = '1px solid #ddd';
        dateInput.style.borderRadius = '4px';
        dateInput.style.fontSize = '16px';
        dateInput.style.cursor = 'pointer';

        // 添加悬停效果
        dateInput.addEventListener('mouseover', function() {
            this.style.borderColor = '#3498db';
        });
        dateInput.addEventListener('mouseout', function() {
            this.style.borderColor = '#ddd';
        });

        // 创建时间选择器，设置step="1"以支持秒级精度
        const timeInput = document.createElement('input');
        timeInput.type = 'time';
        timeInput.id = inputId + '-time';
        timeInput.value = timeValue;
        timeInput.step = '1'; // 支持秒级选择
        timeInput.required = isRequired;

        // 添加样式
        timeInput.style.padding = '8px 10px';
        timeInput.style.border = '1px solid #ddd';
        timeInput.style.borderRadius = '4px';
        timeInput.style.fontSize = '16px';
        timeInput.style.cursor = 'pointer';

        // 添加悬停效果
        timeInput.addEventListener('mouseover', function() {
            this.style.borderColor = '#3498db';
        });
        timeInput.addEventListener('mouseout', function() {
            this.style.borderColor = '#ddd';
        });

        // 将日期和时间选择器添加到容器中
        container.appendChild(dateInput);
        container.appendChild(timeInput);

        // 更新原始输入框的值
        function updateHiddenInput() {
            const dateValue = dateInput.value;
            const timeValue = timeInput.value;

            if (dateValue && timeValue) {
                originalInput.value = `${dateValue}T${timeValue}`;
            } else {
                originalInput.value = '';
            }
        }

        // 添加事件监听器，当日期或时间发生变化时更新原始输入框的值
        dateInput.addEventListener('change', updateHiddenInput);
        timeInput.addEventListener('change', updateHiddenInput);

        // 初始化原始输入框的值
        originalInput.value = datetimeLocalValue;

        // 将容器插入到原始输入框之前
        originalInput.parentNode.insertBefore(container, originalInput);
    }

    // 初始化图片上传功能
    function initImageUpload(uploadInputId, previewContainerId, hiddenInputId) {
        const uploadInput = document.getElementById(uploadInputId);
        const previewContainer = document.getElementById(previewContainerId);
        const hiddenInput = document.getElementById(hiddenInputId);
        let uploadedFiles = [];
        let currentPreviewImages = [];

        // 监听文件选择
        uploadInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files) {
                handleFiles(files);
            }
            // 清空input，允许重复选择相同文件
            uploadInput.value = '';
        });

        // 处理选择的文件
        function handleFiles(files) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    uploadedFiles.push(file);
                    previewFile(file);
                }
            }
        }

        // 预览文件
        function previewFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-image';
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function() {
                    const index = currentPreviewImages.indexOf(preview);
                    if (index !== -1) {
                        currentPreviewImages.splice(index, 1);
                        uploadedFiles.splice(index, 1);
                        previewContainer.removeChild(preview);
                    }
                });

                preview.appendChild(img);
                preview.appendChild(removeBtn);
                previewContainer.appendChild(preview);
                currentPreviewImages.push(preview);
            };
            reader.readAsDataURL(file);
        }

        // 上传文件到服务器
        async function uploadFiles() {
            if (uploadedFiles.length === 0) {
                return [];
            }

            const promises = uploadedFiles.map(async file => {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('did', document.getElementById('problem-did').value);

                try {
                    const response = await fetch('api.php?action=uploadProblemPhoto', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (data.success && data.filePath) {
                        return data.filePath;
                    }
                    return null;
                } catch (error) {
                    console.error('上传文件失败:', error);
                    return null;
                }
            });

            const results = await Promise.all(promises);
            return results.filter(Boolean); // 过滤掉上传失败的文件
        }

        // 清空所有上传的文件
        function clearFiles() {
            uploadedFiles = [];
            currentPreviewImages.forEach(preview => {
                previewContainer.removeChild(preview);
            });
            currentPreviewImages = [];
        }

        // 获取上传的文件列表
        function getFiles() {
            return [...uploadedFiles];
        }

        // 返回公共接口
        return {
            uploadFiles,
            clearFiles,
            getFiles
        };
    }

    // 打开新增记录模态框
    function openAddRecordModal(type) {
        const modal = document.getElementById('add-record-modal');
        const title = document.getElementById('modal-title');
        const recordType = document.getElementById('record-type');
        const recordDid = document.getElementById('record-did');

        // 确保记录ID设置为当前设备ID
        recordDid.value = deviceId;

        if (type === 'inspection') {
            title.textContent = '新增巡视记录';
            recordType.value = '1';
        } else if (type === 'maintenance') {
            title.textContent = '新增检修记录';
            recordType.value = '2';
        }

        // 重置错误状态样式
        const workersWrapper = document.querySelector('#workers-tags').parentElement;
        const workersLabel = document.querySelector('label[for="workers-input"]');
        if (workersWrapper) workersWrapper.classList.remove('error');
        if (workersLabel) workersLabel.classList.remove('error');

        // 初始化作业人员标签功能
        initWorkerTags('workers-input', 'workers-tags', 'workers');

        // 初始化日期时间输入框
        initDateTimeInput('work-date');

        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';
    }

    // 关闭新增记录模态框
    function closeAddRecordModal() {
        document.getElementById('add-record-modal').style.display = 'none';
        // 恢复背景页面滚动
        document.body.style.overflow = '';

        // 重置错误状态样式
        const workersWrapper = document.querySelector('#workers-tags').parentElement;
        const workersLabel = document.querySelector('label[for="workers-input"]');
        const dateInput = document.getElementById('work-date-date');
        const timeInput = document.getElementById('work-date-time');
        const workDateLabel = document.querySelector('label[for="work-date"]');

        if (workersWrapper) workersWrapper.classList.remove('error');
        if (workersLabel) workersLabel.classList.remove('error');
        if (dateInput) dateInput.classList.remove('error');
        if (timeInput) timeInput.classList.remove('error');
        if (workDateLabel) workDateLabel.classList.remove('error');

        // 清空作业人员相关输入框
        const workersInput = document.getElementById('workers-input');
        const workersHidden = document.getElementById('workers');
        const workersTags = document.getElementById('workers-tags');

        if (workersInput) workersInput.value = '';
        if (workersHidden) workersHidden.value = '';
        if (workersTags) workersTags.innerHTML = '';

        // 清空作业说明输入框
        const workRemark = document.getElementById('work-remark');
        if (workRemark) workRemark.value = '';
    }

    // 提交新增记录
    function submitAddRecord() {
        const recordType = document.getElementById('record-type').value;
        const did = document.getElementById('record-did').value;
        const workersHidden = document.getElementById('workers').value;
        const workersInput = document.getElementById('workers-input').value;
        const workDate = document.getElementById('work-date').value;
        const remark = document.getElementById('work-remark').value;

        // 合并标签中的名字和输入框中的名字，并去除重复项
        let workers = '';
        const workerNames = new Set();

        // 添加标签中的名字
        if (workersHidden) {
            workersHidden.split('||').forEach(name => {
                if (name.trim()) {
                    workerNames.add(name.trim());
                }
            });
        }

        // 添加输入框中的名字
        if (workersInput.trim()) {
            // 处理输入框中的多个名字（可能包含各种分隔符）
            const separators = [' ', '、', ',', '，', ';', '；', '||', '\uff0c', '\uff1b'];
            let names = [workersInput.trim()];

            // 使用正则表达式替换所有分隔符为统一的分隔符，然后拆分
            separators.forEach(sep => {
                // 转义特殊字符
                const escapedSep = sep.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                names = names.flatMap(name => name.split(new RegExp(escapedSep)));
            });

            // 添加到集合中（自动去重）
            names.forEach(name => {
                if (name.trim()) {
                    workerNames.add(name.trim());
                }
            });
        }

        // 转换为字符串格式
        workers = Array.from(workerNames).join('||');

        // 更新隐藏输入框的值
        document.getElementById('workers').value = workers;

        // 重置所有错误状态
        const workersWrapper = document.getElementById('workers-tags').parentElement;
        const workersLabel = document.querySelector('label[for="workers-input"]');
        const dateInput = document.getElementById('work-date-date');
        const timeInput = document.getElementById('work-date-time');
        const workDateLabel = document.querySelector('label[for="work-date"]');

        if (workersWrapper) workersWrapper.classList.remove('error');
        if (workersLabel) workersLabel.classList.remove('error');
        if (dateInput) dateInput.classList.remove('error');
        if (timeInput) timeInput.classList.remove('error');
        if (workDateLabel) workDateLabel.classList.remove('error');

        // 验证必填字段
        let hasError = false;
        const errors = [];

        if (!workers) {
            errors.push({
                elements: [workersWrapper, workersLabel]
            });
            hasError = true;
        }

        // 分别验证日期和时间
        const dateValue = document.getElementById('work-date-date').value;
        const timeValue = document.getElementById('work-date-time').value;

        if (!dateValue) {
            errors.push({
                elements: [dateInput]
            });
            hasError = true;
        }

        if (!timeValue) {
            errors.push({
                elements: [timeInput]
            });
            hasError = true;
        }

        // 如果日期或时间有一个为空，标签也显示错误
        if (!dateValue || !timeValue) {
            if (workDateLabel) {
                errors.push({
                    elements: [workDateLabel]
                });
            }
        }

        if (hasError) {
            // 使用setTimeout确保每次点击都能重新触发动画
            setTimeout(() => {
                errors.forEach(error => {
                    error.elements.forEach(element => {
                        if (element) element.classList.add('error');
                    });
                });
            }, 50);
            return;
        }

        // 根据记录类型选择不同的API action
        let apiAction = '';
        let requestData = {};

        if (recordType === '1') {
            // 巡视记录
            apiAction = 'addInspection';
            requestData = {
                did: did,
                inspector: workers, // 直接使用workers字符串
                inspection_time: workDate,
                content: remark
            };
        } else if (recordType === '2') {
            // 检修记录
            apiAction = 'addMaintenance';
            requestData = {
                did: did,
                maintainer: workers, // 直接使用workers字符串
                maintenance_time: workDate,
                content: remark
            };
        }

        // 显示加载提示框
        showLoadingIndicator('记录添加中');

        fetch('api.php?action=' + apiAction, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                // 隐藏加载提示框
                hideLoadingIndicator();

                if (data.success) {
                    closeAddRecordModal();

                    // 重新加载对应记录
                    if (recordType === '1') {
                        document.getElementById('inspection-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadDataWithPagination('inspection');
                        showNotification('已添加巡视记录！', 'success');
                    } else if (recordType === '2') {
                        document.getElementById('maintenance-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadDataWithPagination('maintenance');
                        showNotification('已添加检修记录！', 'success');
                    }
                } else {
                    alert('添加失败: ' + data.message);
                }
            })
            .catch(error => {
                // 隐藏加载提示框
                hideLoadingIndicator();
                alert('添加失败: ' + error.message);
            });
    }

    // 打开新增问题模态框
    function openAddProblemModal() {
        const modal = document.getElementById('add-problem-modal');

        // 如果有全局设备数据，设置部门信息
        if (globalDeviceData) {
            document.getElementById('problem-sid').value = globalDeviceData.sid || '';
            // 设置默认责任部门为设备包保部门
            document.getElementById('problem-department').value = globalDeviceData.department_name || '';
            document.getElementById('problem-department-id').value = globalDeviceData.cid || '';
        }

        // 重置错误状态样式
        const creatorWrapper = document.querySelector('#creator-tags').parentElement;
        const creatorLabel = document.querySelector('label[for="problem-creator-input"]');
        if (creatorWrapper) creatorWrapper.classList.remove('error');
        if (creatorLabel) creatorLabel.classList.remove('error');

        // 初始化发现人标签功能
        initWorkerTags('problem-creator-input', 'creator-tags', 'problem-creator');

        // 初始化日期时间输入框
        initDateTimeInput('problem-date');

        // 清空文件上传控件
        const fileInput = document.getElementById('problem-photos-upload');
        if (fileInput) {
            fileInput.value = '';
        }

        // 清空已上传图片预览容器
        const uploadedPhotosContainer = document.getElementById('uploaded-photos');
        if (uploadedPhotosContainer) {
            uploadedPhotosContainer.innerHTML = '';
        }

        // 初始化图片上传功能
        const imageUploader = initImageUpload('problem-photos-upload', 'uploaded-photos', 'problem-photos');

        // 保存上传器实例，方便在提交时使用
        modal.imageUploader = imageUploader;

        // 添加责任部门选择的点击事件
        document.getElementById('problem-department').addEventListener('click', function() {
            openSelectModal('problem-department', '部门');
        });

        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.body.style.overflow = 'hidden';
    }

    // 关闭新增问题模态框
    function closeAddProblemModal() {
        const modal = document.getElementById('add-problem-modal');

        // 清空已上传的文件预览
        if (modal.imageUploader) {
            modal.imageUploader.clearFiles();
        }

        modal.style.display = 'none';
        // 恢复背景页面滚动
        document.body.style.overflow = '';

        // 重置错误状态样式
        const creatorWrapper = document.querySelector('#creator-tags').parentElement;
        const creatorLabel = document.querySelector('label[for="problem-creator-input"]');
        const dateInput = document.getElementById('problem-date-date');
        const timeInput = document.getElementById('problem-date-time');
        const createTimeLabel = document.querySelector('label[for="problem-date"]');

        if (creatorWrapper) creatorWrapper.classList.remove('error');
        if (creatorLabel) creatorLabel.classList.remove('error');
        if (dateInput) dateInput.classList.remove('error');
        if (timeInput) timeInput.classList.remove('error');
        if (createTimeLabel) createTimeLabel.classList.remove('error');
    }

    // 提交新增问题 - 验证逻辑已修复
    async function submitAddProblem() {
        const did = document.getElementById('problem-did').value;
        const sid = document.getElementById('problem-sid').value;
        const description = document.getElementById('problem-description').value;
        const creatorHidden = document.getElementById('problem-creator').value;
        const creatorInput = document.getElementById('problem-creator-input').value;
        const createTime = document.getElementById('problem-date').value;
        const modal = document.getElementById('add-problem-modal');

        // 合并标签中的名字和输入框中的名字，并去除重复项
        let creator = '';
        const creatorNames = new Set();

        // 添加标签中的名字
        if (creatorHidden) {
            creatorHidden.split('||').forEach(name => {
                if (name.trim()) {
                    creatorNames.add(name.trim());
                }
            });
        }

        // 添加输入框中的名字
        if (creatorInput.trim()) {
            // 处理输入框中的多个名字（可能包含各种分隔符）
            const separators = [' ', '、', ',', '，', ';', '；', '||', '\uff0c', '\uff1b'];
            let names = [creatorInput.trim()];

            // 使用正则表达式替换所有分隔符为统一的分隔符，然后拆分
            separators.forEach(sep => {
                // 转义特殊字符
                const escapedSep = sep.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                names = names.flatMap(name => name.split(new RegExp(escapedSep)));
            });

            // 添加到集合中（自动去重）
            names.forEach(name => {
                if (name.trim()) {
                    creatorNames.add(name.trim());
                }
            });
        }

        // 转换为字符串格式
        creator = Array.from(creatorNames).join('||');

        // 更新隐藏输入框的值
        document.getElementById('problem-creator').value = creator;

        // 重置所有错误状态
        const creatorWrapper = document.getElementById('creator-tags').parentElement;
        const creatorLabel = document.querySelector('label[for="problem-creator-input"]');
        const dateInput = document.getElementById('problem-date-date');
        const timeInput = document.getElementById('problem-date-time');
        const createTimeLabel = document.querySelector('label[for="problem-date"]');

        if (creatorWrapper) creatorWrapper.classList.remove('error');
        if (creatorLabel) creatorLabel.classList.remove('error');
        if (dateInput) dateInput.classList.remove('error');
        if (timeInput) timeInput.classList.remove('error');
        if (createTimeLabel) createTimeLabel.classList.remove('error');

        // 重置所有错误状态
        const descriptionInput = document.getElementById('problem-description');
        const descriptionLabel = document.querySelector('label[for="problem-description"]');
        const departmentContainer = document.querySelector('#problem-department').parentElement;
        const departmentLabel = document.querySelector('label[for="problem-department"]');

        if (descriptionInput) descriptionInput.classList.remove('error');
        if (descriptionLabel) descriptionLabel.classList.remove('error');
        if (departmentContainer) departmentContainer.classList.remove('error');
        if (departmentLabel) departmentLabel.classList.remove('error');

        let hasError = false;
        const errors = [];

        // 验证问题描述
        if (!description) {
            errors.push({
                elements: [descriptionInput, descriptionLabel]
            });
            hasError = true;
        }

        // 验证发现人
        if (!creator) {
            errors.push({
                elements: [creatorWrapper, creatorLabel]
            });
            hasError = true;
        }

        // 分别验证日期和时间
        const dateValue = document.getElementById('problem-date-date').value;
        const timeValue = document.getElementById('problem-date-time').value;

        if (!dateValue) {
            errors.push({
                elements: [dateInput]
            });
            hasError = true;
        }

        if (!timeValue) {
            errors.push({
                elements: [timeInput]
            });
            hasError = true;
        }

        // 如果日期或时间有一个为空，标签也显示错误
        if (!dateValue || !timeValue) {
            if (createTimeLabel) {
                errors.push({
                    elements: [createTimeLabel]
                });
            }
        }

        // 验证责任部门
        const departmentId = document.getElementById('problem-department-id').value;
        if (!departmentId) {
            errors.push({
                elements: [departmentContainer, departmentLabel]
            });
            hasError = true;
        }

        if (hasError) {
            // 使用setTimeout确保每次点击都能重新触发动画
            setTimeout(() => {
                errors.forEach(error => {
                    error.elements.forEach(element => {
                        if (element) element.classList.add('error');
                    });
                });
            }, 50);
            return;
        }

        // 构造表单数据 - 按照实际数据表结构存储
        const formData = new FormData();
        formData.append('did', did);
        formData.append('reporter', creator); // API使用reporter参数
        formData.append('report_time', createTime); // API使用report_time参数
        formData.append('description', description);
        // 注意：根据实际数据表结构，不需要传递urgency字段
        // process字段由API端根据resolver是否为空动态生成，不需要在此处传递

        // 添加责任部门ID (使用已声明的departmentId变量)
        formData.append('department_id', departmentId);

        // 处理照片上传（如果有）
        let hasImages = false;
        if (modal.imageUploader) {
            const uploadedFiles = modal.imageUploader.getFiles();
            hasImages = uploadedFiles.length > 0;
            if (hasImages) {
                uploadedFiles.forEach((file, index) => {
                    formData.append(`photos[${index}]`, file);
                });
            }
        }

        // 显示加载提示框
        const loadingMessage = hasImages ? '图片上传中' : '问题录入中';
        showLoadingIndicator(loadingMessage);

        fetch('api.php?action=addProblem', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // 隐藏加载提示框
                hideLoadingIndicator();

                if (data.success) {
                    // 清空问题描述和发现人输入框
                    document.getElementById('problem-description').value = '';
                    document.getElementById('problem-creator-input').value = '';
                    document.getElementById('problem-creator').value = '';

                    // 清空发现人标签显示
                    const creatorTags = document.getElementById('creator-tags');
                    if (creatorTags) {
                        creatorTags.innerHTML = '';
                    }

                    closeAddProblemModal();

                    // 重新加载问题记录
                    document.getElementById('problem-content').innerHTML = '<div class="loading">加载中...</div>';
                    loadDataWithPagination('problems');

                    showNotification('已添加问题记录！', 'success');
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

        switch (type) {
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
                    showNotification('已删除1条记录！', 'delete');

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
        notification.style.top = '80px'; // 确保低于header导航栏的高度
        notification.style.right = '-400px'; // 初始位置在右侧屏幕外
        // 根据通知类型设置不同的背景色
        if (type === 'success') {
            notification.style.backgroundColor = '#27ae60'; // 绿色 - 用于添加操作
        } else if (type === 'delete') {
            notification.style.backgroundColor = '#e74c3c'; // 红色 - 用于删除操作
        } else {
            notification.style.backgroundColor = '#e74c3c'; // 红色 - 用于错误信息
        }
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
            notification.style.right = '20px'; // 最终位置
        }, 10);

        // 3秒后隐藏通知 - 滑回右侧
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.right = '-400px'; // 回到初始位置

            // 动画结束后移除元素
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 500);
        }, 3000);
    }

    // 显示全屏加载提示框
    function showLoadingIndicator(message) {
        // 移除已存在的加载提示
        const existingLoader = document.querySelector('.loading-overlay');
        if (existingLoader) {
            document.body.removeChild(existingLoader);
        }

        // 创建遮罩层
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        overlay.style.display = 'flex';
        overlay.style.justifyContent = 'center';
        overlay.style.alignItems = 'center';
        overlay.style.zIndex = '10001'; // 确保在最上层
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        // 创建加载提示框
        const loader = document.createElement('div');
        loader.className = 'loading-indicator';
        loader.style.backgroundColor = 'white';
        loader.style.padding = '30px';
        loader.style.borderRadius = '8px';
        loader.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.3)';
        loader.style.display = 'flex';
        loader.style.flexDirection = 'column';
        loader.style.alignItems = 'center';
        loader.style.gap = '15px';

        // 创建旋转动画元素
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner';
        spinner.style.width = '30px';
        spinner.style.height = '30px';
        spinner.style.border = '3px solid #f3f3f3';
        spinner.style.borderTop = '3px solid #3498db';
        spinner.style.borderRadius = '50%';
        spinner.style.animation = 'spin 1s linear infinite';

        // 创建消息文本
        const messageEl = document.createElement('div');
        messageEl.className = 'loading-message';
        messageEl.style.fontSize = '14px';
        messageEl.style.color = '#333';
        messageEl.textContent = message;

        // 添加到提示框
        loader.appendChild(spinner);
        loader.appendChild(messageEl);
        overlay.appendChild(loader);
        document.body.appendChild(overlay);

        // 显示加载提示（添加动画）
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);

        // 添加旋转动画样式
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }

    // 隐藏全屏加载提示框
    function hideLoadingIndicator() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                if (overlay.parentNode) {
                    document.body.removeChild(overlay);
                }
            }, 300);
        }
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
        background-color: #e0f2fe;
        color: #1976d2;
        padding: 4px 10px;
        margin-right: 8px;
        margin-bottom: 4px;
        border-radius: 4px;
        display: inline-block;
    }

    /* 左右晃动动画 */
    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        20%,
        60% {
            transform: translateX(-4px);
        }

        40%,
        80% {
            transform: translateX(4px);
        }
    }

    .shake-animation {
        animation: shake 0.5s ease-in-out;
    }

    /* 作业人员标签样式 */
    .workers-input-wrapper {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 38px;
        transition: border-color 0.3s ease;
    }

    /* 错误状态样式 */
    .workers-input-wrapper.error {
        border-color: #f44336;
        animation: shake 0.5s ease-in-out;
    }

    /* 摇摆动画 */
    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    /* 错误状态的标签文字 */
    label.error {
        color: #f44336;
    }

    /* 必填项标记 */
    .required {
        color: #e74c3c;
    }

    /* 上传提示文字样式 */
    .upload-note {
        font-size: 12px;
        color: #666;
        margin: 0;
    }

    /* 日期和时间输入框的错误状态 */
    input[type="date"].error,
    input[type="time"].error {
        border-color: #f44336;
        animation: shake 0.5s ease-in-out;
    }

    /* 文本域的错误状态 */
    textarea.error {
        border-color: #f44336;
        animation: shake 0.5s ease-in-out;
    }

    /* 责任部门输入框的错误状态 */
    .select-container.error input {
        border-color: #f44336;
        animation: shake 0.5s ease-in-out;
    }

    .workers-tags {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-right: 8px;
        flex-grow: 1;
    }

    .workers-input-wrapper .keeper-tag {
        background-color: #e0f2fe;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 14px;
        cursor: pointer;
        user-select: none;
        transition: all 0.3s ease;
    }

    .workers-input-wrapper .keeper-tag:hover {
        background-color: #bae6fd;
        color: #0369a1;
        transform: scale(1.05);
    }

    .tag-input {
        border: none;
        outline: none;
        flex-grow: 1;
        min-width: 100px;
        font-size: 14px;
    }

    /* 日期时间输入框样式 */
    input[type="datetime-local"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        box-sizing: border-box;
    }

    input[type="datetime-local"]:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    /* 图片上传样式 */
    .image-upload-container {
        margin-top: 15px;
    }

    .upload-button {
        background-color: #f0f0f0;
        color: #333;
        padding: 10px 20px;
        border: 1px dashed #ccc;
        border-radius: 4px;
        cursor: pointer;
        display: inline-block;
        transition: background-color 0.3s;
    }

    .upload-button:hover {
        background-color: #e0e0e0;
    }

    .upload-button input[type="file"] {
        display: none;
    }

    .image-previews {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 15px;
    }

    .image-preview {
        position: relative;
        width: 100%;
        height: 150px;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(255, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .remove-image:hover {
        background-color: rgba(255, 0, 0, 0.9);
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
        overflow-x: auto;
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
        margin-right: 10px;
    }

    .qrcode-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    /* 窄屏设备（手机）上的按钮垂直排列 */
    @media (max-width: 768px) {
        .action-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .edit-btn {
            margin-right: 0;
            order: 2;
            /* 修改按钮在下方 */
        }

        .qrcode-btn {
            order: 1;
            /* 设备码按钮在上方 */
        }
    }

    .edit-btn:hover {
        background-color: #e67e22;
    }

    .drawings-table,
    .records-table {
        width: 100%;
        border-collapse: collapse;
    }

    /* 问题记录列表固定宽度 */
    .problems-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        /* 自动表格布局，适应内容 */
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

    .status-tag {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: normal;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        min-width: 45px;
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

    /* 表格行悬浮或点击时状态标签变色 */
    .problems-table tr:hover .status-red,
    .problems-table tr:active .status-red {
        background-color: #e74c3c;
        color: white;
    }

    .problems-table tr:hover .status-green,
    .problems-table tr:active .status-green {
        background-color: #27ae60;
        color: white;
    }

    .no-result {
        text-align: center;
        color: #999;
        padding: 20px 0;
    }

    .error {
        border-color: #f44336 !important;
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
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
    }

    .cancel-btn:hover {
        opacity: 0.9;
    }

    .confirm-btn:hover {
        background-color: #45a049;
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
        gap: 4px;
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
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin-left: 0px;
        margin-bottom: 0px;
    }

    .record-count-number {
        background-color: #fef2df;
        color: #f39c12;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 10px !important;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .record-count-number:hover {
        background-color: #f39c12;
        color: white;
        transform: scale(1.05);
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

    /* 激活状态的页码按钮在鼠标悬浮时保持原有背景色，只改变边框颜色 */
    .pagination-btn.active:hover:not(:disabled) {
        background-color: #3498db;
        color: white;
        border-color: #2980b9;
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

    /* 作业人员标签样式 */
    .workers-input-wrapper {
        display: block;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 38px;
        transition: border-color 0.3s ease;
    }

    /* 错误状态样式 */
    .workers-input-wrapper.error {
        border-color: #f44336;
        animation: shake 0.5s ease-in-out;
    }

    /* 摇摆动画 */
    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-5px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(5px);
        }
    }

    /* 错误状态的标签文字 */
    label.error {
        color: #f44336;
    }

    .workers-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
        min-height: 22px;
    }

    .keeper-tag {
        background-color: #e0f2fe;
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .keeper-tag:hover {
        background-color: #1976d2;
        color: white;
        transform: scale(1.05);
    }

    .workers-input {
        width: 100%;
        border: none;
        outline: none;
        font-size: 14px;
        padding: 4px 0;
    }

    /* 确保标签在窄屏上也能正常显示 */
    @media (max-width: 768px) {
        .workers-input-wrapper {
            padding: 6px;
        }

        .keeper-tag {
            background-color: #e0f2fe;
            color: #1976d2;
            padding: 3px 10px;
            font-size: 12px;
        }

        .workers-input {
            width: 100%;
            font-size: 12px;
        }
    }
</style>
<script>
    // 分页状态变量
    const paginationStates = {
        drawings: {
            currentPage: 1,
            pageSize: 5
        },
        inspection: {
            currentPage: 1,
            pageSize: 5
        },
        maintenance: {
            currentPage: 1,
            pageSize: 5
        },
        problems: {
            currentPage: 1,
            pageSize: 5
        }
    };

    // 添加分页控件
    function addPaginationControls(total, currentPage, pageSize, type) {
        // 先移除已存在的分页控件
        removePaginationControls(type);

        // 只有在总数大于5的时候才显示控件
        if (total <= 5) {
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
        // 类型到内容区域ID的映射，解决problems类型使用problem-content的问题
        const typeToContentId = {
            'problems': 'problem-content',
            'maintenance': 'maintenance-content',
            'drawings': 'drawings-content',
            'inspection': 'inspection-content'
        };

        const contentId = typeToContentId[type] || (type + '-content');
        const contentDiv = document.getElementById(contentId);

        if (contentDiv) {
            contentDiv.appendChild(paginationContainer);
        } else {
            console.error('Content element not found:', contentId);
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
                // 更新加载状态
                dataCache.drawings.loaded = false;
                loadDrawings(page, pageSize).then(() => {
                    // 加载完成后的处理可以在这里添加
                }).catch(error => {
                    console.error('加载图纸失败:', error);
                });
                break;
            case 'inspection':
                // 更新加载状态
                dataCache.inspection.loaded = false;
                loadInspectionRecords(page, pageSize).then(() => {
                    // 加载完成后的处理可以在这里添加
                }).catch(error => {
                    console.error('加载巡视记录失败:', error);
                });
                break;
            case 'maintenance':
                // 更新加载状态
                dataCache.maintenance.loaded = false;
                loadMaintenanceRecords(page, pageSize).then(() => {
                    // 加载完成后的处理可以在这里添加
                }).catch(error => {
                    console.error('加载检修记录失败:', error);
                });
                break;
            case 'problems':
                // 更新加载状态
                dataCache.problems.loaded = false;
                loadProblemRecords(page, pageSize).then(() => {
                    // 加载完成后的处理可以在这里添加
                }).catch(error => {
                    console.error('加载问题记录失败:', error);
                });
                break;
        }
    }

    // 加载图纸（支持分页） - 平滑加载版本
    function loadDrawings(page = 1, pageSize = 5) {
        return new Promise((resolve, reject) => {
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
                            if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'].includes(fileExtension)) {
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

                    // 更新缓存状态
                    dataCache.drawings.loaded = true;
                    resolve(data);
                })
                .catch(error => {
                    const content = document.getElementById('drawings-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls('drawings');

                    // 更新缓存状态
                    dataCache.drawings.loaded = false;
                    reject(error);
                });
        });
    }

    // 加载巡视记录（支持分页） - 平滑加载版本
    function loadInspectionRecords(page = 1, pageSize = 5) {
        return new Promise((resolve, reject) => {
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
                        const numberElement = countElement.querySelector('.record-count-number');
                        if (numberElement) {
                            numberElement.textContent = total;
                        }
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

                    // 更新缓存状态
                    dataCache.inspection.loaded = true;
                    resolve(data);
                })
                .catch(error => {
                    const content = document.getElementById('inspection-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls('inspection');

                    // 更新缓存状态
                    dataCache.inspection.loaded = false;
                    reject(error);
                });
        });
    }

    // 加载检修记录（支持分页） - 平滑加载版本
    function loadMaintenanceRecords(page = 1, pageSize = 5) {
        return new Promise((resolve, reject) => {
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
                        const numberElement = countElement.querySelector('.record-count-number');
                        if (numberElement) {
                            numberElement.textContent = total;
                        }
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

                    // 更新缓存状态
                    dataCache.maintenance.loaded = true;
                    resolve(data);
                })
                .catch(error => {
                    const content = document.getElementById('maintenance-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls('maintenance');

                    // 更新缓存状态
                    dataCache.maintenance.loaded = false;
                    reject(error);
                });
        });
    }

    // 加载问题记录（仅支持设备ID和分页）
    function loadProblemRecords(page = 1, pageSize = 5) {
        return new Promise((resolve, reject) => {
            const content = document.getElementById('problem-content');

            let url = `api.php?action=getProblems&did=<?php echo $did; ?>`;

            // 添加分页参数
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
                    // 检查数据是否包含success字段和data字段
                    if (!data.success) {
                        throw new Error(data.message || '加载问题记录失败');
                    }

                    // 检查数据是否包含total字段（分页模式）
                    const hasPagination = data.total !== undefined && data.data !== undefined;
                    const problems = hasPagination ? data.data : data.data || [];
                    const total = hasPagination ? data.total : problems.length;

                    // 更新标题计数
                    const countElement = document.getElementById('problem-count');
                    if (countElement) {
                        const numberElement = countElement.querySelector('.record-count-number');
                        if (numberElement) {
                            numberElement.textContent = total;
                        }
                    }

                    if (problems.length > 0) {
                        let html = '<table class="problems-table">';
                        // 移除发现时间列，为问题描述列留出更多空间
                        html += '<thead><tr><th style="min-width: 50px; width: 50px;">序号</th><th style="width: auto; overflow: hidden; text-overflow: ellipsis;">问题描述</th><th style="min-width: 60px; width: 60px;">状态</th></tr></thead>';
                        html += '<tbody>';

                        problems.forEach((problem, index) => {
                            const statusClass = problem.process === 0 ? 'status-red' : 'status-green';
                            const statusText = problem.process === 0 ? '已创建' : '已闭环';

                            // 计算正确的序号（考虑分页）
                            const serialNumber = pageSize === 'all' ? index + 1 : (page - 1) * pageSize + index + 1;

                            // 将发现时间和解决时间作为数据属性存储，用于悬浮提示
                            const reportTime = problem.report_time || '';
                            const resolutionTime = problem.resolution_time || '';
                            html += `<tr data-report-time="${reportTime}" data-resolution-time="${resolutionTime}" data-status="${problem.process}"><td>${serialNumber}</td><td><a href="problems.php?pid=${problem.pid}">${problem.description}</a></td><td><span class="status-tag ${statusClass}">${statusText}</span></td></tr>`;
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

                    // 更新缓存状态
                    dataCache.problems.loaded = true;
                    resolve(data);
                })
                .catch(error => {
                    const content = document.getElementById('problem-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls('problems');

                    // 更新缓存状态
                    dataCache.problems.loaded = false;
                    reject(error);
                });
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

    // 添加问题记录行的悬浮提示功能
    function initProblemTooltip() {
        // 存储所有气泡及其关联的行
        const tooltipsMap = new Map();
        // 存储所有气泡元素的集合
        const allTooltips = new Set();

        // 创建新气泡的函数
        function createTooltip() {
            // 创建气泡容器
            const tooltipContainer = document.createElement('div');
            tooltipContainer.className = 'problem-tooltip-container';
            tooltipContainer.style.cssText = `
                position: fixed;
                z-index: 1000;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            `;

            // 创建气泡内容
            const tooltip = document.createElement('div');
            tooltip.className = 'problem-tooltip';
            tooltip.style.cssText = `
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                font-family: monospace; /* 使用等宽字体，确保所有字符宽度一致 */
                white-space: nowrap;
                position: relative;
                min-width: 230px; /* 再次增加最小宽度，确保能容纳任何日期时间格式 */
                text-align: center;
                width: auto;
                box-sizing: border-box;
            `;

            // 创建文本容器
            const tooltipText = document.createElement('span');
            tooltipText.className = 'problem-tooltip-text';
            tooltip.appendChild(tooltipText);

            // 创建尖角
            const tooltipArrow = document.createElement('div');
            tooltipArrow.className = 'problem-tooltip-arrow';
            tooltipArrow.style.cssText = `
                position: absolute;
                bottom: -5px;
                left: 50%;
                transform: translateX(-50%);
                width: 0;
                height: 0;
                border-left: 5px solid transparent;
                border-right: 5px solid transparent;
                border-top: 5px solid rgba(0, 0, 0, 0.8);
                pointer-events: none;
            `;

            tooltip.appendChild(tooltipArrow);
            tooltipContainer.appendChild(tooltip);
            document.body.appendChild(tooltipContainer);
            allTooltips.add(tooltipContainer);

            return tooltipContainer;
        }

        // 更新提示位置（固定在状态标签上方）
        function updateTooltipPosition(tooltipContainer, row) {
            // 找到状态标签元素
            const statusTag = row.querySelector('.status-tag');
            if (!statusTag) return;

            const tooltip = tooltipContainer.querySelector('.problem-tooltip');

            // 确保tooltip元素已经渲染
            if (!tooltip.offsetWidth) {
                // 如果还没渲染，强制显示一下以获取尺寸
                const originalVisibility = tooltip.style.visibility;
                const originalDisplay = tooltipContainer.style.display;
                tooltip.style.visibility = 'hidden';
                tooltipContainer.style.display = 'block';

                // 强制重排
                void tooltip.offsetWidth;

                tooltip.style.visibility = originalVisibility;
                tooltipContainer.style.display = originalDisplay;
            }

            // 获取状态标签和气泡的位置信息
            const statusRect = statusTag.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();

            // 计算水平居中位置
            let left = statusRect.left + statusRect.width / 2 - tooltipRect.width / 2;

            // 确保气泡不会超出视口
            if (left < 0) left = 0;
            if (left + tooltipRect.width > window.innerWidth) left = window.innerWidth - tooltipRect.width;

            // 设置最终位置
            tooltipContainer.style.left = left + 'px';
            tooltipContainer.style.top = (statusRect.top - tooltipRect.height - 10) + 'px'; // 显示在状态标签上方，不遮挡行内容
        }

        // 显示气泡的函数
        function showTooltip(tooltipContainer, row) {
            const reportTime = row.dataset.reportTime;
            const resolutionTime = row.dataset.resolutionTime;
            const status = row.dataset.status;
            const tooltip = tooltipContainer.querySelector('.problem-tooltip');
            const tooltipText = tooltipContainer.querySelector('.problem-tooltip-text');

            // 构建气泡文本内容
            let tooltipContent = '发现时间: ' + reportTime;
            // 如果是已闭环状态且有解决时间，则添加解决时间显示
            if (status === '1' && resolutionTime && resolutionTime.trim() !== '') {
                tooltipContent += '\n解决时间: ' + resolutionTime;
                // 为多行文本更新样式
                tooltip.style.whiteSpace = 'pre-line';
                tooltip.style.padding = '8px 10px';
            } else {
                // 恢复单行样式
                tooltip.style.whiteSpace = 'nowrap';
                tooltip.style.padding = '5px 10px';
            }

            tooltipText.textContent = tooltipContent;

            // 定位到状态标签上方
            updateTooltipPosition(tooltipContainer, row);

            // 显示气泡
            tooltipContainer.style.display = 'block';
            // 使用setTimeout确保浏览器有时间处理样式变化
            setTimeout(() => {
                tooltipContainer.style.opacity = '1';
            }, 10);
        }

        // 隐藏气泡的函数
        function hideTooltip(tooltipContainer) {
            tooltipContainer.style.opacity = '0';
            setTimeout(() => {
                tooltipContainer.style.display = 'none';
            }, 200);
        }

        // 移除所有气泡的函数
        function removeAllTooltips() {
            allTooltips.forEach(tooltipContainer => {
                tooltipContainer.style.display = 'none';
                if (tooltipContainer.parentNode) {
                    tooltipContainer.parentNode.removeChild(tooltipContainer);
                }
            });
            allTooltips.clear();
            tooltipsMap.clear();
        }

        // 点击行显示/隐藏气泡
        document.addEventListener('click', function(e) {
            const row = e.target.closest('.problems-table tr');

            // 如果点击的是有效行
            if (row && row.dataset.reportTime) {
                // 检查是否已有该行列的气泡
                const existingTooltip = tooltipsMap.get(row);

                if (existingTooltip) {
                    // 如果已有气泡，则隐藏它
                    hideTooltip(existingTooltip);
                    tooltipsMap.delete(row);
                } else {
                    // 如果没有气泡，则创建并显示新气泡
                    const newTooltip = createTooltip();
                    tooltipsMap.set(row, newTooltip);
                    showTooltip(newTooltip, row);
                }
            }
        });

        // 监听分页控件点击，移除所有气泡
        document.addEventListener('click', function(e) {
            // 检测是否点击了分页控件
            if (e.target.closest('.pagination-container') ||
                e.target.closest('.pagination-btn') ||
                e.target.closest('.page-size-select')) {
                removeAllTooltips();
            }
        });

        // 页面滚动时更新所有气泡位置
        function handleScroll() {
            tooltipsMap.forEach((tooltipContainer, row) => {
                if (tooltipContainer.style.display !== 'none') {
                    updateTooltipPosition(tooltipContainer, row);
                }
            });
        }

        // 添加滚动事件监听器
        window.addEventListener('scroll', handleScroll);

        // 添加窗口大小改变事件监听器
        window.addEventListener('resize', handleScroll);

        // 导出清除所有气泡的函数，以便在其他地方调用
        window.clearProblemTooltips = removeAllTooltips;
    }

    // 页面加载完成后初始化悬浮提示功能
    document.addEventListener('DOMContentLoaded', initProblemTooltip);
</script>

<!-- 设备码浮窗 -->
<div id="qrcode-modal" class="modal" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-content" style="width: auto; display: inline-block; text-align: center;">
        <div class="modal-header">
            <h3>设备二维码</h3>
            <button type="button" class="close-btn" onclick="closeQRCodeModal()">×</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div id="qrcode-container" style="margin-bottom: 15px; display: flex; flex-direction: column; align-items: center;">
                <div id="qrcode-loading" class="loading" style="padding: 30px 0;">生成中...</div>
                <!-- 使用canvas元素来合并显示二维码、设备ID和设备名称 -->
                <canvas id="qrcode-combined" style="display: none;"></canvas>
            </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="cancel-btn" onclick="downloadQRCode()">下载二维码</button>
            <button type="button" class="cancel-btn" onclick="closeQRCodeModal()">关闭</button>
        </div>
    </div>
</div>

<script>
    // 存储当前显示的设备信息，用于下载功能
    let currentDeviceInfo = {
        did: '',
        name: ''
    };

    // 显示设备二维码
    function showDeviceQRCode() {
        // 获取全局定义的设备ID
        const did = deviceId; // 使用全局变量

        // 先获取设备名称
        let deviceName = '设备名称加载中...';

        // 显示加载动画
        document.getElementById('qrcode-loading').style.display = 'block';
        document.getElementById('qrcode-combined').style.display = 'none';

        // 显示浮窗
        const modal = document.getElementById('qrcode-modal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // 如果有全局设备数据，使用它；否则通过API获取
        if (window.globalDeviceData && window.globalDeviceData.device_name) {
            deviceName = window.globalDeviceData.device_name;
            currentDeviceInfo = {
                did,
                name: deviceName
            };
            generateCombinedQRCode(did, deviceName);
        } else {
            // 通过API获取设备名称
            fetch(`api.php?action=getDeviceDetail&did=${did}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data) {
                        deviceName = result.data.device_name || '未知设备';
                        currentDeviceInfo = {
                            did,
                            name: deviceName
                        };
                        generateCombinedQRCode(did, deviceName);
                    } else {
                        deviceName = '获取设备名称失败';
                        currentDeviceInfo = {
                            did,
                            name: deviceName
                        };
                        generateCombinedQRCode(did, deviceName);
                    }
                })
                .catch(error => {
                    console.error('获取设备信息失败:', error);
                    deviceName = '获取设备信息失败';
                    currentDeviceInfo = {
                        did,
                        name: deviceName
                    };
                    generateCombinedQRCode(did, deviceName);
                });
        }
    }

    // 下载二维码图片
    function downloadQRCode() {
        const canvas = document.getElementById('qrcode-combined');

        if (!canvas || canvas.style.display === 'none') {
            // alert('二维码尚未生成，请稍候再试');
            return;
        }

        // 从currentDeviceInfo获取设备信息
        const {
            did,
            name
        } = currentDeviceInfo;

        // 格式化文件名：qr_did_name，移除可能的特殊字符
        const safeName = name.replace(/[^a-zA-Z0-9一-龥]/g, '_');
        const fileName = `qr_${did}_${safeName}.png`;

        // 创建下载链接
        const link = document.createElement('a');
        link.download = fileName;
        link.href = canvas.toDataURL('image/png');

        // 模拟点击下载
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // 关闭设备二维码浮窗
    function closeQRCodeModal() {
        const modal = document.getElementById('qrcode-modal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    // 生成合并后的二维码图片（包含二维码、设备ID和设备名称）
    function generateCombinedQRCode(did, deviceName) {
        // 模拟加载过程
        setTimeout(() => {
            try {
                // 创建临时canvas用于生成二维码
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = 200;
                tempCanvas.height = 200;

                // 确保did是字符串并使用qrcode.js库生成二维码
                const dataToEncode = String(did); // 确保是字符串类型
                console.log('编码二维码数据:', dataToEncode);

                // 检查qrcode库是否加载
                if (typeof QRCode === 'undefined') {
                    console.error('QRCode库未加载');
                    document.getElementById('qrcode-loading').textContent = '二维码库未加载';
                    document.getElementById('qrcode-loading').style.color = '#ff6b6b';
                    return;
                }

                // 使用qrcode.js库生成二维码
                QRCode.toCanvas(tempCanvas, dataToEncode, {
                    margin: 1
                }, function(error) {
                    if (error) {
                        document.getElementById('qrcode-loading').textContent = '二维码生成失败';
                        document.getElementById('qrcode-loading').style.color = '#ff6b6b';
                        console.error('QRCode生成错误:', error);
                        return;
                    }

                    // 获取合并显示的主canvas
                    const mainCanvas = document.getElementById('qrcode-combined');
                    const ctx = mainCanvas.getContext('2d');

                    // 计算主canvas的尺寸（调整为合理布局）
                    const qrCodeSize = 200;
                    const lineSpacing = 20; // 增加行间距，避免编号和名称重叠
                    const padding = 10;

                    mainCanvas.width = qrCodeSize + padding * 2;
                    // 调整高度，使下边缘与设备名称平齐
                    mainCanvas.height = qrCodeSize + lineSpacing * 2 + padding * 2;

                    // 填充白色背景
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, mainCanvas.width, mainCanvas.height);

                    // 绘制二维码
                    ctx.drawImage(tempCanvas, padding, padding, qrCodeSize, qrCodeSize);

                    // 绘制设备ID（靠近二维码）
                    ctx.font = 'bold 16px Arial';
                    ctx.fillStyle = '#333333';
                    ctx.textAlign = 'center';
                    ctx.fillText(did, mainCanvas.width / 2, qrCodeSize + padding + 10);

                    // 绘制设备名称（可能需要处理多行）
                    ctx.font = '14px Arial';
                    ctx.fillStyle = '#555555';

                    // 简单的文本换行处理
                    const maxWidth = mainCanvas.width - padding * 4;
                    const words = deviceName.split(' ');
                    let line = '';
                    let y = qrCodeSize + padding + lineSpacing + 8;

                    for (let n = 0; n < words.length; n++) {
                        const testLine = line + words[n] + ' ';
                        const metrics = ctx.measureText(testLine);
                        const testWidth = metrics.width;

                        if (testWidth > maxWidth && n > 0) {
                            ctx.fillText(line, mainCanvas.width / 2, y);
                            line = words[n] + ' ';
                            y += 20;
                        } else {
                            line = testLine;
                        }
                    }
                    ctx.fillText(line, mainCanvas.width / 2, y);

                    // 显示合并后的canvas
                    document.getElementById('qrcode-combined').style.display = 'block';
                    document.getElementById('qrcode-loading').style.display = 'none';

                    // 将下载按钮变为蓝色 - 使用更可靠的选择方式
                    // 先尝试通过按钮文本内容查找
                    const buttons = document.querySelectorAll('.modal-footer .cancel-btn');
                    let downloadBtn = null;
                    for (let i = 0; i < buttons.length; i++) {
                        if (buttons[i].textContent.trim() === '下载二维码') {
                            downloadBtn = buttons[i];
                            break;
                        }
                    }

                    // 如果找到了下载按钮，设置样式
                    if (downloadBtn) {
                        downloadBtn.style.backgroundColor = '#1677ff';
                        downloadBtn.style.color = '#ffffff';
                        downloadBtn.style.borderColor = '#1677ff';
                    } else {
                        console.log('未找到下载按钮');
                    }

                    // 在二维码正中间绘制logo图标
                    const logoImg = new Image();
                    logoImg.src = '/files/logo.svg';
                    logoImg.onload = function() {
                        // 计算logo大小（不超过二维码的20%，避免影响识别）
                        const logoSize = qrCodeSize * 0.2;
                        const logoX = padding + (qrCodeSize - logoSize) / 2;
                        const logoY = padding + (qrCodeSize - logoSize) / 2;

                        // 添加一个小的白色背景，让logo更清晰可见
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(logoX - 2, logoY - 2, logoSize + 4, logoSize + 4);

                        // 绘制logo
                        ctx.drawImage(logoImg, logoX, logoY, logoSize, logoSize);

                        // logo绘制完成后再保存到服务器
                        saveQRCodeToServer(did, deviceName);
                    };
                    logoImg.onerror = function() {
                        console.error('无法加载logo图标');
                        // 即使logo加载失败，也保存二维码（不含logo）
                        saveQRCodeToServer(did, deviceName);
                    };
                });
            } catch (error) {
                document.getElementById('qrcode-loading').textContent = '二维码生成失败';
                document.getElementById('qrcode-loading').style.color = '#ff6b6b';
                console.error('合并图片生成错误:', error);
            }
        }, 500); // 添加延迟以模拟生成过程
    }

    // 为设备码浮窗添加点击背景关闭功能
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('qrcode-modal');
        if (modal) {
            modal.onclick = function(e) {
                if (e.target === modal) {
                    closeQRCodeModal();
                }
            };
        }

        // 为canvas添加点击事件
        const qrCodeCanvas = document.getElementById('qrcode-combined');
        if (qrCodeCanvas) {
            qrCodeCanvas.onclick = function() {
                copyQRCodeImageLink();
            };
            // 添加鼠标悬停效果，提示可以点击复制
            qrCodeCanvas.style.cursor = 'pointer';
        }
    });

    // 复制二维码图片链接
    function copyQRCodeImageLink() {
        const {
            did
        } = currentDeviceInfo;
        const filePath = localStorage.getItem(`qrCodePath_${did}`);

        // 如果有服务器保存的文件路径，优先使用该路径
        if (filePath) {
            // 获取当前网站域名
            const domain = window.location.origin;
            // 组合成完整的URL：网站域名/uploads/qrcode/qr_did_name.png
            const fullUrl = domain + filePath;

            // 使用Clipboard API复制链接
            navigator.clipboard.writeText(fullUrl).then(function() {
                showNotification('二维码链接已复制！');
            }).catch(function(err) {
                console.error('复制失败:', err);
                // 降级方案：使用传统方法创建文本区域并复制
                fallbackCopyTextToClipboard(fullUrl);
            });
        } else {
            // 降级方案：使用canvas生成DataURL
            const canvas = document.getElementById('qrcode-combined');
            if (!canvas) {
                console.error('未找到二维码canvas元素');
                return;
            }

            try {
                // 将canvas转换为DataURL
                const imageUrl = canvas.toDataURL('image/png');

                // 使用Clipboard API复制链接
                navigator.clipboard.writeText(imageUrl).then(function() {
                    showNotification('二维码链接已复制！');
                }).catch(function(err) {
                    console.error('复制失败:', err);
                    // 降级方案：使用传统方法创建文本区域并复制
                    fallbackCopyTextToClipboard(imageUrl);
                });
            } catch (error) {
                console.error('获取图片链接失败:', error);
            }
        }
    }

    // 降级方案：使用传统方法复制文本
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;

        // 隐藏textarea
        textArea.style.position = 'fixed';
        textArea.style.top = '-999999px';
        textArea.style.left = '-999999px';

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            showNotification('二维码链接已复制！');
        } catch (err) {
            console.error('降级复制方法也失败了:', err);
        }

        document.body.removeChild(textArea);
    }

    // 显示右上角提示信息
    function showNotification(message) {
        // 检查是否已存在提示框，如果存在则移除
        let notification = document.getElementById('qr-code-notification');
        if (notification) {
            document.body.removeChild(notification);
        }

        // 创建新的提示框
        notification = document.createElement('div');
        notification.id = 'qr-code-notification';
        notification.textContent = message;

        // 设置样式
        notification.style.position = 'fixed';
        notification.style.top = '80px'; // 再次下移提示框，使其远离导航栏，看起来更美观
        notification.style.right = '-300px'; // 初始位置在屏幕右侧外
        notification.style.backgroundColor = '#1677ff';
        notification.style.color = 'white';
        notification.style.padding = '10px 20px';
        notification.style.borderRadius = '4px';
        notification.style.zIndex = '9999';
        notification.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.15)';
        notification.style.fontSize = '14px';
        notification.style.transition = 'right 0.5s ease'; // 添加向右滑动的过渡效果

        // 添加到文档
        document.body.appendChild(notification);

        // 触发滑入动画
        setTimeout(function() {
            notification.style.right = '20px';
        }, 10);

        // 3秒后自动移除
        setTimeout(function() {
            // 添加淡出动画
            notification.style.transition = 'opacity 0.5s ease';
            notification.style.opacity = '0';

            // 动画结束后移除元素
            setTimeout(function() {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 500);
        }, 3000);
    }

    // 保存二维码到服务器
    function saveQRCodeToServer(did, deviceName) {
        const canvas = document.getElementById('qrcode-combined');
        if (!canvas) {
            console.error('未找到二维码canvas元素');
            return;
        }

        try {
            // 获取canvas的base64数据
            const imageData = canvas.toDataURL('image/png');

            // 发送AJAX请求保存到服务器
            fetch('api.php?action=saveQRCodeImage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'did': did,
                        'deviceName': deviceName,
                        'imageData': imageData
                    })
                })
                .then(response => {
                    // 先检查响应状态
                    if (!response.ok) {
                        throw new Error(`服务器响应错误: ${response.status}`);
                    }

                    // 尝试解析为JSON，但添加错误处理
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (jsonError) {
                            // 如果解析失败，记录原始响应内容便于调试
                            console.error('服务器返回非JSON内容:', text);
                            throw new Error('服务器返回格式错误，无法解析为JSON');
                        }
                    });
                })
                .then(result => {
                    if (result.success) {
                        console.log('二维码成功保存到服务器:', result.filePath);
                        // 保存文件路径到localStorage，供下载时使用
                        localStorage.setItem(`qrCodePath_${did}`, result.filePath);

                        // 刷新页面上显示的二维码图片（清除缓存）
                        refreshDeviceQRCodeImage(did);
                    } else {
                        console.error('保存二维码到服务器失败:', result.message);
                    }
                })
                .catch(error => {
                    console.error('保存二维码到服务器时发生网络错误:', error);
                });
        } catch (error) {
            console.error('获取二维码数据失败:', error);
        }
    }

    // 修改下载二维码函数，从服务器下载图片
    function downloadQRCode() {
        // 从localStorage获取文件路径
        const {
            did,
            name
        } = currentDeviceInfo;
        const filePath = localStorage.getItem(`qrCodePath_${did}`);

        if (filePath) {
            // 直接从服务器下载
            const link = document.createElement('a');

            // 格式化文件名：qr_did_name.png
            const safeName = name.replace(/[^a-zA-Z0-9一-龥]/g, '_');
            const fileName = `qr_${did}_${safeName}.png`;

            link.download = fileName;
            link.href = filePath;

            // 模拟点击下载
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            // 降级方案：如果没有服务器保存的文件，使用canvas生成
            console.log('使用降级方案下载二维码');
            const canvas = document.getElementById('qrcode-combined');

            if (!canvas || canvas.style.display === 'none') {
                return;
            }

            // 格式化文件名：qr_did_name.png
            const safeName = name.replace(/[^a-zA-Z0-9一-龥]/g, '_');
            const fileName = `qr_${did}_${safeName}.png`;

            // 创建下载链接
            const link = document.createElement('a');
            link.download = fileName;
            link.href = canvas.toDataURL('image/png');

            // 模拟点击下载
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    // 刷新页面上显示的二维码图片（清除缓存）
    function refreshDeviceQRCodeImage(did) {
        // 获取页面上的二维码图片元素
        const qrcodeImage = document.getElementById('device-qrcode-image');
        const qrcodeDisplayArea = document.getElementById('qrcode-display-area');
        const qrcodeButton = document.getElementById('qrcode-button');

        if (qrcodeImage && qrcodeDisplayArea) {
            // 构建新的图片URL，添加时间戳来清除缓存
            const timestamp = new Date().getTime();
            const newImgUrl = `/uploads/qrcode/qr_${did}.png?${timestamp}`;

            // 创建新的图片对象来预加载，避免页面闪烁
            const tempImg = new Image();
            tempImg.onload = function() {
                // 预加载完成后更新实际显示的图片
                qrcodeImage.src = newImgUrl;
                
                // 显示二维码区域
                qrcodeDisplayArea.style.display = 'block';
                
                // 更新按钮文本
                if (qrcodeButton) {
                    qrcodeButton.textContent = '重新生成设备码';
                }
                
                console.log('二维码图片缓存已刷新并显示');
            };
            tempImg.src = newImgUrl;
        }
    }
</script>