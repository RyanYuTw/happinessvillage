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
        .content p {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="bg-wrapper">
        <div class="bg-left"></div>
        <div class="bg-right"></div>
        <div class="content">
            {!! html_entity_decode($content) !!}
        </div>
    </div>
</body>
</html>
