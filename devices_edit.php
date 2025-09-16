<?php
// 确保did参数存在
if (!isset($did)) {
    header('Location: /devices.php');
    exit();
}

// 验证did是否为有效的8位数编码
if (!preg_match('/^[1-9]\d{7}$/', $did)) {
    ?>
    <div class="error-message">
        <p>该设备码无效</p>
        <p>请联系<a href="mailto:david.deng.hui@qq.com" target="_blank">管理员</a>核实</p>
    </div>
    <?php
    include 'footer.php';
    exit();
}

// 查询设备信息
$stmt = $pdo->prepare("SELECT * FROM devices WHERE did = :did AND status = 1");
$stmt->execute(['did' => $did]);
$device = $stmt->fetch();

// 获取设备类型名称和ID
$typeId = $device ? $device['tid'] : '1000';
$stmt = $pdo->prepare("SELECT type_name FROM types WHERE tid = :tid AND status = 1");
$stmt->execute(['tid' => $typeId]);
$type = $stmt->fetch();
$typeName = $type ? $type['type_name'] : '其他类型';

// 获取所属站场名称和ID
$stationId = $device ? $device['sid'] : '100000';
$stmt = $pdo->prepare("SELECT station_name FROM stations WHERE sid = :sid AND status = 1");
$stmt->execute(['sid' => $stationId]);
$station = $stmt->fetch();
$stationName = $station ? $station['station_name'] : '其他站场';

// 获取包保部门名称和ID
$departmentId = $device ? $device['cid'] : '100000';
$stmt = $pdo->prepare("SELECT full_name FROM departments WHERE cid = :cid AND status = 1");
$stmt->execute(['cid' => $departmentId]);
$department = $stmt->fetch();
$departmentName = $department ? $department['full_name'] : '其他部门';

