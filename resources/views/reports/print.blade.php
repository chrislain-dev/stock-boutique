<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport {{ $from }} → {{ $to }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; color: #000; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 16px 0 8px; border-bottom: 1px solid #000; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f0f0f0; padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 5px 8px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background: #f9f9f9; }
        .no-print { display: block; }
        @media print { .no-print { display: none !important; } }
        .print-btn { padding: 8px 16px; background: #000; color: #fff; border: none; cursor: pointer; border-radius: 4px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Imprimer</button>
    </div>

    <h1>{{ config('boutique.nom') }}</h1>
    <p>Rapport du {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</p>
    <p style="font-size:10px; color:#666;">Généré le {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Chiffre d'affaires par jour</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-right">Nb ventes</th>
                <th class="text-right">CA total</th>
                <th class="text-right">Encaissé</th>
                <th class="text-right">Reste dû</th>
            </tr>
        </thead>
        <tbody>
            @foreach($caStats as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                <td class="text-right">{{ $row->count }}</td>
                <td class="text-right">{{ number_format($row->total, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($row->paid, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($row->total - $row->paid, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ $caStats->sum('count') }}</td>
                <td class="text-right">{{ number_format($caStats->sum('total'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($caStats->sum('paid'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($caStats->sum('total') - $caStats->sum('paid'), 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Marge par marque</h2>
    <table>
        <thead>
            <tr>
                <th>Marque</th>
                <th class="text-right">Qté</th>
                <th class="text-right">CA</th>
                <th class="text-right">Coût</th>
                <th class="text-right">Bénéfice</th>
                <th class="text-right">Marge %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marginStats as $row)
            <tr>
                <td>{{ $row->brand }}</td>
                <td class="text-right">{{ $row->qty }}</td>
                <td class="text-right">{{ number_format($row->ca, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($row->cost, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($row->profit, 0, ',', ' ') }}</td>
                <td class="text-right">{{ $row->ca > 0 ? round(($row->profit / $row->ca) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ $marginStats->sum('qty') }}</td>
                <td class="text-right">{{ number_format($marginStats->sum('ca'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($marginStats->sum('cost'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($marginStats->sum('profit'), 0, ',', ' ') }}</td>
                <td class="text-right">
                    {{ $marginStats->sum('ca') > 0 ? round(($marginStats->sum('profit') / $marginStats->sum('ca')) * 100, 1) : 0 }}%
                </td>
            </tr>
        </tbody>
    </table>

    <script>
    window.onload = function() {
            // Décommenter pour impression automatique
            window.print();
        }
    </script>
</body>
</html>
