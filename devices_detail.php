<?php
// 确保did参数存在
if (!isset($did)) {
    header('Location: /devices.php');
    exit();
}

// 查询设备信息
$stmt = $pdo->prepare("SELECT * FROM devices WHERE did = :did AND status = 1");
$stmt->execute(['did' => $did]);
$device = $stmt->fetch();

// 如果设备不存在，跳转到编辑页面
if (!$device) {
    header("Location: /devices.php?did=$did&mode=edit");
    exit();
}

// 获取设备类型名称
$stmt = $pdo->prepare("SELECT type_name FROM types WHERE tid = :tid AND status = 1");
$stmt->execute(['tid' => $device['tid']]);
$type = $stmt->fetch();
$type_name = $type ? $type['type_name'] : '其他类型';

// 获取所属站场名称
$stmt = $pdo->prepare("SELECT station_name FROM stations WHERE sid = :sid AND status = 1");
$stmt->execute(['sid' => $device['sid']]);
$station = $stmt->fetch();
$station_name = $station ? $station['station_name'] : '其他站场';

// 获取包保部门名称
$stmt = $pdo->prepare("SELECT full_name FROM departments WHERE cid = :cid AND status = 1");
$stmt->execute(['cid' => $device['cid']]);
$department = $stmt->fetch();
$department_name = $department ? $department['full_name'] : '其他部门';
?>
    <div class="device-detail">
        <h2 style="text-align: center; margin-bottom: 30px;">设备详情</h2>
        
        <div class="device-info">
            <div class="info-item">
                <label>设备名称：</label>
                <span><?php echo $device['device_name']; ?></span>
            </div>
            <div class="info-item">
                <label>设备类型：</label>
                <span><?php echo $type_name; ?></span>
            </div>
            <div class="info-item">
                <label>所属站场：</label>
                <span><?php echo $station_name; ?></span>
            </div>
            <div class="info-item">
                <label>包保部门：</label>
                <span><?php echo $department_name; ?></span>
            </div>
            <div class="info-item">
                <label>包保人姓名：</label>
                <span><?php echo $device['keepers']; ?></span>
            </div>
            <div class="info-item">
                <label>备注：</label>
                <span><?php echo $device['remark'] ? $device['remark'] : '无'; ?></span>
            </div>
        </div>
        
        <!-- 图纸折叠块 -->
        <div class="collapse-block">
            <div class="collapse-header" onclick="toggleCollapse('drawings')">
                <span>设备图纸 (<?php echo $device['drawing_count']; ?>)</span>
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
                <span>巡视记录</span>
                <button class="add-btn" onclick="event.stopPropagation(); openAddRecordModal('inspection')">新增</button>
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
                <span>检修记录</span>
                <button class="add-btn" onclick="event.stopPropagation(); openAddRecordModal('maintenance')">新增</button>
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
                <span>问题库记录</span>
                <button class="add-btn" onclick="event.stopPropagation(); openAddProblemModal()">新增</button>
                <span class="collapse-icon">▼</span>
            </div>
            <div id="problem-records" class="collapse-content" style="display: none;">
                <div id="problem-content">
                    <div class="loading">加载中...</div>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button class="edit-btn" onclick="window.location.href='/devices.php?did=<?php echo $did; ?>&mode=edit'">修改</button>
        </div>
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
                            <input type="text" id="work-department" readonly placeholder="请选择部门" value="<?php echo $department_name; ?>">
                            <input type="hidden" id="work-department-id" value="<?php echo $device['cid']; ?>">
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
                    <input type="hidden" id="problem-sid" value="<?php echo $device['sid']; ?>">
                    
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
                            <input type="text" id="problem-department" readonly placeholder="请选择部门" value="<?php echo $department_name; ?>">
                            <input type="hidden" id="problem-department-id" value="<?php echo $device['cid']; ?>">
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
                        loadDrawings();
                    } else if (id === 'inspection-records') {
                        loadInspectionRecords();
                    } else if (id === 'maintenance-records') {
                        loadMaintenanceRecords();
                    } else if (id === 'problem-records') {
                        loadProblemRecords();
                    }
                }
            } else {
                content.style.display = 'none';
                icon.textContent = '▼';
            }
        }
        
        // 加载图纸
        function loadDrawings() {
            fetch(`api.php?action=getDrawings&did=<?php echo $did; ?>`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('drawings-content');
                    
                    if (data.length > 0) {
                        let html = '<table class="drawings-table">';
                        html += '<thead><tr><th>序号</th><th>图纸名称</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.forEach((drawing, index) => {
                            const fullUrl = drawing.root_dir + drawing.link_name;
                            html += `<tr><td>${index + 1}</td><td><a href="${fullUrl}" target="_blank">${drawing.original_name}</a></td></tr>`;
                        });
                        
                        html += '</tbody></table>';
                        content.innerHTML = html;
                    } else {
                        content.innerHTML = '<p class="no-result">没有查询到图纸</p>';
                    }
                })
                .catch(error => {
                    const content = document.getElementById('drawings-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                });
        }
        
        // 加载巡视记录
        function loadInspectionRecords() {
            fetch(`api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=1`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('inspection-content');
                    
                    if (data.length > 0) {
                        let html = '<table class="records-table">';
                        html += '<thead><tr><th>序号</th><th>作业时间</th><th>作业人员</th><th>操作</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.forEach((record, index) => {
                            html += `<tr data-id="${record.wid}"><td>${index + 1}</td><td>${record.work_date}</td><td>${record.workers}</td><td><button class="delete-btn" onclick="deleteRecord(${record.wid}, 'inspection')">删除</button></td></tr>`;
                        });
                        
                        html += '</tbody></table>';
                        content.innerHTML = html;
                    } else {
                        content.innerHTML = '<p class="no-result">没有查询到巡视记录</p>';
                    }
                })
                .catch(error => {
                    const content = document.getElementById('inspection-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                });
        }
        
        // 加载检修记录
        function loadMaintenanceRecords() {
            fetch(`api.php?action=getWorkLogs&did=<?php echo $did; ?>&type=2`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('maintenance-content');
                    
                    if (data.length > 0) {
                        let html = '<table class="records-table">';
                        html += '<thead><tr><th>序号</th><th>作业时间</th><th>作业人员</th><th>操作</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.forEach((record, index) => {
                            html += `<tr data-id="${record.wid}"><td>${index + 1}</td><td>${record.work_date}</td><td>${record.workers}</td><td><button class="delete-btn" onclick="deleteRecord(${record.wid}, 'maintenance')">删除</button></td></tr>`;
                        });
                        
                        html += '</tbody></table>';
                        content.innerHTML = html;
                    } else {
                        content.innerHTML = '<p class="no-result">没有查询到检修记录</p>';
                    }
                })
                .catch(error => {
                    const content = document.getElementById('maintenance-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                });
        }
        
        // 加载问题库记录
        function loadProblemRecords() {
            fetch(`api.php?action=getProblems&did=<?php echo $did; ?>`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('problem-content');
                    
                    if (data.length > 0) {
                        let html = '<table class="problems-table">';
                        html += '<thead><tr><th>序号</th><th>问题描述</th><th>发现时间</th><th>当前状态</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.forEach((problem, index) => {
                            const statusClass = problem.flow === 0 ? 'status-red' : 'status-green';
                            const statusText = problem.flow === 0 ? '已创建' : '已解决';
                            html += `<tr><td>${index + 1}</td><td><a href="problems.php?pid=${problem.pid}">${problem.description}</a></td><td>${problem.create_time}</td><td class="${statusClass}">${statusText}</td></tr>`;
                        });
                        
                        html += '</tbody></table>';
                        content.innerHTML = html;
                    } else {
                        content.innerHTML = '<p class="no-result">没有查询到问题记录</p>';
                    }
                })
                .catch(error => {
                    const content = document.getElementById('problem-content');
                    content.innerHTML = `<p class="error">加载失败: ${error.message}</p>`;
                });
        }
        
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
            
            modal.style.display = 'block';
        }
        
        // 关闭新增记录模态框
        function closeAddRecordModal() {
            document.getElementById('add-record-modal').style.display = 'none';
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
                        loadInspectionRecords();
                    } else if (recordType === '2') {
                        document.getElementById('maintenance-content').innerHTML = '<div class="loading">加载中...</div>';
                        loadMaintenanceRecords();
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
            
            modal.style.display = 'block';
        }
        
        // 关闭新增问题模态框
        function closeAddProblemModal() {
            document.getElementById('add-problem-modal').style.display = 'none';
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
                    loadProblemRecords();
                    
                    alert('添加成功');
                } else {
                    alert('添加失败: ' + data.message);
                }
            })
            .catch(error => {
                alert('添加失败: ' + error.message);
            });
        }
        
        // 删除记录
        function deleteRecord(wid, type) {
            if (confirm('确定要删除这条记录吗？')) {
                fetch(`api.php?action=deleteWorkLog&wid=${wid}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        
                        // 重新加载对应记录
                        if (type === 'inspection') {
                            document.getElementById('inspection-content').innerHTML = '<div class="loading">加载中...</div>';
                            loadInspectionRecords();
                        } else if (type === 'maintenance') {
                            document.getElementById('maintenance-content').innerHTML = '<div class="loading">加载中...</div>';
                            loadMaintenanceRecords();
                        }
                        
                        alert('删除成功');
                    } else {
                        alert('删除失败: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('删除失败: ' + error.message);
                });
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
        
        .drawings-table th,
        .drawings-table td,
        .records-table th,
        .records-table td,
        .problems-table th,
        .problems-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
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
        .problems-table a:hover {
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
            .device-detail {
                padding: 20px;
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
    </style>