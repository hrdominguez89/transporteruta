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
            $semanas = $request->input('semanas');
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
                                    'Base recaudacion',
                                    'Peajes',
                                    'Estacionamiento',
                                    'Chofer (total)',
                                    'Carg/Desc(B)',
                                    'Carg/Desc(N)',
                                    'Noche B',
                                    'Noche N',
                                    'Chofer c/d N',
                                    'Chofer n N',
                                    'Diferencia',
                                    'Comentarios'
                                ];
                            }

                            public function array(): array
                            {
                                $rows = [];
                                foreach ($this->travelCertificates as $tc) {
                                    $rows[] = [
                                        $tc['date'],
                                        $tc['number'],
                                        $tc['cliente'],
                                        number_format($tc['driverpercent'], 2, ',', '.') . '%',
                                        $tc['importe_neto'],
                                        $tc['baseRecaudacion'],
                                        $tc['total_peajes'],
                                        $tc['estacionamiento'],
                                        $tc['choferTotal'],
                                        $tc['totalcargadescargaB'],
                                        $tc['totalcargadescargaN'],
                                        $tc['totalNocheB'],
                                        $tc['totalNocheN'],
                                        $tc['choferCargDescN'],
                                        $tc['choferNocheN'],
                                        $tc['diferencia'],
                                        $tc['comentarios']
                                    ];
                                }
                                return $rows;
                            }

                            public function styles(Worksheet $sheet): void
                            {
                                $sheet->getStyle('A1:Q1')->applyFromArray([
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
                                'Chofer total',
                                'Diferencia total',
                                'Constancias total',
                                'Peajes total',
                                'Chofer Carg/desc(N) total',
                                'Chofer Noche(N) total ',
                                "Noche N total",
                                "Noche B total",
                                "Carga N total",
                                "Carga B total"

                            ];
                        }

                        public function array(): array
                        {
                            $totalChofer      = 0;
                            $totalDiferencia  = 0;
                            $totalConstancias = 0;
                            $totalPeajes      = 0;
                            $chofercdnt       = 0;
                            $chofernnt        = 0;
                            $nochebt          = 0;
                            $nochent          = 0;
                            $cargabt          = 0;
                            $cargant          = 0;

                            foreach ($this->semanas as $viajes) {
                                foreach ($viajes as $tc) {
                    
                                    $totalChofer      += $tc['choferTotal'];
                                    $totalDiferencia  += $tc['diferencia'];
                                    $totalConstancias += $tc['importe_neto'];
                                    $totalPeajes      += $tc['total_peajes'];
                                    $chofercdnt       += $tc['choferCargDescN'];
                                    $chofernnt        += $tc['choferNocheN'];
                                    $nochebt          += $tc['totalNocheB'];
                                    $nochent          += $tc['totalNocheN'];
                                    $cargabt          += $tc['totalcargadescargaB'];
                                    $cargant          += $tc['totalcargadescargaN'];
                    
                                }
                            }

                            return [[
                               $totalChofer    ,
                            $totalDiferencia,
                            $totalConstancias,
                            $totalPeajes    ,
                            $chofercdnt     ,
                            $chofernnt      ,
                            $nochebt        ,
                            $nochent        ,
                            $cargabt        ,
                            $cargant        ,
                            ]];
                        }

                        public function styles(Worksheet $sheet): void
                        {
                            $sheet->getStyle('A1:L1')->applyFromArray([
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
        $semanas = array_fill(1, 5, []);

        $query = TravelCertificate::query()->where('driverId', $driverId);

        if ($periodo === 'mes') {
            $query->whereMonth('date', date('n'));
            $inicioMes = Carbon::now()->startOfMonth();
        } else {
            $query->whereBetween('date', [$desde, $hasta]);
            $inicioMes = Carbon::parse($desde)->startOfMonth();
        }

        $semanaBase = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);

        foreach ($query->orderBy('date')->get() as $tc) {
            $inicioSemanaFecha = Carbon::parse($tc->date)->startOfWeek(Carbon::MONDAY);
            $semana = min((int) $semanaBase->diffInWeeks($inicioSemanaFecha) + 1, 5);
            $semanas[$semana][] = $tc;
        }

        return $semanas;
    }
   
    private function normalizeSemanas(array $semanas): array
{
    $flag = true;
    $resultado = [];

    foreach ($semanas as $claveSemana => $viajes) {
        $filas = [];

        foreach ($viajes as $tc) {
            if (is_array($tc)) {
                $filas[] = $tc;
                continue;
            }

            $filas[] = [
                'id'                  => $tc->id,
                'date'                => $tc->date->format('d/m/Y'),
                'number'              => $tc->number ?? $tc->id,
                'client'              => ['name' => $tc->client->name],
                'driver'              => [
                    'percent' => $tc->driver->percent,
                    'name'    => $tc->driver->name,
                    'type'    => $tc->driver->type,
                ],
                'subtotal_sin_peajes' => $tc->subtotal_sin_peajes,
                'total_peajes'        => $tc->total_peajes,
                'totalcargadescargaB' => $flag ? $tc->total_carga_descarga : 0,
                'totalcargadescargaN' => $flag ? 0 : $tc->total_carga_descarga,
                'totalNocheB'         => $flag ? $tc->total_noche : 0,
                'totalNocheN'         => $flag ? 0 : $tc->total_noche,
                'cargaDescargaNocheB' => $tc->total_noche + $tc->total_carga_descarga,
                'comentarios'         => $tc->comentarios ?? '',
                'estacionamiento'     => $tc->total_estacionamiento
            ];

            if ($tc->total_carga_descarga > 0 || $tc->total_noche > 0) {
                $flag = !$flag;
            }
        }

        $resultado[$claveSemana] = $filas;
    }

    return $resultado;
}
} 