<?php
// 设置导航标题和页面标题
$nav_title = '设备信息查询';
$page_title = '设备信息查询 - 个人设备信息管理平台';

// 引入配置文件和页眉
include 'config.php';

// 判断是否为设备详情页面
if (isset($_GET['did'])) {
    $did = $_GET['did'];

    // 判断是否为编辑模式
    if (isset($_GET['mode']) && $_GET['mode'] == 'edit') {
        // 设备编辑页面逻辑
        include 'devices_edit.php';
    } else {
        // 设备详情页面逻辑
        include 'devices_detail.php';
    }
} else {
    include 'header.php';
    // 设备查询页面逻辑
?>
    <div class="devices-container">

        <div class="devices-layout">
            <div class="devices-search">
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
                            <label>请输入关键字词<span class="remark-badge" data-remark="与设备名称/备注有关的关键字词">!</span></label>
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
                <p class="no-result">请选择分类后点击按钮查询设备</p>
            </div>
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
            }

            // 发送请求获取数据
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let html = '';

                        // 显示当前路径
                        if (currentSelectPath.length > 0) {
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
                            html += `<div class="select-item" data-id="${item.id}" data-name="${item.name}" data-shortname="${item.shortname || item.name}">${item.name}</div>`;
                        });
                        html += '</div>';

                        contentDiv.innerHTML = html;

                        // 添加选项点击事件
                        document.querySelectorAll('.select-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                const name = this.getAttribute('data-name');
                                const shortname = this.getAttribute('data-shortname');

                                // 添加到选择路径
                                currentSelectPath.push({
                                    id,
                                    name,
                                    shortname
                                });

                                // 加载下一级数据
                                loadSelectData(id);
                            });
                        });

                        // 添加路径点击事件
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
                    } else {
                        // 没有下一级数据，直接确认选择
                        confirmSelect();
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
                } else if (type === 'station') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('station').value = pathStr;
                    document.getElementById('station-id').value = lastItem.id;
                    // 更新删除按钮可见性
                    const btn = document.querySelector('.clear-btn[data-target="station"]');
                    updateClearButtonVisibility(document.getElementById('station'), btn);
                } else if (type === 'type') {
                    const pathStr = currentSelectPath.map(item => item.name).join('/');
                    document.getElementById('type').value = pathStr;
                    document.getElementById('type-id').value = lastItem.id;
                    // 更新删除按钮可见性
                    const btn = document.querySelector('.clear-btn[data-target="type"]');
                    updateClearButtonVisibility(document.getElementById('type'), btn);
                }
            }

            // 关闭模态框
            document.getElementById('select-modal').style.display = 'none';
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
            ['department', 'station', 'type', 'keywords'].forEach(type => {
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
            if (type === 'department' || type === 'station' || type === 'type') {
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
            const departmentId = document.getElementById('department-id').value;
            const stationId = document.getElementById('station-id').value;
            const typeId = document.getElementById('type-id').value;
            const keywords = document.getElementById('keywords').value;

            // 保存当前搜索参数
            currentSearchParams = {
                departmentId,
                stationId,
                typeId,
                keywords
            };

            // 重置为第一页
            currentPage = 1;

            // 发送查询请求
            fetch(`api.php?action=searchDevices&departmentId=${departmentId}&stationId=${stationId}&typeId=${typeId}&keywords=${encodeURIComponent(keywords)}&page=${currentPage}&pageSize=${currentPageSize}`)
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('search-result');

                    if (data.data && data.data.length > 0) {
                        let html = '<table class="devices-table">';
                        html += '<thead><tr><th>序号</th><th>设备名称</th></tr></thead>';
                        html += '<tbody>';

                        data.data.forEach((device, index) => {
                            const hasRemark = device.remark ? 'has-remark' : '';
                            // 计算正确的序号
                            const serialNumber = (currentPage - 1) * currentPageSize + index + 1;
                            html += `<tr><td>${serialNumber}</td><td class="${hasRemark}"><a href="devices.php?did=${device.did}">${device.device_name}</a>${device.remark ? '<span class="remark-badge" data-remark="' + device.remark + '">!</span>' : ''}</td></tr>`;
                        });

                        html += '</tbody></table>';
                        resultDiv.innerHTML = html;
                        // 隐藏无结果提示（如果存在）
                        const noResultElement = document.querySelector('.no-result');
                        if (noResultElement) {
                            noResultElement.style.display = 'none';
                        }
                        // 更新备注图标以确保气泡功能正常
                        updateRemarkBadges();
                        // 添加分页控件
                        addPaginationControls(data.total, currentPage, currentPageSize);
                    } else {
                        resultDiv.innerHTML = '<p class="no-result">没有查询到设备</p>';
                        // 移除可能存在的分页控件
                        removePaginationControls();
                    }
                })
                .catch(error => {
                    const resultDiv = document.getElementById('search-result');
                    resultDiv.innerHTML = `<p class="error">查询失败: ${error.message}</p>`;
                });
        });
    </script>

    <style>
        .devices-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        /* 宽屏设备左右分栏布局 */
        .devices-layout {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }

        .devices-search {
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

        .devices-table {
            width: 100%;
            border-collapse: collapse;
        }

        .devices-table th,
        .devices-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .devices-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .devices-table tr:hover {
            background-color: #f9f9f9;
        }

        .devices-table a {
            color: #3498db;
            text-decoration: none;
        }

        .devices-table a:hover {
            text-decoration: underline;
        }

        .has-remark {
            position: relative;
        }

        .remark-badge {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            text-align: center;
            line-height: 16px;
            font-size: 12px;
            margin-left: 5px;
            cursor: help;
            position: relative;
        }

        /* 模态框样式 */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* 确保选择模态框居中显示 */
        #select-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            /* 确保内容不会溢出容器 */
            overflow: hidden;
            /* 使用flex布局让模态框内容居中 */
            margin: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .modal-body {
            padding: 15px;
            overflow-y: auto;
        }

        .modal-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
        }

        .reset-btn,
        .cancel-btn {
            background-color: #95a5a6;
            color: white;
        }

        .default-btn {
            background-color: #3498db;
            color: white;
        }
        
        .confirm-btn {
            background-color: #4CAF50;
            color: white;
        }

        .reset-btn:hover,
        .cancel-btn:hover,
        .default-btn:hover {
            opacity: 0.9;
        }
        
        .confirm-btn:hover {
            background-color: #45a049;
        }

        .select-path {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .path-item {
            cursor: pointer;
            color: #3498db;
        }

        .path-item:hover {
            text-decoration: underline;
        }

        .select-items {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .select-item {
            padding: 10px 15px;
            background-color: #f5f5f5;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .select-item:hover {
            background-color: #3498db;
            color: white;
        }

        .loading,
        .error {
            text-align: center;
            padding: 50px 0;
            color: #999;
        }

        .error {
            color: #e74c3c;
        }

        @media (max-width: 768px) {

            /* 窄屏时调整全局容器宽度 */
            .container {
                max-width: none;
                width: 100%;
                padding: 0;
                margin: 0;
            }

            /* 窄屏时去除外层容器的背景和内边距 */
            .devices-container {
                background: none;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
                width: 100%;
            }

            .devices-layout {
                flex-direction: column;
                gap: 20px;
                width: 100%;
                padding: 0 10px;
            }

            .devices-search {
                flex: none;
                width: 100%;
                padding: 20px;
                border-radius: 8px;
            }

            .search-result {
                min-height: 300px;
                padding: 15px;
                border-radius: 8px;
            }

            .modal-content {
                width: 95%;
                max-height: 90vh;
            }
        }
    </style>

    <!-- 分页控制样式 -->
    <style>
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
            align-items: center;
            gap: 5px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #f5f5f5;
            border-color: #3498db;
        }

        .pagination-btn.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination-btn:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .pagination-ellipsis {
            padding: 0 10px;
            color: #999;
        }

        .pagination-pageSize {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .pagination-pageSize select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }

        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }

            .pagination-info,
            .pagination-pageSize {
                order: 1;
            }

            .pagination-navigation {
                order: 2;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>

    <!-- 气泡提示样式 -->
    <style>
        .remark-bubble {
            position: absolute;
            background-color: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 1000;
            width: 150px;
            max-width: 200px;
            word-wrap: break-word;
            white-space: normal;
            display: none;
        }

        .remark-bubble::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .remark-badge:hover .remark-bubble,
        .remark-badge.active .remark-bubble {
            display: block;
        }
    </style>

    <script>
        // 为所有备注图标添加气泡提示功能
        document.addEventListener('DOMContentLoaded', function() {
            // 使用事件委托来处理动态生成的备注图标
            document.addEventListener('click', function(event) {
                // 点击其他区域关闭所有气泡
                if (!event.target.classList.contains('remark-badge') && !event.target.classList.contains('remark-bubble')) {
                    document.querySelectorAll('.remark-bubble').forEach(bubble => {
                        bubble.style.display = 'none';
                    });
                    document.querySelectorAll('.remark-badge.active').forEach(badge => {
                        badge.classList.remove('active');
                    });
                    return;
                }

                // 点击备注图标或气泡时显示气泡
                if (event.target.classList.contains('remark-badge') || event.target.classList.contains('remark-bubble')) {
                    const badge = event.target.classList.contains('remark-badge') ? event.target : event.target.closest('.remark-badge');
                    const remark = badge.getAttribute('data-remark');

                    // 检查是否已经有气泡
                    let bubble = badge.querySelector('.remark-bubble');
                    if (!bubble) {
                        // 创建气泡元素
                        bubble = document.createElement('span');
                        bubble.className = 'remark-bubble';
                        bubble.textContent = remark;
                        badge.appendChild(bubble);
                    } else {
                        // 显示气泡
                        bubble.style.display = 'block';
                    }

                    // 计算气泡位置
                    const badgeRect = badge.getBoundingClientRect();

                    // 气泡显示在图标上方
                    bubble.style.bottom = (badgeRect.height + 10) + 'px';
                    bubble.style.left = '50%';
                    bubble.style.transform = 'translateX(-50%)';

                    // 为气泡添加active类
                    badge.classList.add('active');

                    // 确保只有一个气泡显示
                    document.querySelectorAll('.remark-badge.active').forEach(otherBadge => {
                        if (otherBadge !== badge) {
                            otherBadge.classList.remove('active');
                            const otherBubble = otherBadge.querySelector('.remark-bubble');
                            if (otherBubble) {
                                otherBubble.style.display = 'none';
                            }
                        }
                    });

                    // 阻止事件冒泡
                    event.stopPropagation();
                }
            });

            // 为备注图标添加鼠标悬浮事件（桌面端）
            document.addEventListener('mouseover', function(event) {
                if (event.target.classList.contains('remark-badge')) {
                    const badge = event.target;
                    const remark = badge.getAttribute('data-remark');

                    // 检查是否已经有气泡
                    let bubble = badge.querySelector('.remark-bubble');
                    if (!bubble) {
                        // 创建气泡元素
                        bubble = document.createElement('span');
                        bubble.className = 'remark-bubble';
                        bubble.textContent = remark;
                        badge.appendChild(bubble);
                    } else {
                        // 如果气泡已存在但被隐藏，显示它
                        bubble.style.display = 'block';
                    }

                    // 计算气泡位置
                    const badgeRect = badge.getBoundingClientRect();

                    // 气泡显示在图标上方
                    bubble.style.bottom = (badgeRect.height + 10) + 'px';
                    bubble.style.left = '50%';
                    bubble.style.transform = 'translateX(-50%)';
                }
            });

            // 鼠标离开备注图标时隐藏气泡（桌面端）
            document.addEventListener('mouseout', function(event) {
                if (event.target.classList.contains('remark-badge') || event.target.classList.contains('remark-bubble')) {
                    const badge = event.target.classList.contains('remark-badge') ? event.target : event.target.closest('.remark-badge');
                    // 只有非点击模式（无active类）下才在鼠标离开时隐藏气泡
                    if (!badge.classList.contains('active')) {
                        const bubble = badge.querySelector('.remark-bubble');
                        if (bubble) {
                            bubble.style.display = 'none';
                        }
                    }
                }
            });

            // 为备注图标添加触摸事件（移动端）
            document.addEventListener('touchstart', function(event) {
                if (event.target.classList.contains('remark-badge')) {
                    const badge = event.target;
                    const remark = badge.getAttribute('data-remark');

                    // 创建气泡元素
                    let bubble = badge.querySelector('.remark-bubble');
                    if (!bubble) {
                        bubble = document.createElement('span');
                        bubble.className = 'remark-bubble';
                        bubble.textContent = remark;
                        badge.appendChild(bubble);
                    }

                    // 显示气泡
                    bubble.style.display = 'block';

                    // 计算气泡位置
                    const badgeRect = badge.getBoundingClientRect();

                    // 气泡显示在图标上方
                    bubble.style.bottom = (badgeRect.height + 10) + 'px';
                    bubble.style.left = '50%';
                    bubble.style.transform = 'translateX(-50%)';

                    // 为气泡添加active类
                    badge.classList.add('active');

                    // 确保只有一个气泡显示
                    document.querySelectorAll('.remark-badge.active').forEach(otherBadge => {
                        if (otherBadge !== badge) {
                            otherBadge.classList.remove('active');
                            const otherBubble = otherBadge.querySelector('.remark-bubble');
                            if (otherBubble) {
                                otherBubble.style.display = 'none';
                            }
                        }
                    });

                    // 阻止默认行为
                    event.preventDefault();
                }
            }, {
                passive: false
            });
        });

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
                    searchDevicesWithPagination();
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
                    searchDevicesWithPagination();
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
                searchDevicesWithPagination();
            });

            pageSizeDiv.appendChild(pageSizeSelect);
            paginationContainer.appendChild(pageSizeDiv);

            // 添加到结果区域内部下方，实现宽屏状态下的需求
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
                    searchDevicesWithPagination();
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

        // 使用当前分页参数搜索设备
        function searchDevicesWithPagination() {
            const {
                departmentId,
                stationId,
                typeId,
                keywords
            } = currentSearchParams;

            fetch(`api.php?action=searchDevices&departmentId=${departmentId}&stationId=${stationId}&typeId=${typeId}&keywords=${encodeURIComponent(keywords)}&page=${currentPage}&pageSize=${currentPageSize}`)
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('search-result');

                    if (data.data && data.data.length > 0) {
                        let html = '<table class="devices-table">';
                        html += '<thead><tr><th>序号</th><th>设备名称</th></tr></thead>';
                        html += '<tbody>';

                        data.data.forEach((device, index) => {
                            const hasRemark = device.remark ? 'has-remark' : '';
                            // 计算正确的序号
                            const serialNumber = (currentPage - 1) * currentPageSize + index + 1;
                            html += `<tr><td>${serialNumber}</td><td class="${hasRemark}"><a href="devices.php?did=${device.did}">${device.device_name}</a>${device.remark ? '<span class="remark-badge" data-remark="' + device.remark + '">!</span>' : ''}</td></tr>`;
                        });

                        html += '</tbody></table>';
                        resultDiv.innerHTML = html;
                        // 更新备注图标以确保气泡功能正常
                        updateRemarkBadges();
                        // 更新分页控件
                        addPaginationControls(data.total, currentPage, currentPageSize);
                    } else {
                        resultDiv.innerHTML = '<p class="no-result">没有查询到设备</p>';
                        // 移除分页控件
                        removePaginationControls();
                    }
                })
                .catch(error => {
                    const resultDiv = document.getElementById('search-result');
                    resultDiv.innerHTML = `<p class="error">查询失败: ${error.message}</p>`;
                    // 移除分页控件
                    removePaginationControls();
                });
        }

        function updateRemarkBadges() {
            // 移除所有现有气泡
            document.querySelectorAll('.remark-bubble').forEach(bubble => {
                bubble.remove();
            });
        }
    </script>

<?php
}

// 引入页脚
include 'footer.php';
