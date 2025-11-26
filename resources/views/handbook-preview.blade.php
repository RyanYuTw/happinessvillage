<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>手冊{{ $lesson ?? '' }} - 預覽</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Microsoft JhengHei', sans-serif;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(to bottom, #e8f4f8 0%, #fff9e6 100%);
            position: relative;
        }
        .bg-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .bg-left, .bg-right {
            position: absolute;
            bottom: 0;
            width: 300px;
            height: 400px;
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.6;
            pointer-events: none;
            z-index: 0;
        }
        .bg-left {
            right: calc(50% + 400px);
            background-image: url('/images/banner_illustration_left.png');
            background-position: right bottom;
        }
        .bg-right {
            left: calc(50% + 400px);
            background-image: url('/images/banner_illustration_right.png');
            background-position: left bottom;
        }
        .content {
            width: 800px;
            max-width: 100%;
            padding: 40px;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }
        .content img {
            max-width: 100%;
            height: auto;
        }
        .content img[data-align="center"] {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .content img[data-align="right"] {
            display: block;
            margin-left: auto;
            margin-right: 0;
        }
        .content img[data-align="left"] {
            display: block;
            margin-left: 0;
            margin-right: auto;
        }
        .content table[data-align="center"] {
            margin-left: auto;
            margin-right: auto;
        }
        .content table[data-align="right"] {
            margin-left: auto;
            margin-right: 0;
        }
        .content table[data-align="left"] {
            margin-left: 0;
            margin-right: auto;
        }
        .content table {
            border-collapse: collapse;
            border: 1px solid #d1d5db !important;
            border-style: solid !important;
        }
        .content table td,
        .content table th {
            border: 1px solid #d1d5db !important;
            border-style: solid !important;
            padding: 0.5rem;
        }
        .content table[data-border="1"],
        .content table[data-border="1"] td,
        .content table[data-border="1"] th { 
            border-width: 1px !important;
            border-style: solid !important;
            border-color: #d1d5db !important;
        }
        .content table[data-border="0"],
        .content table[data-border="0"] td,
        .content table[data-border="0"] th { 
            border-width: 0px !important;
            border-style: none !important;
            border-color: transparent !important;
        }
        .content table[data-border="2"],
        .content table[data-border="2"] td,
        .content table[data-border="2"] th { 
            border-width: 2px !important;
            border-style: solid !important;
            border-color: #d1d5db !important;
        }
        .content table[data-border="3"],
        .content table[data-border="3"] td,
        .content table[data-border="3"] th { 
            border-width: 3px !important;
            border-style: solid !important;
            border-color: #d1d5db !important;
        }
        .content table[data-border="4"],
        .content table[data-border="4"] td,
        .content table[data-border="4"] th { 
            border-width: 4px !important;
            border-style: solid !important;
            border-color: #d1d5db !important;
        }
        .content table[data-border="5"],
        .content table[data-border="5"] td,
        .content table[data-border="5"] th { 
            border-width: 5px !important;
            border-style: solid !important;
            border-color: #d1d5db !important;
        }
        .content table td p,
        .content table th p {
            margin: 0;
        }
        .content table td img,
        .content table th img {
            width: 100% !important;
            height: auto !important;
            max-width: 100% !important;
            margin: 0 !important;
            display: block;
            object-fit: contain;
        }
        .content div[data-type="button"] {
            margin: 1rem 0;
            text-align: center;
        }
        .content div[data-type="button"] a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .content div[data-type="button"] a:hover {
            opacity: 0.9;
        }
        .content table span[data-input-field] {
            display: inline-block;
        }
        .content span[data-input-field]:not(table *) {
            position: absolute;
            z-index: 1000;
        }
        .content span[data-input-field] input {
            width: 30px;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .content p {
            white-space: pre-wrap;
        }
        ruby {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            vertical-align: baseline !important;
            margin: 0 0.15em !important;
            line-height: 1.8 !important;
        }
        ruby > span {
            line-height: 1.8 !important;
            display: inline-block !important;
        }
        ruby > rt,
        ruby rt {
            display: inline-flex !important;
            font-size: 0.20em !important;
            writing-mode: vertical-rl !important;
            text-orientation: upright !important;
            margin-left: 0.15em !important;
            line-height: 1.2 !important;
            font-family: "Bopomofo", "Microsoft JhengHei", sans-serif !important;
            color: inherit !important;
            white-space: nowrap !important;
            align-self: center !important;
            justify-content: center !important;
        }
        /* 3個或以上注音符號使用較小字體和較緊密的行高 */
        ruby > rt[data-long="true"],
        ruby rt[data-long="true"] {
            font-size: 0.17em !important;
            line-height: 1.1 !important;
        }
        .content table {
            max-width: 100%;
            table-layout: fixed;
            box-sizing: border-box;
        }
        
        .content table td,
        .content table th {
            box-sizing: border-box;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 檢查是否有從塗鴉編輯器返回的結果
        const drawingResult = sessionStorage.getItem('drawingResult');
        if (drawingResult) {
            // 創建一個新的圖片元素
            const img = document.createElement('img');
            img.src = drawingResult;
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.margin = '20px auto';
            img.style.border = '2px solid #e5e7eb';
            img.style.borderRadius = '8px';
            img.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            
            // 添加到內容區域的開頭
            const contentDiv = document.querySelector('.content');
            if (contentDiv) {
                contentDiv.insertBefore(img, contentDiv.firstChild);
                
                // 添加一個標題
                const title = document.createElement('h3');
                title.textContent = '塗鴉結果';
                title.style.textAlign = 'center';
                title.style.color = '#374151';
                title.style.marginBottom = '10px';
                contentDiv.insertBefore(title, img);
                
                // 添加下載按鈕
                const downloadBtn = document.createElement('button');
                downloadBtn.textContent = '下載圖片';
                downloadBtn.style.display = 'block';
                downloadBtn.style.margin = '10px auto';
                downloadBtn.style.padding = '8px 16px';
                downloadBtn.style.backgroundColor = '#3b82f6';
                downloadBtn.style.color = 'white';
                downloadBtn.style.border = 'none';
                downloadBtn.style.borderRadius = '4px';
                downloadBtn.style.cursor = 'pointer';
                downloadBtn.onclick = function() {
                    const link = document.createElement('a');
                    link.href = drawingResult;
                    link.download = 'drawing-' + Date.now() + '.png';
                    link.click();
                };
                contentDiv.insertBefore(downloadBtn, img.nextSibling);
            }
            
            // 清除 sessionStorage
            sessionStorage.removeItem('drawingResult');
        }
        
        document.querySelectorAll('span[data-input-field]:not(table *)').forEach(function(el) {
            const x = el.getAttribute('data-x');
            const y = el.getAttribute('data-y');
            if (x && y) {
                el.style.left = x + 'px';
                el.style.top = y + 'px';
            }
        });
        
        // 應用 colwidth 屬性到表格
        setTimeout(function() {
            var contentElement = document.querySelector('.content');
            // 容器寬度 800px，padding 左右各 40px，所以可用寬度是 720px
            // 但要預留一些空間給 border，所以實際使用 710px
            var maxWidth = 710;
            
            document.querySelectorAll('.content table').forEach(function(table) {
                var cells = table.querySelectorAll('td[colwidth], th[colwidth]');
                
                // 計算表格總寬度
                var firstRow = table.querySelector('tr');
                var totalWidth = 0;
                if (firstRow) {
                    var firstRowCells = firstRow.querySelectorAll('td[colwidth], th[colwidth]');
                    firstRowCells.forEach(function(cell) {
                        totalWidth += parseInt(cell.getAttribute('colwidth') || '0');
                    });
                }
                
                // 如果總寬度超過容器寬度，計算縮放比例
                var scale = 1;
                if (totalWidth > maxWidth) {
                    scale = maxWidth / totalWidth;
                }
                
                // 應用縮放後的寬度
                cells.forEach(function(cell) {
                    var width = cell.getAttribute('colwidth');
                    if (width) {
                        var scaledWidth = parseInt(width) * scale;
                        cell.style.width = scaledWidth + 'px';
                    }
                });
                
                if (totalWidth > 0) {
                    var scaledTableWidth = totalWidth * scale;
                    table.style.width = scaledTableWidth + 'px';
                    console.log('Table width set to:', scaledTableWidth);
                }
            });
        }, 100);
        
        // 將表格中的占位符替換為 input 標籤
        document.querySelectorAll('.content table').forEach(function(table) {
            const html = table.innerHTML;
            const replaced = html.replace(/______/g, '<input type="text" style="width: 30px; border: 1px solid #d1d5db; border-radius: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.875rem;" />');
            if (html !== replaced) {
                table.innerHTML = replaced;
            }
        });
        
        
        // 處理注音聲調位置
        document.querySelectorAll('ruby rt').forEach(function(rt) {
            // 移除 zoom 樣式
            rt.style.zoom = '';
            
            const text = rt.textContent;
            const toneMatch = text.match(/[ˊˇˋ˙]/);
            const baseText = text.replace(/[ˊˇˋ˙]/g, '');
            
            // 檢查是否有3個或以上注音符號
            const isLong = baseText.length >= 3;
            
            // 先設置字體大小（使用 px 單位避免相對計算）
            if (isLong) {
                rt.setAttribute('data-long', 'true');
                rt.style.setProperty('font-size', '5.5px', 'important');
            } else {
                rt.style.setProperty('font-size', '6px', 'important');
            }
            
            if (toneMatch) {
                const tone = toneMatch[0];
                const tonePos = text.indexOf(tone);
                
                if (tone === '˙') {
                    // 輕聲在第一個注音符號上方
                    rt.innerHTML = '<span style="position: relative;">' + 
                        '<span style="position: absolute; top: -0.4em; left: 0.1em; font-size: 0.9em;">' + tone + '</span>' + 
                        baseText + 
                        '</span>';
                } else {
                    // 2,3,4聲在注音符號右側中間偏下
                    rt.innerHTML = '<span style="position: relative; display: inline-block;">' + 
                        baseText + 
                        '<span style="position: absolute; right: -0.4em; top: 35%; font-size: 1.8em;">' + tone + '</span>' + 
                        '</span>';
                }
            }
            
            // 處理完 innerHTML 後再次設置字體大小，確保生效（使用 px 單位）
            if (isLong) {
                rt.style.setProperty('font-size', '5.5px', 'important');
            } else {
                rt.style.setProperty('font-size', '6px', 'important');
            }
        });
    });
    </script>
</head>
<body>
    <div class="bg-wrapper">
        <div class="bg-left"></div>
        <div class="bg-right"></div>
        <div class="content">
            {!! html_entity_decode(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
        </div>
    </div>

    <!-- Drawing Editor Modal -->
    <div id="drawingModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; width: 900px; max-width: 95%; height: 80vh; display: flex; flex-direction: column; border-radius: 8px; overflow: hidden;">
            <!-- Toolbar -->
            <div style="padding: 10px; background: #f3f4f6; border-bottom: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                <span style="font-size: 12px; color: #4b5563; font-weight: 500;">工具:</span>
                <button class="tool-btn" onclick="setMode('select')" id="btn-select">選取</button>
                <button class="tool-btn active" onclick="setMode('free')" id="btn-free">自由繪畫</button>
                <button class="tool-btn" onclick="setMode('line')" id="btn-line">直線</button>
                <button class="tool-btn" onclick="setMode('rect')" id="btn-rect">矩形</button>
                <button class="tool-btn" onclick="setMode('circle')" id="btn-circle">圓形</button>
                <button class="tool-btn" onclick="setMode('triangle')" id="btn-triangle">三角形</button>
                
                <div style="width: 1px; height: 24px; background: #d1d5db; margin: 0 4px;"></div>
                
                <span style="font-size: 12px; color: #4b5563; font-weight: 500;">邊框:</span>
                <input type="color" id="drawColor" value="#000000" style="height: 24px; width: 30px; border: 1px solid #d1d5db; padding: 0;">
                
                <div style="display: flex; align-items: center; gap: 4px; margin-left: 8px;">
                    <input type="checkbox" id="enableFill">
                    <label for="enableFill" style="font-size: 12px; color: #4b5563; font-weight: 500;">填滿:</label>
                    <input type="color" id="fillColor" value="#000000" disabled style="height: 24px; width: 30px; border: 1px solid #d1d5db; padding: 0; opacity: 0.5;">
                    <input type="range" id="fillOpacity" min="0" max="100" value="100" disabled style="width: 60px;" title="填滿透明度">
                    <span id="opacityVal" style="font-size: 12px; color: #6b7280; width: 30px;">100%</span>
                </div>
                
                <span style="font-size: 12px; color: #4b5563; font-weight: 500; margin-left: 8px;">粗細:</span>
                <input type="range" id="drawWidth" min="1" max="20" value="2" style="width: 80px;">
                <span id="widthVal" style="font-size: 12px; color: #6b7280; width: 20px;">2</span>
                
                <div style="width: 1px; height: 24px; background: #d1d5db; margin: 0 4px;"></div>
                
                <span style="font-size: 12px; color: #4b5563; font-weight: 500;">線條:</span>
                <button class="tool-btn" onclick="setStrokeStyle('solid')" title="實線">──</button>
                <button class="tool-btn" onclick="setStrokeStyle('dashed')" title="虛線">━ ━</button>
                <button class="tool-btn" onclick="setStrokeStyle('dotted')" title="點線">・・・</button>
                
                <div style="flex: 1;"></div>
                
                <button onclick="clearCanvas()" style="padding: 5px 10px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">清除</button>
            </div>
            
            <!-- Canvas Area -->
            <div style="flex: 1; overflow: auto; background: #e5e7eb; display: flex; justify-content: center; align-items: center; padding: 20px;">
                <div style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <canvas id="drawingCanvas"></canvas>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="padding: 10px; background: white; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px;">
                <button onclick="closeEditor()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">取消</button>
                <button onclick="saveDrawing()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">儲存</button>
            </div>
        </div>
    </div>

    <style>
        .tool-btn {
            padding: 4px 8px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            color: #374151;
        }
        .tool-btn:hover {
            background: #f3f4f6;
        }
        .tool-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script>
        let canvas = null;
        let currentTargetImg = null;
        let drawMode = 'free';
        let isDrawing = false;
        let startX = 0;
        let startY = 0;
        let currentShape = null;
        let strokeDashArray = [];

        function hexToRgba(hex, opacity) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${opacity / 100})`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // ... existing code ...
            
            document.querySelector('.content').addEventListener('dblclick', function(e) {
                if (e.target.tagName === 'IMG' && e.target.getAttribute('data-from-drawing') === 'true') {
                    openEditor(e.target);
                }
            });

            // 初始化按鈕事件 (These buttons are not in the provided HTML, assuming they are elsewhere or will be added)
            // document.getElementById('cancelBtn').onclick = closeEditor;
            // document.getElementById('saveBtn').onclick = saveDrawing;
            // document.getElementById('clearBtn').onclick = clearCanvas;
            
            document.getElementById('drawColor').oninput = function() {
                updateBrush();
            };
            
            document.getElementById('enableFill').onchange = function() {
                const fillColorInput = document.getElementById('fillColor');
                const fillOpacityInput = document.getElementById('fillOpacity');
                const disabled = !this.checked;
                
                fillColorInput.disabled = disabled;
                fillColorInput.style.opacity = disabled ? '0.5' : '1';
                
                fillOpacityInput.disabled = disabled;
                
                updateBrush();
            };
            
            document.getElementById('fillColor').oninput = function() {
                updateBrush();
            };

            document.getElementById('fillOpacity').oninput = function() {
                document.getElementById('opacityVal').textContent = this.value + '%';
                updateBrush();
            };
            
            document.getElementById('drawWidth').oninput = function() {
                document.getElementById('widthVal').textContent = this.value;
                updateBrush();
            };

            // 監聽鍵盤刪除事件
            document.addEventListener('keydown', function(e) {
                if (!canvas) return;
                
                // 檢查是否按下 Delete 或 Backspace 鍵
                if (e.key === 'Delete' || e.key === 'Backspace') {
                    // 確保不是在輸入框中輸入
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
                    
                    const activeObjects = canvas.getActiveObjects();
                    if (activeObjects.length) {
                        canvas.discardActiveObject();
                        activeObjects.forEach(function(obj) {
                            canvas.remove(obj);
                        });
                        canvas.requestRenderAll();
                    }
                }
            });
        });

        function openEditor(img) {
            currentTargetImg = img;
            const modal = document.getElementById('drawingModal');
            modal.style.display = 'flex';

            // 初始化 Canvas
            if (!canvas) {
                canvas = new fabric.Canvas('drawingCanvas', {
                    isDrawingMode: true,
                    width: 800,
                    height: 600
                });
                
                // 綁定事件
                canvas.on('mouse:down', handleMouseDown);
                canvas.on('mouse:move', handleMouseMove);
                canvas.on('mouse:up', handleMouseUp);
            }

            // 重置狀態
            setMode('free');
            document.getElementById('drawColor').value = '#000000';
            document.getElementById('fillColor').value = '#000000';
            document.getElementById('fillOpacity').value = '100';
            document.getElementById('opacityVal').textContent = '100%';
            document.getElementById('enableFill').checked = false;
            document.getElementById('fillColor').disabled = true;
            document.getElementById('fillColor').style.opacity = '0.5';
            document.getElementById('fillOpacity').disabled = true;
            document.getElementById('drawWidth').value = '2';
            document.getElementById('widthVal').textContent = '2';
            strokeDashArray = [];
            updateBrush();

            // 載入背景
            fabric.Image.fromURL(img.src, function(bgImg) {
                const maxWidth = 800;
                // 如果圖片小於 800，就用原圖大小，如果大於 800，就縮放到 800
                // 這裡我們固定畫布寬度為 800 (與 editor 全螢幕模式一致)
                const targetWidth = 800;
                
                // 計算縮放比例以適應 800px 寬度
                const scale = targetWidth / bgImg.width;
                
                canvas.setWidth(targetWidth);
                canvas.setHeight(bgImg.height * scale);
                
                bgImg.scale(scale);
                canvas.setBackgroundImage(bgImg, canvas.renderAll.bind(canvas));
                
                // 重新計算偏移量，確保點擊位置準確
                canvas.calcOffset();
            });
        }

        function closeEditor() {
            document.getElementById('drawingModal').style.display = 'none';
            if (canvas) {
                canvas.clear();
            }
            currentTargetImg = null;
        }

        function setMode(mode) {
            drawMode = mode;
            canvas.isDrawingMode = (mode === 'free');
            
            // 更新按鈕樣式
            document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
            const btnId = 'btn-' + mode;
            const btn = document.getElementById(btnId);
            if (btn) btn.classList.add('active');
            
            // 如果切換到選取模式，允許選取物件
            canvas.selection = (mode === 'select');
            canvas.defaultCursor = (mode === 'select' ? 'default' : 'crosshair');
            
            canvas.forEachObject(function(o) {
                o.selectable = (mode === 'select');
                o.evented = (mode === 'select');
            });
            
            if (mode !== 'select') {
                canvas.discardActiveObject();
            }
            
            canvas.requestRenderAll();
        }

        function setStrokeStyle(style) {
            switch(style) {
                case 'solid': strokeDashArray = []; break;
                case 'dashed': strokeDashArray = [10, 5]; break;
                case 'dotted': strokeDashArray = [2, 5]; break;
            }
            updateBrush();
        }

        function updateBrush() {
            if (!canvas) return;
            const color = document.getElementById('drawColor').value;
            const width = parseInt(document.getElementById('drawWidth').value);
            const enableFill = document.getElementById('enableFill').checked;
            const fillColor = document.getElementById('fillColor').value;
            const fillOpacity = parseInt(document.getElementById('fillOpacity').value);
            const fill = enableFill ? hexToRgba(fillColor, fillOpacity) : 'transparent';
            
            canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
            canvas.freeDrawingBrush.color = color;
            canvas.freeDrawingBrush.width = width;
            canvas.freeDrawingBrush.strokeDashArray = strokeDashArray;
            
            // 如果有選中的物件，也更新它們的樣式
            const activeObject = canvas.getActiveObject();
            if (activeObject) {
                activeObject.set({
                    stroke: color,
                    strokeWidth: width,
                    strokeDashArray: strokeDashArray,
                    fill: fill
                });
                canvas.requestRenderAll();
            }
        }

        function handleMouseDown(o) {
            if (drawMode === 'free' || drawMode === 'select') return;
            
            isDrawing = true;
            const pointer = canvas.getPointer(o.e);
            startX = pointer.x;
            startY = pointer.y;
            
            const color = document.getElementById('drawColor').value;
            const width = parseInt(document.getElementById('drawWidth').value);
            const enableFill = document.getElementById('enableFill').checked;
            const fillColor = document.getElementById('fillColor').value;
            const fillOpacity = parseInt(document.getElementById('fillOpacity').value);
            const fill = enableFill ? hexToRgba(fillColor, fillOpacity) : 'transparent';
            
            const commonProps = {
                stroke: color,
                strokeWidth: width,
                strokeDashArray: strokeDashArray,
                fill: fill,
                selectable: false // 繪製時不可選取
            };

            switch(drawMode) {
                case 'line':
                    currentShape = new fabric.Line([startX, startY, startX, startY], commonProps);
                    break;
                case 'rect':
                    currentShape = new fabric.Rect({
                        left: startX, top: startY, width: 0, height: 0,
                        ...commonProps
                    });
                    break;
                case 'circle':
                    currentShape = new fabric.Circle({
                        left: startX, top: startY, radius: 0,
                        ...commonProps
                    });
                    break;
                case 'triangle':
                    currentShape = new fabric.Triangle({
                        left: startX, top: startY, width: 0, height: 0,
                        ...commonProps
                    });
                    break;
            }

            if (currentShape) {
                canvas.add(currentShape);
            }
        }

        function handleMouseMove(o) {
            if (!isDrawing || !currentShape) return;
            
            const pointer = canvas.getPointer(o.e);
            
            switch(drawMode) {
                case 'line':
                    currentShape.set({ x2: pointer.x, y2: pointer.y });
                    break;
                case 'rect':
                    const w = pointer.x - startX;
                    const h = pointer.y - startY;
                    currentShape.set({
                        width: Math.abs(w),
                        height: Math.abs(h),
                        left: w < 0 ? pointer.x : startX,
                        top: h < 0 ? pointer.y : startY
                    });
                    break;
                case 'circle':
                    const radius = Math.sqrt(Math.pow(pointer.x - startX, 2) + Math.pow(pointer.y - startY, 2)) / 2;
                    currentShape.set({ radius: radius });
                    break;
                case 'triangle':
                    const tw = pointer.x - startX;
                    const th = pointer.y - startY;
                    currentShape.set({
                        width: Math.abs(tw),
                        height: Math.abs(th),
                        left: tw < 0 ? pointer.x : startX,
                        top: th < 0 ? pointer.y : startY
                    });
                    break;
            }
            canvas.requestRenderAll();
        }

        function handleMouseUp() {
            if (isDrawing) {
                isDrawing = false;
                if (currentShape) {
                    currentShape.setCoords();
                    currentShape = null;
                }
            }
        }

        function clearCanvas() {
            if (!canvas) return;
            const bg = canvas.backgroundImage;
            canvas.clear();
            canvas.setBackgroundImage(bg, canvas.renderAll.bind(canvas));
        }

        function saveDrawing() {
            if (!canvas || !currentTargetImg) return;
            
            const dataUrl = canvas.toDataURL({
                format: 'png',
                quality: 1
            });
            
            currentTargetImg.src = dataUrl;
            closeEditor();
        }
    </script>
</body>
</html>
