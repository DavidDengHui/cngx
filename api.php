<?php
// 确保有数据库连接
if (!isset($pdo)) {
    // 尝试包含配置文件
    if (file_exists('config.php')) {
        include 'config.php';
        // 获取数据库连接
        $pdo = getDbConnection();
    } else {
        // 配置文件不存在，返回错误
        header('Content-Type: application/json');
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['success' => false, 'message' => '服务器配置错误']);
        exit;
    }
}

// 设置默认响应头
header('Content-Type: application/json');

// 获取请求参数
$action = isset($_GET['action']) ? $_GET['action'] : '';

// 响应数据结构
$response = array(
    'success' => false,
    'message' => '',
    'data' => null
);

// 定义问题状态
$problemStatus = [
    0 => '未解决',
    1 => '处理中',
    2 => '已解决'
];

// 根据action分发请求
switch ($action) {
    case 'getDepartments':
        getDepartments();
        break;
    case 'getStations':
        getStations();
        break;
    case 'getTypes':
        getTypes();
        break;
    case 'searchDevices':
        searchDevices();
        break;
    case 'saveDevice':
        saveDevice();
        break;
    case 'deleteDrawing':
        deleteDrawing();
        break;
    case 'addInspection':
        addInspection();
        break;
    case 'addMaintenance':
        addMaintenance();
        break;
    case 'addProblem':
        addProblem();
        break;
    case 'deleteInspection':
        deleteInspection();
        break;
    case 'deleteMaintenance':
        deleteMaintenance();
        break;
    case 'deleteProblemRecord':
        deleteProblemRecord();
        break;
    case 'updateProblemStatus':
        updateProblemStatus();
        break;
    case 'addProblemProcess':
        addProblemProcess();
        break;
    case 'getDrawings':
        getDrawings();
        break;
    case 'getWorkLogs':
        getWorkLogs();
        break;
    case 'getProblems':
        getProblems();
        break;
    case 'getWorkLogsCount':
        getWorkLogsCount();
        break;
    case 'getProblemsCount':
        getProblemsCount();
        break;
    case 'getDeviceDetail':
        getDeviceDetail();
        break;
    case 'getDrawings':
        getDrawings();
        break;
    case 'getDashboardStats':
        getDashboardStats();
        break;
    case 'getProblemDetail':
        getProblemDetail();
        break;
    case 'getProblemList':
        getProblemList();
        break;
    case 'getFilterOptions':
        getFilterOptions();
        break;
    case 'uploadProblemPhoto':
        uploadProblemPhoto();
        break;
    default:
        echo json_encode(['success' => false, 'message' => '未知的操作']);
        break;
}

// 获取部门数据
function getDepartments() {
    global $pdo;
    
    // 检查是否提供了childId参数
    if (isset($_GET['childId'])) {
        $childId = intval($_GET['childId']);
        
        try {
            // 先查询子部门记录，获取parent_id
            $stmt = $pdo->prepare("SELECT parent_id FROM departments WHERE cid = :childId AND status = 1");
            $stmt->execute(['childId' => $childId]);
            $childDept = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($childDept && $childDept['parent_id'] > 0) {
                // 再查询父部门记录
                $parentId = $childDept['parent_id'];
                $stmt = $pdo->prepare("SELECT cid as id, full_name as name, short_name as shortname FROM departments WHERE cid = :parentId AND status = 1");
                $stmt->execute(['parentId' => $parentId]);
                $parentDept = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($parentDept) {
                    echo json_encode($parentDept);
                } else {
                    echo json_encode(['success' => false, 'message' => '未找到父部门信息']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => '未找到子部门或该部门没有父部门']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '获取父部门数据失败: ' . $e->getMessage()]);
        }
    } else {
        // 原有逻辑：通过parentId查询子部门
        $parentId = isset($_GET['parentId']) ? intval($_GET['parentId']) : 0;
        
        try {
            $stmt = $pdo->prepare("SELECT cid as id, full_name as name, short_name as shortname FROM departments WHERE parent_id = :parentId AND status = 1 ORDER BY cid ASC");
            $stmt->execute(['parentId' => $parentId]);
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($departments);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '获取部门数据失败: ' . $e->getMessage()]);
        }
    }
}

// 获取站场数据
function getStations() {
    global $pdo;
    
    $parentId = isset($_GET['parentId']) ? intval($_GET['parentId']) : 0;
    
    try {
        $stmt = $pdo->prepare("SELECT sid as id, station_name as name FROM stations WHERE parent_id = :parentId AND status = 1 ORDER BY sid ASC");
        $stmt->execute(['parentId' => $parentId]);
        $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($stations);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取站场数据失败: ' . $e->getMessage()]);
    }
}

