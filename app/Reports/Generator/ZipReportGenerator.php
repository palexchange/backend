<?php

namespace App\Reports\Generator;

use App\Models\Party;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ZipReportGenerator
{
    public static $images = [];
    public static function transfer()
    {
        # code...
    }
    public static function moneyGram()
    {
        $from = request('from');
        $to = request('to');
        $type = request('transfer_type') ?? 1;
        $party_column = $type == 0 ? 'sender_party_id' : 'receiver_party_id';
        $party_id = request($party_column);
        $images = Transfer::where('delivering_type', 2)
            ->where('type', $type)
            ->where(DB::raw('issued_at::date'), '>=', $from)
            ->where(DB::raw('issued_at::date'), '<=', $to)
            ->where($party_column, $party_id)->get()
            ->filter(function ($transfer) {
                return $transfer->image != null;
            })->map(function ($transfer) {
                return $transfer->image == null ? null : $transfer->image->toArray();
            });

        $party_image = Party::find($party_id)?->image;
        if ($party_image != null) {
            $images[] = $party_image->toArray();
        }

        return  self::ZipFilesGeneratot($images);
    }

    public  static function ZipFilesGeneratot($images)
    {


        $zipFileName = 'images.zip';
        $zipFile = storage_path('app/' . $zipFileName);
        $zip = new ZipArchive;
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            foreach ($images as $image) {
                $path = storage_path() . '\\app\\public\\' . $image['path'];
                $zip->addFile($path,  $image['name']);
            }
            $zip->close();
        } else {
            return response()->json(['message' => 'Failed to create zip file.'], 500);
        }

        $headers = [
            'Content-Type' => 'application/zip',
        ];
        if (!file_exists($zipFile)) {
            return response()->json(['error_messages' => ['لا يوجد بيانات لتحميلها']], 422);
        }
        return response()->download($zipFile, $zipFileName, $headers);
    }
}
