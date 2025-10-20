<?php
// 防止直接访问此页面
if (basename($_SERVER['PHP_SELF']) == 'header.php') {
    header('Location: /');
    exit();
}

// 设置页面标题和导航标题
// 优先使用全局变量，然后是当前作用域变量，最后是默认值
$nav_title = isset($GLOBALS['nav_title']) ? $GLOBALS['nav_title'] : (isset($nav_title) ? $nav_title : (isset($title) ? $title : '长南高信车间设备信息管理平台'));

// 设置页面标题，自动添加尾缀
$base_title = isset($GLOBALS['page_title']) ? $GLOBALS['page_title'] : (isset($page_title) ? $page_title : (isset($title) ? $title : ''));
$site_suffix = ' - 长南高信车间设备信息管理平台';

// 只有当标题不为空且不包含尾缀时才添加尾缀
if (!empty($base_title) && strpos($base_title, $site_suffix) === false) {
    $page_title = $base_title . $site_suffix;
} else if (!empty($base_title)) {
    $page_title = $base_title;
} else {
    $page_title = '长南高信车间设备信息管理平台';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        /* 当页面在iframe中时隐藏导航栏 */
        body.in-iframe .header {
            display: none;
        }

        /* 当页面在iframe中时移除顶部padding */
        body.in-iframe {
            padding-top: 0;
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
            background-color: rgba(255, 255, 255, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        /* 移动端响应式 */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
                width: 100%;
                overflow-x: hidden;
            }
        }
    </style>
    <script>
        // 检测页面是否在iframe中加载
        window.addEventListener('load', function() {
            if (window.self !== window.top) {
                // 页面在iframe中
                document.body.classList.add('in-iframe');
            }
        });
    </script>
</head>

<body>
    <header class="header">
        <button class="header-button" onclick="window.history.back();">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <div class="header-title">
            <?php echo $nav_title; ?>
        </div>
        <button class="header-button" onclick="window.location.href='/';">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 10L12 4L20 10V19C20 19.5523 19.5523 20 19 20H5C4.44772 20 4 19.5523 4 19V10Z
       M10 15H14V20H10V15Z" fill="white" fill-rule="evenodd" clip-rule="evenodd" />
            </svg>
        </button>
    </header>

    <div class="container">