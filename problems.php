<?php
// 设置页面标题
$title = '问题库管理';

// 引入配置文件和页眉
include 'config.php';

// 获取数据库连接
$pdo = getDbConnection();

// 检查是否是查看问题详情
$viewDetail = isset($_GET['pid']) && preg_match('/^[1-9]\d{9}$/', $_GET['pid']);

// 获取当前页码
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$pageSize = 10;
$offset = ($page - 1) * $pageSize;

// 获取所有部门（用于筛选）
$departments = [];
$stmt = $pdo->query("SELECT cid, full_name FROM departments WHERE status = 1 AND parent_id = 0 ORDER BY cid ASC");
$departments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

// 获取所有站场（用于筛选）
$stations = [];
$stmt = $pdo->query("SELECT sid, station_name FROM stations WHERE status = 1 AND parent_id = 0 ORDER BY sid ASC");
$stations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

// 获取所有设备类型（用于筛选）
$types = [];
$stmt = $pdo->query("SELECT tid, type_name FROM types WHERE status = 1 AND parent_id = 0 ORDER BY tid ASC");
$types = $stmt->fetchAll(\PDO::FETCH_ASSOC);

// 定义问题状态
$problemStatus = [
    0 => '未解决',
    1 => '处理中',
    2 => '已解决'
];

