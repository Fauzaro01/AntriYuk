<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>Cetak Tiket #{{ $ticket->ticket_number }}</title>
    <style>
        @page {
            size: auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 76mm; /* Standard receipt width */
            margin: 0;
            padding: 4mm;
            color: #000;
            background-color: #fff;
            text-align: center;
            font-size: 11pt;
            line-height: 1.3;
        }
        .header {
            margin-bottom: 4mm;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            margin: 1mm 0;
        }
        .subtitle {
            font-size: 9pt;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
            margin-bottom: 3mm;
        }
        .number-title {
            font-size: 10pt;
            margin-bottom: 1mm;
        }
        .number {
            font-size: 32pt;
            font-weight: bold;
            margin: 2mm 0;
        }
        .priority-badge {
            border: 1px solid #000;
            padding: 1mm 2mm;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
            margin-bottom: 2mm;
        }
        .details {
            font-size: 9pt;
            text-align: left;
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }
        .qr-container {
            margin: 4mm 0;
        }
        .footer {
            font-size: 8pt;
            margin-top: 4mm;
            border-top: 1px dashed #000;
            padding-top: 3mm;
        }
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 2mm;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">AntriYuk</div>
        <div class="subtitle">{{ $ticket->location->name }}</div>
    </div>

    @if($ticket->is_priority)
        <div class="priority-badge">
            🚨 PRIORITAS
        </div>
    @endif

    <div class="number-title">NOMOR ANTRIAN</div>
    <div class="number">{{ $ticket->ticket_number }}</div>

    @if($ticket->serviceCategory)
        <div style="font-weight: bold; font-size: 10pt; margin-bottom: 2mm;">
            {{ $ticket->serviceCategory->name }}
        </div>
    @endif

    @if($ticket->customer_name)
        <div style="font-size: 10pt; margin-bottom: 2mm;">
            Nama: {{ $ticket->customer_name }}
        </div>
    @endif

    {{-- Render QR Code container --}}
    <div class="qr-container">
        <div id="qrcode" style="display: inline-block;"></div>
    </div>

    <div class="details">
        <div class="detail-row">
            <span>Tanggal:</span>
            <span>{{ $ticket->created_at->format('d-m-Y') }}</span>
        </div>
        <div class="detail-row">
            <span>Waktu:</span>
            <span>{{ $ticket->created_at->format('H:i:s') }}</span>
        </div>
        @if($ticket->status === 'waiting')
            <div class="detail-row">
                <span>Antrian Menunggu:</span>
                <span>{{ $ticket->position - 1 }} orang</span>
            </div>
            <div class="detail-row">
                <span>Est. Waktu Tunggu:</span>
                <span>~{{ $ticket->estimated_wait_time }} menit</span>
            </div>
        @endif
    </div>

    <div class="footer">
        Terima kasih atas kunjungan Anda.<br>
        Silakan tunggu nomor Anda dipanggil.<br>
        Cek status antrian real-time dengan scan QR Code di atas.
    </div>

    {{-- Include QRCode library --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generate QR Code
        const qrEl = document.getElementById('qrcode');
        if (qrEl) {
            new QRCode(qrEl, {
                text: "{{ route('tickets.show', $ticket) }}",
                width: 90,
                height: 90,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        }

        // Print and close
        window.onload = function() {
            setTimeout(function() {
                window.print();
                // Close window/tab if opened in new tab, or just redirect back
                setTimeout(function() {
                    window.close();
                }, 500);
            }, 500);
        }
    </script>
</body>
</html>
