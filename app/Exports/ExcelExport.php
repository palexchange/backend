<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
// use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\FromArray;

class ExcelExport implements FromArray, WithHeadings, WithEvents
{
    public $data, $headers;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($data, $headers)
    {
        $this->headers = $headers;
        $this->data = $data;
    }
    public function array(): array
    {
        return $this->data;
    }
    // public function collection()
    // {
    //     return $this->data;
    // }
    public function headings(): array
    {
        return $this->headers;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setRightToLeft(true);
            },

        ];
    }
}