// 获取图纸列表
$drawings = [];
if ($device) {
    $stmt = $pdo->prepare("SELECT * FROM drawings WHERE did = :did AND status = 1 ORDER BY upload_time ASC");
    $stmt->execute(['did' => $did]);
    $drawings = $stmt->fetchAll();
}
?>
    <div class="device-edit">
        <h2 style="text-align: center; margin-bottom: 30px;">设备信息编辑</h2>
        
        <form id="edit-form" enctype="multipart/form-data">
            <input type="hidden" name="did" value="<?php echo $did; ?>">
            
            <div class="form-group">
                <label for="device-name">设备名称 <span class="required">*</span>：</label>
                <input type="text" id="device-name" name="device_name" required value="<?php echo $device ? $device['device_name'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="device-type">设备类型 <span class="required">*</span>：</label>
                <div class="select-container">
                    <input type="text" id="device-type" name="device_type" readonly required placeholder="请选择设备类型" value="<?php echo $typeName; ?>">
                    <input type="hidden" id="device-type-id" name="device_type_id" value="<?php echo $typeId; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="device-station">所属站场 <span class="required">*</span>：</label>
                <div class="select-container">
                    <input type="text" id="device-station" name="device_station" readonly required placeholder="请选择所属站场" value="<?php echo $stationName; ?>">
                    <input type="hidden" id="device-station-id" name="device_station_id" value="<?php echo $stationId; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="device-department">包保部门 <span class="required">*</span>：</label>
                <div class="select-container">
                    <input type="text" id="device-department" name="device_department" readonly required placeholder="请选择包保部门" value="<?php echo $departmentName; ?>">
                    <input type="hidden" id="device-department-id" name="device_department_id" value="<?php echo $departmentId; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="device-keepers">包保人姓名 <span class="required">*</span>：</label>
                <input type="text" id="device-keepers" name="device_keepers" required placeholder="多个人用||分隔" value="<?php echo $device ? $device['keepers'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="device-remark">备注：</label>
                <textarea id="device-remark" name="device_remark" rows="4" placeholder="可以输入多行文本"><?php echo $device ? $device['remark'] : ''; ?></textarea>
            </div>
            
            <div class="drawings-section">
                <h3>图纸管理</h3>
                
                <div class="drawings-list">
                    <?php if (count($drawings) > 0): ?>
                        <table class="drawings-table">
                            <thead>
                                <tr>
                                    <th>序号</th>
                                    <th>图纸名称</th>
                                    <th>上传时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($drawings as $index => $drawing): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><a href="<?php echo $drawing['root_dir'] . $drawing['link_name']; ?>" target="_blank"><?php echo $drawing['original_name']; ?></a></td>
                                        <td><?php echo $drawing['upload_time']; ?></td>
                                        <td><button type="button" class="delete-btn" onclick="deleteDrawing(<?php echo $drawing['id']; ?>)">删除</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-drawings">暂无图纸</p>
                    <?php endif; ?>
                </div>
                
                <div class="upload-section">
                    <h4>上传新图纸</h4>
                    <input type="file" id="drawing-upload" name="drawing_upload[]" multiple accept=".jpg,.jpeg,.png,.pdf,.dwg,.dxf">
                    <p class="upload-note">支持的文件格式：JPG、PNG、PDF、DWG、DXF</p>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="cancel-btn" onclick="window.location.href='/devices.php?did=<?php echo $did; ?>'">取消</button>
                <button type="submit" class="save-btn">保存</button>
            </div>
        </form>
    </div>
    
    <!-- 多级选择菜单模态框 -->
    <div id="select-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-left">
                    <button type="button" class="modal-btn reset-btn">重置</button>
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
    </div>
    
    <script>
        // 全局变量存储当前选择类型和路径
        let currentSelectType = '';
        let currentSelectPath = [];
        
        // 初始化选择框点击事件
        document.getElementById('device-type').addEventListener('click', function() {
            openSelectModal('type', '设备类型');
        });
        
        document.getElementById('device-station').addEventListener('click', function() {
            openSelectModal('station', '所属站场');
        });
        
        document.getElementById('device-department').addEventListener('click', function() {
            openSelectModal('department', '包保部门');
        });
        
        // 打开选择模态框
        function openSelectModal(type, label) {
            currentSelectType = type;
            
            // 重置选择路径
            currentSelectPath = [];
            
            // 获取当前选中的值
            let currentId = '';
            if (type === 'type') {
                currentId = document.getElementById('device-type-id').value;
            } else if (type === 'station') {
                currentId = document.getElementById('device-station-id').value;
            } else if (type === 'department') {
                currentId = document.getElementById('device-department-id').value;
            }
            
            // 加载第一级数据
            loadSelectData(0, currentId);
            
            // 显示模态框
            document.getElementById('select-modal').style.display = 'block';
        }
        
        // 加载选择数据
        function loadSelectData(parentId, currentId) {
            const type = currentSelectType;
            const contentDiv = document.getElementById('select-content');
            
            // 清空内容
            contentDiv.innerHTML = '<div class="loading">加载中...</div>';
            
            // 根据类型获取API URL
            let apiUrl = '';
            switch(type) {
                case 'department':
                    apiUrl = `api.php?action=getDepartments&parentId=${parentId}`;
                    break;
                case 'station':
                    apiUrl = `api.php?action=getStations&parentId=${parentId}`;
                    break;
                case 'type':
                    apiUrl = `api.php?action=getTypes&parentId=${parentId}`;
                    break;
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
                            const isSelected = item.id == currentId ? 'selected' : '';
                            html += `<div class="select-item ${isSelected}" data-id="${item.id}" data-name="${item.name}" data-shortname="${item.shortname || item.name}">${item.name}</div>`;
                        });
                        html += '</div>';
                        
                        contentDiv.innerHTML = html;
                        
                        // 添加选项点击事件
                        document.querySelectorAll('.select-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                const name = this.getAttribute('data-name');
                                const shortname = this.getAttribute('data-shortname');
                                
                                // 添加到选择路径
                                currentSelectPath.push({id, name, shortname});
                                
                                // 加载下一级数据
                                loadSelectData(id, currentId);
                            });
                        });
                        
                        // 添加路径点击事件
                        document.querySelectorAll('.path-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                // 重置路径到点击的位置
                                currentSelectPath = currentSelectPath.filter(item => item.id === id);
                                // 加载对应级别的数据
                                loadSelectData(id, currentId);
                            });
                        });
                    } else {
                        // 没有下一级数据，直接确认选择
                        confirmSelect();
                    }
                })
                .catch(error => {
                    contentDiv.innerHTML = `<div class="error">加载失败: ${error.message}</div>`;
                });
        }
        
        // 确认选择
        function confirmSelect() {
            if (currentSelectPath.length > 0) {
                const type = currentSelectType;
                const lastItem = currentSelectPath[currentSelectPath.length - 1];
                
                // 根据类型更新输入框
                if (type === 'department') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('device-department').value = pathStr;
                    document.getElementById('device-department-id').value = lastItem.id;
                } else if (type === 'station') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('device-station').value = pathStr;
                    document.getElementById('device-station-id').value = lastItem.id;
                } else if (type === 'type') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('device-type').value = pathStr;
                    document.getElementById('device-type-id').value = lastItem.id;
                }
            }
            
            // 关闭模态框
            document.getElementById('select-modal').style.display = 'none';
        }
        
        // 重置选择
        function resetSelect() {
            currentSelectPath = [];
            loadSelectData(0);
        }
        
        // 添加模态框按钮事件
        document.querySelector('.confirm-btn').addEventListener('click', confirmSelect);
        document.querySelector('.cancel-btn').addEventListener('click', function() {
            document.getElementById('select-modal').style.display = 'none';
        });
        document.querySelector('.reset-btn').addEventListener('click', resetSelect);
        
        // 删除图纸
        function deleteDrawing(drawingId) {
            if (confirm('确定要删除这个图纸吗？')) {
                fetch(`api.php?action=deleteDrawing&id=${drawingId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 刷新页面
                        location.reload();
                    } else {
                        alert('删除失败: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('删除失败: ' + error.message);
                });
            }
        }
        
        // 提交表单
        document.getElementById('edit-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api.php?action=saveDevice', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('保存成功');
                    window.location.href = `/devices.php?did=${<?php echo $did; ?>}`;
                } else {
                    alert('保存失败: ' + data.message);
                }
            })
            .catch(error => {
                alert('保存失败: ' + error.message);
            });
        });
    </script>
    
    <style>
        .device-edit {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .error-message {
            text-align: center;
            padding: 50px 0;
            color: #e74c3c;
        }
        
        .error-message a {
            color: #3498db;
            text-decoration: none;
        }
        
        .error-message a:hover {
            text-decoration: underline;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        .required {
            color: #e74c3c;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .select-container {
            position: relative;
        }
        
        .select-container input[type="text"] {
            cursor: pointer;
            background-color: white;
        }
        
        .select-container input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .drawings-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .drawings-section h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .drawings-section h4 {
            margin-bottom: 10px;
            color: #555;
        }
        
        .drawings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .drawings-table th,
        .drawings-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .drawings-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .drawings-table tr:hover {
            background-color: #f0f0f0;
        }
        
        .drawings-table a {
            color: #3498db;
            text-decoration: none;
        }
        
        .drawings-table a:hover {
            text-decoration: underline;
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
        
        .no-drawings {
            text-align: center;
            color: #999;
            padding: 20px 0;
        }
        
        .upload-section {
            margin-top: 20px;
        }
        
        #drawing-upload {
            margin-bottom: 10px;
        }
        
        .upload-note {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .form-buttons {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 20px;
        }
        
        .cancel-btn,
        .save-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .cancel-btn {
            background-color: #95a5a6;
            color: white;
        }
        
        .save-btn {
            background-color: #27ae60;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #7f8c8d;
        }
        
        .save-btn:hover {
            background-color: #229954;
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
            max-width: 600px;
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
        
        .modal-body {
            padding: 15px;
            overflow-y: auto;
        }
        
        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
        }
        
        .reset-btn,
        .cancel-btn {
            background-color: #95a5a6;
            color: white;
        }
        
        .confirm-btn {
            background-color: #3498db;
            color: white;
        }
        
        .modal-btn:hover {
            opacity: 0.9;
        }
        
        .select-path {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .path-item {
            cursor: pointer;
            color: #3498db;
        }
        
        .path-item:hover {
            text-decoration: underline;
        }
        
        .select-items {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .select-item {
            padding: 10px 15px;
            background-color: #f5f5f5;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .select-item.selected {
            background-color: #3498db;
            color: white;
        }
        
        .select-item:hover:not(.selected) {
            background-color: #e0e0e0;
        }
        
        .loading,
        .error {
            text-align: center;
            padding: 50px 0;
            color: #999;
        }
        
        .error {
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .device-edit {
                padding: 20px;
            }
            
            .form-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cancel-btn,
            .save-btn {
                width: 100%;
                max-width: 300px;
            }
            
            .modal-content {
                width: 95%;
                max-height: 90vh;
            }
        }
    </style>