// 获取设备类型数据
function getTypes() {
    global $pdo;
    
    $parentId = isset($_GET['parentId']) ? intval($_GET['parentId']) : 0;
    
    try {
        $stmt = $pdo->prepare("SELECT tid as id, type_name as name FROM types WHERE parent_id = :parentId AND status = 1 ORDER BY tid ASC");
        $stmt->execute(['parentId' => $parentId]);
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($types);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取设备类型数据失败: ' . $e->getMessage()]);
    }
}

// 查询设备
function searchDevices() {
    global $pdo;
    
    try {
        $departmentId = isset($_GET['departmentId']) ? trim($_GET['departmentId']) : '';
        $stationId = isset($_GET['stationId']) ? trim($_GET['stationId']) : '';
        $typeId = isset($_GET['typeId']) ? trim($_GET['typeId']) : '';
        $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
        
        // 获取分页参数
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 20;
        
        // 如果pageSize为0，表示查询所有记录
        $limitClause = '';
        if ($pageSize > 0) {
            $offset = ($page - 1) * $pageSize;
            $limitClause = "LIMIT $offset, $pageSize";
        }
        
        // 构建查询条件
        $conditions = "WHERE status = 1";
        $params = [];
        
        // 处理部门查询（包含子部门）
        if ($departmentId) {
            $departmentIds = getChildIds('departments', 'cid', 'parent_id', $departmentId);
            $departmentIds[] = $departmentId; // 包含自身
            $inClause = implode(',', array_fill(0, count($departmentIds), '?'));
            $conditions .= " AND cid IN ($inClause)";
            $params = array_merge($params, $departmentIds);
        }
        
        // 处理站场查询（包含子站场）
        if ($stationId) {
            $stationIds = getChildIds('stations', 'sid', 'parent_id', $stationId);
            $stationIds[] = $stationId; // 包含自身
            $inClause = implode(',', array_fill(0, count($stationIds), '?'));
            $conditions .= " AND sid IN ($inClause)";
            $params = array_merge($params, $stationIds);
        }
        
        // 处理设备类型查询（包含子类型）
        if ($typeId) {
            $typeIds = getChildIds('types', 'tid', 'parent_id', $typeId);
            $typeIds[] = $typeId; // 包含自身
            $inClause = implode(',', array_fill(0, count($typeIds), '?'));
            $conditions .= " AND tid IN ($inClause)";
            $params = array_merge($params, $typeIds);
        }
        
        // 处理关键字搜索
        if ($keywords) {
            $keywords = '%' . $keywords . '%'; // 添加通配符
            $conditions .= " AND (device_name LIKE ? OR remark LIKE ?)";
            $params[] = $keywords;
            $params[] = $keywords;
        }
        
        // 查询总记录数
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM devices $conditions");
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // 查询当前页数据
        $stmt = $pdo->prepare("SELECT did, device_name, remark FROM devices $conditions ORDER BY device_name ASC $limitClause");
        $stmt->execute($params);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 返回分页结果
        echo json_encode([
            'data' => $devices,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '查询设备失败: ' . $e->getMessage()]);
    }
}

// 递归获取所有子分类ID
function getChildIds($table, $idField, $parentField, $parentId) {
    global $pdo;
    
    $childIds = [];
    try {
        // 获取直接子分类
        $stmt = $pdo->prepare("SELECT $idField FROM $table WHERE $parentField = ? AND status = 1");
        $stmt->execute([$parentId]);
        $directChildren = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // 递归获取所有间接子分类
        foreach ($directChildren as $childId) {
            $childIds[] = $childId;
            $grandChildIds = getChildIds($table, $idField, $parentField, $childId);
            $childIds = array_merge($childIds, $grandChildIds);
        }
    } catch (Exception $e) {
        // 错误处理
    }
    
    return $childIds;
}

