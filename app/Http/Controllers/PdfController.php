<?php

namespace App\Http\Controllers;

use App\Models\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdfResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StorePdfRequest;
use App\Http\Requests\UpdatePdfRequest;
use App\Models\Transfer;
use Illuminate\Support\Facades\Validator;
use ExportPDF;

class PdfController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Pdf");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Pdf::class, Str::snake("Pdf"));
    }
    public function index(Request $request)
    {

        $model_id = $request->id; // transfer
        $model_name = $request->model; // transfer
        $model_class = 'App\\Models\\' . ucfirst($model_name);
        $headers = $model_class::pdf_translated_headers();
        $headers_name = $model_class::$pdf_headers;
        $item = $model_class::find($model_id);
        $title = __($item->pdf_title());

        $pdf = ExportPDF::loadView('pdf', compact("headers", 'item', 'title', 'headers_name'));


        return $pdf->stream('document.pdf');
        // return PdfResource::collection(Pdf::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StorePdfRequest $request)
    {
        $pdf = Pdf::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $pdf->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new PdfResource($pdf);
    }
    public function show(Request $request, Pdf $pdf)
    {
        return new PdfResource($pdf);
    }
    public function update(UpdatePdfRequest $request, Pdf $pdf)
    {
        $pdf->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $pdf->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new PdfResource($pdf);
    }
    public function destroy(Request $request, Pdf $pdf)
    {
        $pdf->delete();
        return new PdfResource($pdf);
    }
}
