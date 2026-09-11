<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $query = Warranty::with(['serialNumber.item', 'customer', 'saleItem.sale']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('serialNumber', function ($sq) use ($search) {
                    $sq->where('serial_number', 'like', "%{$search}%");
                })->orWhereHas('saleItem.sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('customer', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        $warranties = $query->latest()->paginate(20);

        return view('admin.warranties.index', compact('warranties'));
    }

    public function show($id)
    {
        $warranty = Warranty::with(['serialNumber.item', 'customer', 'saleItem.sale'])->findOrFail($id);
        return view('admin.warranties.show', compact('warranty'));
    }
}
