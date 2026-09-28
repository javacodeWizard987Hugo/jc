<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $query = Warranty::with(['serialNumber.item', 'customer', 'saleItem.sale.branch']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('serialNumber', function ($sq) use ($search) {
                    $sq->where('serial_number', 'like', "%{$search}%");
                })->orWhereHas('serialNumber.item', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('item_code', 'like', "%{$search}%");
                })->orWhereHas('saleItem.sale', function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('customer', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('nic', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('branch_id')) {
            $branchId = $request->input('branch_id');
            $query->whereHas('saleItem.sale', function ($sq) use ($branchId) {
                $sq->where('branch_id', $branchId);
            });
        }

        $warranties = $query->latest()->paginate(20);
        $branches = \App\Models\Branch::all();

        return view('admin.warranties.index', compact('warranties', 'branches'));
    }

    public function show($id)
    {
        $warranty = Warranty::with(['serialNumber.item', 'customer', 'saleItem.sale.branch', 'warrantyJobs.branch'])->findOrFail($id);
        return view('admin.warranties.show', compact('warranty'));
    }
}
