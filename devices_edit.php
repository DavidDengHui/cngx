<?php
// 检查是否被直接访问
if (basename($_SERVER['PHP_SELF']) == 'devices_edit.php') {
    // 直接输出404页面内容
    include '404.html';
    exit();
}

$nav_title = '设备信息编辑';
$page_title = '设备信息编辑';

// 引入页眉（config.php已在devices.php中引入）
include 'header.php';

// 获取设备ID
$did = isset($_GET['did']) ? $_GET['did'] : '';
$is_edit_mode = !empty($did);

// 如果did为空，跳转到设备搜索页面
if (empty(trim($did))) {
    header('Location: /devices.php');
    exit();
}
?>

<div class="devices-container">
    <h2 id="page-title">设备信息编辑</h2>

    <div class="devices-layout">
        <div class="devices-form">
            <form id="device-form" novalidate>
                <input type="hidden" id="did" value="<?php echo $did; ?>">

                <div class="form-layout">
                    <div class="form-column left-column">
                        <div class="form-row">
                            <div class="form-item">
                                <label for="device_name">设备名称 <span class="required">*</span></label>
                                <div class="select-container">
                                    <input type="text" id="device_name" placeholder="请输入设备名称" required>
                                    <button type="button" class="clear-btn" data-target="device_name"></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-item">
                                <label>设备类型 <span class="required">*</span></label>
                                <div class="select-container">
                                    <input type="text" id="type" readonly placeholder="请选择设备类型" required>
                                    <input type="hidden" id="type-id">
                                    <button type="button" class="clear-btn" data-target="type"></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-item">
                                <label>所属站场 <span class="required">*</span></label>
                                <div class="select-container">
                                    <input type="text" id="station" readonly placeholder="请选择所属站场" required>
                                    <input type="hidden" id="station-id">
                                    <button type="button" class="clear-btn" data-target="station"></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-item">
                                <label>包保部门 <span class="required">*</span></label>
                                <div class="select-container">
                                    <input type="text" id="department" readonly placeholder="请选择包保部门" required>
                                    <input type="hidden" id="department-id">
                                    <button type="button" class="clear-btn" data-target="department"></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-item">
                                <label for="keepers">包保人员 <span class="required">*</span></label>
                                <div class="workers-input-wrapper">
                                    <div class="workers-tags"></div>
                                    <input type="text" class="workers-input" placeholder="输入姓名后按空格添加">
                                    <input type="hidden" id="keepers">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-column right-column">
                        <div class="form-row">
                            <div class="form-item">
                                <label for="remark">备注信息</label>
                                <textarea id="remark" rows="4" placeholder="请输入备注信息"></textarea>
                            </div>
                        </div>

                        <!-- 图纸管理 -->
                        <div class="form-row">
                            <div class="form-item">
                                <label>图纸管理</label>
                                <div class="drawing-upload-section">
                                    <div class="drawing-upload-area" id="drawing-upload-area">
                                        <input type="file" id="drawing-upload" multiple accept=".jpg,.jpeg,.png,.bmp,.pdf,.dwg,.dxf,.doc,.docx">
                                        <div class="upload-text">
                                            <p>点击或拖拽文件到此处上传</p>
                                            <p class="upload-tip">支持图片、Word文档、PDF文档、CAD文档</p>
                                        </div>
                                    </div>
                                    <div id="drawing-list" class="drawing-list"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="save-btn">保存</button>
                    <?php if ($is_edit_mode) { ?>
                        <button type="button" class="cancel-btn">取消</button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 选择模态框 -->
<div id="select-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="select-modal-title">选择</h3>
            <button type="button" class="close-btn" onclick="closeSelectModal()">×</button>
        </div>
        <div class="modal-body">
            <div id="select-path" class="select-path"></div>
            <div id="select-items" class="select-items"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="reset-btn">重置</button>
            <button type="button" class="confirm-btn">确认</button>
        </div>
    </div>
</div>

<!-- 等待提示框 -->
<div id="loading-modal" class="modal loading-modal" style="display: none;">
    <div class="modal-content loading-content">
        <div class="loading-spinner"></div>
        <p id="loading-text">加载中，请稍候...</p>
    </div>
</div>

<!-- 确认保存模态框 -->
<div id="confirm-save-modal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 400px;">
        <div class="modal-header">
            <h3>确认保存</h3>
            <button type="button" class="close-btn" onclick="closeConfirmSaveModal()">×</button>
        </div>
        <div class="modal-body">
            <p style="text-align: center; margin: 10px 0;">确认保存设备信息吗？</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="reset-btn" onclick="closeConfirmSaveModal()">取消</button>
            <button type="button" class="confirm-btn" onclick="confirmSaveDevice()">确认</button>
        </div>
    </div>
</div>

<!-- 确认删除图纸模态框 -->
<div id="confirm-delete-drawing-modal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 400px;">
        <div class="modal-header">
            <h3>确认删除</h3>
            <button type="button" class="close-btn" onclick="closeConfirmDeleteDrawingModal()">×</button>
        </div>
        <div class="modal-body">
            <p style="text-align: left; margin: 10px 0;">确认要删除此图纸吗？</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="reset-btn" onclick="closeConfirmDeleteDrawingModal()">取消</button>
            <button type="button" class="confirm-btn" onclick="confirmDeleteDrawing()" style="color: white; background-color: #e74c3c;">确认</button>
        </div>
    </div>
</div>

<script>
    // 当前选中的类型
    let currentSelectType = '';
    let currentPath = [];
    let initialPath = [];
    let selectedItem = null; // 保存当前选择但未确认的项

    // 保存从数据库加载的原始数据
    let originalDeviceData = {};

    // 初始化页面
    window.onload = function() {
        initWorkerTags();
        initDrawingUpload();
        initInputEventListeners();

        // 绑定选择器点击事件
        document.getElementById('type').addEventListener('click', function() {
            openSelectModal('type', '选择设备类型');
        });
        document.getElementById('station').addEventListener('click', function() {
            openSelectModal('station', '选择所属站场');
        });
        document.getElementById('department').addEventListener('click', function() {
            openSelectModal('department', '选择包保部门');
        });

        // 绑定清除按钮事件
        document.querySelectorAll('.clear-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const target = this.getAttribute('data-target');
                document.getElementById(target).value = '';
                if (document.getElementById(target + '-id')) {
                    document.getElementById(target + '-id').value = '';
                }
                this.style.display = 'none';
            });
        });

        // 为设备名称输入框添加input事件监听器，显示/隐藏清除按钮
        const deviceNameInput = document.getElementById('device_name');
        if (deviceNameInput) {
            deviceNameInput.addEventListener('input', function() {
                updateClearButtonVisibility('device_name');
            });

            // 初始化时检查值并更新清除按钮可见性
            updateClearButtonVisibility('device_name');
        }

        // 绑定表单提交事件
        document.getElementById('device-form').addEventListener('submit', function(e) {
            e.preventDefault();
            saveDevice();
        });

        // 绑定取消按钮事件
        const cancelBtn = document.querySelector('.cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                window.history.back();
            });
        }

        // 加载设备信息（如果是编辑模式）
        const didElement = document.getElementById('did');
        if (didElement && didElement.value) {
            loadDeviceDetail();
        }
    };

    // 加载设备详情
    function loadDeviceDetail() {
        const did = document.getElementById('did').value;
        showLoadingModal();

        fetch(`api.php?action=getDeviceDetail&did=${did}`)
            .then(response => response.json())
            .then(data => {
                hideLoadingModal();

                if (data.success && data.data) {
                    const device = data.data;

                    // 设置页面标题和H2标题，用[]括起设备名称
                    const formattedTitle = `编辑[${device.device_name}]`;
                    document.getElementById('page-title').textContent = formattedTitle;

                    // 更新页面标题
                    document.title = `${formattedTitle} - 长南高信车间设备信息管理平台`;

                    // 保存从数据库加载的原始数据
                    originalDeviceData = {
                        device_name: device.device_name || '',
                        type_name: device.type_name || '',
                        tid: device.tid || '',
                        station_name: device.station_name || '',
                        sid: device.sid || '',
                        department_name: device.department_name || '',
                        cid: device.cid || '',
                        remark: device.remark || '',
                        keepers: device.keepers || ''
                    };

                    // 填充表单数据
                    document.getElementById('device_name').value = originalDeviceData.device_name;
                    document.getElementById('type').value = originalDeviceData.type_name;
                    document.getElementById('type-id').value = originalDeviceData.tid;
                    document.getElementById('station').value = originalDeviceData.station_name;
                    document.getElementById('station-id').value = originalDeviceData.sid;
                    document.getElementById('department').value = originalDeviceData.department_name;
                    document.getElementById('department-id').value = originalDeviceData.cid;
                    document.getElementById('remark').value = originalDeviceData.remark;

                    // 显示清除按钮
                    updateClearButtonVisibility('type');
                    updateClearButtonVisibility('station');
                    updateClearButtonVisibility('department');
                    updateClearButtonVisibility('device_name');

                    // 设置包保人员
                    if (device.keepers) {
                        const keepers = device.keepers.split('||');
                        document.getElementById('keepers').value = device.keepers;
                        const tagsContainer = document.querySelector('.workers-tags');
                        tagsContainer.innerHTML = '';

                        keepers.forEach(name => {
                            addKeeperTag(name);
                        });
                    }

                    // 加载图纸列表
                    loadDrawingList(did);
                } else {
                    // 如果设备不存在，切换为新增模式
                    document.getElementById('page-title').textContent = `新增[${did}]`;
                    // 更新浏览器标签标题
                    document.title = `新增[${did}] - 长南高信车间设备信息管理平台`;
                }
            })
            .catch(error => {
                hideLoadingModal();
                alert('加载设备信息失败：' + error.message);
            });
    }

    // 加载图纸列表
    function loadDrawingList(did) {
        fetch(`api.php?action=getDrawings&did=${did}&pageSize=0`)
            .then(response => response.json())
            .then(data => {
                const drawingList = document.getElementById('drawing-list');
                drawingList.innerHTML = '';

                if (data.data && data.data.length > 0) {
                    data.data.forEach(drawing => {
                        addDrawingItem(drawing);
                    });
                } else {
                    drawingList.innerHTML = '<p class="no-drawing">暂无图纸</p>';
                }
            })
            .catch(error => {
                console.error('加载图纸列表失败：', error);
            });
    }

    // 保存设备信息 - 显示确认模态框
    function saveDevice() {
        // 先更新隐藏输入框，确保包含未被加标签的文本
        updateHiddenInput();

        // 验证表单
        const deviceName = document.getElementById('device_name').value.trim();
        const typeId = document.getElementById('type-id').value;
        const stationId = document.getElementById('station-id').value;
        const departmentId = document.getElementById('department-id').value;
        const keepers = document.getElementById('keepers').value;

        // 重置所有错误状态
        resetAllErrorStates();

        let hasError = false;

        // 验证设备名称
        if (!deviceName) {
            showErrorState('device_name', '设备名称');
            hasError = true;
        }

        // 验证设备类型
        if (!typeId) {
            showErrorState('type', '设备类型');
            hasError = true;
        }

        // 验证所属站场
        if (!stationId) {
            showErrorState('station', '所属站场');
            hasError = true;
        }

        // 验证包保部门
        if (!departmentId) {
            showErrorState('department', '包保部门');
            hasError = true;
        }

        // 验证包保人员
        if (!keepers) {
            showErrorState('workers-input-wrapper', '包保人员');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // 显示自定义确认保存模态框
        showConfirmSaveModal();
    }

    // 显示确认保存模态框
    function showConfirmSaveModal() {
        const modal = document.getElementById('confirm-save-modal');
        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    // 关闭确认保存模态框
    function closeConfirmSaveModal() {
        const modal = document.getElementById('confirm-save-modal');
        modal.style.display = 'none';
        // 恢复背景页面滚动
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    // 显示确认删除图纸模态框
    function showConfirmDeleteDrawingModal() {
        const modal = document.getElementById('confirm-delete-drawing-modal');
        modal.style.display = 'flex';
        // 阻止背景页面滚动
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';

        // 获取设备名称和图纸名称并更新提示信息
        const deviceName = document.getElementById('device_name').value;
        const drawing = window.currentDrawingData;

        if (deviceName && drawing) {
            const drawingName = drawing.original_name || drawing.name;
            const confirmText = document.querySelector('#confirm-delete-drawing-modal .modal-body p');
            confirmText.innerHTML = `确认删除${deviceName}的以下图纸吗？<br><br>` +
                `<span style="color: #e74c3c;">[${drawingName}]</span><br><br>` +
                `<span style="color: #e74c3c;">注意：会立即删除无法撤销！</span>`;
        }
    }

    // 关闭确认删除图纸模态框
    function closeConfirmDeleteDrawingModal() {
        const modal = document.getElementById('confirm-delete-drawing-modal');
        modal.style.display = 'none';
        // 恢复背景页面滚动
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';

        // 清除存储的当前图纸信息
        window.currentDrawingItem = null;
        window.currentDrawingData = null;
    }

    // 确认删除图纸
    function confirmDeleteDrawing() {
        // 先保存当前图纸信息到局部变量
        const item = window.currentDrawingItem;
        const drawing = window.currentDrawingData;
        const drawingList = document.getElementById('drawing-list');

        // 关闭确认模态框
        closeConfirmDeleteDrawingModal();

        if (item && drawing) {
            // 显示加载动画，提示"删除中，请稍后"
            showLoadingModal('删除中，请稍后...');

            // 模拟异步操作，确保加载动画有足够时间显示
            setTimeout(() => {
                // 移除元素
                item.remove();

                // 如果是新增的文件，从input中移除
                if (drawing.is_new) {
                    const fileInput = document.getElementById('drawing-upload');
                    const files = Array.from(fileInput.files);
                    const filteredFiles = files.filter(file => file.name !== drawing.name);

                    // 创建新的FileList
                    const dataTransfer = new DataTransfer();
                    filteredFiles.forEach(file => dataTransfer.items.add(file));
                    fileInput.files = dataTransfer.files;
                } else {
                    // 已存在的文件，发送删除请求
                    deleteDrawing(drawing.id);
                }

                // 检查是否需要显示"暂无图纸"提示
                if (drawingList.children.length === 0) {
                    drawingList.innerHTML = '<p class="no-drawing">暂无图纸</p>';
                }

                // 隐藏加载动画
                hideLoadingModal();

                // 在右上角浮窗提示"图纸已删除！"
                showNotification('图纸已删除！', 'success');
            }, 500); // 短暂延迟，确保用户能看到加载动画
        }
    }

    // 确认保存设备信息
    function confirmSaveDevice() {
        // 关闭确认模态框
        closeConfirmSaveModal();

        // 获取表单数据
        const deviceName = document.getElementById('device_name').value.trim();
        const typeId = document.getElementById('type-id').value;
        const stationId = document.getElementById('station-id').value;
        const departmentId = document.getElementById('department-id').value;
        const keepers = document.getElementById('keepers').value;
        const remark = document.getElementById('remark').value.trim();
        const did = document.getElementById('did').value;

        // 准备表单数据
        const formData = new FormData();
        // 只有在编辑模式下（did不为空）才添加did字段
        if (did) {
            formData.append('did', did);
        }
        formData.append('device_name', deviceName);
        formData.append('device_type_id', typeId);
        formData.append('device_station_id', stationId);
        formData.append('device_department_id', departmentId);
        formData.append('device_keepers', keepers);
        formData.append('device_remark', remark);

        // 添加上传的图纸文件
        const drawingFiles = document.getElementById('drawing-upload').files;
        for (let i = 0; i < drawingFiles.length; i++) {
            formData.append('drawing_upload[]', drawingFiles[i]);
        }

        // 显示加载提示，修改提示文本为"提交中，请稍后"
        showLoadingModal('提交中，请稍后...');

        // 提交保存请求
        fetch('api.php?action=saveDevice', {
                method: 'POST',
                body: formData,
                // 添加移动设备兼容性配置
                credentials: 'same-origin',
                keepalive: true,
                // 超时设置
                signal: AbortSignal.timeout(60000) // 60秒超时
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`网络错误：${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                hideLoadingModal();

                if (data.success) {
                    // 显示绿色横幅提示
                    showNotification('设备信息编辑成功', 'success');
                    // 延迟跳转，让用户看到成功提示
                    setTimeout(() => {
                        // 由于API返回中没有data.did，使用当前表单中的设备ID进行跳转
                        window.location.href = `devices.php?did=${did}`;
                    }, 2000);
                } else {
                    // 显示错误提示
                    showNotification('保存失败：' + data.message, 'error');
                }
            })
            .catch(error => {
                hideLoadingModal();
                // 显示更具体的错误提示
                if (error.name === 'TimeoutError') {
                    showNotification('保存失败：网络连接超时，请检查网络后重试', 'error');
                } else if (error.message.includes('Failed to fetch')) {
                    showNotification('保存失败：网络连接异常，请检查网络后重试', 'error');
                } else {
                    showNotification('保存失败：' + error.message, 'error');
                }
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
        notification.style.position = 'fixed';
        notification.style.top = '80px'; // 确保低于header导航栏的高度
        notification.style.right = '-400px'; // 初始位置在右侧屏幕外

        // 根据类型设置不同的背景色
        if (type === 'success') {
            notification.style.backgroundColor = '#27ae60'; // 绿色
        } else if (type === 'error') {
            notification.style.backgroundColor = '#e74c3c'; // 红色
        } else {
            notification.style.backgroundColor = '#3498db'; // 蓝色
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

    // 显示错误状态
    function showErrorState(elementId, labelText) {
        // 查找对应的表单项目容器
        let formItem;
        if (elementId === 'workers-input-wrapper') {
            formItem = document.querySelector(`.${elementId}`).closest('.form-item');
        } else {
            formItem = document.getElementById(elementId).closest('.form-item');
        }

        // 查找标签并改变颜色
        const label = formItem.querySelector('label');
        label.style.color = '#e74c3c';

        // 根据元素类型查找输入框并添加错误样式
        if (elementId === 'workers-input-wrapper') {
            const wrapper = document.querySelector(`.${elementId}`);
            wrapper.classList.add('error');
            // 添加动画类
            wrapper.classList.add('shake-animation');

            // 动画结束后移除动画类
            setTimeout(() => {
                wrapper.classList.remove('shake-animation');
            }, 500);
        } else {
            const input = document.getElementById(elementId);
            input.style.borderColor = '#e74c3c';
            input.classList.add('error');
            // 添加动画类
            input.classList.add('shake-animation');

            // 动画结束后移除动画类
            setTimeout(() => {
                input.classList.remove('shake-animation');
            }, 500);
        }
    }

    // 重置所有错误状态
    function resetAllErrorStates() {
        // 重置所有标签颜色
        document.querySelectorAll('.form-item label').forEach(label => {
            label.style.color = '#555';
        });

        // 重置所有输入框样式
        document.querySelectorAll('.form-item input').forEach(input => {
            input.style.borderColor = '#ddd';
            input.classList.remove('error', 'shake-animation');
        });

        // 重置workers-input-wrapper样式
        const workersWrapper = document.querySelector('.workers-input-wrapper');
        workersWrapper.classList.remove('error', 'shake-animation');
    }

    // 添加输入事件监听器，在输入内容时恢复正常样式
    function initInputEventListeners() {
        // 设备名称输入框
        document.getElementById('device_name').addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#ddd';
                this.classList.remove('error');
                const label = this.closest('.form-item').querySelector('label');
                label.style.color = '#555';
            }
        });

        // 设备类型输入框
        document.getElementById('type').addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#ddd';
                this.classList.remove('error');
                const label = this.closest('.form-item').querySelector('label');
                label.style.color = '#555';
            }
        });

        // 所属站场输入框
        document.getElementById('station').addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#ddd';
                this.classList.remove('error');
                const label = this.closest('.form-item').querySelector('label');
                label.style.color = '#555';
            }
        });

        // 包保部门输入框
        document.getElementById('department').addEventListener('input', function() {
            if (this.value.trim()) {
                this.style.borderColor = '#ddd';
                this.classList.remove('error');
                const label = this.closest('.form-item').querySelector('label');
                label.style.color = '#555';
            }
        });

        // 包保人员输入框
        const workersInput = document.querySelector('.workers-input');
        const workersWrapper = document.querySelector('.workers-input-wrapper');
        const hiddenInput = document.getElementById('keepers');

        workersInput.addEventListener('input', function() {
            if (this.value.trim() || hiddenInput.value.trim()) {
                workersWrapper.classList.remove('error', 'shake-animation');
                const label = workersWrapper.closest('.form-item').querySelector('label');
                label.style.color = '#555';
            }
        });
    }

    // 初始化作业人员标签输入
    function initWorkerTags() {
        const inputWrapper = document.querySelector('.workers-input-wrapper');
        const tagsContainer = document.querySelector('.workers-tags');
        const input = document.querySelector('.workers-input');
        const hiddenInput = document.getElementById('keepers');

        // 支持的分隔符
        const separators = [' ', '、', ',', '，', ';', '；', '\uff0c', '\uff1b'];

        // 输入处理
        input.addEventListener('input', function(e) {
            const originalValue = e.target.value;
            const trimmedValue = originalValue.trim();

            // 检查是否输入了任何分隔符（使用原始值检测分隔符）
            for (const separator of separators) {
                if (originalValue.includes(separator)) {
                    // 处理输入框中的多个名字（可能包含各种分隔符）
                    let names = [originalValue];

                    // 使用正则表达式替换所有分隔符为统一的分隔符，然后拆分
                    separators.forEach(sep => {
                        // 转义特殊字符
                        const escapedSep = sep.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        names = names.flatMap(name => name.split(new RegExp(escapedSep)));
                    });

                    // 添加有效的姓名标签
                    names.forEach(name => {
                        const trimmedName = name.trim();
                        if (trimmedName) {
                            addKeeperTag(trimmedName);
                        }
                    });

                    this.value = '';
                    updateHiddenInput();
                    break;
                }
            }
        });

        // 回车添加
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const value = this.value.trim();

                if (value) {
                    addKeeperTag(value);
                    this.value = '';
                    updateHiddenInput();
                }
            }
        });

        // 添加标签函数
        function addKeeperTag(name) {
            // 检查是否已存在
            const existingTags = Array.from(tagsContainer.querySelectorAll('.keeper-tag'));
            if (existingTags.some(tag => tag.dataset.name === name)) {
                return;
            }

            const tag = document.createElement('div');
            tag.className = 'keeper-tag';
            tag.dataset.name = name;
            tag.innerHTML = `${name} <span class="remove-tag">×</span>`;

            // 删除标签事件
            tag.querySelector('.remove-tag').addEventListener('click', function() {
                tag.remove();
                updateHiddenInput();
            });

            tagsContainer.appendChild(tag);
        }

        // 更新隐藏输入
        function updateHiddenInput() {
            const tags = Array.from(tagsContainer.querySelectorAll('.keeper-tag'));
            const names = tags.map(tag => tag.dataset.name);

            // 获取输入框中未加标签的文本内容
            const inputText = input.value.trim();
            if (inputText) {
                // 如果有未加标签的文本，也添加到名称数组中
                names.push(inputText);
            }

            hiddenInput.value = names.join('||');

            // 显示/隐藏错误状态
            if (inputWrapper.classList.contains('error')) {
                inputWrapper.classList.remove('error', 'shake-animation');
            }
        }

        // 暴露给外部使用的方法
        window.addKeeperTag = addKeeperTag;
        window.updateHiddenInput = updateHiddenInput;
    }

    // 初始化图纸上传
    function initDrawingUpload() {
        const uploadArea = document.getElementById('drawing-upload-area');
        const fileInput = document.getElementById('drawing-upload');
        const drawingList = document.getElementById('drawing-list');

        // 点击上传区域触发文件选择
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });

        // 阻止事件冒泡，避免触发两次
        fileInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // 文件选择变化
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                // 清空"暂无图纸"提示
                if (drawingList.querySelector('.no-drawing')) {
                    drawingList.innerHTML = '';
                }

                // 添加新文件到列表
                Array.from(this.files).forEach(file => {
                    // 检查文件类型
                    const validExtensions = ['.jpg', '.jpeg', '.png', '.bmp', '.pdf', '.dwg', '.dxf', '.doc', '.docx'];
                    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                    if (validExtensions.includes(fileExtension)) {
                        addDrawingItem({
                            name: file.name,
                            size: file.size,
                            is_new: true
                        });
                    } else {
                        alert(`文件类型不支持：${file.name}`);
                    }
                });
            }
        });

        // 拖拽上传
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('highlight');
        }

        function unhighlight() {
            uploadArea.classList.remove('highlight');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                // 设置文件到input
                fileInput.files = files;
                // 触发change事件
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        }
    }

    // 添加图纸项到列表
    function addDrawingItem(drawing) {
        const drawingList = document.getElementById('drawing-list');
        const item = document.createElement('div');
        item.className = 'drawing-item';
        item.dataset.id = drawing.id;

        // 文件大小格式化
        let fileSize = '';
        if (drawing.size) {
            if (drawing.size < 1024) {
                fileSize = `${drawing.size} B`;
            } else if (drawing.size < 1024 * 1024) {
                fileSize = `${(drawing.size / 1024).toFixed(2)} KB`;
            } else {
                fileSize = `${(drawing.size / (1024 * 1024)).toFixed(2)} MB`;
            }
        }

        item.innerHTML = `
        <div class="drawing-info">
            <div class="drawing-name">${drawing.original_name || drawing.name}</div>
            <div class="drawing-meta">${fileSize} ${drawing.is_new ? '<span class="new-tag">新上传</span>' : ''}</div>
        </div>
        <button type="button" class="delete-drawing-btn" title="删除">×</button>
    `;

        // 删除按钮事件
        item.querySelector('.delete-drawing-btn').addEventListener('click', function() {
            // 存储当前要删除的图纸信息
            window.currentDrawingItem = item;
            window.currentDrawingData = drawing;

            // 显示确认删除模态框
            showConfirmDeleteDrawingModal();
        });

        drawingList.appendChild(item);
    }

    // 删除图纸
    function deleteDrawing(drawingId) {
        fetch(`api.php?action=deleteDrawing&id=${drawingId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('删除图纸失败：', data.message);
                }
            })
            .catch(error => {
                console.error('删除图纸失败：', error);
            });
    }

    // 打开选择模态框
    function openSelectModal(type, title) {
        currentSelectType = type;
        currentPath = [];
        selectedItem = null;

        const modal = document.getElementById('select-modal');
        document.getElementById('select-modal-title').textContent = title;
        document.getElementById('select-path').textContent = title;

        // 保存初始路径，用于重置
        initialPath = [];

        // 检查是否已有选中值，如果有则尝试加载到对应路径
        const input = document.getElementById(currentSelectType);
        const hiddenInput = document.getElementById(currentSelectType + '-id');

        // 显示加载动画
        showLoadingModal();

        if (input.value && hiddenInput.value) {
            // 尝试加载到已有路径，完成后显示模态框
            navigateToExistingPath(type, hiddenInput.value, input.value).then(() => {
                // 显示模态框
                modal.style.display = 'flex';
                // 设置背景禁止滚动
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
                // 隐藏加载动画
                hideLoadingModal();
            }).catch(() => {
                // 加载失败时也显示模态框
                loadSelectItems(type);
                // 显示模态框
                modal.style.display = 'flex';
                // 设置背景禁止滚动
                document.documentElement.style.overflow = 'hidden';
                document.body.style.overflow = 'hidden';
                // 隐藏加载动画
                hideLoadingModal();
            });
        } else {
            // 直接加载顶级数据
            loadSelectItems(type);
            // 显示模态框
            modal.style.display = 'flex';
            // 设置背景禁止滚动
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            // 隐藏加载动画
            hideLoadingModal();
        }

        // 绑定重置和确认按钮事件
        document.querySelector('.reset-btn').onclick = resetSelection;
        document.querySelector('.confirm-btn').onclick = confirmSelection;
    }

    // 尝试导航到已有路径
    function navigateToExistingPath(type, id, name) {
        return new Promise((resolve, reject) => {
            // 保存完整的初始路径信息
            const fullPathInfo = {
                id: id,
                name: name,
                type: type
            };
            initialPath = [fullPathInfo];

            // 清空当前路径
            currentPath = [];

            // 根据类型设置actionName
            let actionName = '';
            switch (type) {
                case 'department':
                    actionName = 'getDepartments';
                    break;
                case 'station':
                    actionName = 'getStations';
                    break;
                case 'type':
                    actionName = 'getTypes';
                    break;
                default:
                    actionName = `get${type.charAt(0).toUpperCase() + type.slice(1)}List`;
            }

            // 使用递归函数获取完整路径
            const getFullPath = (targetId, path = []) => {
                return fetch(`api.php?action=${actionName}&childId=${targetId}`)
                    .then(response => response.json())
                    .then(data => {
                        // 检查是否获取到父部门数据
                        if ((data && data.id) || (data.success && data.data)) {
                            const parentData = data.success ? data.data : data;

                            // 将父部门添加到路径
                            const parentId = parentData.id || parentData.cid;
                            const parentName = parentData.name || parentData.department_name || parentData.type_name || parentData.station_name;

                            // 构建新路径
                            const newPath = [{
                                id: parentId,
                                name: parentName
                            }, ...path];

                            // 递归获取父级的父级
                            return getFullPath(parentId, newPath);
                        } else {
                            // 没有更多父级，路径已完整
                            return path;
                        }
                    })
                    .catch(error => {
                        // 出错时返回当前已获取的路径
                        console.error('获取完整路径失败:', error);
                        return path;
                    });
            };

            // 开始获取完整路径
            getFullPath(id)
                .then(fullPath => {
                    // 设置完整路径
                    currentPath = fullPath;
                    updatePathDisplay();

                    // 加载当前位置的同级列表
                    // 如果有路径，则使用最后一个节点的ID作为parentId
                    let parentId = 0;
                    if (currentPath.length > 0) {
                        parentId = currentPath[currentPath.length - 1].id;
                    }

                    // 使用Promise处理loadSelectItems
                    return new Promise((resolveLoad, rejectLoad) => {
                        // 重写loadSelectItems的回调行为
                        const originalCallback = loadSelectItems.callback;
                        loadSelectItems.callback = () => {
                            // 恢复原始回调
                            loadSelectItems.callback = originalCallback;
                            resolveLoad();
                        };

                        // 加载同级列表
                        loadSelectItems(type, parentId);
                    });
                })
                .then(() => {
                    resolve();
                })
                .catch(error => {
                    console.error('导航到已有路径失败:', error);
                    reject(error);
                });
        });
    }

    // 递归加载完整路径
    function loadFullPath(type, actionName, targetId, targetName) {
        // 先加载顶级数据
        fetch(`api.php?action=${actionName}`)
            .then(response => response.json())
            .then(data => {
                // 处理数据
                const items = Array.isArray(data) ? data : (data.success ? data.data : []);

                // 检查顶级数据中是否有目标ID
                const targetItem = items.find(item => {
                    // 对于部门类型，优先使用cid字段
                    const itemId = type === 'department' ? (item.cid || item.id) : (item.id || item.tid || item.sid || item.cid);
                    return itemId == targetId;
                });

                if (targetItem) {
                    // 如果在顶级找到目标项，直接添加到路径
                    currentPath.push({
                        id: type === 'department' ? (targetItem.cid || targetItem.id) : (targetItem.id || targetItem.tid || targetItem.sid || targetItem.cid),
                        name: targetItem.name || targetItem.full_name || targetItem.short_name || targetItem.type_name || targetItem.station_name
                    });
                    updatePathDisplay();
                    loadSelectItems(type);
                    return;
                }

                // 如果顶级没找到，查找目标ID所在的路径
                findPathInItems(items, type, actionName, targetId, targetName);
            })
            .catch(error => {
                console.error('加载路径失败:', error);
                // 如果加载失败，显示当前项
                loadSelectItems(type);
            });
    }

    // 在项目列表中查找路径
    function findPathInItems(items, type, actionName, targetId, targetName) {
        if (!items || items.length === 0) {
            // 如果没有数据，加载顶级数据
            if (currentPath.length === 0) {
                loadSelectItems(type);
            }
            return;
        }

        // 遍历所有项
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            // 对于部门类型，优先使用cid字段
            const itemId = type === 'department' ? (item.cid || item.id) : (item.id || item.tid || item.sid || item.cid);
            const itemName = item.name || item.type_name || item.station_name || item.full_name || item.short_name;

            // 检查当前项是否有子项
            fetch(`api.php?action=${actionName}&parentId=${itemId}`)
                .then(response => response.json())
                .then(children => {
                    const childItems = Array.isArray(children) ? children : (children.success ? children.data : []);

                    if (childItems && childItems.length > 0) {
                        // 递归检查子项中是否有目标ID
                        for (let j = 0; j < childItems.length; j++) {
                            const childItem = childItems[j];
                            // 对于部门类型，优先使用cid字段
                            const childItemId = type === 'department' ? (childItem.cid || childItem.id) : (childItem.id || childItem.tid || childItem.sid || childItem.cid);
                            const childItemName = childItem.name || childItem.type_name || childItem.station_name || childItem.full_name || childItem.short_name;

                            if (childItemId == targetId) {
                                // 将当前项添加到路径
                                currentPath.push({
                                    id: itemId,
                                    name: itemName
                                });
                                updatePathDisplay();

                                // 加载子项
                                loadSelectItems(type);
                                return;
                            } else {
                                // 递归查找更深层级
                                findPathInItems([childItem], type, actionName, targetId, targetName);

                                // 如果已经找到路径，就不再继续查找
                                if (currentPath.length > 0) {
                                    return;
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('检查子项失败:', error);
                });
        }

        // 如果没有找到路径，加载顶级数据
        setTimeout(() => {
            if (currentPath.length === 0) {
                loadSelectItems(type);
            }
        }, 500);
    }

    // 关闭选择模态框
    function closeSelectModal() {
        document.getElementById('select-modal').style.display = 'none';
        // 恢复背景页面滚动 - 同时控制html和body
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }

    // 加载选择项数据
    function loadSelectItems(type, customParentId = null) {
        const selectItems = document.getElementById('select-items');
        selectItems.innerHTML = '<div class="loading">加载中...</div>';

        // 构建API参数
        let actionName = '';
        switch (type) {
            case 'department':
                actionName = 'getDepartments';
                break;
            case 'station':
                actionName = 'getStations';
                break;
            case 'type':
                actionName = 'getTypes';
                break;
            default:
                actionName = `get${type.charAt(0).toUpperCase() + type.slice(1)}List`;
        }
        let apiParams = `action=${actionName}`;

        // 如果提供了自定义父ID，使用它；否则使用当前路径的最后一个节点ID
        let parentId = 0;
        if (customParentId !== null) {
            parentId = customParentId;
        } else if (currentPath.length > 0) {
            const lastPath = currentPath[currentPath.length - 1];
            if (lastPath.id) {
                parentId = lastPath.id;
            }
        }

        if (parentId > 0) {
            apiParams += `&parentId=${parentId}`;
        }

        fetch(`api.php?${apiParams}`)
            .then(response => response.json())
            .then(data => {
                selectItems.innerHTML = '';

                // 处理直接返回数组的情况
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'select-item';
                        // 兼容不同字段名称
                        itemDiv.textContent = item.name || item.type_name || item.station_name || item.full_name || item.short_name;
                        itemDiv.dataset.id = item.id || item.tid || item.sid || item.cid;
                        itemDiv.dataset.name = item.name || item.type_name || item.station_name || item.full_name || item.short_name;

                        itemDiv.addEventListener('click', function() {
                            // 检查是否有子项
                            fetch(`api.php?action=${actionName}&parentId=${itemDiv.dataset.id}`)
                                .then(response => response.json())
                                .then(children => {
                                    // 检查是否有子项，数组长度大于0或data.success为true且data.data长度大于0
                                    const hasChildren = (Array.isArray(children) && children.length > 0) ||
                                        (children.success && children.data && children.data.length > 0);

                                    if (hasChildren) {
                                        // 添加到路径
                                        currentPath.push({
                                            id: itemDiv.dataset.id,
                                            name: itemDiv.dataset.name
                                        });
                                        updatePathDisplay();
                                        loadSelectItems(type);
                                    } else {
                                        // 选择最后一级，保存但不立即更新到输入框
                                        selectedItem = {
                                            id: itemDiv.dataset.id,
                                            name: itemDiv.dataset.name
                                        };

                                        // 自动确认选择
                                        confirmSelection();
                                    }
                                })
                                .catch(error => {
                                    console.error('加载子项失败:', error);
                                    // 发生错误时保存选择但不立即更新
                                    selectedItem = {
                                        id: itemDiv.dataset.id,
                                        name: itemDiv.dataset.name
                                    };
                                });
                        });

                        selectItems.appendChild(itemDiv);
                    });

                    // 数据加载完成后调用回调
                    loadSelectItems.callback();
                }
                // 处理带有success和data字段的情况
                else if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'select-item';
                        // 兼容不同字段名称
                        itemDiv.textContent = item.name || item.type_name || item.station_name || item.full_name || item.short_name;
                        itemDiv.dataset.id = item.id || item.tid || item.sid || item.cid;
                        itemDiv.dataset.name = item.name || item.type_name || item.station_name || item.full_name || item.short_name;

                        itemDiv.addEventListener('click', function() {
                            // 检查是否有子项
                            fetch(`api.php?action=${actionName}&parentId=${itemDiv.dataset.id}`)
                                .then(response => response.json())
                                .then(children => {
                                    // 检查是否有子项，数组长度大于0或data.success为true且data.data长度大于0
                                    const hasChildren = (Array.isArray(children) && children.length > 0) ||
                                        (children.success && children.data && children.data.length > 0);

                                    if (hasChildren) {
                                        // 添加到路径
                                        currentPath.push({
                                            id: itemDiv.dataset.id,
                                            name: itemDiv.dataset.name
                                        });
                                        updatePathDisplay();
                                        loadSelectItems(type);
                                    } else {
                                        // 选择最后一级，保存但不立即更新到输入框
                                        selectedItem = {
                                            id: itemDiv.dataset.id,
                                            name: itemDiv.dataset.name
                                        };

                                        // 自动确认选择
                                        confirmSelection();
                                    }
                                })
                                .catch(error => {
                                    console.error('加载子项失败:', error);
                                    // 发生错误时保存选择但不立即更新
                                    selectedItem = {
                                        id: itemDiv.dataset.id,
                                        name: itemDiv.dataset.name
                                    };
                                });
                        });

                        selectItems.appendChild(itemDiv);
                    });

                    // 数据加载完成后调用回调
                    loadSelectItems.callback();
                } else {
                    selectItems.innerHTML = '<p class="no-data">暂无数据</p>';

                    // 数据加载完成后调用回调
                    loadSelectItems.callback();
                }
            })
            .catch(error => {
                console.error('加载数据失败:', error);
                selectItems.innerHTML = '<p class="error">加载失败，请重试</p>';

                // 加载失败时也调用回调
                loadSelectItems.callback();
            });
    }

    // 更新路径显示
    function updatePathDisplay() {
        const pathContainer = document.getElementById('select-path');
        pathContainer.innerHTML = '';

        // 添加返回上级按钮（如果有路径）
        if (currentPath.length > 0) {
            const backButton = document.createElement('span');
            backButton.className = 'path-item';
            backButton.textContent = '返回上级';
            backButton.addEventListener('click', function() {
                currentPath.pop();
                updatePathDisplay();
                loadSelectItems(currentSelectType);
            });
            pathContainer.appendChild(backButton);
            pathContainer.appendChild(document.createTextNode(' > '));
        }

        // 添加当前路径
        currentPath.forEach((path, index) => {
            const pathItem = document.createElement('span');
            pathItem.className = 'path-item';
            pathItem.textContent = path.name;
            pathItem.addEventListener('click', function() {
                // 跳转到指定路径
                currentPath = currentPath.slice(0, index + 1);
                updatePathDisplay();
                loadSelectItems(currentSelectType);
            });

            pathContainer.appendChild(pathItem);

            if (index < currentPath.length - 1) {
                pathContainer.appendChild(document.createTextNode(' > '));
            }
        });
    }

    // 选择项处理
    function selectItem(id, name) {
        const input = document.getElementById(currentSelectType);
        const hiddenInput = document.getElementById(currentSelectType + '-id');

        // 设置值
        input.value = name;
        hiddenInput.value = id;

        // 显示清除按钮
        updateClearButtonVisibility(currentSelectType);

        // 移除错误状态
        input.classList.remove('error');
        input.style.borderColor = '#ddd';
        const label = input.closest('.form-item').querySelector('label');
        if (label) {
            label.style.color = '#555';
        }
    }

    // 确认选择
    function confirmSelection() {
        if (selectedItem) {
            // 不管是否选择到了最末端级别，都直接将当前已选的路径计入输入框
            selectItem(selectedItem.id, selectedItem.name);
            closeSelectModal();

            // 确保移除错误状态
            const input = document.getElementById(currentSelectType);
            const hiddenInput = document.getElementById(currentSelectType + '-id');

            // 强制移除错误状态
            if (input.classList.contains('error')) {
                input.classList.remove('error');
                input.style.borderColor = '#ddd';
                const label = input.closest('.form-item').querySelector('label');
                if (label) {
                    label.style.color = '#555';
                }
            }
        } else if (currentPath.length > 0) {
            // 如果有当前路径但没有selectedItem，使用当前路径的最后一项
            const lastPath = currentPath[currentPath.length - 1];
            selectItem(lastPath.id, lastPath.name);
            closeSelectModal();

            // 确保移除错误状态
            const input = document.getElementById(currentSelectType);
            const hiddenInput = document.getElementById(currentSelectType + '-id');

            // 强制移除错误状态
            if (input.classList.contains('error')) {
                input.classList.remove('error');
                input.style.borderColor = '#ddd';
                const label = input.closest('.form-item').querySelector('label');
                if (label) {
                    label.style.color = '#555';
                }
            }
        }
    }

    // 重置选择
    function resetSelection() {
        const input = document.getElementById(currentSelectType);
        const hiddenInput = document.getElementById(currentSelectType + '-id');

        // 判断是编辑模式还是新增模式
        const didElement = document.getElementById('did');
        const isEditMode = didElement && didElement.value;

        if (isEditMode && originalDeviceData) {
            // 编辑模式：恢复到从数据库获取的最原始记录的路径
            if (currentSelectType === 'type') {
                input.value = originalDeviceData.type_name || '';
                hiddenInput.value = originalDeviceData.tid || '';
            } else if (currentSelectType === 'station') {
                input.value = originalDeviceData.station_name || '';
                hiddenInput.value = originalDeviceData.sid || '';
            } else if (currentSelectType === 'department') {
                input.value = originalDeviceData.department_name || '';
                hiddenInput.value = originalDeviceData.cid || '';
            }
        } else {
            // 新增模式：清空输入内容
            input.value = '';
            hiddenInput.value = '';
        }

        // 更新清除按钮可见性
        updateClearButtonVisibility(currentSelectType);

        // 移除错误状态
        input.classList.remove('error');
        input.style.borderColor = '#ddd';
        const label = input.closest('.form-item').querySelector('label');
        if (label) {
            label.style.color = '#555';
        }

        // 关闭模态框
        closeSelectModal();
    }

    // 更新清除按钮可见性
    function updateClearButtonVisibility(target) {
        const input = document.getElementById(target);
        const clearBtn = document.querySelector(`.clear-btn[data-target="${target}"]`);

        if (input.value) {
            clearBtn.style.display = 'flex';
        } else {
            clearBtn.style.display = 'none';
        }
    }

    // 显示加载模态框
    function showLoadingModal(text = '加载中，请稍候...') {
        const modal = document.getElementById('loading-modal');
        document.getElementById('loading-text').textContent = text;
        modal.style.display = 'flex';
        // 阻止背景页面滚动 - 同时控制html和body
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    // 隐藏加载模态框
    function hideLoadingModal() {
        document.getElementById('loading-modal').style.display = 'none';
        // 检查是否有模态框正在显示
        const selectModal = document.getElementById('select-modal');
        if (selectModal && selectModal.style.display !== 'flex') {
            // 如果没有模态框显示，恢复背景页面滚动
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }
    }

    // 加载选择项完成后的回调函数（默认不做任何事）
    loadSelectItems.callback = function() {}
</script>

<style>
    .devices-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 1200px;
        margin: 20px auto;
        box-sizing: border-box;
    }

    .devices-container h2 {
        margin-bottom: 20px;
        color: #2c3e50;
        text-align: center;
    }

    .devices-layout {
        display: flex;
        gap: 30px;
        margin-top: 30px;
        width: 100%;
        box-sizing: border-box;
    }

    .devices-form {
        flex: 1;
        min-width: 0;
        background: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        height: fit-content;
        box-sizing: border-box;
    }

    /* 宽屏模式两栏布局 */
    .form-layout {
        display: flex;
        gap: 20px;
    }

    .form-column {
        flex: 1;
    }

    .left-column {
        flex: 1;
    }

    .right-column {
        flex: 1;
    }

    /* 在窄屏模式下恢复单列布局 */
    @media (max-width: 768px) {
        .form-layout {
            flex-direction: column;
            gap: 0;
        }
    }

    .form-row {
        margin-bottom: 20px;
    }

    .form-item {
        margin-bottom: 15px;
    }

    .form-item label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }

    .form-item label .required {
        color: #e74c3c;
    }

    .form-item input[type="text"],
    .form-item textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        box-sizing: border-box;
    }

    .form-item textarea {
        resize: vertical;
        min-height: 80px;
    }

    .select-container {
        position: relative;
    }

    .select-container input[type="text"] {
        cursor: pointer;
        background-color: white;
        padding-right: 40px;
    }

    /* 为设备名称输入框设置默认的文本输入光标 */
    .select-container input#device_name {
        cursor: text;
    }

    .select-container input[type="text"]:focus {
        outline: none;
        border-color: #3498db;
    }

    .clear-btn {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        border: none;
        background: #ddd;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        line-height: 1;
        color: #666;
    }

    .clear-btn:before {
        content: '×';
        font-weight: bold;
    }

    .clear-btn:hover {
        background: #ccc;
        color: #333;
    }

    /* 作业人员输入样式 */
    .workers-input-wrapper {
        display: block;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 38px;
        transition: border-color 0.3s ease;
        background-color: white;
    }

    /* 错误状态样式 */
    .workers-input-wrapper.error {
        border-color: #f44336;
    }

    /* 输入框错误状态 */
    .form-item input.error {
        border-color: #f44336;
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

    /* 错误输入框摇摆动画类 */
    .shake-animation {
        animation: shake 0.5s ease-in-out;
    }

    .workers-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
        /* 移除min-height，当没有标签时不显示高度 */
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
    }

    .keeper-tag:hover {
        background-color: #bae7ff;
        color: #0d47a1;
    }

    .remove-tag {
        margin-left: 6px;
        font-weight: bold;
        cursor: pointer;
    }

    .workers-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 100px;
        padding: 4px 8px;
        font-size: 14px;
        /* 让输入框上下左右等宽 */
        width: calc(100% - 16px);
    }

    /* 图纸管理样式 */
    .drawing-upload-section {
        margin-top: 10px;
    }

    .drawing-upload-area {
        border: 2px dashed #ddd;
        border-radius: 4px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: white;
    }

    .drawing-upload-area:hover,
    .drawing-upload-area.highlight {
        border-color: #3498db;
        background-color: #f8f9fa;
    }

    .upload-text p {
        margin: 5px 0;
        color: #666;
    }

    .upload-tip {
        font-size: 14px;
        color: #999;
    }

    .drawing-list {
        margin-top: 20px;
        max-height: 300px;
        overflow-y: auto;
    }

    .drawing-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        margin-bottom: 10px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .drawing-item:hover {
        border-color: #3498db;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .drawing-info {
        flex: 1;
    }

    .drawing-name {
        font-weight: bold;
        margin-bottom: 4px;
        word-break: break-all;
        /* 确保过长的文件名能够换行 */
        white-space: normal;
        /* 允许文本换行 */
        max-width: 100%;
        /* 限制最大宽度，防止撑出容器 */
    }

    .drawing-meta {
        font-size: 14px;
        color: #999;
    }

    .new-tag {
        color: #27ae60;
        font-size: 12px;
        margin-left: 8px;
    }

    .delete-drawing-btn {
        width: 28px;
        height: 28px;
        border: none;
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        transition: all 0.3s ease;
    }

    .delete-drawing-btn:hover {
        background-color: #c0392b;
    }

    .no-drawing {
        text-align: center;
        color: #999;
        padding: 30px 0;
    }

    /* 表单操作按钮 */
    .form-actions {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .save-btn {
        order: 2;
    }

    .cancel-btn {
        order: 1;
    }

    .save-btn {
        background-color: #27ae60;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .save-btn:hover {
        background-color: #229954;
    }

    .cancel-btn {
        background-color: #95a5a6;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .cancel-btn:hover {
        background-color: #7f8c8d;
    }

    /* 模态框样式 */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        color: #333;
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
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .reset-btn {
        background-color: #95a5a6;
        color: white;
        border: none;
        padding: 8px 20px;
        font-size: 14px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .reset-btn:hover {
        background-color: #7f8c8d;
    }

    .confirm-btn {
        background-color: #27ae60;
        color: white;
        border: none;
        padding: 8px 20px;
        font-size: 14px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .confirm-btn:hover {
        background-color: #229954;
    }

    /* 选择模态框样式 */
    .select-path {
        padding: 10px 15px;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .path-item {
        color: #3498db;
        cursor: pointer;
        text-decoration: underline;
    }

    .path-item:hover {
        color: #2980b9;
    }

    .select-items {
        max-height: 400px;
        overflow-y: auto;
    }

    .select-item {
        padding: 12px 15px;
        background-color: #f8f9fa;
        color: #333;
        cursor: pointer;
        border: 1px solid transparent;
        margin-bottom: 5px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .select-item:hover {
        background-color: #3498db;
        color: white;
    }

    /* 加载模态框样式 */
    .loading-modal .modal-content {
        width: 300px;
        padding: 30px;
        text-align: center;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* 基础样式确保移动设备兼容性 */
    html,
    body {
        overflow-x: hidden;
        width: 100%;
    }

    /* 响应式布局 */
    @media (max-width: 768px) {

        /* 根容器设置 */
        .devices-container {
            background: none;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
            margin: 0 auto;
            overflow-x: hidden;
        }

        .devices-container h2 {
            padding: 0 10px;
            font-size: 20px;
        }

        /* 布局容器设置 */
        .devices-layout {
            flex-direction: column;
            gap: 20px;
            width: 100%;
            padding: 0 10px;
            box-sizing: border-box;
            max-width: 100%;
            margin: 0 auto;
            overflow-x: hidden;
        }

        /* 表单容器设置 */
        .devices-form {
            flex: none;
            width: 100%;
            padding: 15px;
            border-radius: 8px;
            box-sizing: border-box;
            max-width: 100%;
            margin: 0 auto;
            overflow-x: hidden;
        }

        /* 表单行和项目设置 */
        .form-row {
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .form-item {
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        /* 确保所有表单元素都不会超出容器 */
        input,
        textarea,
        .select-container,
        .workers-input-wrapper {
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        /* 特定元素的额外控制 */
        .select-container {
            position: relative;
            width: 100%;
            box-sizing: border-box;
        }

        .select-container input[type="text"] {
            padding-right: 35px;
            /* 为清除按钮留出空间 */
        }

        .workers-input-wrapper {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 38px;
            width: 100%;
            box-sizing: border-box;
        }

        .clear-btn {
            right: 8px;
        }

        .modal-content {
            width: 95%;
            max-height: 90vh;
            box-sizing: border-box;
        }

        .form-actions {
            flex-direction: column;
            gap: 10px;
        }

        .save-btn {
            width: 100%;
            order: 1;
        }

        .cancel-btn {
            width: 100%;
            order: 2;
        }
    }
</style>