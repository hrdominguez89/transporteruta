<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Settlement;
use App\Models\SettlementDetail;
use App\Models\TravelCertificate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Facades\Excel;
class SettlementController extends Controller
{
    public function index()
    {
        $settlements = Settlement::with('driver')->orderByDesc('periodo')->get();
        $drivers = Driver::orderBy('name')->get();

        return view('settlements.index', compact('settlements', 'drivers'));
    }

    public function create()
    {
        $drivers = Driver::orderBy('name')->get();

        return view('settlements.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'periodo'   => ['required', 'date_format:Y-m'],
        ]);

        $periodo = Carbon::createFromFormat('Y-m', $data['periodo'])->startOfMonth();

        $settlement = DB::transaction(function () use ($data, $periodo) {
            $settlement = Settlement::create([
                'driver_id' => $data['driver_id'],
                'periodo'   => $periodo,
            ]);

            $this->cargarSemana($settlement, 1);

            return $settlement;
        });

        return redirect()
            ->route('Settlements.show', $settlement)
            ->with('success', 'Liquidación creada. Semana 1 cargada.');
    }

    /**
     * Trae los TC del chofer en la semana indicada del período de la cabecera
     * y crea los SettlementDetail correspondientes.
     */
    private function cargarSemana(Settlement $settlement, int $semana): int
    {
        $inicioMes = $settlement->periodo->copy()->startOfMonth();
        $finMes    = $settlement->periodo->copy()->endOfMonth();
        $semanaBase = $inicioMes->copy()->startOfWeek(Carbon::MONDAY);

        $inicioSemana = $semanaBase->copy()->addWeeks($semana - 1);
        $finSemana    = $inicioSemana->copy()->endOfWeek(Carbon::SUNDAY);

        // Recortar al mes para no incluir días del mes anterior o siguiente
        if ($inicioSemana->lt($inicioMes)) $inicioSemana = $inicioMes->copy();
        if ($finSemana->gt($finMes))       $finSemana    = $finMes->copy();

        $tcs = TravelCertificate::where('driverId', $settlement->driver_id)
            ->whereBetween('date', [$inicioSemana, $finSemana])
            ->orderBy('date')
            ->get();


        $flag = $this->validarUltimoIntervalo($settlement);
        
        $creados = 0;

        foreach ($tcs as $tc) {
            $cargaDescarga = $tc->total_carga_descarga;
            $noche         = $tc->total_noche;

            SettlementDetail::create([
                'settlement_id'         => $settlement->id,
                'semana'                => $semana,
                'fecha'                 => $tc->date,
                'travel_certificate_id' => $tc->id,
                'cliente_id'            => $tc->client->id,
                'chofer_porcentaje'     => $tc->driver->percent,
                'importe_neto'          => $tc->importe_neto,
                'peajes'                => $tc->total_peajes,
                'estacionamiento'       => $tc->total_estacionamiento,
                'carga_descarga_b'      => $flag ? $cargaDescarga : 0,
                'carga_descarga_n'      => $flag ? 0 : $cargaDescarga,
                'chofer_cd_n'           => $flag ? 0 : ($cargaDescarga * 0.20),
                'noche_b'               => $flag ? $noche : 0,
                'noche_n'               => $flag ? 0 : $noche,
                'chofer_n_n'               => $flag ? 0 : ($noche * 0.20),
            ]);

            $creados++;

            if ($cargaDescarga > 0 || $noche > 0) {
                $flag = !$flag;
            }
        }

        return $creados;
    }

    private function validarUltimoIntervalo($settlement)
    {
        $ultimoDetalle = SettlementDetail::whereHas('settlement', function ($q) use ($settlement) {
        $q->where('driver_id', $settlement->driver_id);
            })
            ->where('settlement_id', '!=', $settlement->id)
            ->where(function ($q) {
                $q->where('carga_descarga_b', '>', 0)
                ->orWhere('carga_descarga_n', '>', 0)
                ->orWhere('noche_b', '>', 0)
                ->orWhere('noche_n', '>', 0);
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($ultimoDetalle) {
            $ultimoFueBlanco = $ultimoDetalle->carga_descarga_b > 0 || $ultimoDetalle->noche_b > 0;
            return  !$ultimoFueBlanco;
        } else {
            return  true;
        }
    }
    public function show(Settlement $settlement)
    {
        $settlement->load(['driver', 'details.travelCertificate', 'details.client']);

        $semanas = $settlement->details
            ->groupBy('semana')
            ->sortKeys()
            ->all();

        $ultimaSemanaCargada = $settlement->details->max('semana') ?? 0;

        return view('settlements.show', compact('settlement', 'semanas', 'ultimaSemanaCargada'));
    }
    public function delete(Request $request, Settlement $settlement)
    {
        $settlment = Settlement::find($settlement->id);
        $detalles  = SettlementDetail::where('settlement_id',$settlment->id);
        foreach($detalles as $detalle)
        {   
            $detalle->delete();
        }
        $settlement->delete();
        return $this->index();
    }

    public function siguienteSemana(Request $request, Settlement $settlement)
    {
        $data = $request->validate([
            'semana' => ['required', 'integer', 'between:1,5'],
        ]);

        $semana = (int) $data['semana'];

        $yaExiste = $settlement->details()->where('semana', $semana)->exists();

        if ($yaExiste) {
            return back()->with('warning', "La semana {$semana} ya fue cargada.");
        }

        $creados = DB::transaction(
            fn () => $this->cargarSemana($settlement, $semana)
        );

        $mensaje = $creados > 0
            ? "Semana {$semana} cargada ({$creados} viajes)."
            : "Semana {$semana} cargada sin viajes.";

        return back()->with('success', $mensaje);
    }

    public function guardarEdicion(Request $request, Settlement $settlement)
    {
        $data = $request->validate([
            'detalles'                        => ['required', 'array'],
            'detalles.*.id'                   => ['required', 'exists:settlement_details,id'],
            'detalles.*.chofer_porcentaje'    => ['nullable', 'numeric'],
            'detalles.*.base_recaudacion'     => ['nullable', 'numeric'],
            'detalles.*.chofer_total'         => ['nullable', 'numeric'],
            'detalles.*.carga_descarga_b'     => ['nullable', 'numeric'],
            'detalles.*.carga_descarga_n'     => ['nullable', 'numeric'],
            'detalles.*.noche_b'              => ['nullable', 'numeric'],
            'detalles.*.noche_n'              => ['nullable', 'numeric'],
            'detalles.*.chofer_cd_n'          => ['nullable', 'numeric'],
            'detalles.*.chofer_n_n'           => ['nullable', 'numeric'],
            'detalles.*.base_recaudacion_n'   => ['nullable', 'numeric'],
            'detalles.*.chofer_n'             => ['nullable', 'numeric'],
            'detalles.*.diferencia'           => ['nullable', 'numeric'],
            'detalles.*.comentarios'          => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $settlement) {
            $ids = collect($data['detalles'])->pluck('id');

            $detalles = SettlementDetail::where('settlement_id', $settlement->id)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($data['detalles'] as $payload) {
                $detalle = $detalles->get($payload['id']);
                if (!$detalle) {
                    continue;
                }

                $detalle->update(collect($payload)->except('id')->all());
            }
        });

        return response()->json(['ok' => true, 'message' => 'Cambios guardados.']);
    }
    public function generateExcel(Settlement $settlement)
    {
        $settlement->load(['driver', 'details.client']);

        $semanas = $settlement->details
            ->groupBy('semana')
            ->sortKeys()
            ->all();

        $filename = 'liquidacion_'
            . $settlement->driver->name . '_'
            . $settlement->periodo->format('Y-m')
            . '.xlsx';

        return Excel::download(
            new class($semanas) implements WithMultipleSheets {
                public function __construct(protected array $semanas) {}

                public function sheets(): array
                {
                    $sheets = [];

                    for ($s = 1; $s <= 5; $s++) {
                        $detalles = $this->semanas[$s] ?? collect();

                        $sheets[] = new class($s, $detalles) implements FromArray, WithTitle, WithHeadings, WithStyles {
                            public function __construct(
                                protected int $semana,
                                protected $detalles
                            ) {}

                            public function title(): string
                            {
                                return "Semana {$this->semana}";
                            }

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
                                    'Base recaudacion N',
                                    'Chofer N',
                                    'Diferencia',
                                    'Comentarios',
                                ];
                            }

                            public function array(): array
                            {
                                $rows = $this->detalles->map(fn ($d) => [
                                    optional($d->fecha)->format('d/m/Y'),
                                    $d->travel_certificate_id,
                                    $d->client?->name,
                                    number_format($d->chofer_porcentaje, 2, ',', '.') . '%',
                                    $d->importe_neto,
                                    $d->base_recaudacion,
                                    $d->peajes,
                                    $d->estacionamiento,
                                    $d->chofer_total,
                                    $d->carga_descarga_b,
                                    $d->carga_descarga_n,
                                    $d->noche_b,
                                    $d->noche_n,
                                    $d->chofer_cd_n,
                                    $d->chofer_n_n,
                                    $d->base_recaudacion_n,
                                    $d->chofer_n,
                                    $d->diferencia,
                                    $d->comentarios,
                                ])->all();

                                if ($this->detalles->isNotEmpty()) {
                                    $rows[] = [
                                        'TOTAL', '', '', '',
                                        $this->detalles->sum('importe_neto'),
                                        $this->detalles->sum('base_recaudacion'),
                                        $this->detalles->sum('peajes'),
                                        $this->detalles->sum('estacionamiento'),
                                        $this->detalles->sum('chofer_total'),
                                        $this->detalles->sum('carga_descarga_b'),
                                        $this->detalles->sum('carga_descarga_n'),
                                        $this->detalles->sum('noche_b'),
                                        $this->detalles->sum('noche_n'),
                                        $this->detalles->sum('chofer_cd_n'),
                                        $this->detalles->sum('chofer_n_n'),
                                        $this->detalles->sum('base_recaudacion_n'),
                                        $this->detalles->sum('chofer_n'),
                                        $this->detalles->sum('diferencia'),
                                        '',
                                    ];
                                }
                                $rows[] = [
                                    'Total a pagar',
                                    $this->detalles->sum('chofer_cd_n')
                                        + $this->detalles->sum('chofer_n_n')
                                        + $this->detalles->sum('chofer_n'),
                                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                                ];

                                return $rows;
                            }

                            public function styles(Worksheet $sheet): void
                            {
                                $sheet->getStyle('A1:S1')->applyFromArray([
                                    'fill' => [
                                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => 'FFCCCC'],
                                    ],
                                    'font' => ['bold' => true],
                                ]);

                                $lastRow = $this->detalles->count() + 1;

                                $sheet->getStyle("A1:S{$lastRow}")->applyFromArray([
                                    'alignment' => [
                                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                        'wrapText'   => true,
                                    ],
                                ]);

                               if ($this->detalles->isNotEmpty()) {
                                    $totalRow   = $lastRow + 1;
                                    $aPagarRow  = $lastRow + 2;
                                    $sheet->getStyle("A{$totalRow}:S{$aPagarRow}")->applyFromArray([
                                        'fill' => [
                                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => 'FFE699'],
                                        ],
                                        'font' => ['bold' => true],
                                        'alignment' => [
                                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                        ],
                                    ]);
                                }
                            }
                        };
                    }

                    $sheets[] = new class($this->semanas) implements FromArray, WithTitle, WithHeadings, WithStyles {
                        public function __construct(protected array $semanas) {}

                        public function title(): string
                        {
                            return 'Resumen de totales';
                        }

                        public function headings(): array
                        {
                            return [
                                'Chofer total',
                                'Diferencia total',
                                'Constancias total',
                                'Peajes total',
                                'Chofer Carg/desc(N) total',
                                'Chofer Noche(N) total',
                                'Noche N total',
                                'Noche B total',
                                'Carga N total',
                                'Carga B total',
                                'Base recaudacion N',
                                'Chofer recaudacion N',
                            ];
                        }

                        public function array(): array
                        {
                            $todos = collect($this->semanas)->flatten(1);

                            return [[
                                $todos->sum('chofer_total'),
                                $todos->sum('diferencia'),
                                $todos->sum('importe_neto'),
                                $todos->sum('peajes'),
                                $todos->sum('chofer_cd_n'),
                                $todos->sum('chofer_n_n'),
                                $todos->sum('noche_n'),
                                $todos->sum('noche_b'),
                                $todos->sum('carga_descarga_n'),
                                $todos->sum('carga_descarga_b'),
                                $todos->sum('base_recaudacion_n'),
                                $todos->sum('chofer_n'),
                            ]];
                        }

                        public function styles(Worksheet $sheet): void
                        {
                            $sheet->getStyle('A1:L1')->applyFromArray([
                                'fill' => [
                                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFCCCC'],
                                ],
                                'font' => ['bold' => true],
                            ]);
                        }
                    };

                    return $sheets;
                }
            },
            $filename
        );
    }
}