<?php
// 引入配置文件
include 'config.php';

// 页面标题
$page_title = "问题管理";
$nav_title = "问题管理";

// 获取当前页面类型
$pid = isset($_GET['pid']) ? $_GET['pid'] : '';
$view = isset($_GET['view']) ? $_GET['view'] : '';

// 获取部门、站场、设备类型等信息，用于筛选
$filterOptions = [];

// 保存查询参数到JS变量
$jsParams = [
    'pid' => $pid,
    'view' => $view,
    'department' => isset($_GET['department']) ? $_GET['department'] : '',
    'station' => isset($_GET['station']) ? $_GET['station'] : '',
    'type' => isset($_GET['type']) ? $_GET['type'] : '',
    'status' => isset($_GET['status']) ? $_GET['status'] : '',
    'keyword' => isset($_GET['keyword']) ? $_GET['keyword'] : ''
];

include 'header.php';
?>
<style>
/* 问题状态标签样式 */
.status-tag {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.status-red {
    background-color: #fee;
    color: #e74c3c;
    transition: all 0.3s ease;
}

.status-red:hover {
    background-color: #e74c3c;
    color: white;
}

.status-green {
    background-color: #efe;
    color: #27ae60;
    transition: all 0.3s ease;
}

.status-green:hover {
    background-color: #27ae60;
    color: white;
}

/* 表格行悬浮或点击时状态标签变色 */
.data-table tr:hover .status-red,
.data-table tr:active .status-red {
    background-color: #e74c3c;
    color: white;
}

.data-table tr:hover .status-green,
.data-table tr:active .status-green {
    background-color: #27ae60;
    color: white;
}
</style>
    
    <div class="container">
        <div class="content">
            <?php if ($pid && $view === 'detail'): ?>
                <!-- 问题详情视图 -->
                <div class="detail-header">
                    <h2>问题详情</h2>
                    <button class="btn btn-primary" onclick="window.history.back()">返回列表</button>
                </div>
                <div id="problem-detail-container" class="detail-container">
                    <!-- 问题详情内容将由JavaScript动态加载 -->
                    <div class="loading">加载中...</div>
                </div>
            <?php else: ?>
                <!-- 问题列表视图 -->
                <div class="header">
                    <h2>问题列表</h2>
                    <a href="problem_add.php" class="btn btn-primary">添加问题</a>
                </div>
                
                <!-- 筛选面板 -->
                <div class="filter-panel">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label for="department">所属部门:</label>
                            <select id="department" class="form-control">
                                <option value="">全部</option>
                                <!-- 部门选项将由JavaScript动态加载 -->
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="station">所属站场:</label>
                            <select id="station" class="form-control">
                                <option value="">全部</option>
                                <!-- 站场选项将由JavaScript动态加载 -->
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="type">设备类型:</label>
                            <select id="type" class="form-control">
                                <option value="">全部</option>
                                <!-- 设备类型选项将由JavaScript动态加载 -->
                            </select>
                        </div>
                        <div class="filter-item">
                            <label for="status">问题状态:</label>
                            <select id="status" class="form-control">
                                <option value="">全部</option>
                                <option value="0">已录入</option>
                                <option value="1">已闭环</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-item search-box">
                            <input type="text" id="keyword" placeholder="搜索设备名称/问题编号/问题描述" class="form-control">
                            <button id="search-btn" class="btn btn-primary">搜索</button>
                            <button id="reset-btn" class="btn btn-default">重置</button>
                        </div>
                    </div>
                </div>
                
                <!-- 问题列表 -->
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>问题编号</th>
                                <th>所属部门</th>
                                <th>所属站场</th>
                                <th>设备名称</th>
                                <th>设备类型</th>
                                <th style="width: auto;">问题描述</th>
                                <th>紧急程度</th>
                                <th>问题状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="problem-list-body">
                            <!-- 问题列表将由JavaScript动态加载 -->
                            <tr>
                                <td colspan="10" class="loading">加载中...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- 分页控件 -->
                <div class="pagination" id="pagination">
                    <!-- 分页控件将由JavaScript动态加载 -->
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // 保存PHP传递的参数
        const params = <?php echo json_encode($jsParams); ?>;
        
        // 初始化时设置筛选条件的值
        document.addEventListener('DOMContentLoaded', function() {
            // 根据当前页面类型执行不同的初始化操作
            if (params.pid && params.view === 'detail') {
                loadProblemDetail(params.pid);
            } else {
                // 加载筛选选项
                loadFilterOptions();
                
                // 设置筛选条件的默认值
                if (params.department) document.getElementById('department').value = params.department;
                if (params.station) document.getElementById('station').value = params.station;
                if (params.type) document.getElementById('type').value = params.type;
                if (params.status) document.getElementById('status').value = params.status;
                if (params.keyword) document.getElementById('keyword').value = params.keyword;
                
                // 加载问题列表
                loadProblemList(1);
                
                // 绑定搜索和重置按钮事件
                document.getElementById('search-btn').addEventListener('click', function() {
                    loadProblemList(1);
                });
                
                document.getElementById('reset-btn').addEventListener('click', function() {
                    document.getElementById('department').value = '';
                    document.getElementById('station').value = '';
                    document.getElementById('type').value = '';
                    document.getElementById('status').value = '';
                    document.getElementById('keyword').value = '';
                    loadProblemList(1);
                });

                // 初始化问题记录行的悬浮提示功能
                initProblemTooltip();
                
                // 绑定回车键搜索
                document.getElementById('keyword').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        loadProblemList(1);
                    }
                });
            }
        });
        
        // 加载问题详情
        function loadProblemDetail(pid) {
            fetch('api.php?action=getProblemDetail&pid=' + pid)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayProblemDetail(data.data);
                    } else {
                        document.getElementById('problem-detail-container').innerHTML = '<div class="error">' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('获取问题详情失败:', error);
                    document.getElementById('problem-detail-container').innerHTML = '<div class="error">获取问题详情失败，请刷新页面重试</div>';
                });
        }
        
        // 显示问题详情
        function displayProblemDetail(problem) {
            const container = document.getElementById('problem-detail-container');
            
            // 格式化日期
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleString('zh-CN');
            };
            
            // 构建附件列表HTML
            let attachmentsHtml = '';
            if (problem.attachments && problem.attachments.length > 0) {
                attachmentsHtml = '<div class="attachment-list">';
                problem.attachments.forEach(attachment => {
                    const fileUrl = attachment.root_dir + attachment.link_name;
                    attachmentsHtml += `
                        <div class="attachment-item">
                            <a href="${fileUrl}" target="_blank" class="attachment-link">
                                <span class="attachment-name">${attachment.original_name}</span>
                                <span class="attachment-size">(${(attachment.file_size / 1024).toFixed(2)}KB)</span>
                            </a>
                        </div>
                    `;
                });
                attachmentsHtml += '</div>';
            } else {
                attachmentsHtml = '<p>无附件</p>';
            }
            
            // 构建处理记录HTML
            let processRecordsHtml = '';
            if (problem.process_records && problem.process_records.length > 0) {
                processRecordsHtml = '<div class="process-records">';
                problem.process_records.forEach(record => {
                    processRecordsHtml += `
                        <div class="process-record">
                            <div class="process-header">
                                <span class="process-operator">${record.operator}</span>
                                <span class="process-time">${formatDate(record.process_time)}</span>
                            </div>
                            <div class="process-content">${record.content}</div>
                        </div>
                    `;
                });
                processRecordsHtml += '</div>';
            } else {
                processRecordsHtml = '<p>暂无处理记录</p>';
            }
            
            // 构建问题详情HTML
            const html = `
                <div class="detail-content">
                    <div class="detail-info">
                        <div class="info-row">
                            <div class="info-item">
                                <label>问题编号:</label>
                                <span>${problem.pid}</span>
                            </div>
                            <div class="info-item">
                                <label>所属部门:</label>
                                <span>${problem.department_name}</span>
                            </div>
                            <div class="info-item">
                                <label>所属站场:</label>
                                <span>${problem.station_name}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <label>设备名称:</label>
                                <span>${problem.device_name}</span>
                            </div>
                            <div class="info-item">
                                <label>设备类型:</label>
                                <span>${problem.type_name}</span>
                            </div>
                            <div class="info-item">
                                <label>问题状态:</label>
                                <span class="status-tag status-${problem.status}">${problem.status_text}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <label>报告人:</label>
                                <span>${problem.reporter}</span>
                            </div>
                            <div class="info-item">
                                <label>报告时间:</label>
                                <span>${formatDate(problem.report_time)}</span>
                            </div>
                            <div class="info-item">
                                <label>紧急程度:</label>
                                <span class="urgency-tag urgency-${problem.urgency}">${problem.urgency_text}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item full-width">
                                <label>问题描述:</label>
                                <p>${problem.description}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 问题附件 -->
                    <div class="section">
                        <h3>问题附件</h3>
                        ${attachmentsHtml}
                    </div>
                    
                    <!-- 处理记录 -->
                    <div class="section">
                        <h3>处理记录</h3>
                        ${processRecordsHtml}
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
        }
        
        // 加载筛选选项
        function loadFilterOptions() {
            fetch('api.php?action=getFilterOptions')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const filterOptions = data.data;
                        
                        // 填充部门下拉框
                        const departmentSelect = document.getElementById('department');
                        filterOptions.departments.forEach(dept => {
                            const option = document.createElement('option');
                            option.value = dept.cid;
                            option.textContent = dept.full_name;
                            departmentSelect.appendChild(option);
                        });
                        
                        // 填充站场下拉框
                        const stationSelect = document.getElementById('station');
                        filterOptions.stations.forEach(station => {
                            const option = document.createElement('option');
                            option.value = station.sid;
                            option.textContent = station.station_name;
                            stationSelect.appendChild(option);
                        });
                        
                        // 填充设备类型下拉框
                        const typeSelect = document.getElementById('type');
                        filterOptions.types.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.tid;
                            option.textContent = type.type_name;
                            typeSelect.appendChild(option);
                        });
                        
                        // 如果预设的筛选值，重新应用它们
                        if (params.department) document.getElementById('department').value = params.department;
                        if (params.station) document.getElementById('station').value = params.station;
                        if (params.type) document.getElementById('type').value = params.type;
                    } else {
                        console.error('获取筛选选项失败:', data.message);
                    }
                })
                .catch(error => {
                    console.error('获取筛选选项失败:', error);
                });
        }
        
        // 加载问题列表
        function loadProblemList(page) {
            // 获取筛选条件
            const department = document.getElementById('department').value;
            const station = document.getElementById('station').value;
            const type = document.getElementById('type').value;
            const status = document.getElementById('status').value;
            const keyword = document.getElementById('keyword').value;
            
            // 构建查询参数
            const queryParams = new URLSearchParams({
                action: 'getProblemList',
                page: page,
                pageSize: 10,
                department: department,
                station: station,
                type: type,
                status: status,
                keyword: keyword
            });
            
            fetch('api.php?' + queryParams)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayProblemList(data.data, data.total, data.page, data.pageSize);
                    } else {
                        document.getElementById('problem-list-body').innerHTML = '<tr><td colspan="10" class="error">' + data.message + '</td></tr>';
                        document.getElementById('pagination').innerHTML = '';
                    }
                })
                .catch(error => {
                    console.error('获取问题列表失败:', error);
                    document.getElementById('problem-list-body').innerHTML = '<tr><td colspan="10" class="error">获取问题列表失败，请刷新页面重试</td></tr>';
                    document.getElementById('pagination').innerHTML = '';
                });
        }
        
        // 显示问题列表
        function displayProblemList(problems, total, currentPage, pageSize) {
            const tbody = document.getElementById('problem-list-body');
            
            if (problems.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="empty">暂无问题记录</td></tr>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            
            // 格式化日期
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleDateString('zh-CN');
            };
            
            let html = '';
            problems.forEach(problem => {
                html += `
                    <tr data-report-time="${problem.report_time}">
                        <td>${problem.pid}</td>
                        <td>${problem.department_name}</td>
                        <td>${problem.station_name}</td>
                        <td>${problem.device_name}</td>
                        <td>${problem.type_name}</td>
                        <td class="description-cell">${problem.description}</td>
                        <td><span class="urgency-tag urgency-${problem.urgency}">${problem.urgency_text}</span></td>
                        <td><span class="status-tag status-${problem.status}">${problem.status_text}</span></td>
                        <td>
                            <a href="problems.php?pid=${problem.pid}&view=detail" class="btn btn-sm btn-primary">查看</a>
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            
            // 更新分页控件
            buildPagination(total, currentPage, pageSize);
        }
        
        // 构建分页控件
        function buildPagination(total, currentPage, pageSize) {
            const pagination = document.getElementById('pagination');
            const totalPages = Math.ceil(total / pageSize);
            
            // 构建分页参数（包含筛选条件）
            const buildPaginationParams = () => {
                const department = document.getElementById('department').value;
                const station = document.getElementById('station').value;
                const type = document.getElementById('type').value;
                const status = document.getElementById('status').value;
                const keyword = document.getElementById('keyword').value;
                
                let params = '';
                if (department) params += '&department=' + department;
                if (station) params += '&station=' + station;
                if (type) params += '&type=' + type;
                if (status) params += '&status=' + status;
                if (keyword) params += '&keyword=' + encodeURIComponent(keyword);
                
                return params;
            };
            
            let html = '';
            
            // 上一页
            const prevDisabled = currentPage <= 1 ? 'disabled' : '';
            const prevPage = currentPage - 1;
            html += `<a href="javascript:void(0)" class="page-btn prev ${prevDisabled}" onclick="loadProblemList(${prevPage})">上一页</a>`;
            
            // 页码
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);
            
            for (let i = startPage; i <= endPage; i++) {
                const active = currentPage === i ? 'active' : '';
                html += `<a href="javascript:void(0)" class="page-btn ${active}" onclick="loadProblemList(${i})">${i}</a>`;
            }
            
            // 下一页
            const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
            const nextPage = currentPage + 1;
            html += `<a href="javascript:void(0)" class="page-btn next ${nextDisabled}" onclick="loadProblemList(${nextPage})">下一页</a>`;
            
            // 页码跳转
            html += `
                <div class="page-jump">
                    <span>共 ${totalPages} 页</span>
                    <input type="number" id="page-input" min="1" max="${totalPages}" value="${currentPage}">
                    <button onclick="jumpToPage(${totalPages})">GO</button>
                </div>
            `;
            
            pagination.innerHTML = html;
        }
        
        // 跳转到指定页码
        function jumpToPage(totalPages) {
            const pageInput = document.getElementById('page-input');
            let page = parseInt(pageInput.value);
            
            // 验证页码范围
            if (isNaN(page) || page < 1) {
                page = 1;
            } else if (page > totalPages) {
                page = totalPages;
            }
            
            pageInput.value = page;
            loadProblemList(page);
        }
        
        // 添加问题记录行的悬浮提示功能
        function initProblemTooltip() {
            // 创建悬浮提示元素
            let tooltip = document.createElement('div');
            tooltip.className = 'problem-tooltip';
            tooltip.style.cssText = `
                position: fixed;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                pointer-events: auto;
                z-index: 1000;
                display: none;
                white-space: nowrap;
            `;
            
            // 创建尖角元素
            let arrow = document.createElement('div');
            arrow.style.cssText = `
                position: absolute;
                width: 0;
                height: 0;
                border-left: 6px solid transparent;
                border-right: 6px solid transparent;
                border-top: 6px solid rgba(0, 0, 0, 0.8);
                bottom: -6px;
                left: 50%;
                transform: translateX(-50%);
            `;
            tooltip.appendChild(arrow);
            
            document.body.appendChild(tooltip);
            
            // 跟踪当前显示的行
            let currentVisibleRow = null;
            
            // 格式化日期
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                return new Date(dateStr).toLocaleString('zh-CN');
            };
            
            // 定位到状态标签上方
            function positionTooltipToStatusLabel(row) {
                // 找到状态标签单元格（第8列）
                const statusCell = row.cells[7]; // 假设状态是第8列
                if (statusCell) {
                    const statusRect = statusCell.getBoundingClientRect();
                    const tooltipRect = tooltip.getBoundingClientRect();
                    
                    // 计算位置，显示在状态标签上方，不遮挡当前行
                    let left = statusRect.left + statusRect.width / 2 - tooltipRect.width / 2;
                    let top = statusRect.top - tooltipRect.height - 10; // 10px的间距
                    
                    // 确保提示不会超出视口
                    if (left < 0) left = 0;
                    if (left + tooltipRect.width > window.innerWidth) left = window.innerWidth - tooltipRect.width;
                    
                    tooltip.style.left = left + 'px';
                    tooltip.style.top = top + 'px';
                }
            }
            
            // 点击行切换显示/隐藏提示
            document.addEventListener('click', function(e) {
                const row = e.target.closest('.data-table tr');
                
                // 如果点击的是当前显示的行，则隐藏提示
                if (row === currentVisibleRow) {
                    tooltip.style.display = 'none';
                    currentVisibleRow = null;
                    return;
                }
                
                // 如果点击的是其他行且有报告时间数据，则显示提示
                if (row && row.dataset.reportTime) {
                    const reportTime = row.dataset.reportTime;
                    tooltip.textContent = '报告时间: ' + formatDate(reportTime);
                    
                    // 先临时显示气泡以正确测量尺寸
                    tooltip.style.opacity = '0';
                    tooltip.style.display = 'block';
                    
                    // 确保tooltip元素已经渲染
                    if (!tooltip.offsetWidth) {
                        // 如果还没渲染，强制显示一下以获取尺寸
                        const originalVisibility = tooltip.style.visibility;
                        tooltip.style.visibility = 'hidden';
                        tooltip.style.display = 'block';
                        
                        // 重置visibility
                        tooltip.style.visibility = originalVisibility;
                    }
                    
                    positionTooltipToStatusLabel(row);
                    
                    // 恢复气泡可见性
                    setTimeout(() => {
                        tooltip.style.opacity = '1';
                    }, 10);
                      
                    currentVisibleRow = row;
                    // 更新全局引用
                    window.currentProblemsTooltip = {
                        tooltip: tooltip,
                        updatePosition: positionTooltipToStatusLabel
                    };
                    window.currentProblemsTooltipRow = row;
                } else if (currentVisibleRow) {
                    // 如果点击的不是行且有显示的提示，则隐藏提示
                    tooltip.style.display = 'none';
                    currentVisibleRow = null;
                    window.currentProblemsTooltipRow = null;
                }
            });
            
            // 页面滚动时更新气泡位置
            function handleProblemsScroll() {
                // 确保当前有显示的气泡
                if (window.currentProblemsTooltip && window.currentProblemsTooltipRow) {
                    // 重新计算气泡位置
                    window.currentProblemsTooltip.updatePosition(window.currentProblemsTooltipRow);
                }
            }
        
            // 添加滚动事件监听器
            window.addEventListener('scroll', handleProblemsScroll);
        }
    </script>

<?php include 'footer.php'; ?>