<?php
// 设置页面标题
$title = '设备信息管理平台';

// 引入配置文件和页眉
include 'config.php';
include 'header.php';

// 获取数据库连接
$pdo = getDbConnection();

// 查询设备总数
$stmt = $pdo->query("SELECT COUNT(*) as device_count FROM devices WHERE status = 1");
$device_count = $stmt->fetch()['device_count'];

// 查询问题库总数
$stmt = $pdo->query("SELECT COUNT(*) as problem_count FROM problems WHERE status = 1");
$problem_count = $stmt->fetch()['problem_count'];
?>
    <div class="dashboard">
        <h1 style="text-align: center; margin-bottom: 40px;">欢迎使用设备信息管理平台</h1>
        
        <div class="dashboard-cards">
            <div class="dashboard-card" onclick="window.location.href='/devices.php'">
                <div class="dashboard-card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 18v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="11" r="2" fill="currentColor"/>
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="dashboard-card-content">
                    <h3>设备总数</h3>
                    <p class="dashboard-card-count"><?php echo $device_count; ?></p>
                </div>
            </div>
            
            <div class="dashboard-card" onclick="window.location.href='/problems.php'">
                <div class="dashboard-card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <div class="dashboard-card-content">
                    <h3>问题库总数</h3>
                    <p class="dashboard-card-count"><?php echo $problem_count; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .dashboard {
            padding: 20px;
        }
        
        .dashboard-cards {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 300px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-card-icon {
            margin-bottom: 20px;
            color: #3498db;
        }
        
        .dashboard-card:nth-child(2) .dashboard-card-icon {
            color: #e74c3c;
        }
        
        .dashboard-card-content h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .dashboard-card-count {
            font-size: 48px;
            font-weight: bold;
            color: #3498db;
            margin: 0;
        }
        
        .dashboard-card:nth-child(2) .dashboard-card-count {
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .dashboard-cards {
                flex-direction: column;
                align-items: center;
            }
            
            .dashboard-card {
                width: 90%;
                margin-bottom: 20px;
            }
        }
    </style>

<?php
// 引入页脚
include 'footer.php';