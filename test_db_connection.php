<?php
// 引入配置文件
require_once 'config.php';

// 设置错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 设置内容类型为纯文本，避免JSON解析问题
header('Content-Type: text/plain; charset=utf-8');

// 显示配置信息摘要
print("=== 数据库连接测试 ===\n\n");
print("配置信息:\n");
print("主机: " . DB_HOST . "\n");
print("端口: " . DB_PORT . "\n");
print("数据库: " . DB_NAME . "\n");
print("用户名: " . DB_USER . "\n\n");

// 尝试连接数据库并测量连接时间
print("连接测试结果:\n");
try {
    $startTime = microtime(true);
    $pdo = getDbConnection();
    $endTime = microtime(true);
    $connectTime = round(($endTime - $startTime) * 1000, 2); // 转换为毫秒
    
    print("✅ 数据库连接成功!\n");
    print("连接时间: " . $connectTime . " ms\n");
    
    // 执行简单查询验证连接
    try {
        $stmt = $pdo->query('SELECT VERSION() as version');
        $row = $stmt->fetch();
        print("MySQL 版本: " . $row['version'] . "\n");
        
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        print("数据库中表的数量: " . count($tables) . "\n");
    } catch (PDOException $e) {
        print("警告: 无法执行验证查询 - " . $e->getMessage() . "\n");
    }
    
    // 关闭连接
    $pdo = null;
    print("\n连接已关闭。\n");
    
} catch (Exception $e) {
    // 捕获所有异常并以纯文本格式显示
    print("❌ 数据库连接失败:\n");
    print("错误信息: " . $e->getMessage() . "\n\n");
    
    // 显示故障排除建议
    print("=== 故障排除建议 ===\n");
    print("1. 检查数据库服务器是否正在运行\n");
    print("2. 验证config.php中的主机名、端口、用户名和密码是否正确\n");
    print("3. 确认网络连接是否正常\n");
    print("4. 检查防火墙设置是否阻止了连接\n");
    print("5. 确认数据库用户具有足够的权限\n");
}

// 也可以创建一个简单的HTML版本供浏览器访问
// 可以通过浏览器直接访问此文件查看结果
?>