<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportPdfExport
{
    public function export(array $filters)
    {
        $tickets = Ticket::filter($filters)->get();
        $html = View::make('reports.pdf', ['tickets' => $tickets])->render();
        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->download('report-' . now()->format('Ymd_Hi') . '.pdf');
    }
}
?>
