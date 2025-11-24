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
                
                console.log('Table total width:', totalWidth, 'Max width:', maxWidth);
                
                // 檢查表格是否超過容器寬度
                var scale = 1;
                if (totalWidth > maxWidth) {
                    scale = maxWidth / totalWidth;
                    console.log('Scaling table by:', scale);
                }
                
                // 應用縮放後的寬度
                cells.forEach(function(cell) {
                    var width = cell.getAttribute('colwidth');
                    if (width) {
                        var scaledWidth = parseInt(width) * scale;
                        cell.style.width = scaledWidth + 'px';
                        console.log('Cell width:', width, '-> scaled:', scaledWidth);
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
                        '<span style="position: absolute; right: -1em; top: 35%; font-size: 1em;">' + tone + '</span>' + 
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
</body>
</html>
