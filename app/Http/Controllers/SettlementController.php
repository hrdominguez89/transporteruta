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
    $drivers  = Driver::all();
    $periodo  = $request->input('periodo');
    $driverId = $request->input('driver_id');
    $desde    = $periodo !== 'mes' ? $request->input('desde') : null;
    $hasta    = $periodo !== 'mes' ? $request->input('hasta') : null;

    $semanas = $this->normalizeSemanas(
        $this->buildSemanas($driverId, $periodo, $desde, $hasta)
    );

    return view('settlements.index', compact('drivers', 'semanas'));
}   
   public function generateExcel(Request $request)
    {
    if ($request->isMethod('POST')) {
        $semanas = $this->normalizeSemanas($request->input('semanas'));
    } else {
        $periodo  = $request->input('periodo');
        $driverId = $request->input('driver_id');
        $desde    = $periodo !== 'mes' ? $request->input('desde') : null;
        $hasta    = $periodo !== 'mes' ? $request->input('hasta') : null;

        $semanas = $this->normalizeSemanas(
            $this->buildSemanas($driverId, $periodo, $desde, $hasta)
        );
    }
    
    $filename = 'liquidacion_' . now()->format('Y-m-d') . '.xlsx';

    return Excel::download(
        new class($semanas) implements WithMultipleSheets {
            public function __construct(protected array $semanas) {}

            public function sheets(): array
            {
                $sheets = [];

                for ($s = 1; $s <= 5; $s++) {
                    $tcs = $this->semanas[$s] ?? [];
                    $sheets[] = new class($s, $tcs) implements FromArray, WithTitle, WithHeadings, WithStyles {
                        public function __construct(
                            protected int   $semana,
                            protected array $travelCertificates
                        ) {}

                        public function title(): string { return "Semana {$this->semana}"; }

                        public function headings(): array
                        {
                            return [
                                'Fecha',
                                'N° Constancia',
                                'Cliente',
                                'Chofer %',
                                'Importe neto',
                                'Peajes',
                                'Chofer (total)',
                                'Carg/Desc(B)',
                                'Carg/Desc(N)',
                                'Diferencia',
                            ];
                        }

                        public function array(): array
                        {
                            $rows = [];
                            foreach ($this->travelCertificates as $tc) {
                                $subtotal    = $tc['subtotal_sin_peajes'];
                                $pct         = $tc['driver']['percent'] / 100;
                                $choferTotal = round($pct * $subtotal, 2);
                                $diferencia  = round(($subtotal * 0.25) - $choferTotal, 2);

                                $rows[] = [
                                    $tc['date'],
                                    $tc['number'],
                                    $tc['client']['name'],
                                    number_format($tc['driver']['percent'], 2, ',', '.') . '%',
                                    $subtotal,
                                    $tc['total_peajes'],
                                    $choferTotal,
                                    $tc['totalcargadescargaB'],
                                    $tc['totalcargadescargaN'],
                                    $diferencia,
                                ];
                            }
                            return $rows;
                        }

                        public function styles(Worksheet $sheet): void
                        {
                            $sheet->getStyle('A1:J1')->applyFromArray([
                                'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFCCCC'],
                                ],
                            ]);

                            $lastRow = count($this->travelCertificates) + 1;
                            $sheet->getStyle("A1:J{$lastRow}")->applyFromArray([
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                    'wrapText'   => true,
                                ],
                            ]);
                        }
                    };
                }

                $sheets[] = new class($this->semanas) implements FromArray, WithTitle, WithHeadings, WithStyles {
                    public function __construct(private array $semanas) {}

                    public function title(): string { return "Resumen de totales."; }

                    public function headings(): array
                    {
                        return [
                            'Chofer',
                            'Constancias',
                            'Peajes',
                            'Diferencia',
                            'Carg/desc(B)',
                            'Carg/desc(N)',
                            'Noche(B)',
                            'Noche(N)'
                        ];
                    }

                    public function array(): array
                    {
                        $totalChofer     = 0;
                        $totalDiferencia = 0;
                        $totalConstancias = 0;
                        $totalPeajes     = 0;
                        $totalCargDesB   = 0;
                        $totalCargDesN   = 0;
                        $totalNocheB   = 0;
                        $totalNocheN   = 0;

                        foreach ($this->semanas as $viajes) {
                            foreach ($viajes as $tc) {
                                $subtotal         = $tc['subtotal_sin_peajes'];
                                $pct              = $tc['driver']['percent'] / 100;
                                $choferTotal      = round($pct * $subtotal, 2);

                                $totalConstancias += $subtotal;
                                $totalPeajes      += $tc['total_peajes'];
                                $totalChofer      += $choferTotal;
                                $totalDiferencia  += round(($subtotal * 0.25) - $choferTotal, 2);
                                $totalCargDesB    += $tc['totalcargadescargaB'];
                                $totalCargDesN    += $tc['totalcargadescargaN'];
                                $totalNocheB      += $tc['totalNocheB'];
                                $totalNocheN      += $tc['totalNocheN'];
                            }
                        }

                        return [[
                            $totalChofer,
                            $totalConstancias,
                            $totalPeajes,
                            $totalDiferencia,
                            $totalCargDesB,
                            $totalCargDesN,
                            $totalNocheB,    
                            $totalNocheN  
                        ]];
                    }

                    public function styles(Worksheet $sheet): void
                    {
                        $sheet->getStyle('A1:H1')->applyFromArray([
                            'fill' => [
                                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFCCCC'],
                            ],
                        ]);

                        $sheet->getStyle('A1:F2')->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText'   => true,
                            ],
                        ]);
                    }
                };

                return $sheets;
            }
        },
        $filename
    );
}
    private function buildSemanas($driverId,$periodo,$desde = null,$hasta = null): array
    {
        $semanas = [];

        $query = TravelCertificate::query()->where('driverId', $driverId); 
        

        if ($periodo === 'mes') 
        {
            $query->whereMonth('date',  date('n'));
        }
        else{
            $query->whereBetween('date', [ $desde, $hasta ]);
        }

        foreach ($query->orderBy('date')->get() as $tc) {
            $semana = min((int) ceil(Carbon::parse($tc->date)->day / 7), 5);
            $semanas[$semana][] = $tc;
        }

        return $semanas;
    }
    private function normalizeSemanas(array $semanas): array
{
    return collect($semanas)->map(fn($viajes) =>
        collect($viajes)->map(fn($tc) => is_array($tc) ? $tc : [
            'id'                  => $tc->id,
            'date'                => $tc->date->format('d/m/Y'),
            'number'              => $tc->number ?? $tc->id,
            'client'              => ['name' => $tc->client->name],
            'driver'              => ['percent' => $tc->driver->percent],
            'subtotal_sin_peajes' => $tc->subtotal_sin_peajes,
            'total_peajes'        => $tc->total_peajes,
            'totalcargadescargaB' => $tc->total_carga_descarga,
            'totalcargadescargaN' => 0,
            'totalNocheB'         => $tc->total_noche,
            'totalNocheN'         => 0
        ])->all()
    )->all();
}
}