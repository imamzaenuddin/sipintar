<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Posyandu F1</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        td.text-left {
            text-align: left;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .signature {
            display: inline-block;
            text-align: center;
            width: 200px;
        }
        .signature p {
            margin-top: 80px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        /* Instruksi Cetak */
        .print-controls {
            margin-bottom: 20px;
            padding: 15px;
            background: #eef2f5;
            border: 1px solid #ccd5dc;
            text-align: center;
            border-radius: 5px;
        }
        .btn-print {
            padding: 10px 20px;
            background: #009639;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        
        @media print {
            .print-controls {
                display: none;
            }
            body {
                padding: 0;
            }
            @page {
                size: landscape;
            }
        }
    </style>
</head>
<body>

    <div class="print-controls">
        <p>Silakan klik tombol di bawah atau tekan <strong>Ctrl + P</strong> untuk mencetak dokumen ini / menyimpannya sebagai PDF.</p>
        <button class="btn-print" onclick="window.print()">Cetak Dokumen Sekarang</button>
    </div>

    <div class="header">
        <h2>LAPORAN HASIL PENIMBANGAN POSYANDU (F1)</h2>
        <p>Aplikasi SiPintar - Sistem Informasi & Pemantauan Posyandu Terintegrasi</p>
        <p>Tanggal Cetak: {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Penimbangan</th>
                <th>Nama</th>
                <th>Nama Orang Tua / Wali</th>
                <th>Umur</th>
                <th>Berat Badan (kg)</th>
                <th>Tinggi Badan (cm)</th>
                <th>LILA (cm)</th>
                <th>Lingkar Kepala (cm)</th>
                <th>Status Gizi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->recorded_date)->format('d/m/Y') }}</td>
                    <td class="text-left">{{ $record->familyMember->name }}</td>
                    <td class="text-left">{{ $record->familyMember->family->user->name ?? '-' }}</td>
                    <td>
                        @php
                            $diff = \Carbon\Carbon::parse($record->familyMember->birth_date)->diff(\Carbon\Carbon::parse($record->recorded_date));
                        @endphp
                        {{ $diff->y }}th {{ $diff->m }}bln {{ $diff->d }}hr
                    </td>
                    <td>{{ $record->weight }}</td>
                    <td>{{ $record->height }}</td>
                    <td>{{ $record->lila ?? '-' }}</td>
                    <td>{{ $record->head_circumference ?? '-' }}</td>
                    <td>{{ $record->status_gizi }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Belum ada data penimbangan sasaran terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            Mengetahui,<br>
            Ketua / Kader Posyandu
            <p>{{ auth()->user()->name }}</p>
        </div>
    </div>

</body>
</html>
