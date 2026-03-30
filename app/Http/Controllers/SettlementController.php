<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\TravelCertificate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::all();
        $semanas = $this->buildSemanas($request);

        return view('settlements.index', compact('drivers', 'semanas'));
    }

   public function generateExcel(Request $request)
    {
        $semanas  = $this->buildSemanas($request);
        $filename = 'liquidacion_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new class($semanas) implements WithMultipleSheets {
                public function __construct(protected array $semanas) {}

                public function sheets(): array
                {
                    $sheets = [];
                    for ($s = 1; $s <= 5; $s++) {
                        $tcs = $this->semanas[$s] ?? [];
                        $sheets[] = new class($s, $tcs) implements FromArray, WithTitle, WithHeadings ,WithStyles {
                            public function __construct(
                                protected int   $semana,
                                protected array $travelCertificates
                            ) {}

                            public function title(): string { return "Semana {$this->semana}"; }

                            public function headings(): array
                            {
                                return ['Fecha','N° Constancia','Cliente','Chofer %','Importe neto','Peajes','Chofer (total)','Diferencia'];
                            }

                            public function array(): array
                            {
                                $rows = [];
                                foreach ($this->travelCertificates as $tc) {
                                    $subtotal    = $tc->subtotal_sin_peajes;
                                    $pct         = $tc->driver->percent / 100;
                                    $choferTotal = round($pct * $subtotal, 2);
                                    $diferencia  = round(($subtotal * 0.25) - $choferTotal, 2);

                                    $rows[] = [
                                        $tc->date->format('d/m/Y'),
                                        $tc->number ?? $tc->id,
                                        $tc->client->name,
                                        number_format($tc->driver->percent, 2, ',', '.') . '%',
                                        $subtotal,
                                        $tc->total_peajes,
                                        $choferTotal,
                                        $diferencia,
                                    ];
                                }
                                return $rows;
                            }
                             public function styles(Worksheet $sheet): void
                            {
                                // Header: fondo rojo suave
                                $sheet->getStyle('A1:H1')->applyFromArray([
                                    'fill' => [
                                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => 'FFCCCC'],
                                    ],
                                ]);

                                // Todas las celdas: ajuste de texto + alineación
                                $lastRow = count($this->travelCertificates) + 1; // +1 por el header
                                $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
                                    'alignment' => [
                                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                        'wrapText'   => true,
                                    ],
                                ]);
                            }
                        };
                    }
                    return $sheets;
                }
            },
            $filename
        );
    }

    private function buildSemanas(Request $request): array
    {
        $semanas = [];

        if (!$request->filled('driver_id')) {
            return $semanas;
        }
        $query = TravelCertificate::query()->where('driverId', $request->input('driver_id')); 
        
        $periodo = $request->input('periodo');

        if ($periodo === 'mes') 
        {
            $query->whereMonth('date',  date('n'));
        }
        else{
            $desde = $request->input('desde');
            $hasta = $request->input('hasta');
            $query->whereBetween('date', [ $desde, $hasta ]);
        }

        foreach ($query->orderBy('date')->get() as $tc) {
            $semana = min((int) ceil(Carbon::parse($tc->date)->day / 7), 5);
            $semanas[$semana][] = $tc;
        }

        return $semanas;
    }
}