// 如果是查看问题详情
if ($viewDetail) {
    $pid = $_GET['pid'];
    
    // 查询问题详情
    $stmt = $pdo->prepare("SELECT * FROM problems WHERE pid = :pid AND status != -1");
    $stmt->execute(['pid' => $pid]);
    $problem = $stmt->fetch();
    
    if (!$problem) {
        ?>
        <div class="error-message">
            <p>该问题不存在或已被删除</p>
            <p><a href="/problems.php">返回问题列表</a></p>
        </div>
        <?php
        include 'footer.php';
        exit();
    }
    
    // 获取关联设备信息
    $deviceInfo = '';
    if ($problem['did']) {
        $stmt = $pdo->prepare("SELECT device_name FROM devices WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $problem['did']]);
        $device = $stmt->fetch();
        if ($device) {
            $deviceInfo = $device['device_name'];
        }
    }
    
    // 获取上报部门信息
    $departmentInfo = '';
    if ($problem['cid']) {
        $stmt = $pdo->prepare("SELECT full_name FROM departments WHERE cid = :cid AND status = 1");
        $stmt->execute(['cid' => $problem['cid']]);
        $department = $stmt->fetch();
        if ($department) {
            $departmentInfo = $department['full_name'];
        }
    }
    
    // 获取处理记录
    $processingRecords = [];
    $stmt = $pdo->prepare("SELECT * FROM problem_process WHERE pid = :pid ORDER BY process_time ASC");
    $stmt->execute(['pid' => $pid]);
    $processingRecords = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    // 获取附件列表
    $attachments = [];
    $stmt = $pdo->prepare("SELECT * FROM problem_attachments WHERE pid = :pid AND status = 1");
    $stmt->execute(['pid' => $pid]);
    $attachments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>
    <div class="problem-detail">
        <div class="detail-header">
            <h2>问题详情</h2>
            <button class="back-btn" onclick="window.location.href='/problems.php'">返回列表</button>
        </div>
        
        <div class="detail-content">
            <div class="detail-section">
                <div class="detail-row">
                    <div class="detail-label">问题ID：</div>
                    <div class="detail-value"><?php echo $problem['pid']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">问题描述：</div>
                    <div class="detail-value"><?php echo $problem['description']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">关联设备：</div>
                    <div class="detail-value"><?php echo $deviceInfo ? $deviceInfo : '无'; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">上报部门：</div>
                    <div class="detail-value"><?php echo $departmentInfo; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">上报人员：</div>
                    <div class="detail-value"><?php echo $problem['reporter']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">上报时间：</div>
                    <div class="detail-value"><?php echo $problem['report_time']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">当前状态：</div>
                    <div class="detail-value status-badge status-<?php echo $problem['status']; ?>">
                        <?php echo $problemStatus[$problem['status']]; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($attachments)): ?>
            <div class="detail-section">
                <h3>问题附件</h3>
                <div class="attachments-list">
                    <?php foreach ($attachments as $attachment): ?>
                        <div class="attachment-item">
                            <a href="<?php echo $attachment['root_dir'] . $attachment['link_name']; ?>" target="_blank">
                                <span class="attachment-icon">📎</span>
                                <span class="attachment-name"><?php echo $attachment['original_name']; ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="detail-section">
                <h3>处理记录</h3>
                <div class="processing-records">
                    <?php if (empty($processingRecords)): ?>
                        <p class="no-records">暂无处理记录</p>
                    <?php else: ?>
                        <?php foreach ($processingRecords as $record): ?>
                            <div class="processing-record">
                                <div class="record-header">
                                    <span class="record-person"><?php echo $record['operator']; ?></span>
                                    <span class="record-time"><?php echo $record['process_time']; ?></span>
                                </div>
                                <div class="record-content"><?php echo $record['content']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    // 构建查询条件
    $conditions = "WHERE p.status != -1";
    $params = [];
    
    // 部门筛选
    if (isset($_GET['cid']) && !empty($_GET['cid'])) {
        $cid = $_GET['cid'];
        $conditions .= " AND p.cid = :cid";
        $params['cid'] = $cid;
    }
    
    // 站场筛选
    if (isset($_GET['sid']) && !empty($_GET['sid'])) {
        $sid = $_GET['sid'];
        $conditions .= " AND d.sid = :sid";
        $params['sid'] = $sid;
    }
    
    // 设备类型筛选
    if (isset($_GET['tid']) && !empty($_GET['tid'])) {
        $tid = $_GET['tid'];
        $conditions .= " AND d.tid = :tid";
        $params['tid'] = $tid;
    }
    
    // 状态筛选
    if (isset($_GET['status']) && is_numeric($_GET['status']) && in_array($_GET['status'], array_keys($problemStatus))) {
        $status = $_GET['status'];
        $conditions .= " AND p.status = :status";
        $params['status'] = $status;
    }
    
    // 关键词搜索
    if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
        $keyword = '%' . trim($_GET['keyword']) . '%';
        $conditions .= " AND (p.description LIKE :keyword OR d.device_name LIKE :keyword OR p.pid LIKE :keyword)";
        $params['keyword'] = $keyword;
    }
    
    // 查询问题总数
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM problems p LEFT JOIN devices d ON p.did = d.did " . $conditions);
    $stmt->execute($params);
    $totalCount = $stmt->fetchColumn();
    $totalPages = ceil($totalCount / $pageSize);
    
    // 查询问题列表
    $problems = [];
    $stmt = $pdo->prepare("SELECT p.*, d.device_name, dept.full_name as department_name 
        FROM problems p 
        LEFT JOIN devices d ON p.did = d.did 
        LEFT JOIN departments dept ON p.cid = dept.cid 
        " . $conditions . " 
        ORDER BY p.report_time DESC 
        LIMIT :offset, :pageSize");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
    // 绑定其他参数
    foreach ($params as $key => $value) {
        if ($key != ':offset' && $key != ':pageSize') {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $problems = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>
    <div class="problems-container">
        <div class="problems-header">
            <h2>问题库查询</h2>
        </div>
        
        <div class="problems-content">
            <div class="filter-panel">
                <form id="filter-form" method="get" action="/problems.php">
                    <div class="filter-section">
                        <h3>筛选条件</h3>
                        
                        <div class="filter-group">
                            <label for="department-filter">上报部门：</label>
                            <select id="department-filter" name="cid">
                                <option value="">全部部门</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['cid']; ?>" <?php echo (isset($_GET['cid']) && $_GET['cid'] == $dept['cid']) ? 'selected' : ''; ?>>
                                        <?php echo $dept['full_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="station-filter">所属站场：</label>
                            <select id="station-filter" name="sid">
                                <option value="">全部站场</option>
                                <?php foreach ($stations as $station): ?>
                                    <option value="<?php echo $station['sid']; ?>" <?php echo (isset($_GET['sid']) && $_GET['sid'] == $station['sid']) ? 'selected' : ''; ?>>
                                        <?php echo $station['station_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="type-filter">设备类型：</label>
                            <select id="type-filter" name="tid">
                                <option value="">全部类型</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?php echo $type['tid']; ?>" <?php echo (isset($_GET['tid']) && $_GET['tid'] == $type['tid']) ? 'selected' : ''; ?>>
                                        <?php echo $type['type_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="status-filter">问题状态：</label>
                            <select id="status-filter" name="status">
                                <option value="">全部状态</option>
                                <?php foreach ($problemStatus as $statusCode => $statusName): ?>
                                    <option value="<?php echo $statusCode; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $statusCode) ? 'selected' : ''; ?>>
                                        <?php echo $statusName; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="keyword-filter">关键词搜索：</label>
                            <input type="text" id="keyword-filter" name="keyword" placeholder="问题描述、设备名称或问题ID" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="search-btn">查询</button>
                        <button type="button" class="reset-btn" onclick="resetFilter()">重置</button>
                    </div>
                </form>
            </div>
            
            <div class="problems-list">
                <div class="list-header">
                    <div class="total-count">共找到 <span><?php echo $totalCount; ?></span> 个问题</div>
                </div>
                
                <div class="list-table">
                    <table>
                        <thead>
                            <tr>
                                <th>问题ID</th>
                                <th>问题描述</th>
                                <th>关联设备</th>
                                <th>上报部门</th>
                                <th>上报时间</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($problems)): ?>
                                <tr class="no-data">
                                    <td colspan="6">暂无数据</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($problems as $problem): ?>
                                    <tr>
                                        <td><a href="/problems.php?pid=<?php echo $problem['pid']; ?>" class="problem-link"><?php echo $problem['pid']; ?></a></td>
                                        <td class="description-col" title="<?php echo $problem['description']; ?>"><?php echo substr($problem['description'], 0, 50); ?><?php echo strlen($problem['description']) > 50 ? '...' : ''; ?></td>
                                        <td><?php echo $problem['device_name'] ? $problem['device_name'] : '无'; ?></td>
                                        <td><?php echo $problem['department_name']; ?></td>
                                        <td><?php echo $problem['report_time']; ?></td>
                                        <td><span class="status-badge status-<?php echo $problem['status']; ?>"><?php echo $problemStatus[$problem['status']]; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <a href="/problems.php?<?php echo buildPaginationParams(1); ?>" class="page-btn <?php echo $page == 1 ? 'disabled' : ''; ?>">首页</a>
                    <a href="/problems.php?<?php echo buildPaginationParams(max(1, $page - 1)); ?>" class="page-btn <?php echo $page == 1 ? 'disabled' : ''; ?>">上一页</a>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="/problems.php?<?php echo buildPaginationParams($i); ?>" class="page-btn <?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <a href="/problems.php?<?php echo buildPaginationParams(min($totalPages, $page + 1)); ?>" class="page-btn <?php echo $page == $totalPages ? 'disabled' : ''; ?>">下一页</a>
                    <a href="/problems.php?<?php echo buildPaginationParams($totalPages); ?>" class="page-btn <?php echo $page == $totalPages ? 'disabled' : ''; ?>">末页</a>
                    
                    <span class="page-info">
                        第 <input type="text" id="goto-page" value="<?php echo $page; ?>" min="1" max="<?php echo $totalPages; ?>" size="3"> 页
                        / 共 <?php echo $totalPages; ?> 页
                        <button onclick="gotoPage()">跳转</button>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 重置筛选条件
        function resetFilter() {
            document.getElementById('department-filter').value = '';
            document.getElementById('station-filter').value = '';
            document.getElementById('type-filter').value = '';
            document.getElementById('status-filter').value = '';
            document.getElementById('keyword-filter').value = '';
        }
        
        // 构建分页参数
        function buildPaginationParams(page) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('page', page);
            return urlParams.toString();
        }
        
        // 跳转到指定页码
        function gotoPage() {
            const input = document.getElementById('goto-page');
            let page = parseInt(input.value);
            const totalPages = <?php echo $totalPages; ?>;
            
            if (isNaN(page) || page < 1) {
                page = 1;
            } else if (page > totalPages) {
                page = totalPages;
            }
            
            input.value = page;
            window.location.href = '/problems.php?' + buildPaginationParams(page);
        }
        
        // 监听页码输入框的回车事件
        document.getElementById('goto-page').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                gotoPage();
            }
        });
    </script>
    
    <style>
        .problems-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .problems-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .problems-header h2 {
            margin: 0;
            color: #333;
        }
        
        .problems-content {
            display: flex;
            min-height: 500px;
        }
        
        .filter-panel {
            width: 280px;
            padding: 20px;
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .filter-section h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 16px;
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        .filter-group select,
        .filter-group input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .search-btn,
        .reset-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .search-btn {
            background-color: #3498db;
            color: white;
        }
        
        .reset-btn {
            background-color: #95a5a6;
            color: white;
        }
        
        .search-btn:hover {
            background-color: #2980b9;
        }
        
        .reset-btn:hover {
            background-color: #7f8c8d;
        }
        
        .problems-list {
            flex: 1;
            padding: 20px;
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .total-count {
            font-size: 14px;
            color: #666;
        }
        
        .total-count span {
            color: #3498db;
            font-weight: bold;
        }
        
        .list-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .list-table th,
        .list-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .list-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
            font-size: 14px;
        }
        
        .list-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .list-table tr.no-data td {
            text-align: center;
            padding: 50px 0;
            color: #999;
        }
        
        .problem-link {
            color: #3498db;
            text-decoration: none;
        }
        
        .problem-link:hover {
            text-decoration: underline;
        }
        
        .description-col {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-0 {
            background-color: #ffcccc;
            color: #cc0000;
        }
        
        .status-1 {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-2 {
            background-color: #d4edda;
            color: #155724;
        }
        
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
            background-color: white;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .page-btn:hover:not(.disabled) {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .page-btn.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .page-info {
            margin-left: 10px;
            font-size: 14px;
        }
        
        .page-info input {
            width: 50px;
            text-align: center;
        }
        
        /* 问题详情页样式 */
        .problem-detail {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .detail-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-header h2 {
            margin: 0;
            color: #333;
        }
        
        .back-btn {
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .back-btn:hover {
            background-color: #2980b9;
        }
        
        .detail-content {
            padding: 30px;
        }
        
        .detail-section {
            margin-bottom: 30px;
        }
        
        .detail-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .detail-row {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
        }
        
        .detail-label {
            width: 120px;
            font-weight: bold;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
            min-width: 200px;
        }
        
        .attachments-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .attachment-item a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            text-decoration: none;
            color: #3498db;
            transition: background-color 0.3s;
        }
        
        .attachment-item a:hover {
            background-color: #e9ecef;
        }
        
        .attachment-icon {
            font-size: 18px;
        }
        
        .processing-records {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .processing-record {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .record-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .record-person {
            font-weight: bold;
            color: #333;
        }
        
        .record-time {
            font-size: 12px;
            color: #666;
        }
        
        .record-content {
            padding: 15px;
            line-height: 1.6;
        }
        
        .no-records {
            text-align: center;
            color: #999;
            padding: 20px;
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
        
        @media (max-width: 1024px) {
            .problems-content {
                flex-direction: column;
            }
            
            .filter-panel {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
        }
        
        @media (max-width: 768px) {
            .filter-actions {
                flex-direction: column;
            }
            
            .list-table th,
            .list-table td {
                padding: 8px;
                font-size: 14px;
            }
            
            .description-col {
                max-width: 150px;
            }
            
            .pagination {
                flex-direction: column;
                gap: 10px;
            }
            
            .page-info {
                margin-left: 0;
            }
            
            .detail-content {
                padding: 20px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
<?php
}

// 构建分页参数函数
function buildPaginationParams($page) {
    $params = [];
    
    // 保留部门筛选参数
    if (isset($_GET['cid']) && !empty($_GET['cid'])) {
        $params['cid'] = $_GET['cid'];
    }
    
    // 保留站场筛选参数
    if (isset($_GET['sid']) && !empty($_GET['sid'])) {
        $params['sid'] = $_GET['sid'];
    }
    
    // 保留设备类型筛选参数
    if (isset($_GET['tid']) && !empty($_GET['tid'])) {
        $params['tid'] = $_GET['tid'];
    }
    
    // 保留状态筛选参数
    if (isset($_GET['status']) && is_numeric($_GET['status'])) {
        $params['status'] = $_GET['status'];
    }
    
    // 保留关键词搜索参数
    if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
        $params['keyword'] = trim($_GET['keyword']);
    }
    
    // 添加页码参数
    $params['page'] = $page;
    
    // 构建URL参数字符串
    $queryString = http_build_query($params);
    return $queryString;
}