// 保存设备信息
function saveDevice() {
    global $pdo;
    
    try {
        // 检查是否有文件上传
        $drawingFiles = [];
        if (!empty($_FILES['drawing_upload']['name'][0])) {
            $drawingFiles = processDrawingUploads();
        }
        
        // 获取表单数据
        $did = $_POST['did'];
        $deviceName = $_POST['device_name'];
        $typeId = $_POST['device_type_id'];
        $stationId = $_POST['device_station_id'];
        $departmentId = $_POST['device_department_id'];
        $keepers = $_POST['device_keepers'];
        $remark = $_POST['device_remark'];
        
        // 检查设备是否存在
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM devices WHERE did = :did");
        $stmt->execute(['did' => $did]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            // 更新设备信息
            $stmt = $pdo->prepare("UPDATE devices SET device_name = :deviceName, tid = :typeId, sid = :stationId, cid = :departmentId, keepers = :keepers, remark = :remark, update_time = NOW() WHERE did = :did");
            $stmt->execute([
                'deviceName' => $deviceName,
                'typeId' => $typeId,
                'stationId' => $stationId,
                'departmentId' => $departmentId,
                'keepers' => $keepers,
                'remark' => $remark,
                'did' => $did
            ]);
        } else {
            // 创建新设备
            $stmt = $pdo->prepare("INSERT INTO devices (did, device_name, tid, sid, cid, keepers, remark, create_time, update_time, status) VALUES (:did, :deviceName, :typeId, :stationId, :departmentId, :keepers, :remark, NOW(), NOW(), 1)");
            $stmt->execute([
                'did' => $did,
                'deviceName' => $deviceName,
                'typeId' => $typeId,
                'stationId' => $stationId,
                'departmentId' => $departmentId,
                'keepers' => $keepers,
                'remark' => $remark
            ]);
        }
        
        // 保存上传的图纸
        if (!empty($drawingFiles)) {
            foreach ($drawingFiles as $file) {
                $stmt = $pdo->prepare("INSERT INTO drawings (did, original_name, link_name, root_dir, file_size, upload_time, status) VALUES (:did, :originalName, :linkName, :rootDir, :fileSize, NOW(), 1)");
                $stmt->execute([
                    'did' => $did,
                    'originalName' => $file['originalName'],
                    'linkName' => $file['linkName'],
                    'rootDir' => $file['rootDir'],
                    'fileSize' => $file['fileSize']
                ]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => '保存成功']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '保存失败: ' . $e->getMessage()]);
    }
}

// 处理图纸上传
function processDrawingUploads() {
    $uploadDir = __DIR__ . '/uploads/drawings/';
    $webDir = '/uploads/drawings/';
    
    // 确保上传目录存在
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $result = [];
    
    foreach ($_FILES['drawing_upload']['name'] as $key => $name) {
        $tmpName = $_FILES['drawing_upload']['tmp_name'][$key];
        $error = $_FILES['drawing_upload']['error'][$key];
        
        if ($error === UPLOAD_ERR_OK) {
            // 生成唯一文件名
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $linkName = uniqid('drawing_', true) . '.' . $ext;
            $fileSize = filesize($tmpName);
            
            // 移动文件
            if (move_uploaded_file($tmpName, $uploadDir . $linkName)) {
                $result[] = [
                    'originalName' => $name,
                    'linkName' => $linkName,
                    'rootDir' => $webDir,
                    'fileSize' => $fileSize
                ];
            }
        }
    }
    
    return $result;
}

