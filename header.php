<?php
// 防止直接访问此页面
if (basename($_SERVER['PHP_SELF']) == 'header.php') {
    header('Location: /');
    exit();
}

// 设置页面标题
$page_title = isset($title) ? $title : '个人设备信息管理平台';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding-top: 60px;
        }
        
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #2c3e50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            flex: 1;
        }
        
        .header-button {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .header-button:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header class="header">
        <button class="header-button" onclick="window.history.back();">
            &lt;
        </button>
        <div class="header-title">
            <?php echo $page_title; ?>
        </div>
        <button class="header-button" onclick="window.location.href='/';">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3L2 12h3v8h6v-6h4v6h6v-8h3L12 3z" fill="white"/>
            </svg>
        </button>
    </header>
    
    <div class="container">