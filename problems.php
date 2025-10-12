<?php
// 设置导航标题和页面标题
$nav_title = '问题信息查询';
$page_title = '问题信息查询 - 个人设备信息管理平台';

// 引入配置文件和页眉
include 'config.php';
include 'header.php';

// 判断是否为问题详情页面
if (isset($_GET['pid'])) {
    // 问题详情页面逻辑（暂时留空，后续可添加）
    echo "<div class='container'><h1>问题详情页面正在开发中...</h1></div>";
} else {
    // 问题查询页面逻辑
?>
    <div class="problems-container">

        <div class="problems-layout">
            <div class="problems-search">
                <form id="search-form">
                    <div class="search-row">
                        <div class="search-item">
                            <label>请选择部门</label>
                            <div class="select-container">
                                <input type="text" id="department" readonly placeholder="请选择部门">
                                <input type="hidden" id="department-id">
                                <button type="button" class="clear-btn" data-target="department"></button>
                            </div>
                        </div>
                    </div>

                    <div class="search-row">
                        <div class="search-item">
                            <label>请选择站场</label>
                            <div class="select-container">
                                <input type="text" id="station" readonly placeholder="请选择站场">
                                <input type="hidden" id="station-id">
                                <button type="button" class="clear-btn" data-target="station"></button>
                            </div>
                        </div>
                    </div>

                    <div class="search-row">
                        <div class="search-item">
                            <label>请选择类型</label>
                            <div class="select-container">
                                <input type="text" id="type" readonly placeholder="请选择类型">
                                <input type="hidden" id="type-id">
                                <button type="button" class="clear-btn" data-target="type"></button>
                            </div>
                        </div>
                    </div>

                    <div class="search-row">
                        <div class="search-item">
                            <label>请选择设备</label>
                            <div class="select-container">
                                <input type="text" id="device" readonly placeholder="请选择设备">
                                <input type="hidden" id="device-id">
                                <button type="button" class="clear-btn" data-target="device"></button>
                            </div>
                        </div>
                    </div>

                    <div class="search-row">
                        <div class="search-item">
                            <label>请输入关键字词<span class="remark-badge" data-remark="与问题描述/解决说明有关的关键字词">!</span></label>
                            <div class="select-container">
                                <input type="text" id="keywords" placeholder="请输入关键字词">
                                <button type="button" class="clear-btn" data-target="keywords"></button>
                            </div>
                        </div>
                    </div>

                    <div class="search-button-container">
                        <button type="button" id="search-button">查询</button>
                    </div>
                </form>
            </div>

            <div id="search-result" class="search-result">
                <p class="no-result">请选择分类后点击按钮查询问题</p>
            </div>
        </div>
    </div>

    <!-- 加载提示框 -->
    <div id="loading-modal" class="modal" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>查询中，请稍候...</p>
        </div>
    </div>

    <!-- 多级选择菜单模态框 -->
    <div id="select-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-left">
                    <button type="button" class="modal-btn reset-btn">重置</button>
                    <button type="button" class="modal-btn default-btn" style="display: none;">默认</button>
                </div>
                <div class="modal-header-right">
                    <button type="button" class="modal-btn cancel-btn">取消</button>
                    <button type="button" class="modal-btn confirm-btn">确认</button>
                </div>
            </div>
            <div class="modal-body">
                <div id="select-content"></div>
            </div>
        </div>
    </div>

    <script>
        // 全局变量存储当前选择类型和路径
        let currentSelectType = '';
        let currentSelectPath = [];

        // 初始化选择框点击事件
        document.getElementById('department').addEventListener('click', function() {
            openSelectModal('department', '部门');
        });

        document.getElementById('station').addEventListener('click', function() {
            openSelectModal('station', '站场');
        });

        document.getElementById('type').addEventListener('click', function() {
            openSelectModal('type', '类型');
        });

        document.getElementById('device').addEventListener('click', function() {
            openSelectModal('device', '设备');
        });

        // 打开选择模态框
        function openSelectModal(type, label) {
            currentSelectType = type;

            // 根据类型决定是否显示默认按钮
            if (type === 'department') {
                document.querySelector('.default-btn').style.display = 'inline-block';
            } else {
                document.querySelector('.default-btn').style.display = 'none';
            }

            // 重置选择路径
            currentSelectPath = [];

            // 加载第一级数据
            loadSelectData(0);

            // 显示模态框并确保居中
            const modal = document.getElementById('select-modal');
            modal.style.display = 'block';

            // 确保模态框容器居中
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';

            // 确保模态框内容居中
            const modalContent = document.querySelector('.modal-content');
            modalContent.style.margin = 'auto';

            // 阻止背景页面滚动
            document.body.style.overflow = 'hidden';
        }

        // 加载选择数据
        function loadSelectData(parentId) {
            const type = currentSelectType;
            const contentDiv = document.getElementById('select-content');

            // 清空内容
            contentDiv.innerHTML = '<div class="loading">加载中...</div>';

            // 根据类型获取API URL
            let apiUrl = '';
            switch (type) {
                case 'department':
                    apiUrl = `api.php?action=getDepartments&parentId=${parentId}`;
                    break;
                case 'station':
                    apiUrl = `api.php?action=getStations&parentId=${parentId}`;
                    break;
                case 'type':
                    apiUrl = `api.php?action=getTypes&parentId=${parentId}`;
                    break;
                case 'device':
                    // 获取设备可以传入部门、站场、类型参数中的任意组合
                    const departmentId = document.getElementById('department-id').value;
                    const stationId = document.getElementById('station-id').value;
                    const typeId = document.getElementById('type-id').value;
                    // 获取关键字
                    const keywords = document.getElementById('keywords').value;
                    
                    // 构建查询参数
                    let params = [];
                    if (departmentId) params.push(`departmentId=${encodeURIComponent(departmentId)}`);
                    if (stationId) params.push(`stationId=${encodeURIComponent(stationId)}`);
                    if (typeId) params.push(`typeId=${encodeURIComponent(typeId)}`);
                    if (keywords) params.push(`keywords=${encodeURIComponent(keywords)}`);
                    params.push('pageSize=0');
                    
                    apiUrl = `api.php?action=searchDevices&${params.join('&')}`;
                    break;
            }

            // 发送请求获取数据
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    // 处理设备数据的特殊情况
                    if (type === 'device') {
                        // 设备数据在data.data中
                        data = data.data || [];
                    }

                    if (data.length > 0) {
                        let html = '';

                        // 显示当前路径（仅对部门、站场、类型有效）
                        if (type !== 'device' && currentSelectPath.length > 0) {
                            html += '<div class="select-path">';
                            currentSelectPath.forEach((item, index) => {
                                html += `<span class="path-item" data-id="${item.id}">${item.name}</span>`;
                                if (index < currentSelectPath.length - 1) {
                                    html += ' / ';
                                }
                            });
                            html += '</div>';
                        }

                        // 显示选项列表
                        html += '<div class="select-items">';
                        data.forEach(item => {
                            // 设备数据的字段不同
                            if (type === 'device') {
                                html += `<div class="select-item" data-id="${item.did}" data-name="${item.device_name}">${item.device_name}</div>`;
                            } else {
                                html += `<div class="select-item" data-id="${item.id}" data-name="${item.name}" data-shortname="${item.shortname || item.name}">${item.name}</div>`;
                            }
                        });
                        html += '</div>';

                        contentDiv.innerHTML = html;

                        // 添加选项点击事件
                        document.querySelectorAll('.select-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                const name = this.getAttribute('data-name');
                                const shortname = this.getAttribute('data-shortname') || name;

                                if (type === 'device') {
                                    // 设备选择直接确认
                                    currentSelectPath = [{
                                        id,
                                        name,
                                        shortname
                                    }];
                                    confirmSelect();
                                } else {
                                    // 添加到选择路径
                                    currentSelectPath.push({
                                        id,
                                        name,
                                        shortname
                                    });
                                    // 加载下一级数据
                                    loadSelectData(id);
                                }
                            });
                        });

                        // 添加路径点击事件（仅对部门、站场、类型有效）
                        if (type !== 'device') {
                            document.querySelectorAll('.path-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    const id = this.getAttribute('data-id');
                                    // 找到点击项在路径中的索引
                                    const clickedIndex = currentSelectPath.findIndex(item => item.id === id);
                                    // 如果找到了对应项，则重置路径到该位置
                                    if (clickedIndex !== -1) {
                                        currentSelectPath = currentSelectPath.slice(0, clickedIndex + 1);
                                        // 加载对应级别的数据
                                        loadSelectData(id);
                                    }
                                });
                            });
                        }
                    } else {
                        // 没有下一级数据，直接确认选择（仅对部门、站场、类型有效）
                        if (type !== 'device') {
                            confirmSelect();
                        } else {
                            contentDiv.innerHTML = '<div class="no-data">没有找到符合条件的设备</div>';
                        }
                    }
                })
                .catch(error => {
                    contentDiv.innerHTML = `<div class="error">加载失败: ${error.message}</div>`;
                });
        }

        // 确认选择
        function confirmSelect() {
            if (currentSelectPath.length > 0) {
                const type = currentSelectType;
                const lastItem = currentSelectPath[currentSelectPath.length - 1];

                // 根据类型更新输入框
                if (type === 'department') {
                    const pathStr = currentSelectPath.map(item => item.shortname).join('/');
                    document.getElementById('department').value = pathStr;
                    document.getElementById('department-id').value = lastItem.id;
                    // 更新删除按钮可见性
                    const btn = document.querySelector('.clear-btn[data-target="department"]');
                    updateClearButtonVisibility(document.getElementById('department'), btn);
                    // 清空后续选择
                    clearInput('station');
                    clearInput('type');
                    clearInput('device');
                } else if (type === 'station') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('station').value = pathStr;
                    document.getElementById('station-id').value = lastItem.id;
                    // 更新删除按钮可见性
                    const btn = document.querySelector('.clear-btn[data-target="station"]');
                    updateClearButtonVisibility(document.getElementById('station'), btn);
                    // 清空后续选择
                    clearInput('type');
                    clearInput('device');
                } else if (type === 'type') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('type').value = pathStr;
                    document.getElementById('type-id').value = lastItem.id;
                    // 更新删除按钮可见性
                    const btn = document.querySelector('.clear-btn[data-target="type"]');
                    updateClearButtonVisibility(document.getElementById('type'), btn);
                    // 清空后续选择
                    clearInput('device');
                } else if (type === 'device') {
                    document.getElementById('device').value = lastItem.name;
                    document.getElementById('device-id').value = lastItem.id;
                    // 更新删除按钮可见性
                    const btn = document.querySelector('.clear-btn[data-target="device"]');
                    updateClearButtonVisibility(document.getElementById('device'), btn);
                }
            }

            // 关闭模态框
            document.getElementById('select-modal').style.display = 'none';
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        }

        // 重置选择
        function resetSelect() {
            currentSelectPath = [];
            loadSelectData(0);

            // 清空对应的输入框值
            if (currentSelectType === 'department') {
                document.getElementById('department').value = '';
                document.getElementById('department-id').value = '';
            } else if (currentSelectType === 'station') {
                document.getElementById('station').value = '';
                document.getElementById('station-id').value = '';
            } else if (currentSelectType === 'type') {
                document.getElementById('type').value = '';
                document.getElementById('type-id').value = '';
            } else if (currentSelectType === 'device') {
                document.getElementById('device').value = '';
                document.getElementById('device-id').value = '';
            }
        }

        // 设置默认部门
        function setDefaultDepartment() {
            currentSelectPath = [{
                    id: '100001',
                    name: '中国铁路',
                    shortname: '国铁'
                },
                {
                    id: '100002',
                    name: '中国铁路广州局集团有限公司',
                    shortname: '广州局'
                },
                {
                    id: '100003',
                    name: '长沙电务段',
                    shortname: '长电段'
                },
                {
                    id: '100004',
                    name: '长沙南高铁信号车间',
                    shortname: '长南高信车间'
                }
            ];
            confirmSelect();
        }

        // 添加模态框按钮事件
        document.querySelector('.confirm-btn').addEventListener('click', confirmSelect);
        document.querySelector('.cancel-btn').addEventListener('click', function() {
            document.getElementById('select-modal').style.display = 'none';
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        });
        document.querySelector('.reset-btn').addEventListener('click', resetSelect);
        document.querySelector('.default-btn').addEventListener('click', setDefaultDepartment);

        // 初始化删除按钮功能
        function initClearButtons() {
            // 为所有删除按钮添加点击事件
            document.querySelectorAll('.clear-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation(); // 阻止事件冒泡，避免触发输入框的点击事件
                    const target = this.getAttribute('data-target');
                    clearInput(target);
                });
            });

            // 监听输入框内容变化，控制删除按钮的显示/隐藏
            ['department', 'station', 'type', 'device', 'keywords'].forEach(type => {
                const input = document.getElementById(type);
                const btn = document.querySelector(`.clear-btn[data-target="${type}"]`);

                // 初始化检查
                updateClearButtonVisibility(input, btn);

                // 添加事件监听
                input.addEventListener('input', function() {
                    updateClearButtonVisibility(this, btn);
                });
            });
        }

        // 更新删除按钮的可见性
        function updateClearButtonVisibility(input, btn) {
            if (input.value.trim() !== '') {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
            }
        }

        // 清空输入框内容
        function clearInput(type) {
            document.getElementById(type).value = '';

            // 对于带ID的输入框，也清空对应的ID值
            if (type === 'department' || type === 'station' || type === 'type' || type === 'device') {
                document.getElementById(`${type}-id`).value = '';
            }

            // 隐藏对应的删除按钮
            const btn = document.querySelector(`.clear-btn[data-target="${type}"]`);
            btn.style.display = 'none';
        }

        // 页面加载完成后初始化删除按钮功能
        window.addEventListener('DOMContentLoaded', initClearButtons);

        // 全局变量存储当前分页状态
        let currentPage = 1;
        let currentPageSize = 10;
        let currentSearchParams = {};

        // 查询按钮事件
        document.getElementById('search-button').addEventListener('click', function() {
            // 显示加载提示框
            const loadingModal = document.getElementById('loading-modal');
            loadingModal.style.display = 'flex';
            
            // 禁止背景页面滚动
            document.body.style.overflow = 'hidden';

            const departmentId = document.getElementById('department-id').value;
            const stationId = document.getElementById('station-id').value;
            const typeId = document.getElementById('type-id').value;
            const deviceId = document.getElementById('device-id').value;
            const keywords = document.getElementById('keywords').value;

            // 保存当前搜索参数
            currentSearchParams = {
                departmentId,
                stationId,
                typeId,
                deviceId,
                keywords
            };

            // 重置为第一页
            currentPage = 1;

            // 发送查询请求
            fetch(`api.php?action=getProblems&cid=${departmentId}&sid=${stationId}&tid=${typeId}&did=${deviceId}&keyword=${encodeURIComponent(keywords)}&page=${currentPage}&pageSize=${currentPageSize}`)
                .then(response => response.json())
                .then(data => {
                    // 隐藏加载提示框
                    const loadingModal = document.getElementById('loading-modal');
                    loadingModal.style.display = 'none';
                    
                    // 恢复背景页面滚动
                    document.body.style.overflow = '';

                    const resultDiv = document.getElementById('search-result');

                    if (data.success && data.data && data.data.length > 0) {
                        let html = '<table class="problems-table">';
                        html += '<thead><tr><th>序号</th><th>设备名称</th><th>问题描述</th><th>状态</th></tr></thead>';
                        html += '<tbody>';

                        data.data.forEach((problem, index) => {
                            // 计算正确的序号
                            const serialNumber = (currentPage - 1) * currentPageSize + index + 1;

                            // 状态显示
                            const statusClass = problem.process === 0 ? 'status-red' : 'status-green';
                            const statusText = problem.process === 0 ? '已创建' : '已闭环';

                            // 将发现时间和解决时间作为数据属性存储，用于悬浮提示
                            const reportTime = problem.report_time || '';
                            const resolutionTime = problem.resolution_time || '';
                            html += `<tr data-report-time="${reportTime}" data-resolution-time="${resolutionTime}" data-status="${problem.process}">
                                <td>${serialNumber}</td>
                                <td><span class="device-link-btn" data-did="${problem.did}">${getDeviceName(problem)}</span></td>
                                <td><a href="problems.php?pid=${problem.pid}" title="${reportTime}">${problem.description}</a></td>
                                <td><span class="status-tag ${statusClass}">${statusText}</span></td>
                            </tr>`;
                        });

                        html += '</tbody></table>';
                        resultDiv.innerHTML = html;
                        // 更新分页控件
                        addPaginationControls(data.total, currentPage, currentPageSize);
                    } else {
                        resultDiv.innerHTML = '<p class="no-result">没有查询到问题</p>';
                        // 移除分页控件
                        removePaginationControls();
                    }
                    
                    // 只在手机窄屏设备上滑动页面到查询按钮上方
                    if (isMobileDevice()) {
                        // 使用setTimeout确保表格完全渲染后再滚动
                        setTimeout(function() {
                            const searchButton = document.getElementById('search-button');
                            const headerHeight = 60; // 导航栏高度
                            const elementPosition = searchButton.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - headerHeight;
                            
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                        }, 100);
                    }
                })
                .catch(error => {
                    // 隐藏加载提示框
                    const loadingModal = document.getElementById('loading-modal');
                    loadingModal.style.display = 'none';
                    
                    // 恢复背景页面滚动
                    document.body.style.overflow = '';
                    
                    const resultDiv = document.getElementById('search-result');
                    resultDiv.innerHTML = `<p class="error">查询失败: ${error.message}</p>`;
                    
                    // 只在手机窄屏设备上滑动页面到查询按钮上方
                    if (isMobileDevice()) {
                        // 使用setTimeout确保页面元素完全渲染后再滚动
                        setTimeout(function() {
                            const searchButton = document.getElementById('search-button');
                            const headerHeight = 60; // 导航栏高度
                            const elementPosition = searchButton.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - headerHeight;
                            
                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                        }, 100);
                    }
                });
        });

        // 检测是否为窄屏设备（手机）
        function isMobileDevice() {
            return window.matchMedia("(max-width: 768px)").matches;
        }

        // 获取设备名称（使用真实的设备名称）
        function getDeviceName(problem) {
            // 如果有设备名称字段，则使用设备名称，否则使用默认格式
            return problem.device_name || ('设备' + problem.did);
        }

        // 添加分页控件
        function addPaginationControls(total, page, pageSize) {
            // 移除可能存在的分页控件
            removePaginationControls();

            // 计算总页数
            const totalPages = pageSize > 0 ? Math.ceil(total / pageSize) : 1;

            // 创建分页容器
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination-container';

            // 左侧：显示页码信息
            const infoDiv = document.createElement('div');
            infoDiv.className = 'pagination-info';
            infoDiv.textContent = `共 ${total} 条记录，第 ${page} / ${totalPages} 页`;
            paginationContainer.appendChild(infoDiv);

            // 中间：页码导航
            const navigationDiv = document.createElement('div');
            navigationDiv.className = 'pagination-navigation';

            // 上一页按钮
            const prevButton = document.createElement('button');
            prevButton.className = 'pagination-btn';
            prevButton.textContent = '上一页';
            prevButton.disabled = page <= 1;
            prevButton.addEventListener('click', function() {
                if (page > 1) {
                    currentPage = page - 1;
                    searchProblemsWithPagination();
                }
            });
            navigationDiv.appendChild(prevButton);

            // 页码按钮
            const startPage = Math.max(1, page - 2);
            const endPage = Math.min(totalPages, startPage + 4);

            // 如果开始页码大于1，显示第一页按钮
            if (startPage > 1) {
                addPageButton(navigationDiv, 1);
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    navigationDiv.appendChild(ellipsis);
                }
            }

            // 添加连续的页码按钮
            for (let i = startPage; i <= endPage; i++) {
                addPageButton(navigationDiv, i);
            }

            // 如果结束页码小于总页数，显示最后一页按钮
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    navigationDiv.appendChild(ellipsis);
                }
                addPageButton(navigationDiv, totalPages);
            }

            // 下一页按钮
            const nextButton = document.createElement('button');
            nextButton.className = 'pagination-btn';
            nextButton.textContent = '下一页';
            nextButton.disabled = page >= totalPages;
            nextButton.addEventListener('click', function() {
                if (page < totalPages) {
                    currentPage = page + 1;
                    searchProblemsWithPagination();
                }
            });
            navigationDiv.appendChild(nextButton);

            paginationContainer.appendChild(navigationDiv);

            // 右侧：每页显示数量选择器
            const pageSizeDiv = document.createElement('div');
            pageSizeDiv.className = 'pagination-pageSize';

            const pageSizeLabel = document.createElement('span');
            pageSizeLabel.textContent = '每页显示：';
            pageSizeDiv.appendChild(pageSizeLabel);

            const pageSizeSelect = document.createElement('select');

            // 添加选项
            const options = [5, 10, 20, 50, 100, 0];
            const labels = [5, 10, 20, 50, 100, '全部'];

            for (let i = 0; i < options.length; i++) {
                const option = document.createElement('option');
                option.value = options[i];
                option.textContent = labels[i];
                // 根据当前pageSize设置选中值
                if (options[i] === pageSize) {
                    option.selected = true;
                }
                pageSizeSelect.appendChild(option);
            }

            // 监听每页显示数量变化
            pageSizeSelect.addEventListener('change', function() {
                currentPageSize = parseInt(this.value);
                currentPage = 1; // 重置为第一页
                searchProblemsWithPagination();
            });

            pageSizeDiv.appendChild(pageSizeSelect);
            paginationContainer.appendChild(pageSizeDiv);

            // 添加到结果区域内部下方
            const resultDiv = document.getElementById('search-result');
            resultDiv.appendChild(paginationContainer);
        }

        // 添加页码按钮
        function addPageButton(container, pageNum) {
            const button = document.createElement('button');
            button.className = 'pagination-btn' + (pageNum === currentPage ? ' active' : '');
            button.textContent = pageNum;

            if (pageNum !== currentPage) {
                button.addEventListener('click', function() {
                    currentPage = pageNum;
                    searchProblemsWithPagination();
                });
            }

            container.appendChild(button);
        }

        // 移除分页控件
        function removePaginationControls() {
            // 查找并移除所有分页容器
            const existingPaginationElements = document.querySelectorAll('.pagination-container');
            existingPaginationElements.forEach(element => {
                if (element && element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });
        }

        // 添加问题记录行的悬浮提示功能
        function initProblemTooltip() {
            // 存储所有气泡及其关联的行
            const tooltipsMap = new Map();
            // 存储所有气泡元素的集合
            const allTooltips = new Set();

            // 创建新气泡的函数
            function createTooltip() {
                // 创建气泡容器
                const tooltipContainer = document.createElement('div');
                tooltipContainer.className = 'problem-tooltip-container';

                // 创建气泡内容
                const tooltip = document.createElement('div');
                tooltip.className = 'problem-tooltip';

                // 创建文本容器
                const tooltipText = document.createElement('span');
                tooltipText.className = 'problem-tooltip-text';
                tooltip.appendChild(tooltipText);

                // 创建尖角
                const tooltipArrow = document.createElement('div');
                tooltipArrow.className = 'problem-tooltip-arrow';

                tooltip.appendChild(tooltipArrow);
                tooltipContainer.appendChild(tooltip);
                document.body.appendChild(tooltipContainer);
                allTooltips.add(tooltipContainer);

                return tooltipContainer;
            }

            // 更新提示位置（固定在状态标签上方）
            function updateTooltipPosition(tooltipContainer, row) {
                // 找到状态标签元素
                const statusTag = row.querySelector('.status-tag');
                if (!statusTag) return;

                const tooltip = tooltipContainer.querySelector('.problem-tooltip');

                // 确保tooltip元素已经渲染
                if (!tooltip.offsetWidth) {
                    // 如果还没渲染，强制显示一下以获取尺寸
                    const originalVisibility = tooltip.style.visibility;
                    const originalDisplay = tooltipContainer.style.display;
                    tooltip.style.visibility = 'hidden';
                    tooltipContainer.style.display = 'block';

                    // 强制重排
                    void tooltip.offsetWidth;

                    tooltip.style.visibility = originalVisibility;
                    tooltipContainer.style.display = originalDisplay;
                }

                // 获取状态标签和气泡的位置信息
                const statusRect = statusTag.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();

                // 计算水平居中位置
                let left = statusRect.left + statusRect.width / 2 - tooltipRect.width / 2;

                // 确保气泡不会超出视口
                if (left < 0) left = 0;
                if (left + tooltipRect.width > window.innerWidth) left = window.innerWidth - tooltipRect.width;

                // 设置最终位置 (减少间距从10px到5px)
                tooltipContainer.style.left = left + 'px';
                tooltipContainer.style.top = (statusRect.top - tooltipRect.height - 5) + 'px';
            }

            // 显示气泡的函数
            function showTooltip(tooltipContainer, row) {
                const reportTime = row.dataset.reportTime;
                const resolutionTime = row.dataset.resolutionTime;
                const status = row.dataset.status;
                const tooltip = tooltipContainer.querySelector('.problem-tooltip');
                const tooltipText = tooltipContainer.querySelector('.problem-tooltip-text');

                // 构建气泡文本内容
                let tooltipContent = '发现时间: ' + reportTime;
                // 如果是已闭环状态且有解决时间，则添加解决时间显示
                if (status === '1' && resolutionTime && resolutionTime.trim() !== '') {
                    tooltipContent += '\n解决时间: ' + resolutionTime;
                    // 为多行文本更新样式
                    tooltip.style.whiteSpace = 'pre-line';
                    tooltip.style.padding = '8px 10px';
                } else {
                    // 恢复单行样式
                    tooltip.style.whiteSpace = 'nowrap';
                    tooltip.style.padding = '5px 10px';
                }

                tooltipText.textContent = tooltipContent;

                // 定位到状态标签上方
                updateTooltipPosition(tooltipContainer, row);

                // 显示气泡
                tooltipContainer.style.display = 'block';
                // 使用setTimeout确保浏览器有时间处理样式变化
                setTimeout(() => {
                    tooltipContainer.style.opacity = '1';
                }, 10);
            }

            // 隐藏气泡的函数
            function hideTooltip(tooltipContainer) {
                tooltipContainer.style.opacity = '0';
                setTimeout(() => {
                    tooltipContainer.style.display = 'none';
                }, 200);
            }

            // 移除所有气泡的函数
            function removeAllTooltips() {
                allTooltips.forEach(tooltipContainer => {
                    tooltipContainer.style.display = 'none';
                    if (tooltipContainer.parentNode) {
                        tooltipContainer.parentNode.removeChild(tooltipContainer);
                    }
                });
                allTooltips.clear();
                tooltipsMap.clear();
            }

            // 点击行显示/隐藏气泡
            document.addEventListener('click', function(e) {
                const row = e.target.closest('.problems-table tr');

                // 如果点击的是有效行
                if (row && row.dataset.reportTime) {
                    // 检查是否已有该行列的气泡
                    const existingTooltip = tooltipsMap.get(row);

                    if (existingTooltip) {
                        // 如果已有气泡，则隐藏它
                        hideTooltip(existingTooltip);
                        tooltipsMap.delete(row);
                    } else {
                        // 如果没有气泡，则创建并显示新气泡
                        const newTooltip = createTooltip();
                        tooltipsMap.set(row, newTooltip);
                        showTooltip(newTooltip, row);
                    }
                }
            });

            // 监听分页控件点击，移除所有气泡
            document.addEventListener('click', function(e) {
                // 检测是否点击了分页控件
                if (e.target.closest('.pagination-container') ||
                    e.target.closest('.pagination-btn') ||
                    e.target.closest('.page-size-select')) {
                    removeAllTooltips();
                }
            });

            // 页面滚动时更新所有气泡位置
            function handleScroll() {
                tooltipsMap.forEach((tooltipContainer, row) => {
                    if (tooltipContainer.style.display !== 'none') {
                        updateTooltipPosition(tooltipContainer, row);
                    }
                });
            }

            // 添加滚动事件监听器
            window.addEventListener('scroll', handleScroll);

            // 添加窗口大小改变事件监听器
            window.addEventListener('resize', handleScroll);

            // 导出清除所有气泡的函数，以便在其他地方调用
            window.clearProblemTooltips = removeAllTooltips;
        }

        // 页面加载完成后初始化悬浮提示功能
        document.addEventListener('DOMContentLoaded', initProblemTooltip);

        // 处理设备名称按钮点击事件
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('device-link-btn')) {
                const deviceId = e.target.getAttribute('data-did');
                if (deviceId) {
                    openDeviceDetailModal(deviceId);
                }
            }
        });

        // 绑定设备名称点击事件
        function bindDeviceLinkEvents() {
            const deviceLinks = document.querySelectorAll('.device-link-btn');
            deviceLinks.forEach(function(link) {
                // 确保每个元素只绑定一次事件
                if (!link.hasAttribute('data-event-bound')) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault(); // 阻止默认行为
                        const deviceId = this.getAttribute('data-did');
                        if (deviceId) {
                            openDeviceDetailModal(deviceId);
                        }
                    });
                    link.setAttribute('data-event-bound', 'true');
                }
            });
        }

        // 打开设备详情模态框
        function openDeviceDetailModal(deviceId) {
            const modal = document.getElementById('device-detail-modal');
            const iframe = document.getElementById('device-detail-iframe');
            
            // 设置iframe的src
            iframe.src = `devices.php?did=${deviceId}`;
            
            // 显示模态框
            modal.style.display = 'flex';
            
            // 阻止背景滚动
            document.body.style.overflow = 'hidden';
        }

        // 关闭设备详情模态框
        function closeDeviceDetailModal() {
            const modal = document.getElementById('device-detail-modal');
            const iframe = document.getElementById('device-detail-iframe');
            
            // 隐藏模态框
            modal.style.display = 'none';
            
            // 清空iframe的src
            iframe.src = '';
            
            // 恢复背景滚动
            document.body.style.overflow = '';
        }

        // 最大化/还原模态框
        function toggleModalMaximize() {
            const modal = document.getElementById('device-detail-modal');
            const maximizeBtn = document.getElementById('maximize-modal-btn');
            
            if (modal.classList.contains('maximized')) {
                // 还原
                modal.classList.remove('maximized');
                maximizeBtn.textContent = '□';
            } else {
                // 最大化
                modal.classList.add('maximized');
                maximizeBtn.textContent = '❐';
            }
        }

        // 等待DOM加载完成后绑定模态框按钮事件
        document.addEventListener('DOMContentLoaded', function() {
            const closeModalBtn = document.getElementById('close-modal-btn');
            const maximizeModalBtn = document.getElementById('maximize-modal-btn');
            const deviceDetailModal = document.getElementById('device-detail-modal');
            
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeDeviceDetailModal);
            }
            
            if (maximizeModalBtn) {
                maximizeModalBtn.addEventListener('click', toggleModalMaximize);
            }
            
            if (deviceDetailModal) {
                deviceDetailModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeDeviceDetailModal();
                    }
                });
            }
            
            // 初始绑定设备名称点击事件
            bindDeviceLinkEvents();
        });

        // 使用当前分页参数搜索问题
        function searchProblemsWithPagination() {
            // 显示加载框
            showLoadingModal('加载中，请稍候...');
            
            const {
                departmentId,
                stationId,
                typeId,
                deviceId,
                keywords
            } = currentSearchParams;

            fetch(`api.php?action=getProblems&cid=${departmentId}&sid=${stationId}&tid=${typeId}&did=${deviceId}&keyword=${encodeURIComponent(keywords)}&page=${currentPage}&pageSize=${currentPageSize}`)
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('search-result');

                    if (data.success && data.data && data.data.length > 0) {
                        let html = '<table class="problems-table">';
                        html += '<thead><tr><th>序号</th><th>设备名称</th><th>问题描述</th><th>状态</th></tr></thead>';
                        html += '<tbody>';

                        data.data.forEach((problem, index) => {
                            // 计算正确的序号
                            const serialNumber = (currentPage - 1) * currentPageSize + index + 1;

                            // 状态显示
                            const statusClass = problem.process === 0 ? 'status-red' : 'status-green';
                            const statusText = problem.process === 0 ? '已创建' : '已闭环';

                            // 添加data属性用于悬浮提示
                            html += `<tr data-report-time="${problem.report_time}" data-resolution-time="${problem.resolution_time || ''}" data-status="${problem.process}">
                                <td>${serialNumber}</td>
                                <td><span class="device-link-btn" data-did="${problem.did}">${getDeviceName(problem)}</span></td>
                                <td><a href="problems.php?pid=${problem.pid}">${problem.description}</a></td>
                                <td><span class="status-tag ${statusClass}">${statusText}</span></td>
                            </tr>`;
                        });

                        html += '</tbody></table>';
                        resultDiv.innerHTML = html;
                        
                        // 重新初始化悬浮提示功能
                        if (typeof window.clearProblemTooltips === 'function') {
                            window.clearProblemTooltips();
                        }
                        
                        // 重新绑定设备名称点击事件
                        bindDeviceLinkEvents();
                        
                        // 更新分页控件
                        addPaginationControls(data.total, currentPage, currentPageSize);
                    } else {
                        resultDiv.innerHTML = '<p class="no-result">没有查询到问题</p>';
                        // 移除分页控件
                        removePaginationControls();
                    }
                    
                    // 隐藏加载框
                    hideLoadingModal();
                })
                .catch(error => {
                    const resultDiv = document.getElementById('search-result');
                    resultDiv.innerHTML = `<p class="error">查询失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls();
                    
                    // 隐藏加载框
                    hideLoadingModal();
                });
        }

        // 更新备注图标功能
        function updateRemarkBadges() {
            // 移除所有现有气泡
            document.querySelectorAll('.remark-bubble').forEach(bubble => {
                bubble.remove();
            });

            // 为所有备注图标添加悬浮事件
            document.querySelectorAll('.remark-badge').forEach(badge => {
                badge.addEventListener('mouseenter', function(e) {
                    const remark = this.getAttribute('data-remark');
                    if (remark) {
                        // 创建气泡容器
                        const bubble = document.createElement('div');
                        bubble.className = 'remark-bubble';
                        bubble.textContent = remark;

                        // 设置位置
                        const rect = this.getBoundingClientRect();
                        bubble.style.position = 'fixed';
                        bubble.style.left = rect.left + 'px';
                        bubble.style.top = (rect.top - 30) + 'px';
                        bubble.style.zIndex = '1000';

                        // 添加样式
                        bubble.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
                        bubble.style.color = 'white';
                        bubble.style.padding = '4px 8px';
                        bubble.style.borderRadius = '4px';
                        bubble.style.fontSize = '12px';
                        bubble.style.whiteSpace = 'nowrap';

                        document.body.appendChild(bubble);
                    }
                });

                badge.addEventListener('mouseleave', function() {
                    // 移除气泡
                    const bubble = document.querySelector('.remark-bubble');
                    if (bubble) {
                        bubble.remove();
                    }
                });
            });
        }

        // 显示加载模态框
        function showLoadingModal(text = '加载中，请稍候...') {
            const modal = document.getElementById('loading-modal');
            const loadingText = modal.querySelector('.loading-content p');
            if (loadingText) {
                loadingText.textContent = text;
            }
            modal.style.display = 'flex';
            // 阻止背景页面滚动
            document.body.style.overflow = 'hidden';
        }

        // 隐藏加载模态框
        function hideLoadingModal() {
            const modal = document.getElementById('loading-modal');
            modal.style.display = 'none';
            // 恢复背景页面滚动
            document.body.style.overflow = '';
        }

        // 页面加载完成后初始化备注图标功能
        window.addEventListener('DOMContentLoaded', updateRemarkBadges);
    </script>

    <style>
        .problems-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        /* 宽屏设备左右分栏布局 */
        .problems-layout {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }

        .problems-search {
            flex: 0 0 320px;
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
        }

        .search-row {
            margin-bottom: 20px;
        }

        .search-item {
            margin-bottom: 15px;
        }

        .search-item label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .select-container {
            position: relative;
        }

        .select-container input[type="text"] {
            width: 100%;
            padding: 12px 40px 12px 12px;
            /* 右侧留出40px空间给删除按钮 */
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            background-color: white;
            box-sizing: border-box;
        }

        /* 为关键字输入框设置正确的光标样式 */
        .select-container #keywords {
            cursor: text;
        }

        .select-container input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }

        /* 删除按钮样式 */
        .clear-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: none;
            background: #ddd;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            line-height: 1;
            color: #666;
        }

        .clear-btn:before {
            content: '×';
            font-weight: bold;
        }

        .clear-btn:hover {
            background: #ccc;
            color: #333;
        }

        .search-button-container {
            text-align: center;
            margin-top: 30px;
        }

        #search-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        #search-button:hover {
            background-color: #2980b9;
        }

        .search-result {
            flex: 1;
            min-height: 400px;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .no-result {
            text-align: center;
            color: #999;
            padding: 50px 0;
        }

        .error {
            text-align: center;
            color: #e74c3c;
            padding: 20px 0;
        }

        .problems-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* 固定表格布局，防止内容撑开 */
        }

        .problems-table th,
        .problems-table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        /* 设置序号列固定宽度（两个字符宽度） */
        .problems-table th:nth-child(1),
        .problems-table td:nth-child(1) {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
        }
        
        /* 设置设备名称列固定宽度（至少4个字符宽度） */
        .problems-table th:nth-child(2),
        .problems-table td:nth-child(2) {
            width: 120px;
            min-width: 120px;
        }
        
        /* 设置问题描述列占据剩余空间 */
        .problems-table th:nth-child(3),
        .problems-table td:nth-child(3) {
            width: auto;
        }
        
        /* 设置状态列固定宽度（刚好显示状态内容） */
        .problems-table th:nth-child(4),
        .problems-table td:nth-child(4) {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
        }
        
        /* 问题描述列左对齐 */
        .problems-table td:nth-child(3) {
            text-align: left;
        }
        
        /* 问题描述链接样式 - 限制行数并显示省略号 */
        .problems-table td:nth-child(3) a {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            /* 电脑上最多显示三行 */
            -webkit-line-clamp: 3;
        }
        
        /* 手机上最多显示五行 */
        @media (max-width: 768px) {
            .problems-table td:nth-child(3) a {
                -webkit-line-clamp: 5;
            }
        }
        
        /* 悬浮提示样式 */
        .problem-tooltip-container {
            position: fixed;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .problem-tooltip {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-family: monospace;
            white-space: nowrap;
            position: relative;
            min-width: 230px;
            text-align: center;
            width: auto;
            box-sizing: border-box;
        }

        .problem-tooltip-text {
            display: block;
        }

        .problem-tooltip-arrow {
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid rgba(0, 0, 0, 0.8);
            pointer-events: none;
        }

        .problems-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .problems-table td a {
            color: #3498db;
            text-decoration: none;
        }

        .problems-table td a:hover {
            text-decoration: underline;
        }
        
        /* 表格行悬停效果 */
        .problems-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* 表格行点击效果（用于触摸设备） */
        .problems-table tr:active {
            background-color: #f0f0f0;
        }

        .status-tag {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: normal;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            min-width: 40px;
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

        /* 分页控件样式 */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }

        .pagination-info {
            font-size: 14px;
            color: #666;
        }

        .pagination-navigation {
            display: flex;
            gap: 5px;
        }

        .pagination-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #f5f5f5;
            color: #333;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination-ellipsis {
            padding: 6px 12px;
            color: #999;
        }

        .pagination-pageSize {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #666;
        }

        .pagination-pageSize select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* 模态框样式 */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        /* 加载提示框样式 */
        #loading-modal {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-content {
            background: white;
            border-radius: 8px;
            padding: 30px 40px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: #f9f9f9;
        }

        .modal-header-left,
        .modal-header-right {
            display: flex;
            gap: 10px;
        }

        .modal-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-btn:hover {
            background: #f5f5f5;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* 设备详情模态框样式 */
        #device-detail-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #device-detail-modal .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 1200px;
            height: 80vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        #device-detail-modal.maximized .modal-content {
            width: 100%;
            height: 100%;
            max-width: none;
            border-radius: 0;
        }

        #device-detail-modal .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: #f9f9f9;
        }

        #device-detail-modal .modal-buttons {
            display: flex;
            gap: 10px;
        }

        #device-detail-modal .maximize-btn,
        #device-detail-modal .close-btn {
            background: none;
            border: 1px solid #ddd;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
        }

        #device-detail-modal .maximize-btn:hover,
        #device-detail-modal .close-btn:hover {
            background: #f5f5f5;
        }

        #device-detail-modal .modal-body {
            flex: 1;
            padding: 0;
            overflow: hidden;
        }

        #device-detail-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .device-link-btn {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #3498db;
            cursor: pointer;
            padding: 2px 6px;
            font-size: 12px;
            border-radius: 4px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .device-link-btn:hover,
        .device-link-btn:active {
            background: #1d6fa5;
            color: white;
            border-color: #1d6fa5;
        }

        .select-path {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #666;
        }

        .path-item {
            cursor: pointer;
            color: #3498db;
        }

        .path-item:hover {
            text-decoration: underline;
        }

        .select-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        .select-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .select-item:hover {
            background: #f5f5f5;
            border-color: #3498db;
        }

        .loading,
        .error,
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .error {
            color: #e74c3c;
        }

        /* 备注图标样式 */
        .remark-badge {
            display: inline-block;
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            background: #3498db;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            cursor: help;
            margin-left: 5px;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            .problems-container {
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
            
            .problems-layout {
                flex-direction: column;
                gap: 0;
            }

            .problems-search {
                flex: none;
                width: 100%;
                border-radius: 0;
                padding: 15px;
            }

            .search-result {
                padding: 10px;
                box-shadow: none;
                border-radius: 0;
            }

            .select-items {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .pagination-container {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .pagination-info {
                text-align: center;
            }

            .pagination-navigation {
                justify-content: center;
            }

            .pagination-pageSize {
                justify-content: center;
            }
            
            /* 设置设备名称列在移动端的宽度为三个字符 */
            .problems-table th:nth-child(2),
            .problems-table td:nth-child(2) {
                width: 60px;
                min-width: 60px;
            }
            
            /* 设置状态列在移动端的固定宽度 */
            .problems-table th:nth-child(4),
            .problems-table td:nth-child(4) {
                width: 60px;
                min-width: 60px;
                max-width: 60px;
            }
            
            /* 确保分页控件在移动端居中显示 */
            .pagination-btn,
            .pagination-ellipsis {
                margin: 0 2px;
            }
        }
    </style>

    <!-- 设备详情模态框 -->
    <div id="device-detail-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>设备详情</h3>
                <div class="modal-buttons">
                    <button type="button" class="maximize-btn" id="maximize-modal-btn">□</button>
                    <button type="button" class="close-btn" id="close-modal-btn">×</button>
                </div>
            </div>
            <div class="modal-body">
                <iframe id="device-detail-iframe" src="" frameborder="0"></iframe>
            </div>
        </div>
    </div>

<?php
}
include 'footer.php';
?>