// 删除图纸
function deleteDrawing() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        // 获取图纸信息
        $stmt = $pdo->prepare("SELECT link_name, root_dir FROM drawings WHERE id = :id AND status = 1");
        $stmt->execute(['id' => $id]);
        $drawing = $stmt->fetch();
        
        if ($drawing) {
            // 删除文件
            $filePath = __DIR__ . $drawing['root_dir'] . $drawing['link_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // 更新数据库状态
            $stmt = $pdo->prepare("UPDATE drawings SET status = -1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

// 添加巡视记录
function addInspection() {
    global $pdo;
    
    try {
        // 获取请求数据（支持JSON和表单格式）
        $data = [];
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // JSON格式请求
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
        } else {
            // 表单格式请求
            $data = $_POST;
        }
        
        $did = isset($data['did']) ? $data['did'] : '';
        $inspector = isset($data['inspector']) ? $data['inspector'] : ''; // 格式为"姓名1||姓名2||姓名3"
        $inspectionTime = isset($data['inspection_time']) ? $data['inspection_time'] : '';
        $content = isset($data['content']) ? $data['content'] : '';
        
        $stmt = $pdo->prepare("INSERT INTO inspections (did, inspector, inspection_time, content, create_time, status) VALUES (:did, :inspector, :inspectionTime, :content, NOW(), 1)");
        $stmt->execute([
            'did' => $did,
            'inspector' => $inspector,
            'inspectionTime' => $inspectionTime,
            'content' => $content
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '添加失败: ' . $e->getMessage()]);
    }
}

// 添加检修记录
function addMaintenance() {
    global $pdo;
    
    try {
        // 获取请求数据（支持JSON和表单格式）
        $data = [];
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // JSON格式请求
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
        } else {
            // 表单格式请求
            $data = $_POST;
        }
        
        $did = isset($data['did']) ? $data['did'] : '';
        $maintainer = isset($data['maintainer']) ? $data['maintainer'] : ''; // 格式为"姓名1||姓名2||姓名3"
        $maintenanceTime = isset($data['maintenance_time']) ? $data['maintenance_time'] : '';
        $content = isset($data['content']) ? $data['content'] : '';
        
        $stmt = $pdo->prepare("INSERT INTO maintenances (did, maintainer, maintenance_time, content, create_time, status) VALUES (:did, :maintainer, :maintenanceTime, :content, NOW(), 1)");
        $stmt->execute([
            'did' => $did,
            'maintainer' => $maintainer,
            'maintenanceTime' => $maintenanceTime,
            'content' => $content
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '添加失败: ' . $e->getMessage()]);
    }
}

// 添加问题记录
function addProblem() {
    global $pdo;
    
    try {
        // 获取请求数据（支持JSON和表单格式）
        $data = [];
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // JSON格式请求
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
        } else {
            // 表单格式请求
            $data = $_POST;
        }
        
        $did = isset($data['did']) ? $data['did'] : '';
        $reporter = isset($data['reporter']) ? $data['reporter'] : '';
        $reportTime = isset($data['report_time']) ? $data['report_time'] : '';
        $description = isset($data['description']) ? $data['description'] : '';
        $urgency = isset($data['urgency']) ? $data['urgency'] : '';
        
        // 获取部门ID - 优先使用客户端提交的department_id参数
        $cid = isset($data['department_id']) ? $data['department_id'] : '';
        
        // 如果客户端没有提交部门ID或者提交的值为空，则从设备表获取
        if (empty($cid)) {
            $stmt = $pdo->prepare("SELECT cid FROM devices WHERE did = :did AND status = 1");
            $stmt->execute(['did' => $did]);
            $device = $stmt->fetch();
            $cid = $device ? $device['cid'] : '';
        }
        
        // 生成问题ID（10位数）
        $pid = generateProblemId();
        
        // 插入问题记录
        $stmt = $pdo->prepare("INSERT INTO problems (pid, did, cid, reporter, report_time, description, urgency, status, create_time, update_time) VALUES (:pid, :did, :cid, :reporter, :reportTime, :description, :urgency, 0, NOW(), NOW())");
        $stmt->execute([
            'pid' => $pid,
            'did' => $did,
            'cid' => $cid,
            'reporter' => $reporter,
            'reportTime' => $reportTime,
            'description' => $description,
            'urgency' => $urgency
        ]);
        
        // 处理问题照片上传
        $photos = [];
        if (!empty($_FILES['photos']['name'][0])) {
            $photos = processProblemPhotos($pid);
        }
        
        // 保存照片信息
        if (!empty($photos)) {
            foreach ($photos as $photo) {
                $stmt = $pdo->prepare("INSERT INTO problem_attachments (pid, original_name, link_name, root_dir, file_size, upload_time, status) VALUES (:pid, :originalName, :linkName, :rootDir, :fileSize, NOW(), 1)");
                $stmt->execute([
                    'pid' => $pid,
                    'originalName' => $photo['originalName'],
                    'linkName' => $photo['linkName'],
                    'rootDir' => $photo['rootDir'],
                    'fileSize' => $photo['fileSize']
                ]);
            }
        }
        
        echo json_encode(['success' => true, 'pid' => $pid]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '添加失败: ' . $e->getMessage()]);
    }
}

// 处理问题照片上传
function processProblemPhotos($pid) {
    // 设置上传目录（统一放在problems文件夹下，不按日期分类）
    $uploadDir = __DIR__ . '/uploads/problems/';
    $webDir = '/uploads/problems/';
    
    // 确保上传目录存在
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $result = [];
    
    foreach ($_FILES['photos']['name'] as $key => $name) {
        $tmpName = $_FILES['photos']['tmp_name'][$key];
        $error = $_FILES['photos']['error'][$key];
        
        if ($error === UPLOAD_ERR_OK) {
            // 获取原始文件名和扩展名
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            
            // 生成唯一文件名: problem_10位时间戳.文件哈希值.文件类型
            $timestamp = time();
            // 计算文件哈希值
            $fileHash = md5_file($tmpName);
            $linkName = 'problem_' . $timestamp . '.' . $fileHash . '.' . $ext;
            $fileSize = filesize($tmpName);
            
            // 移动文件
            if (move_uploaded_file($tmpName, $uploadDir . $linkName)) {
                $result[] = [
                    'originalName' => $name,
                    'linkName' => $linkName,
                    'rootDir' => $webDir,
                    'fileSize' => $fileSize
                ];
            }
        }
    }
    
    return $result;
}

