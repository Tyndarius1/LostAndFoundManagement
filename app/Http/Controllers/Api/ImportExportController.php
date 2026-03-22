<?php

namespace App\Http\Controllers\Api;

use App\Exports\CategoriesExport;
use App\Exports\ClaimRequestsExport;
use App\Exports\FoundItemsExport;
use App\Exports\LostItemsExport;
use App\Http\Controllers\Controller;
use App\Imports\CategoriesImport;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportExportController extends Controller
{
    use ApiResponse;

    public function exportCategories()
    {
        return Excel::download(new CategoriesExport, 'categories.xlsx');
    }

    public function exportLostItems()
    {
        return Excel::download(new LostItemsExport, 'lost-items.xlsx');
    }

    public function exportFoundItems()
    {
        return Excel::download(new FoundItemsExport, 'found-items.xlsx');
    }

    public function exportClaimRequests()
    {
        return Excel::download(new ClaimRequestsExport, 'claim-requests.xlsx');
    }

    public function importCategories(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,csv,xls',
                'max:5120',
            ],
        ]);

        Excel::import(new CategoriesImport, $request->file('file'));

        return $this->successResponse(
            'Categories imported successfully.'
        );
    }
}
