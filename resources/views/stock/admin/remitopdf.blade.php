<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; }
        html, body { width: 100%; height: 100%; }
        .wrap {
            width: 100%;
            height: 100%;
            text-align: center;
            page-break-inside: avoid;
            page-break-after: avoid;
        }
        img {
            display: block;
            max-width: 100%;
            max-height: 950px;   /* fijo, no vh */
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <img src="{{ $imagen }}">
    </div>
</body>
</html>