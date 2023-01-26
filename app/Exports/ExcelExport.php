<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
// use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExcelExport implements FromArray, WithHeadings, WithEvents, WithMapping
{
    public $data, $headers, $options;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($data, $headers, $options)
    {
        $this->headers = $headers;
        $this->data = $data;
        $this->options = $options;
    }
    public function map($row): array
    {

        $arr = [];
        foreach ($row as $key => $value) {

            if (isset($value) && isset($this->options[$key])) {
                logger($key);
                logger($value);
                $arr[] = $this->options[$key][$value];
            } else {
                $arr[] = __($value);
            }
        }
        return   $arr;
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
