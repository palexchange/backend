<?php

namespace App\Exports;

// use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
// use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportTable implements WithHeadings, WithEvents, FromArray, WithMapping
{
    public $model;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($model)
    {
        $this->model = $model;
    }
    public function array(): array
    {
        // \DB::enableQueryLog(); // Enable query log
        $data = $this->model::exportData();
        $index = 0;
        foreach ($data as   $value) {
            $data[$index] = array_intersect_key($value, array_flip($this->model::$exportHeaders));
            $index++;
        }

        return  $data;
    }
    public function map($row): array
    {

        $arr = [];
        foreach ($row as $key => $value) {
            if (isset($this->model::$export_options[$key])) {
                logger("key");
                logger($key);
                logger("value");
                logger($value);
                $arr[] = $this->model::$export_options[$key][$value];
            } else {
                $arr[] = $value;
            }
        }
        return   $arr;
    }
    public function headings(): array
    {
        return $this->model::exportHeaders();
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
