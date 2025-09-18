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
        <h2 style="text-align: center; margin-bottom: 40px;">您好，<?php echo '长南高信车间'; ?>！</h2>
        
        <div class="dashboard-cards">
            <div class="dashboard-card" onclick="window.location.href='/devices.php'">
                <div class="dashboard-card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <rect x="5" y="6" width="14" height="12" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 9H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M9 6C9 4.5 10.5 3 12 3C13.5 3 15 4.5 15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="dashboard-card-content">
                    <h3>设备总数</h3>
                    <p class="dashboard-card-count"><?php echo $device_count; ?></p>
                </div>
            </div>
            
            <div class="dashboard-card" onclick="window.location.href='/problems.php'">
                <div class="dashboard-card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <path d="M12 4L4 20H20L12 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="15" r="1" fill="currentColor"/>
                        <line x1="12" y1="9" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="dashboard-card-content">
                    <h3>问题总数</h3>
                    <p class="dashboard-card-count"><?php echo $problem_count; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 功能介绍区域 -->
    <div class="features-section">
        <h2 class="features-title">平台功能</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <rect x="5" y="6" width="14" height="12" rx="1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 9H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M9 6C9 4.5 10.5 3 12 3C13.5 3 15 4.5 15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>设备管理</h3>
                    <p>全面管理各类信号设备信息，包括设备详情、图纸和维护记录</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <circle cx="12" cy="13" r="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 9V13H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="8" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>作业记录</h3>
                    <p>记录设备巡视和检修作业信息，跟踪设备维护历史</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                        <path d="M12 4L4 20H20L12 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="15" r="1" fill="currentColor"/>
                        <line x1="12" y1="9" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>问题跟踪</h3>
                    <p>记录和跟踪设备问题，从发现到解决的全流程管理</p>
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
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .dashboard-card-icon svg {
            width: 100%;
            height: 100%;
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
        
        /* 功能介绍区域样式 */
        .features-section {
            padding: 60px 20px;
            background-color: #f9f9f9;
            margin-top: 40px;
            border-radius: 12px;
        }
        
        .features-title {
            text-align: center;
            font-size: 32px;
            color: #333;
            margin-bottom: 40px;
            font-weight: 600;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f4f8;
            border-radius: 50%;
            margin-bottom: 20px;
            color: #3498db;
        }
        
        .feature-icon svg {
            width: 48px;
            height: 48px;
        }
        
        .feature-card:nth-child(2) .feature-icon {
            color: #2ecc71;
        }
        
        .feature-card:nth-child(3) .feature-icon {
            color: #e74c3c;
        }
        
        .feature-content h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .feature-content p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .features-title {
                font-size: 28px;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .feature-card {
                padding: 25px;
            }
            
            .feature-icon {
                width: 70px;
                height: 70px;
            }
            
            .feature-icon svg {
                width: 40px;
                height: 40px;
            }
        }
    </style>

<?php
// 引入页脚
include 'footer.php';