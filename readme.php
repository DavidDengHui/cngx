<?php
// 设置页面标题
$title = '设备信息管理平台';

// 引入配置文件和页眉
include 'config.php';
include 'header.php';
?>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <h1 style="text-align: center; margin-bottom: 40px; color: #333;">网站信息</h1>
        
        <div class="备案信息" style="background-color: #f9f9f9; padding: 30px; border-radius: 12px; margin-bottom: 30px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">基本信息</h2>
            <p><strong>备案网站主办者：</strong>长南高信车间</p>
            <p><strong>域名：</strong>csngx.cn</p>
            <p><strong>网站性质：</strong>企业内部管理系统</p>
        </div>
        
        <div class="section" style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">一、网站内容介绍</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">1. 网站内容概述</h3>
                <p style="line-height: 1.6; color: #666;">设备信息管理平台是为长南高信车间开发的一款内部管理系统，旨在实现对各类信号设备的全面管理、作业记录追踪和问题跟踪处理。系统采用现代化的Web技术，提供直观易用的界面，帮助车间提高设备管理效率，优化维护流程。</p>
                
                <!-- 网站内容截图 -->
                <div style="margin: 20px 0; text-align: center;">
                    <img src="/files/screenshot.png" alt="网站首页截图" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">图1：网站首页截图</p>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">2. 网站栏目及内容介绍</h3>
                <ul style="list-style-type: none; padding-left: 0;">
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">首页（Dashboard）</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">显示设备总数、问题总数等关键指标概览，以及平台主要功能介绍。</p>
                    </li>
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">设备管理</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">全面管理各类信号设备信息，包括设备详情、图纸和维护记录的添加、编辑、查询功能。</p>
                    </li>
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">作业记录</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">记录设备巡视和检修作业信息，跟踪设备维护历史，支持按时间、设备类型等条件查询。</p>
                    </li>
                    <li style="margin-bottom: 15px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                        <strong style="color: #333;">问题跟踪</strong>
                        <p style="line-height: 1.6; color: #666; margin-top: 5px;">记录和跟踪设备问题，实现从发现到解决的全流程管理，支持问题状态更新和责任人分配。</p>
                    </li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 15px;">3. 网站用途和域名拓展使用情况</h3>
                <p style="line-height: 1.6; color: #666; margin-bottom: 15px;"><strong>网站用途：</strong>设备信息管理平台主要用于长南高信车间内部的设备管理工作，包括设备台账管理、维护记录追踪、问题处理等核心业务流程。系统通过集中管理设备信息，提高工作效率，确保设备安全稳定运行。</p>
                <p style="line-height: 1.6; color: #666;"><strong>域名拓展使用情况：</strong>目前域名csngx.cn仅用于本设备信息管理平台，无其他子域名或拓展应用。所有网站内容均与设备管理相关，不包含任何违法违规内容。</p>
            </div>
        </div>
        
        <div class="section" style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">二、组网方案</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">1. 设备配置</h3>
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
                            <td style="border: 1px solid #ddd; padding: 12px;">Intel Core i5 CPU, 8GB RAM, 500GB SSD</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">运行网站应用程序</td>
                        </tr>
                        <tr style="background-color: #f9f9f9;">
                            <td style="border: 1px solid #ddd; padding: 12px;">数据库服务器</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">Intel Core i7 CPU, 16GB RAM, 1TB SSD</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">存储设备信息、作业记录和问题数据</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 12px;">网络设备</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">企业级路由器、交换机</td>
                            <td style="border: 1px solid #ddd; padding: 12px;">提供内部网络连接</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">2. 组网结构</h3>
                <p style="line-height: 1.6; color: #666; margin-bottom: 15px;">系统采用经典的三层架构设计，包括表示层、业务逻辑层和数据访问层。网络结构为内部局域网环境，通过防火墙与互联网隔离，确保系统安全。</p>
                
                <!-- 组网结构图 -->
                <div style="margin: 20px 0; text-align: center;">
                    <img src="/files/network.png" alt="组网结构图" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">图2：系统组网结构图</p>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">3. 使用技术</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 8px;"><strong>前端技术：</strong>HTML5, CSS3, JavaScript</li>
                    <li style="margin-bottom: 8px;"><strong>后端技术：</strong>PHP 7.x</li>
                    <li style="margin-bottom: 8px;"><strong>数据库：</strong>MySQL 5.7+</li>
                    <li style="margin-bottom: 8px;"><strong>Web服务器：</strong>Apache/Nginx</li>
                    <li style="margin-bottom: 8px;"><strong>安全技术：</strong>HTTPS加密传输，数据访问控制</li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 15px;">4. 部署情况</h3>
                <p style="line-height: 1.6; color: #666; margin-bottom: 15px;">系统部署在长南高信车间内部机房，采用物理服务器方式部署。所有数据存储在本地服务器，确保数据安全和访问控制。系统支持Windows和Linux操作系统，目前主要面向内部员工访问使用。</p>
                <p style="line-height: 1.6; color: #666;">备份策略：系统数据每日自动备份，备份数据存储在独立的存储设备上，保留最近30天的备份记录。</p>
            </div>
        </div>
        
        <div class="section" style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">三、网站安全与信息安全管理制度</h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">1. 网络安全防御措施</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 10px;"><strong>防火墙保护：</strong>部署企业级防火墙，设置严格的访问控制策略，仅允许授权IP访问系统。</li>
                    <li style="margin-bottom: 10px;"><strong>SSL/TLS加密：</strong>采用HTTPS协议进行数据传输加密，确保数据在传输过程中的安全性。</li>
                    <li style="margin-bottom: 10px;"><strong>定期安全扫描：</strong>每月进行系统安全漏洞扫描和渗透测试，及时发现并修复安全隐患。</li>
                    <li style="margin-bottom: 10px;"><strong>操作系统和软件更新：</strong>定期更新服务器操作系统、Web服务器和数据库软件，安装最新的安全补丁。</li>
                    <li style="margin-bottom: 10px;"><strong>访问日志监控：</strong>记录并分析系统访问日志，及时发现异常访问行为。</li>
                </ul>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="color: #333; margin-bottom: 15px;">2. 信息安全管控制度</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 10px;"><strong>用户权限管理：</strong>实施严格的用户权限管理，根据用户角色分配不同的系统操作权限。</li>
                    <li style="margin-bottom: 10px;"><strong>密码策略：</strong>强制用户设置复杂密码，定期更换密码，密码长度不少于8位，包含字母、数字和特殊字符。</li>
                    <li style="margin-bottom: 10px;"><strong>数据访问控制：</strong>对敏感数据实施访问控制，记录数据访问日志，防止未授权访问。</li>
                    <li style="margin-bottom: 10px;"><strong>员工安全培训：</strong>定期对员工进行信息安全培训，提高安全意识和防范能力。</li>
                    <li style="margin-bottom: 10px;"><strong>第三方访问管理：</strong>严格控制第三方对系统的访问，签订保密协议，限制访问范围和权限。</li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #333; margin-bottom: 15px;">3. 应急处理方案</h3>
                <ul style="line-height: 1.6; color: #666; padding-left: 20px;">
                    <li style="margin-bottom: 10px;"><strong>安全事件分类：</strong>根据安全事件的性质和影响程度，将安全事件分为高、中、低三个等级。</li>
                    <li style="margin-bottom: 10px;"><strong>应急响应流程：</strong>制定详细的安全事件应急响应流程，明确各环节的责任人和处理步骤。</li>
                    <li style="margin-bottom: 10px;"><strong>数据恢复机制：</strong>建立完善的数据备份和恢复机制，确保在发生安全事件时能够快速恢复数据。</li>
                    <li style="margin-bottom: 10px;"><strong>事件报告制度：</strong>安全事件发生后，及时向上级主管部门报告，记录事件处理过程和结果。</li>
                    <li style="margin-bottom: 10px;"><strong>事后总结改进：</strong>安全事件处理完毕后，进行总结分析，找出系统安全漏洞，提出改进措施，防止类似事件再次发生。</li>
                </ul>
            </div>
        </div>
    </div>

<?php
// 引入页脚
include 'footer.php';
?>