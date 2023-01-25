<?php

namespace App\Exports;

use App\Models\Transfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Events\AfterSheet;

class TransferExport implements FromCollection, WithHeadings, WithMapping
{
    public $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Transfer::search($this->request)->get();
    }
    public function map($transfer): array
    {
        return [
            $transfer->id,
            $transfer->issued_at,
            $transfer->type == 0 ? 'صادرة' : 'واردة',
            $this->getTransferStatusString($transfer->status),
            $this->getTransferDeliveringString($transfer->delivering_type),
            $transfer->sender_party->name,
            $transfer->office->name,
            $transfer->receiver_party->name,
            $transfer->delivery_currency->name,
            $transfer->received_currency->name,
            $transfer->office_currency->name,
            $transfer->to_send_amount,
            $transfer->office_amount_in_office_currency,
            $transfer->profit,
        ];
    }

    // this is fine
    public function headings(): array
    {
        return [
            __("id"),
            __("issued_at"),
            __("type"),
            __("status"),
            __("delivering_type"),
            __("sender_name"),
            __("office_name"),
            __("receiver_name"),
            __("delivery_currency"),
            __("office_currency"),
            __("received_currency"),
            __("to_send_amount"),
            __("office_amount_in_office_currency"),
            __("profit")
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setRightToLeft(true);
            },
        ];
    }
    public function getTransferStatusString($status)
    {
        $all_statuses = [0 => 'مسودة', 1 => 'معتمدة', 255 => 'ملغاة'];
        return  $all_statuses[$status] ?? '';
    }
    public function getTransferDeliveringString($status)
    {
        $all_statuses = [1 => 'تسليم يد', 2 => 'موني غرام', 255 => 'على الحساب'];
        return  $all_statuses[$status] ?? '';
    }
}