// 生成问题ID
function generateProblemId() {
    global $pdo;
    
    do {
        $year = date('Y');
        $random = mt_rand(100000, 999999);
        $pid = $year . substr($random, 0, 6);
        
        // 检查ID是否已存在
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM problems WHERE pid = :pid");
        $stmt->execute(['pid' => $pid]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);
    
    return $pid;
}

// 处理问题附件上传
function processProblemAttachments($pid) {
    // 设置上传目录（统一放在problems文件夹下，不按日期分类）
    $uploadDir = __DIR__ . '/uploads/problems/';
    $webDir = '/uploads/problems/';
    
    // 确保上传目录存在
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $result = [];
    
    foreach ($_FILES['attachments']['name'] as $key => $name) {
        $tmpName = $_FILES['attachments']['tmp_name'][$key];
        $error = $_FILES['attachments']['error'][$key];
        
        if ($error === UPLOAD_ERR_OK) {
            // 生成唯一文件名
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $linkName = uniqid('attachment_', true) . '.' . $ext;
            $fileSize = filesize($tmpName);
            
            // 移动文件
            if (move_uploaded_file($tmpName, $uploadDir . $linkName)) {
                $result[] = [
                    'originalName' => $name,
                    'linkName' => $linkName,
                    'rootDir' => $webDir,
                    'fileSize' => $fileSize
                ];
            }
        }
    }
    
    return $result;
}

// 删除巡视记录 - 将status设置为0
function deleteInspection() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("UPDATE inspections SET status = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

// 删除检修记录 - 将status设置为0
function deleteMaintenance() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("UPDATE maintenances SET status = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

// 删除问题记录（设备详情页中的问题记录） - 将status设置为0
function deleteProblemRecord() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("UPDATE problems SET status = 0 WHERE pid = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

// 更新问题状态
function updateProblemStatus() {
    global $pdo;
    
    try {
        $pid = $_POST['pid'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE problems SET status = :status, update_time = NOW() WHERE pid = :pid");
        $stmt->execute([
            'status' => $status,
            'pid' => $pid
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '更新失败: ' . $e->getMessage()]);
    }
}

// 添加问题处理记录
function addProblemProcess() {
    global $pdo;
    
    try {
        $pid = $_POST['pid'];
        $operator = $_POST['operator'];
        $content = $_POST['content'];
        
        $stmt = $pdo->prepare("INSERT INTO problem_process (pid, operator, content, process_time) VALUES (:pid, :operator, :content, NOW())");
        $stmt->execute([
            'pid' => $pid,
            'operator' => $operator,
            'content' => $content
        ]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '添加失败: ' . $e->getMessage()]);
    }
}

// 获取设备图纸
function getDrawings() {
    global $pdo;
    
    try {
        $did = $_GET['did'];
        // 获取分页参数
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 5;
        
        // 如果pageSize为0，表示查询所有记录
        $limitClause = '';
        if ($pageSize > 0) {
            $offset = ($page - 1) * $pageSize;
            $limitClause = "LIMIT $offset, $pageSize";
        }
        
        // 查询总记录数
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM drawings WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $did]);
        $total = $stmt->fetchColumn();
        
        // 查询当前页数据
        $stmt = $pdo->prepare("SELECT id, original_name, link_name, root_dir, file_size FROM drawings WHERE did = :did AND status = 1 ORDER BY upload_time DESC $limitClause");
        $stmt->execute(['did' => $did]);
        $drawings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 返回分页结果
        echo json_encode([
            'data' => $drawings,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取图纸失败: ' . $e->getMessage()]);
    }
}

// 获取工作记录（巡视记录和检修记录）
function getWorkLogs() {
    global $pdo;
    
    try {
        $did = $_GET['did'];
        $type = $_GET['type'];
        // 获取分页参数
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 5;
        
        // 如果pageSize为0，表示查询所有记录
        $limitClause = '';
        if ($pageSize > 0) {
            $offset = ($page - 1) * $pageSize;
            $limitClause = "LIMIT $offset, $pageSize";
        }
        
        if ($type == 1 || $type === 'inspection') {
            // 获取巡视记录总记录数
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inspections WHERE did = :did AND status = 1");
            $stmt->execute(['did' => $did]);
            $total = $stmt->fetchColumn();
            
            // 查询当前页巡视记录
            $stmt = $pdo->prepare("SELECT id as wid, inspector as workers, inspection_time as work_date, content, create_time, status as flow FROM inspections WHERE did = :did AND status = 1 ORDER BY inspection_time DESC $limitClause");
        } else if ($type == 2 || $type === 'maintenance') {
            // 获取检修记录总记录数
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM maintenances WHERE did = :did AND status = 1");
            $stmt->execute(['did' => $did]);
            $total = $stmt->fetchColumn();
            
            // 查询当前页检修记录
            $stmt = $pdo->prepare("SELECT id as wid, maintainer as workers, maintenance_time as work_date, content, create_time, status as flow FROM maintenances WHERE did = :did AND status = 1 ORDER BY maintenance_time DESC $limitClause");
        } else {
            echo json_encode(['success' => false, 'message' => '无效的记录类型']);
            return;
        }
        
        $stmt->execute(['did' => $did]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 格式化状态
        foreach ($records as &$record) {
            switch ($record['flow']) {
                case 1:
                    $record['flow_text'] = '已完成';
                    break;
                default:
                    $record['flow_text'] = '未知状态';
                    break;
            }
        }
        
        // 返回分页结果
        echo json_encode([
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取记录失败: ' . $e->getMessage()]);
    }
}

// 获取问题记录
function getProblems() {
    global $pdo;
    
    try {
        $did = $_GET['did'];
        // 获取分页参数
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 5;
        
        // 如果pageSize为0，表示查询所有记录
        $limitClause = '';
        if ($pageSize > 0) {
            $offset = ($page - 1) * $pageSize;
            $limitClause = "LIMIT $offset, $pageSize";
        }
        
        // 查询总记录数
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM problems WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $did]);
        $total = $stmt->fetchColumn();
        
        // 查询当前页数据
        $stmt = $pdo->prepare("SELECT pid, reporter, report_time, description, urgency, status, create_time, update_time FROM problems WHERE did = :did AND status = 1 ORDER BY report_time DESC $limitClause");
        $stmt->execute(['did' => $did]);
        $problems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 格式化紧急程度和状态，并映射字段名以匹配前端代码
        foreach ($problems as &$problem) {
            // 紧急程度
            switch ($problem['urgency']) {
                case 1:
                    $problem['urgency_text'] = '低';
                    break;
                case 2:
                    $problem['urgency_text'] = '中';
                    break;
                case 3:
                    $problem['urgency_text'] = '高';
                    break;
                default:
                    $problem['urgency_text'] = '未知';
                    break;
            }
            
            // 问题状态 - 映射为前端期望的字段名flow
            $problem['flow'] = $problem['status'];
            
            // 将report_time映射为create_time，以匹配前端代码
            $problem['create_time'] = $problem['report_time'];
        }
        
        // 返回分页结果
        echo json_encode([
            'data' => $problems,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取问题记录失败: ' . $e->getMessage()]);
    }
}

// 获取巡视记录和检修记录总数
function getWorkLogsCount() {
    global $pdo;
    
    try {
        $did = $_GET['did'];
        $type = $_GET['type'];
        
        // 根据类型查询记录总数
        if ($type == 1 || $type === 'inspection') {
            // 查询巡视记录总数
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM inspections WHERE did = :did AND status = 1");
        } else if ($type == 2 || $type === 'maintenance') {
            // 查询检修记录总数
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM maintenances WHERE did = :did AND status = 1");
        } else {
            echo json_encode(['success' => false, 'message' => '无效的记录类型']);
            return;
        }
        
        $stmt->execute(['did' => $did]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => $result['count']
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取记录总数失败: ' . $e->getMessage()]);
    }
}

// 获取问题记录总数
function getProblemsCount() {
    global $pdo;
    
    try {
        $did = $_GET['did'];
        
        // 查询问题记录总数
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM problems WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $did]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => $result['count']
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取问题记录总数失败: ' . $e->getMessage()]);
    }
}

// 获取设备详情信息
function getDeviceDetail() {
    global $pdo;
    
    try {
        $did = $_GET['did'];
        
        // 查询设备信息
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $did]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$device) {
            echo json_encode(['success' => false, 'message' => '设备不存在']);
            return;
        }
        
        // 获取设备类型名称
        $stmt = $pdo->prepare("SELECT type_name FROM types WHERE tid = :tid AND status = 1");
        $stmt->execute(['tid' => $device['tid']]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        $device['type_name'] = $type ? $type['type_name'] : '其他类型';
        
        // 获取所属站场名称
        $stmt = $pdo->prepare("SELECT station_name FROM stations WHERE sid = :sid AND status = 1");
        $stmt->execute(['sid' => $device['sid']]);
        $station = $stmt->fetch(PDO::FETCH_ASSOC);
        $device['station_name'] = $station ? $station['station_name'] : '其他站场';
        
        // 获取包保部门名称
        $stmt = $pdo->prepare("SELECT full_name FROM departments WHERE cid = :cid AND status = 1");
        $stmt->execute(['cid' => $device['cid']]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
        $device['department_name'] = $department ? $department['full_name'] : '其他部门';
        
        // 获取设备图纸数量
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM drawings WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $device['did']]);
        $drawing_count = $stmt->fetch(PDO::FETCH_ASSOC);
        $device['drawing_count'] = $drawing_count ? $drawing_count['count'] : 0;
        
        echo json_encode(['success' => true, 'data' => $device]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取设备详情失败: ' . $e->getMessage()]);
    }
}



// 获取问题详情
function getProblemDetail() {
    global $pdo, $problemStatus;
    
    try {
        $pid = $_GET['pid'];
        
        // 查询问题基本信息
        $stmt = $pdo->prepare("SELECT * FROM problems WHERE pid = :pid AND status = 1");
        $stmt->execute(['pid' => $pid]);
        $problem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$problem) {
            echo json_encode(['success' => false, 'message' => '问题不存在']);
            return;
        }
        
        // 获取相关设备信息
        $stmt = $pdo->prepare("SELECT device_name FROM devices WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $problem['did']]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        $problem['device_name'] = $device ? $device['device_name'] : '未知设备';
        
        // 获取设备类型信息
        $stmt = $pdo->prepare("SELECT type_name FROM types WHERE tid = :tid AND status = 1");
        $stmt->execute(['tid' => $problem['tid']]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        $problem['type_name'] = $type ? $type['type_name'] : '未知类型';
        
        // 获取站场信息
        $stmt = $pdo->prepare("SELECT station_name FROM stations WHERE sid = :sid AND status = 1");
        $stmt->execute(['sid' => $problem['sid']]);
        $station = $stmt->fetch(PDO::FETCH_ASSOC);
        $problem['station_name'] = $station ? $station['station_name'] : '未知站场';
        
        // 获取部门信息
        $stmt = $pdo->prepare("SELECT full_name FROM departments WHERE cid = :cid AND status = 1");
        $stmt->execute(['cid' => $problem['cid']]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);
        $problem['department_name'] = $department ? $department['full_name'] : '未知部门';
        
        // 格式化紧急程度
        switch ($problem['urgency']) {
            case 1:
                $problem['urgency_text'] = '低';
                break;
            case 2:
                $problem['urgency_text'] = '中';
                break;
            case 3:
                $problem['urgency_text'] = '高';
                break;
            default:
                $problem['urgency_text'] = '未知';
                break;
        }
        
        // 格式化问题状态
        $problem['status_text'] = isset($problemStatus[$problem['status']]) ? $problemStatus[$problem['status']] : '未知状态';
        
        // 获取问题附件
        $stmt = $pdo->prepare("SELECT * FROM problem_attachments WHERE pid = :pid AND status = 1");
        $stmt->execute(['pid' => $pid]);
        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $problem['attachments'] = $attachments;
        
        // 获取问题处理记录
        $stmt = $pdo->prepare("SELECT * FROM problem_process WHERE pid = :pid ORDER BY process_time ASC");
        $stmt->execute(['pid' => $pid]);
        $process_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $problem['process_records'] = $process_records;
        
        echo json_encode(['success' => true, 'data' => $problem]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取问题详情失败: ' . $e->getMessage()]);
    }
}

// 获取问题列表
function getProblemList() {
    global $pdo, $problemStatus;
    
    try {
        // 获取筛选参数
        $department = isset($_GET['department']) ? $_GET['department'] : '';
        $station = isset($_GET['station']) ? $_GET['station'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        // 获取分页参数
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 10;
        $offset = ($page - 1) * $pageSize;
        
        // 构建查询条件
        $conditions = "1 = 1 AND problems.status = 1";
        $params = [];
        
        if (!empty($department)) {
            $conditions .= " AND devices.cid = :department";
            $params[':department'] = $department;
        }
        
        if (!empty($station)) {
            $conditions .= " AND devices.sid = :station";
            $params[':station'] = $station;
        }
        
        if (!empty($type)) {
            $conditions .= " AND devices.tid = :type";
            $params[':type'] = $type;
        }
        
        if (!empty($status)) {
            $conditions .= " AND problems.status = :status";
            $params[':status'] = $status;
        }
        
        if (!empty($keyword)) {
            $conditions .= " AND (devices.device_name LIKE CONCAT('%', :keyword, '%') OR problems.pid LIKE CONCAT('%', :keyword, '%') OR problems.description LIKE CONCAT('%', :keyword, '%'))";
            $params[':keyword'] = $keyword;
        }
        
        // 查询总记录数
        $countSql = "SELECT COUNT(*) as total FROM problems INNER JOIN devices ON problems.did = devices.did WHERE $conditions";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // 查询当前页数据
        $sql = "SELECT problems.*, devices.device_name, devices.device_code, types.type_name, stations.station_name, departments.full_name as department_name 
                FROM problems 
                INNER JOIN devices ON problems.did = devices.did 
                INNER JOIN types ON devices.tid = types.tid 
                INNER JOIN stations ON devices.sid = stations.sid 
                INNER JOIN departments ON devices.cid = departments.cid 
                WHERE $conditions 
                ORDER BY problems.report_time DESC 
                LIMIT :offset, :pageSize";
        $stmt = $pdo->prepare($sql);
        
        // 绑定参数
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $problems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 格式化紧急程度和状态
        foreach ($problems as &$problem) {
            // 紧急程度
            switch ($problem['urgency']) {
                case 1:
                    $problem['urgency_text'] = '低';
                    break;
                case 2:
                    $problem['urgency_text'] = '中';
                    break;
                case 3:
                    $problem['urgency_text'] = '高';
                    break;
                default:
                    $problem['urgency_text'] = '未知';
                    break;
            }
            
            // 问题状态
            $problem['status_text'] = isset($problemStatus[$problem['status']]) ? $problemStatus[$problem['status']] : '未知状态';
        }
        
        // 返回分页结果
        echo json_encode([
            'success' => true,
            'data' => $problems,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取问题列表失败: ' . $e->getMessage()]);
    }
}

// 获取筛选选项
function getFilterOptions() {
    global $pdo;
    
    try {
        $options = [];
        
        // 获取部门列表
        $stmt = $pdo->prepare("SELECT cid, full_name FROM departments WHERE status = 1 ORDER BY full_name");
        $stmt->execute();
        $options['departments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取站场列表
        $stmt = $pdo->prepare("SELECT sid, station_name FROM stations WHERE status = 1 ORDER BY station_name");
        $stmt->execute();
        $options['stations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 获取设备类型列表
        $stmt = $pdo->prepare("SELECT tid, type_name FROM types WHERE status = 1 ORDER BY type_name");
        $stmt->execute();
        $options['types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $options]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取筛选选项失败: ' . $e->getMessage()]);
    }
}

// 记录错误日志
function logError($message, $data = null) {
    $log = "[" . date('Y-m-d H:i:s') . "] " . $message;
    if ($data) {
        $log .= " " . json_encode($data);
    }
    $log .= "\n";
    file_put_contents(__DIR__ . '/error.log', $log, FILE_APPEND);
}

// 获取首页统计数据
function getDashboardStats() {
    global $pdo;
    
    try {
        // 查询设备总数
        $stmt = $pdo->prepare("SELECT COUNT(*) as device_count FROM devices WHERE status = 1");
        $stmt->execute();
        $device_count = $stmt->fetch()['device_count'];
        
        // 查询问题总数
        $stmt = $pdo->prepare("SELECT COUNT(*) as problem_count FROM problems WHERE status = 1");
        $stmt->execute();
        $problem_count = $stmt->fetch()['problem_count'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'device_count' => $device_count,
                'problem_count' => $problem_count
            ]
        ]);
    } catch (Exception $e) {
        logError('获取首页统计数据失败: ' . $e->getMessage(), 'getDashboardStats');
        echo json_encode(['success' => false, 'message' => '获取首页统计数据失败: ' . $e->getMessage()]);
    }
}

// 上传问题照片
function uploadProblemPhoto() {
    global $pdo;
    
    try {
        // 检查是否有文件上传
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => '上传文件失败: 未收到文件']);
            return;
        }
        
        // 获取设备ID
        $did = isset($_POST['did']) ? intval($_POST['did']) : 0;
        if ($did <= 0) {
            echo json_encode(['success' => false, 'message' => '上传文件失败: 无效的设备ID']);
            return;
        }
        
        // 设置上传目录（统一放在problems文件夹下，不按日期分类）
        $uploadDir = __DIR__ . '/uploads/problems/';
        $rootDir = 'uploads/problems/';
        
        // 创建目录（如果不存在）
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 获取原始文件名和扩展名
        $originalName = basename($_FILES['file']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // 生成唯一的链接名称: problem_10位时间戳.文件哈希值.文件类型
        $timestamp = time();
        // 计算文件哈希值
        $fileHash = md5_file($_FILES['file']['tmp_name']);
        $linkName = 'problem_' . $timestamp . '.' . $fileHash . '.' . $extension;
        
        // 构建完整的文件路径
        $filePath = $uploadDir . $linkName;
        $relativePath = $rootDir . $linkName;
        
        // 移动上传的文件
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            echo json_encode(['success' => false, 'message' => '上传文件失败: 无法移动文件']);
            return;
        }
        
        // 由于问题记录尚未创建，这里暂时不保存到problem_attachments表
        // 当问题记录创建后，会通过addProblem接口处理照片关联
        
        // 返回成功信息和文件路径
        echo json_encode([
            'success' => true,
            'filePath' => $relativePath,
            'originalName' => $originalName
        ]);
    } catch (Exception $e) {
        logError('上传问题照片失败: ' . $e->getMessage(), 'uploadProblemPhoto');
        echo json_encode(['success' => false, 'message' => '上传文件失败: ' . $e->getMessage()]);
    }
}