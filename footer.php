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
                <img src="/files/logo.svg"><a href="/">csngx.cn</a> © 2025<?php if(date('Y')>2025) echo ' - '.date('Y'); ?> 版权所有
            </div>
            <div class="footer-right">
                <img src="/files/gaba.ico"><a href="https://beian.mps.gov.cn/#/query/webSearch?code=43020002000058" target="_blank">湘公网安备43020002000058号</a> | <img src="/files/icpba.ico" ><a href="https://beian.miit.gov.cn/" target="_blank">湘ICP备17019987号</a>
            </div>
        </div>
        <div class="footer-mobile">
            <div><img src="/files/logo.svg"><a href="/">csngx.cn</a> © 2025<?php if(date('Y')>2025) echo ' - '.date('Y'); ?> 版权所有</div>
            <div><img src="/files/gaba.ico"><a href="https://beian.mps.gov.cn/#/query/webSearch?code=43020002000058" target="_blank">湘公网安备43020002000058号</a></div>
            <div><img src="/files/icpba.ico"><a href="https://beian.miit.gov.cn/" target="_blank">湘ICP备17019987号</a></div>
        </div>
        
        <!-- 预留网站统计代码区域 -->
        <div class="analytics-code">
            <!-- 网站统计代码将在此处添加 -->
        </div>
    </footer>
    
    <style>
        .footer {
            background-color: #f5f5f5;
            color: #999;
            padding: 20px;
            margin-top: auto;
            font-size: 12px;
        }
        
        .footer a {
            color: #999;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        /* 备案号图标样式 */
        .footer-content img, .footer-mobile img {
            width: 1rem;
            height: auto;
            margin-right: 5px;
            vertical-align: middle;
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