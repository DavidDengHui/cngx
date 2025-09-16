<?php
// 防止直接访问此页面
if (basename($_SERVER['PHP_SELF']) == 'footer.php') {
    header('Location: /');
    exit();
}
?>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                csngx.cn © 2025 版权所有
            </div>
            <div class="footer-right">
                湘ICP备17019987号 | 湘公网安备43020002000058号
            </div>
        </div>
        <div class="footer-mobile">
            <div>csngx.cn © 2025 版权所有</div>
            <div>湘ICP备17019987号</div>
            <div>湘公网安备43020002000058号</div>
        </div>
        
        <!-- 预留网站统计代码区域 -->
        <div class="analytics-code">
            <!-- 网站统计代码将在此处添加 -->
        </div>
    </footer>
    
    <style>
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            margin-top: auto;
            font-size: 14px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-mobile {
            display: none;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            line-height: 1.8;
        }
        
        .analytics-code {
            display: none;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                display: none;
            }
            
            .footer-mobile {
                display: block;
            }
        }
        
        /* 确保页脚在页面底部 */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            flex: 1;
        }
    </style>
</body>
</html>