<?php
// 设置页面标题
$title = '网站备案信息 - 个人设备信息管理平台';

// 引入配置文件和页眉
include 'config.php';
include 'header.php';
?>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h1 style="text-align: center; margin-bottom: 40px; color: #333;">网站备案信息</h1>
        
        <div class="备案信息" style="background-color: #f9f9f9; padding: 30px; border-radius: 12px; margin-bottom: 30px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">基本信息</h2>
            <p><strong>网站名称：</strong>个人设备信息管理平台</p>
            <p><strong>备案网站主办者：</strong>个人</p>
            <p><strong>域名：</strong>csngx.cn</p>
            <p><strong>网站性质：</strong>个人技术记录平台</p>
        </div>
        
        <div class="section" style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">一、网站内容介绍</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">1. 网站内容概述</h3>
                <p style="line-height: 1.6; color: #666;">本网站是本人设计开发的个人设备信息管理平台，用于记录我经手的设备公开信息和问题库，方便查询和管理。</p>
                
                <!-- 网站内容截图 -->
                <div style="margin: 20px 0; text-align: center;">
                    <img src="/files/screenshot.png" alt="网站首页截图" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">网站首页截图</p>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">2. 网站栏目及内容介绍</h3>
                <ul style="list-style-type: none; padding-left: 0;">
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">首页</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">显示设备总数、问题总数等关键指标概览。</p>
                    </li>
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">设备管理</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">记录和管理各类设备的公开信息。</p>
                    </li>
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">作业记录</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">记录设备维护和操作的相关信息。</p>
                    </li>
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">问题跟踪</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">记录和跟踪设备问题及解决过程。</p>
                    </li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 15px;">3. 网站用途和域名使用情况</h3>
                <p style="line-height: 1.6; color: #666; margin-bottom: 15px;"><strong>网站用途：</strong>个人技术记录和管理平台，用于整理和查询个人经手的设备信息和问题记录。</p>
                <p style="line-height: 1.6; color: #666;"><strong>域名使用情况：</strong>域名csngx.cn仅用于本个人设备信息管理平台，无其他子域名或用途。网站内容不包含违法违规信息。</p>
            </div>
        </div>
        
        <div class="section" style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">二、网站技术信息</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">1. 服务器配置</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">设备类型</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">配置详情</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">用途</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 12px;">Web服务器</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">租赁于“火山引擎”的4核CPU、4GB RAM、40GB SSD</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">运行网站应用程序和存储数据</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="border: 1px solid #ddd; padding: 12px;">数据备份</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">租赁于“又拍云”的储存池</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">备份网站文件和数据库</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 12px;">网络</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">5M宽带、1个IPv4地址</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">公网访问</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">2. 网络结构</h3>
                <p style="line-height: 1.6; color: #666; margin-bottom: 15px;">网站采用云服务器部署，通过公网提供访问服务。</p>
                
                <!-- 组网结构图 -->
                <div style="margin: 20px 0; text-align: center;">
                    <img src="/files/network.png" alt="网络结构图" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">网络结构图</p>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">3. 技术实现</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 8px;"><strong>前端技术：</strong>HTML5, CSS3, JavaScript</li>
                    <li style="margin-bottom: 8px;"><strong>后端技术：</strong>PHP</li>
                    <li style="margin-bottom: 8px;"><strong>数据库：</strong>MySQL</li>
                    <li style="margin-bottom: 8px;"><strong>Web服务器：</strong>Nginx</li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 15px;">4. 部署情况</h3>
                <p style="line-height: 1.6; color: #666;">系统部署在“火山引擎”ECS云服务器上，数据每日自动备份至“又拍云”存储服务。</p>
            </div>
        </div>
        
        <div class="section" style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">三、网站安全管理</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">1. 安全措施</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 10px;"><strong>数据保护：</strong>采用HTTPS加密传输，定期数据备份。</li>
                    <li style="margin-bottom: 10px;"><strong>访问控制：</strong>设置访问权限，定期更新系统和软件。</li>
                    <li style="margin-bottom: 10px;"><strong>密码策略：</strong>使用复杂密码，定期更换。</li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 15px;">2. 应急处理</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 10px;"><strong>数据恢复：</strong>利用备份数据进行恢复。</li>
                    <li style="margin-bottom: 10px;"><strong>安全事件处理：</strong>发现问题及时处理并记录。</li>
                </ul>
            </div>
        </div>
    </div>

<?php
// 引入页脚
include 'footer.php';
?>