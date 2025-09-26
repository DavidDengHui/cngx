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
    default:
        echo json_encode(['success' => false, 'message' => '未知的操作']);
        break;
}

// 获取部门数据
function getDepartments() {
    global $pdo;
    
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
        $did = $_POST['did'];
        $inspector = $_POST['inspector'];
        $inspectionTime = $_POST['inspection_time'];
        $content = $_POST['content'];
        
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
        $did = $_POST['did'];
        $maintainer = $_POST['maintainer'];
        $maintenanceTime = $_POST['maintenance_time'];
        $content = $_POST['content'];
        
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
        $did = $_POST['did'];
        $reporter = $_POST['reporter'];
        $reportTime = $_POST['report_time'];
        $description = $_POST['description'];
        $urgency = $_POST['urgency'];
        
        // 获取部门ID
        $stmt = $pdo->prepare("SELECT cid FROM devices WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $did]);
        $device = $stmt->fetch();
        $cid = $device ? $device['cid'] : '';
        
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
        
        // 处理附件上传
        $attachments = [];
        if (!empty($_FILES['attachments']['name'][0])) {
            $attachments = processProblemAttachments($pid);
        }
        
        // 保存附件信息
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $stmt = $pdo->prepare("INSERT INTO problem_attachments (pid, original_name, link_name, root_dir, file_size, upload_time, status) VALUES (:pid, :originalName, :linkName, :rootDir, :fileSize, NOW(), 1)");
                $stmt->execute([
                    'pid' => $pid,
                    'originalName' => $attachment['originalName'],
                    'linkName' => $attachment['linkName'],
                    'rootDir' => $attachment['rootDir'],
                    'fileSize' => $attachment['fileSize']
                ]);
            }
        }
        
        echo json_encode(['success' => true, 'pid' => $pid]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '添加失败: ' . $e->getMessage()]);
    }
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
    $uploadDir = __DIR__ . '/uploads/problems/' . substr($pid, 0, 4) . '/';
    $webDir = '/uploads/problems/' . substr($pid, 0, 4) . '/';
    
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

// 删除巡视记录
function deleteInspection() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("UPDATE inspections SET status = -1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

// 删除检修记录
function deleteMaintenance() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("UPDATE maintenances SET status = -1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '删除失败: ' . $e->getMessage()]);
    }
}

// 删除问题记录（设备详情页中的问题记录）
function deleteProblemRecord() {
    global $pdo;
    
    try {
        $id = $_GET['id'];
        
        $stmt = $pdo->prepare("UPDATE problems SET status = -1 WHERE pid = :id");
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
        
        $stmt = $pdo->prepare("SELECT id, original_name, link_name, root_dir, file_size FROM drawings WHERE did = :did AND status = 1");
        $stmt->execute(['did' => $did]);
        $drawings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($drawings);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '获取图纸失败: ' . $e->getMessage()]);
    }
}