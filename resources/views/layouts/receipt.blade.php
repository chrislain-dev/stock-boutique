<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #fff;
            color: #000;
            max-width: 380px;
            margin: 0 auto;
            padding: 20px 10px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .divider-double { border-top: 2px solid #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin: 3px 0; }
        .row-item { margin: 5px 0; }
        .label { color: #555; }
        .text-right { text-align: right; }
        .text-large { font-size: 16px; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 6px; border: 1px solid #000; border-radius: 3px; font-size: 10px; }
        .no-print { display: block; }
        @media print {
            .no-print { display: none !important; }
            body { max-width: 100%; }
        }
        .print-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Imprimer le reçu</button>
    </div>
    {{ $slot }}
</body>
</html>
