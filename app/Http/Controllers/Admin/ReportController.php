<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\SupplierPayment;
use App\Models\InstallmentPayment;
use App\Models\StockMovement;
use App\Models\BranchItemSetting;
use App\Models\InstallmentAgreement;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'date');
        $categoryId = $request->get('category_id'); // Filter by category
        $itemId = $request->get('item_id'); // Filter by specific item
        $itemSearch = trim($request->get('item_search')); // Keyword search for item

        // Pre-process keywords to avoid repeated parsing and empty string matches
        $keywords = $itemSearch ? array_filter(array_map('trim', explode(',', $itemSearch))) : [];

        $salesQuery = Sale::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->where('status', 'completed')
            ->with(['cashier', 'items.item', 'installmentAgreement']);

        // Filter by payment method
        if ($request->has('payment_method')) {
            $salesQuery->where('payment_method', $request->get('payment_method'));
        }
        
        // Filter by category if selected
        if ($categoryId) {
            $salesQuery->whereHas('items.item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        // Filter by item if selected
        if ($itemId) {
            $salesQuery->whereHas('items', function($query) use ($itemId) {
                $query->where('item_id', $itemId);
            });
        }

        // Filter by item keyword search
        if (!empty($keywords)) {
            $salesQuery->whereHas('items.item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $sales = $salesQuery->get();

        $totalRevenue = 0;
        $totalCogs = 0;
        $totalMrp = 0;
        $totalLoanOutstanding = 0;

        foreach ($sales as $sale) {
            $saleMrp = 0;
            foreach ($sale->items as $saleItem) {
                if ($categoryId && (!$saleItem->item || $saleItem->item->category_id != $categoryId)) continue;
                if ($itemId && $saleItem->item_id != $itemId) continue;
                
                if (!empty($keywords) && $saleItem->item) {
                    $found = false;
                    foreach ($keywords as $keyword) {
                        if (stripos($saleItem->item->name, $keyword) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) continue;
                }

                $totalRevenue += $saleItem->total_price;
                
                // Requirement 1: Total sale from sale value (MRP)
                $itemMrp = ($saleItem->item->selling_price ?? 0) * $saleItem->quantity;
                $totalMrp += $itemMrp;
                $saleMrp += $itemMrp;

                if ($saleItem->item && $saleItem->item->cost_price !== null) {
                    $totalCogs += $saleItem->quantity * $saleItem->item->cost_price;
                }
            }

            // Requirement 2: Loan outstanding (current balance) - Sale Report
            if ($sale->payment_method === 'installment' && $sale->installmentAgreement) {
                $totalLoanOutstanding += $sale->installmentAgreement->balance_amount;
            }
        }

        $summary = [
            'total_sales' => $totalRevenue,
            'total_mrp' => $totalMrp,
            'total_loan_outstanding' => $totalLoanOutstanding,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalRevenue - $totalCogs,
            'total_bills' => $sales->count(),
            'total_items' => $sales->sum(function($sale) {
                return $sale->items->sum('quantity');
            }),
            'by_payment_method' => $sales->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                ];
            }),
            'by_cashier' => $sales->groupBy('cashier_id')->map(function($group) {
                $cashier = $group->first()->cashier;
                return [
                    'cashier' => $cashier ? $cashier->name : 'Unknown',
                    'count' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                ];
            })->filter(function($data) {
                return $data['cashier'] !== 'Unknown';
            }),
        ];

        // Top selling items (top 10)
        $topItemsQuery = SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                  ->where('status', 'completed');
        })
        ->whereHas('item'); // Only include items that still exist
        
        if ($categoryId) {
            $topItemsQuery->whereHas('item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        if ($itemId) {
            $topItemsQuery->where('item_id', $itemId);
        }

        if (!empty($keywords)) {
            $topItemsQuery->whereHas('item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $topItems = $topItemsQuery->select('item_id', DB::raw('sum(quantity) as total_quantity'), DB::raw('sum(total_price) as total_revenue'))
        ->groupBy('item_id')
        ->with('item')
        ->orderBy('total_quantity', 'desc')
        ->limit(10)
        ->get()
        ->filter(function($item) {
            return $item->item !== null; // Filter out items that were deleted
        });

        // Item-wise detailed report (all items)
        $itemWiseReportQuery = SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                  ->where('status', 'completed');
        })
        ->whereHas('item'); // Only include items that still exist
        
        if ($categoryId) {
            $itemWiseReportQuery->whereHas('item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        if ($itemId) {
            $itemWiseReportQuery->where('item_id', $itemId);
        }

        if (!empty($keywords)) {
            $itemWiseReportQuery->whereHas('item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $itemWiseReportRaw = $itemWiseReportQuery->select(
            'sale_items.item_id',
            DB::raw('sum(sale_items.quantity) as total_quantity'),
            DB::raw('sum(sale_items.total_price) as total_revenue'),
            DB::raw('avg(sale_items.unit_price) as avg_unit_price'),
            DB::raw('min(sale_items.unit_price) as min_unit_price'),
            DB::raw('max(sale_items.unit_price) as max_unit_price'),
            DB::raw('count(*) as times_sold'),
            DB::raw('sum(sale_items.discount_amount) as total_discount'),
            DB::raw('sum(sale_items.quantity * items.cost_price) as total_cogs')
        )
        ->join('items', 'sale_items.item_id', '=', 'items.id')
        ->groupBy('sale_items.item_id')
        ->with('item.category')
        ->orderBy('total_quantity', 'desc')
        ->get()
        ->filter(function($item) {
            return $item->item !== null; // Filter out items that were deleted
        });

        // Requirement 4: Item wise Report (Best sales Item, Average, low)
        $maxQty = $itemWiseReportRaw->max('total_quantity') ?: 0;
        $minQty = $itemWiseReportRaw->min('total_quantity') ?: 0;
        $avgQty = $itemWiseReportRaw->avg('total_quantity') ?: 0;

        $itemWiseReport = $itemWiseReportRaw->map(function($item) use ($maxQty, $minQty, $avgQty) {
            if ($item->total_quantity >= $maxQty && $item->total_quantity > 0) {
                $item->performance_label = 'Best';
                $item->label_class = 'bg-green-100 text-green-800';
            } elseif ($item->total_quantity <= $minQty) {
                $item->performance_label = 'Low';
                $item->label_class = 'bg-red-100 text-red-800';
            } else {
                $item->performance_label = 'Average';
                $item->label_class = 'bg-blue-100 text-blue-800';
            }
            return $item;
        });

        // Category-wise report (show when category is selected)
        $categoryWiseReportQuery = SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                  ->where('status', 'completed');
        })
        ->join('items', 'sale_items.item_id', '=', 'items.id')
        ->join('categories', 'items.category_id', '=', 'categories.id')
        ->whereNotNull('items.category_id'); // Only include items with categories
        
        if ($categoryId) {
            $categoryWiseReportQuery->where('categories.id', $categoryId);
        }
        
        if ($itemId) {
            $categoryWiseReportQuery->where('sale_items.item_id', $itemId);
        }

        if (!empty($keywords)) {
            $categoryWiseReportQuery->whereHas('item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $categoryWiseReport = $categoryWiseReportQuery->select(
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('count(distinct sale_items.item_id) as items_count'),
            DB::raw('sum(sale_items.quantity) as total_quantity'),
            DB::raw('sum(sale_items.total_price) as total_revenue'),
            DB::raw('sum(COALESCE(sale_items.discount_amount, 0)) as total_discount'),
            DB::raw('sum(sale_items.quantity * items.cost_price) as total_cogs')
        )
        ->groupBy('categories.id', 'categories.name')
        ->orderBy('total_revenue', 'desc')
        ->get();

        // Get all items for dropdown
        $items = Item::where('is_active', true)
            ->when($categoryId, function($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->get();

        // Get all categories for dropdown
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ensure all collections are properly initialized even if empty
        if (!isset($topItems) || $topItems === null) {
            $topItems = collect();
        }
        if (!isset($itemWiseReport) || $itemWiseReport === null) {
            $itemWiseReport = collect();
        }
        if (!isset($categoryWiseReport) || $categoryWiseReport === null) {
            $categoryWiseReport = collect();
        }

        // Requirement 3: Total Items sales report weekly /daily /monthly
        $salesByPeriod = $sales->groupBy(function($sale) use ($groupBy) {
            if ($groupBy === 'week') {
                return $sale->created_at->startOfWeek()->format('Y-m-d') . ' to ' . $sale->created_at->endOfWeek()->format('Y-m-d');
            } elseif ($groupBy === 'month') {
                return $sale->created_at->format('Y-F');
            }
            return $sale->created_at->format('Y-m-d');
        })->map(function($group) {
            return [
                'revenue' => $group->sum('total_amount'),
                'bills' => $group->count(),
                'items' => $group->sum(function($sale) {
                    return $sale->items->sum('quantity');
                }),
            ];
        });

        return view('admin.reports.sales', compact('sales', 'summary', 'topItems', 'itemWiseReport', 'categoryWiseReport', 'startDate', 'endDate', 'groupBy', 'categoryId', 'itemId', 'itemSearch', 'keywords', 'items', 'categories', 'salesByPeriod'));
    }
    
    public function stock(Request $request)
        {
                $asOfDate  = $request->get('as_of_date', now()->format('Y-m-d'));
                $categoryId = $request->get('category_id');
                $itemId     = $request->get('item_id');
                $branchId   = $request->get('branch_id');
                $viewType   = $request->get('view_type', 'item'); // item | category

                /* ---------------------------------------------------
                * Load items with filters
                * --------------------------------------------------- */
                $query = Item::with(['category', 'supplier']);

                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }

                if ($itemId) {
                    $query->where('id', $itemId);
                }

                $itemsList = $query->get();

                /* ---------------------------------------------------
                * Load reorder levels (branch + item)
                * key format: branch_id-item_id
                * --------------------------------------------------- */
                $reorderLevels = BranchItemSetting::when($branchId, function ($q) use ($branchId) {
                    return $q->where('branch_id', $branchId);
                })
                ->get()
                ->keyBy(fn ($r) => $r->branch_id . '-' . $r->item_id);


                /* ---------------------------------------------------
                * Calculate stock per item
                * --------------------------------------------------- */
                $items = $itemsList->map(function ($item) use ($asOfDate, $branchId, $reorderLevels) {

                    $movementsQuery = StockMovement::where('item_id', $item->id)
                        ->whereDate('created_at', '<=', $asOfDate);

                    if ($branchId) {
                        $movementsQuery->where('branch_id', $branchId);
                    }

                    $stockQty = $movementsQuery->sum('quantity');

                    $reorderKey = $branchId ? $branchId . '-' . $item->id : null;
                    $reorderLevel = $reorderKey && isset($reorderLevels[$reorderKey])
                        ? $reorderLevels[$reorderKey]->reorder_level
                        : null;

                    $isLowStock = $reorderLevel !== null && $stockQty < $reorderLevel;


                    return [
                        'item'           => $item,
                        'stock_on_date'  => $stockQty,
                        'current_stock'  => $stockQty,
                        'valuation'      => $stockQty * $item->cost_price,
                        'reorder_level'  => $reorderLevel,
                        'is_low_stock'   => $isLowStock,
                    ];
                });


                /* ---------------------------------------------------
                * Category-wise grouping
                * --------------------------------------------------- */
                $itemsByCategory = $items->groupBy(fn ($row) => $row['item']->category_id)
                    ->map(function ($categoryItems) {
                        $category = $categoryItems->first()['item']->category;

                        return [
                            'category'        => $category,
                            'items'           => $categoryItems,
                            'total_stock'     => $categoryItems->sum('current_stock'),
                            'total_valuation' => $categoryItems->sum('valuation'),
                            'item_count'      => $categoryItems->count(),
                        ];
                    })
                    ->sortBy(fn ($row) => $row['category']->name);

                /* ---------------------------------------------------
                * Totals & dropdown data
                * --------------------------------------------------- */
                $totalValuation = $items->sum('valuation');

                $categories = \App\Models\Category::where('is_active', true)->get();
                $branches   = Branch::all();

                $allItems = Item::where('is_active', true)
                    ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
                    ->orderBy('name')
                    ->get();

                $itemsForJs = $allItems->map(fn ($item) => [
                    'id'          => $item->id,
                    'name'        => $item->name,
                    'item_code'   => $item->item_code,
                    'category_id' => $item->category_id,
                ])->values();

                /* ---------------------------------------------------
                * Route prefix (admin / cashier)
                * --------------------------------------------------- */
                $routePrefix = request()->route()->getName();
                $routePrefix = str_starts_with($routePrefix, 'cashier.') ? 'cashier' : 'admin';

                return view('admin.reports.stock', compact(
                    'items',
                    'itemsByCategory',
                    'totalValuation',
                    'asOfDate',
                    'categories',
                    'categoryId',
                    'branches',
                    'branchId',
                    'allItems',
                    'itemsForJs',
                    'itemId',
                    'viewType',
                    'routePrefix'
                ));
        }
   
   
 public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'all'); // all, item
        $categoryId = $request->get('category_id'); // Filter by category
        $itemId = $request->get('item_id'); // Filter by specific item

        // Sales revenue
        $salesQuery = Sale::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->where('status', 'completed')
            ->with('items.item');
        
        // Filter by category if selected
        if ($categoryId) {
            $salesQuery->whereHas('items.item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        // Filter by item if selected
        if ($itemId) {
            $salesQuery->whereHas('items', function($query) use ($itemId) {
                $query->where('item_id', $itemId);
            });
        }
        
        $sales = $salesQuery->get();

        $revenue = 0;
        $cogs = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $saleItem) {
                if ($categoryId && (!$saleItem->item || $saleItem->item->category_id != $categoryId)) continue;
                if ($itemId && $saleItem->item_id != $itemId) continue;

                $revenue += $saleItem->total_price;
                if ($saleItem->item && $saleItem->item->cost_price !== null) {
                    $cogs += $saleItem->quantity * $saleItem->item->cost_price;
                }
            }
        }

        // Expenses
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        // Stock Losses (Expire, Loss)
        $stockLossesQuery = StockMovement::whereIn('type', ['expire', 'loss'])
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->with('item');

        if ($categoryId) {
            $stockLossesQuery->whereHas('item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }
        if ($itemId) {
            $stockLossesQuery->where('item_id', $itemId);
        }

        $stockLosses = $stockLossesQuery->get()->sum(function($movement) {
            return abs($movement->quantity) * ($movement->item->cost_price ?? 0);
        });

        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses - $stockLosses;

        $summary = [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'stock_losses' => $stockLosses,
            'net_profit' => $netProfit,
            'gross_margin' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
            'net_margin' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
        ];

        // Item-wise Profit & Loss Report
        $itemWiseReportQuery = \App\Models\SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                  ->where('status', 'completed');
        })
        ->join('items', 'sale_items.item_id', '=', 'items.id');
        
        // Filter by category if selected
        if ($categoryId) {
            $itemWiseReportQuery->where('items.category_id', $categoryId);
        }
        
        // Filter by item if selected
        if ($itemId) {
            $itemWiseReportQuery->where('sale_items.item_id', $itemId);
        }
        
        $itemWiseReport = $itemWiseReportQuery
        ->select(
            'sale_items.item_id',
            DB::raw('sum(sale_items.quantity) as total_quantity'),
            DB::raw('sum(sale_items.total_price) as total_revenue'),
            DB::raw('sum(sale_items.quantity * items.cost_price) as total_cogs'),
            DB::raw('sum(sale_items.total_price) - sum(sale_items.quantity * items.cost_price) as total_profit'),
            DB::raw('avg(sale_items.unit_price) as avg_selling_price'),
            DB::raw('count(*) as times_sold')
        )
        ->groupBy('sale_items.item_id')
        ->with('item.category')
        ->orderBy('total_profit', 'desc')
        ->get()
        ->map(function($item) {
            // Calculate profit margin
            $item->profit_margin = $item->total_revenue > 0 
                ? (($item->total_profit / $item->total_revenue) * 100) 
                : 0;
            return $item;
        });

        // Get all categories for dropdown
        $categories = \App\Models\Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all items for dropdown (filtered by category if selected)
        $items = Item::where('is_active', true)
            ->when($categoryId, function($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->get();

        // Get ALL items for JavaScript filtering (not filtered by category)
        $allItemsForJs = Item::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Prepare items data for JavaScript (simplified format)
        $itemsForJs = $allItemsForJs->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'category_id' => $item->category_id
            ];
        })->values();

        // Daily Profit & Loss
        $dailyProfitLoss = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Optimized Daily P&L calculation
        $salesByDate = $sales->groupBy(fn($s) => $s->created_at->format('Y-m-d'));
        
        $expensesByDate = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($e) => $e->expense_date->format('Y-m-d'));
            
        $stockLossesInRange = StockMovement::whereIn('type', ['expire', 'loss'])
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->with('item')
            ->get();
        $stockLossesByDate = $stockLossesInRange->groupBy(fn($m) => $m->created_at->format('Y-m-d'));

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            
            $daySales = $salesByDate->get($dateStr, collect());
            $dayRevenue = 0;
            $dayCogs = 0;
            foreach ($daySales as $s) {
                foreach ($s->items as $si) {
                    if ($categoryId && (!$si->item || $si->item->category_id != $categoryId)) continue;
                    if ($itemId && $si->item_id != $itemId) continue;
                    
                    $dayRevenue += $si->total_price;
                    if ($si->item && $si->item->cost_price !== null) {
                        $dayCogs += $si->quantity * $si->item->cost_price;
                    }
                }
            }
            
            $dayExpenses = $expensesByDate->get($dateStr, collect())->sum('amount');
            
            $dayStockLosses = $stockLossesByDate->get($dateStr, collect())->sum(function($m) use ($categoryId, $itemId) {
                if ($categoryId && (!$m->item || $m->item->category_id != $categoryId)) return 0;
                if ($itemId && $m->item_id != $itemId) return 0;
                return abs($m->quantity) * ($m->item->cost_price ?? 0);
            });

            $dailyProfitLoss[$dateStr] = [
                'date' => $dateStr,
                'revenue' => $dayRevenue,
                'cogs' => $dayCogs,
                'gross_profit' => $dayRevenue - $dayCogs,
                'expenses' => $dayExpenses,
                'stock_losses' => $dayStockLosses,
                'net_profit' => ($dayRevenue - $dayCogs) - $dayExpenses - $dayStockLosses,
            ];
            
            $current->addDay();
        }
        $dailyProfitLoss = array_reverse($dailyProfitLoss);

        return view('admin.reports.profit-loss', compact('summary', 'startDate', 'endDate', 'reportType', 'itemWiseReport', 'categoryId', 'itemId', 'categories', 'items', 'itemsForJs', 'dailyProfitLoss', 'sales'));
    }


    public function supplierLedger(Request $request)
    {
        // If no supplier selected, show list view
        $supplierId = $request->get('supplier_id');
        if (!$supplierId) {
            return $this->supplierLedgerList($request);
        }
        
        // Otherwise show detail view for selected supplier
        return $this->supplierLedgerDetail($request, $supplierId);
    }

    public function supplierLedgerList(Request $request)
    {
       $search = trim($request->get('search'));
        $showOnlyOutstanding = $request->get('show_outstanding', false);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = \App\Models\Supplier::where('is_active', true);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }
        
        if ($showOnlyOutstanding) {
            $query->where('outstanding_balance', '>', 0);
        }
        
        $suppliers = $query->get()->map(function($supplier) use ($startDate, $endDate) {
            $supplier->fresh();
            
            // Calculate totals for date range if provided
            $grnQuery = \App\Models\Grn::where('supplier_id', $supplier->id);
            $paymentQuery = \App\Models\SupplierPayment::where('supplier_id', $supplier->id);
            
            if ($startDate) {
                $grnQuery->whereDate('grn_date', '>=', $startDate);
                $paymentQuery->whereDate('payment_date', '>=', $startDate);
            }
            
            if ($endDate) {
                $grnQuery->whereDate('grn_date', '<=', $endDate);
                $paymentQuery->whereDate('payment_date', '<=', $endDate);
            }
            
            $totalPurchases = $grnQuery->sum('total_amount');
            $totalPaid = $paymentQuery->sum('paid_amount');
            
            // Get last transaction date
            $lastGrn = $grnQuery->latest('grn_date')->first();
            $lastPayment = $paymentQuery->latest('payment_date')->first();
            
            $lastTransaction = null;
            if ($lastGrn && $lastPayment) {
                $lastTransaction = $lastGrn->grn_date > $lastPayment->payment_date ? $lastGrn->grn_date : $lastPayment->payment_date;
            } elseif ($lastGrn) {
                $lastTransaction = $lastGrn->grn_date;
            } elseif ($lastPayment) {
                $lastTransaction = $lastPayment->payment_date;
            }
            
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'total_purchases' => $totalPurchases,
                'total_paid' => $totalPaid,
                'outstanding_balance' => $supplier->outstanding_balance,
                'last_transaction' => $lastTransaction,
            ];
        })->sortByDesc('outstanding_balance');
        
        // Get latest account details from previous payments for each supplier (optional - for future use)
        // For now, we'll just pass empty array as account details are handled per supplier in detail view
        
        return view('admin.reports.supplier-ledger-list', compact('suppliers', 'search', 'showOnlyOutstanding', 'startDate', 'endDate'));
    }

    public function supplierLedgerDetail(Request $request, $supplierId)
    {
        $supplier = \App\Models\Supplier::findOrFail($supplierId);
        $supplier->fresh();
        
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $transactionType = $request->get('transaction_type', 'all'); // all, purchases, payments
        
        // Get all GRNs (purchases/debits)
        $grnQuery = \App\Models\Grn::where('supplier_id', $supplierId);
        if ($startDate) {
            $grnQuery->whereDate('grn_date', '>=', $startDate);
        }
        if ($endDate) {
            $grnQuery->whereDate('grn_date', '<=', $endDate);
        }
        $grns = $grnQuery->orderBy('grn_date', 'asc')->orderBy('created_at', 'asc')->get();
        
        // Get all payments (credits)
        $paymentQuery = \App\Models\SupplierPayment::where('supplier_id', $supplierId);
        if ($startDate) {
            $paymentQuery->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $paymentQuery->whereDate('payment_date', '<=', $endDate);
        }
        $payments = $paymentQuery->orderBy('payment_date', 'asc')->orderBy('created_at', 'asc')->get();
        
        // Combine and sort by date
        $ledgerEntries = collect();
        
        // Add GRNs as debits
        foreach ($grns as $grn) {
            if ($transactionType === 'all' || $transactionType === 'purchases') {
                $ledgerEntries->push([
                    'date' => $grn->grn_date,
                    'reference' => $grn->grn_number,
                    'description' => 'Purchase - ' . ($grn->reference_document ?? 'GRN'),
                    'debit' => $grn->total_amount,
                    'credit' => 0,
                    'type' => 'purchase',
                    'id' => $grn->id,
                    'grn' => $grn,
                ]);
            }
        }
        
        // Add payments as credits
        foreach ($payments as $payment) {
            if ($transactionType === 'all' || $transactionType === 'payments') {
                $ledgerEntries->push([
                    'date' => $payment->payment_date ?? $payment->created_at->toDateString(),
                    'reference' => $payment->invoice_number ?? 'PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT),
                    'description' => 'Payment - ' . ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    'debit' => 0,
                    'credit' => $payment->paid_amount,
                    'type' => 'payment',
                    'id' => $payment->id,
                    'payment' => $payment,
                ]);
            }
        }
        
        // Sort by date and calculate running balance
        $ledgerEntries = $ledgerEntries->sortBy(function($entry) {
            return $entry['date'] . ' ' . ($entry['type'] === 'purchase' ? '0' : '1');
        })->values();
        
        $balance = 0;
        $ledgerEntries = $ledgerEntries->map(function($entry) use (&$balance) {
            $balance += $entry['debit'] - $entry['credit'];
            $entry['balance'] = $balance;
            return $entry;
        });
        
        // Calculate totals
        $totalPurchases = $grns->sum('total_amount');
        $totalPaid = $payments->sum('paid_amount');
        $outstandingBalance = $supplier->outstanding_balance;
        
        // Get latest account details from previous payments
        $latestPayment = \App\Models\SupplierPayment::where('supplier_id', $supplierId)
            ->whereNotNull('account_holder_name')
            ->whereNotNull('account_number')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $accountDetails = null;
        if ($latestPayment) {
            $accountDetails = [
                'account_holder_name' => $latestPayment->account_holder_name,
                'account_number' => $latestPayment->account_number,
                'account_bank' => $latestPayment->account_bank,
                'account_branch' => $latestPayment->account_branch,
            ];
        }
        
        return view('admin.reports.supplier-ledger-detail', compact(
            'supplier', 
            'ledgerEntries', 
            'totalPurchases', 
            'totalPaid', 
            'outstandingBalance',
            'startDate',
            'endDate',
            'transactionType',
            'accountDetails'
        ));
    }

    public function supplierLedgerApi(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = SupplierPayment::with(['supplier', 'grn', 'paidBy']);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        
        if ($startDate) {
            $query->whereDate('payment_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        // Get all payments (not paginated) to calculate running balance
        $allPayments = $query->orderBy('payment_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Calculate running balance
        $runningBalance = 0;
        $paymentsWithBalance = $allPayments->map(function($payment) use (&$runningBalance) {
            $runningBalance += $payment->outstanding_amount;
            $payment->running_balance = $runningBalance;
            return $payment;
        })->values();
        
        // Reverse for display (most recent first)
        $paymentsWithBalance = $paymentsWithBalance->reverse()->values();
        
        // Limit to latest 20 for API
        $payments = $paymentsWithBalance->take(20)->values();
        
        // Calculate summary totals
        $summary = [
            'total_invoice_amount' => $allPayments->sum('invoice_amount'),
            'total_paid_amount' => $allPayments->sum('paid_amount'),
            'total_outstanding' => $allPayments->sum('outstanding_amount'),
            'total_payments' => $allPayments->count(),
        ];
        
        // Get current outstanding balance
        if ($supplierId) {
            $supplier = \App\Models\Supplier::find($supplierId);
            $summary['current_outstanding_balance'] = $supplier ? $supplier->fresh()->outstanding_balance : 0;
        } else {
            $summary['current_outstanding_balance'] = \App\Models\Supplier::sum('outstanding_balance');
        }
        
        return response()->json([
            'payments' => $payments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : 'N/A',
                    'supplier_name' => $payment->supplier->name,
                    'invoice_number' => $payment->invoice_number ?? 'N/A',
                    'due_date' => $payment->due_date ? $payment->due_date->format('Y-m-d') : null,
                    'invoice_amount' => number_format($payment->invoice_amount, 2),
                    'paid_amount' => number_format($payment->paid_amount, 2),
                    'outstanding_amount' => number_format($payment->outstanding_amount, 2),
                    'running_balance' => number_format($payment->running_balance ?? 0, 2),
                    'payment_method' => $payment->payment_method,
                    'credit_repay_date' => $payment->credit_repay_date ? $payment->credit_repay_date->format('Y-m-d') : null,
                    'cheque_number' => $payment->cheque_number,
                    'bank_name' => $payment->bank_name,
                    'cheque_date' => $payment->cheque_date ? $payment->cheque_date->format('Y-m-d') : null,
                    'cheque_repay_date' => $payment->cheque_repay_date ? $payment->cheque_repay_date->format('Y-m-d') : null,
                    'account_holder_name' => $payment->account_holder_name,
                    'account_number' => $payment->account_number,
                    'account_bank' => $payment->account_bank,
                    'account_branch' => $payment->account_branch,
                    'status' => $payment->outstanding_amount > 0 && $payment->due_date && $payment->due_date < now() 
                        ? 'overdue' 
                        : ($payment->outstanding_amount > 0 ? 'pending' : 'paid'),
                    'updated_at_timestamp' => $payment->updated_at->timestamp,
                ];
            }),
            'summary' => $summary,
        ]);
    }

    public function expenses(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');

        $query = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'creator']);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses = $query->latest()->get();
        
        $summary = [
            'total' => $expenses->sum('amount'),
            'by_category' => $expenses->groupBy('expense_category_id')->map(function($group) {
                return [
                    'category' => $group->first()->category->name,
                    'amount' => $group->sum('amount'),
                ];
            }),
        ];

        $categories = \App\Models\ExpenseCategory::where('is_active', true)->get();

        // Determine route prefix based on current route
        $routePrefix = request()->route()->getName();
        $routePrefix = strpos($routePrefix, 'cashier.') === 0 ? 'cashier' : 'admin';

        return view('admin.reports.expenses', compact('expenses', 'summary', 'startDate', 'endDate', 'categories', 'categoryId', 'routePrefix'));
    }

    public function export(Request $request, $type)
    {
        $format = $request->get('format', 'pdf');

        switch ($type) {
            case 'sales':
                return $this->exportSales($request, $format);
            case 'stock':
                return $this->exportStock($request, $format);
            case 'profit-loss':
                return $this->exportProfitLoss($request, $format);
            case 'expenses':
                return $this->exportExpenses($request, $format);
            case 'supplier-ledger':
                return $this->exportSupplierLedger($request, $format);
            case 'daily-installments':
                return $this->exportDailyInstallments($request, $format);
            default:
                return back()->withErrors(['error' => 'Invalid export type']);
        }
    }

  private function exportDailyInstallments(Request $request, $format)
    {
        $startDate = $request->get('start_date', now()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $customerId = $request->get('customer_id');
        $reportType = $request->get('report_type', 'all');
        $paymentMethod = $request->get('payment_method');

        $reportData = $this->getDailyInstallmentData($startDate, $endDate, $customerId, $reportType, $paymentMethod);
        $customer = $customerId ? Customer::find($customerId) : null;

        if ($format === 'pdf') {
            return view('admin.reports.daily-installments-pdf', compact('reportData', 'startDate', 'endDate', 'reportType', 'customer', 'paymentMethod'));
        }

        if ($format === 'csv' || $format === 'excel') {
            $filename = "daily_installments_report_{$startDate}_to_{$endDate}.csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($reportData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date', 'Type', 'Customer', 'Invoice', 'Due Date', 'Method', 'Interest', 'Amount', 'Notes']);
                
                foreach ($reportData as $row) {
                    fputcsv($file, [
                        $row['date'],
                        $row['type'],
                        $row['customer'],
                        $row['invoice'],
                        $row['due_date'],
                        $row['method'],
                        number_format($row['interest'], 2, '.', ''),
                        number_format($row['amount'], 2, '.', ''),
                        $row['notes'],
                    ]);
                }
                
                // Total row
                fputcsv($file, []);
                fputcsv($file, [
                    'TOTAL',
                    '',
                    '',
                    '',
                    '',
                    '',
                    number_format($reportData->sum('interest'), 2, '.', ''),
                    number_format($reportData->sum('amount'), 2, '.', ''),
                    ''
                ]);
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Unsupported format');
    }

    private function exportSales(Request $request, $format)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $categoryId = $request->get('category_id'); // Filter by category
        $itemId = $request->get('item_id'); // Filter by specific item
        $itemSearch = trim($request->get('item_search')); // Keyword search for item
        $reportType = $request->get('report_type', 'all');

        // Pre-process keywords to avoid repeated parsing and empty string matches
        $keywords = $itemSearch ? array_filter(array_map('trim', explode(',', $itemSearch))) : [];

        $salesQuery = Sale::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->where('status', 'completed')
            ->with(['cashier', 'items.item', 'installmentAgreement']);
        
        // Filter by category if selected
        if ($categoryId) {
            $salesQuery->whereHas('items.item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        // Filter by item if selected
        if ($itemId) {
            $salesQuery->whereHas('items', function($query) use ($itemId) {
                $query->where('item_id', $itemId);
            });
        }

        if (!empty($keywords)) {
            $salesQuery->whereHas('items.item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $sales = $salesQuery->get();

        // Calculate Summary for all formats
        $totalRevenue = 0;
        $totalCogs = 0;
        $totalMrp = 0;
        $totalLoanOutstanding = 0;

        foreach ($sales as $sale) {
            $saleMrp = 0;
            foreach ($sale->items as $saleItem) {
                if ($categoryId && (!$saleItem->item || $saleItem->item->category_id != $categoryId)) continue;
                if ($itemId && $saleItem->item_id != $itemId) continue;
                
                if (!empty($keywords) && $saleItem->item) {
                    $found = false;
                    foreach ($keywords as $keyword) {
                        if (stripos($saleItem->item->name, $keyword) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) continue;
                }

                $totalRevenue += $saleItem->total_price;
                $itemMrp = ($saleItem->item->selling_price ?? 0) * $saleItem->quantity;
                $totalMrp += $itemMrp;
                $saleMrp += $itemMrp;

                if ($saleItem->item && $saleItem->item->cost_price !== null) {
                    $totalCogs += $saleItem->quantity * $saleItem->item->cost_price;
                }
            }

            if ($sale->payment_method === 'installment' && $sale->installmentAgreement) {
                $totalLoanOutstanding += $sale->installmentAgreement->balance_amount;
            }
        }

        $summary = [
            'total_sales' => $totalRevenue,
            'total_mrp' => $totalMrp,
            'total_loan_outstanding' => $totalLoanOutstanding,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalRevenue - $totalCogs,
            'total_bills' => $sales->count(),
            'total_items' => $sales->sum(function($sale) use ($categoryId, $itemId, $keywords) {
                return $sale->items->filter(function($si) use ($categoryId, $itemId, $keywords) {
                    if ($categoryId && (!$si->item || $si->item->category_id != $categoryId)) return false;
                    if ($itemId && $si->item_id != $itemId) return false;
                    
                    if (!empty($keywords) && $si->item) {
                        $found = false;
                        foreach ($keywords as $keyword) {
                            if (stripos($si->item->name, $keyword) !== false) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) return false;
                    }

                    return true;
                })->sum('quantity');
            }),
        ];

        // Prepare Item Wise Report
        $itemWiseReportQuery = SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                  ->where('status', 'completed');
        })->whereHas('item');
        
        if ($categoryId) $itemWiseReportQuery->whereHas('item', fn($q) => $q->where('category_id', $categoryId));
        if ($itemId) $itemWiseReportQuery->where('item_id', $itemId);
        if (!empty($keywords)) {
            $itemWiseReportQuery->whereHas('item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $itemWiseReportRaw = $itemWiseReportQuery->select(
            'sale_items.item_id',
            DB::raw('sum(sale_items.quantity) as total_quantity'),
            DB::raw('sum(sale_items.total_price) as total_revenue'),
            DB::raw('avg(sale_items.unit_price) as avg_unit_price'),
            DB::raw('min(sale_items.unit_price) as min_unit_price'),
            DB::raw('max(sale_items.unit_price) as max_unit_price'),
            DB::raw('count(*) as times_sold'),
            DB::raw('sum(sale_items.discount_amount) as total_discount'),
            DB::raw('sum(sale_items.quantity * items.cost_price) as total_cogs')
        )
        ->join('items', 'sale_items.item_id', '=', 'items.id')
        ->groupBy('sale_items.item_id')
        ->with('item.category')
        ->orderBy('total_quantity', 'desc')
        ->get();

        $maxQty = $itemWiseReportRaw->max('total_quantity') ?: 0;
        $minQty = $itemWiseReportRaw->min('total_quantity') ?: 0;
        $itemWiseReport = $itemWiseReportRaw->map(function($item) use ($maxQty, $minQty) {
            if ($item->total_quantity >= $maxQty && $item->total_quantity > 0) { $item->performance_label = 'Best'; }
            elseif ($item->total_quantity <= $minQty) { $item->performance_label = 'Low'; }
            else { $item->performance_label = 'Average'; }
            return $item;
        });

        // Prepare Category Wise Report
        $categoryWiseReportQuery = SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                  ->where('status', 'completed');
        })
        ->join('items', 'sale_items.item_id', '=', 'items.id')
        ->join('categories', 'items.category_id', '=', 'categories.id');
        
        if ($categoryId) $categoryWiseReportQuery->where('categories.id', $categoryId);
        if ($itemId) $categoryWiseReportQuery->where('sale_items.item_id', $itemId);
        if (!empty($keywords)) {
            $categoryWiseReportQuery->whereHas('item', function($query) use ($keywords) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', "%{$keyword}%");
                    }
                });
            });
        }
        
        $categoryWiseReport = $categoryWiseReportQuery->select(
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('count(distinct sale_items.item_id) as items_count'),
            DB::raw('sum(sale_items.quantity) as total_quantity'),
            DB::raw('sum(sale_items.total_price) as total_revenue'),
            DB::raw('sum(COALESCE(sale_items.discount_amount, 0)) as total_discount'),
            DB::raw('sum(sale_items.quantity * items.cost_price) as total_cogs')
        )->groupBy('categories.id', 'categories.name')->orderBy('total_revenue', 'desc')->get();

        if ($format === 'pdf') {
            $categoryName = $categoryId ? \App\Models\Category::find($categoryId)?->name : null;
            return view('admin.reports.sales-print', compact('sales', 'summary', 'itemWiseReport', 'categoryWiseReport', 'startDate', 'endDate', 'reportType', 'categoryId', 'categoryName', 'itemSearch'));
        }

        if ($format === 'csv' || $format === 'excel') {
            $headers = ['Content-Type' => 'text/csv'];

            if ($reportType === 'category') {
                $filename = "sales_report_category_wise_{$startDate}_to_{$endDate}.csv";
                $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";
                $callback = function() use ($categoryWiseReport) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['Category Name', 'Items Sold', 'Total Quantity', 'Revenue', 'COGS', 'Profit']);
                    foreach ($categoryWiseReport as $category) {
                        fputcsv($file, [
                            $category->category_name, 
                            $category->items_count, 
                            number_format($category->total_quantity, 2, '.', ''), 
                            number_format($category->total_revenue, 2, '.', ''), 
                            number_format($category->total_cogs ?? 0, 2, '.', ''), 
                            number_format($category->total_revenue - ($category->total_cogs ?? 0), 2, '.', '')
                        ]);
                    }
                    fputcsv($file, [
                        'TOTAL', 
                        $categoryWiseReport->sum('items_count'), 
                        number_format($categoryWiseReport->sum('total_quantity'), 2, '.', ''), 
                        number_format($categoryWiseReport->sum('total_revenue'), 2, '.', ''), 
                        number_format($categoryWiseReport->sum('total_cogs'), 2, '.', ''), 
                        number_format($categoryWiseReport->sum('total_revenue') - $categoryWiseReport->sum('total_cogs'), 2, '.', '')
                    ]);
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            } elseif ($reportType === 'item') {
                $filename = "sales_report_item_wise_{$startDate}_to_{$endDate}.csv";
                $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";
                $callback = function() use ($itemWiseReport) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['Item Name', 'Item Code', 'Category', 'Performance', 'Quantity Sold', 'Unit', 'Times Sold', 'Avg Unit Price', 'Total Revenue', 'Total COGS', 'Profit']);
                    foreach ($itemWiseReport as $item) {
                        fputcsv($file, [
                            $item->item->name, 
                            $item->item->item_code ?? 'N/A', 
                            $item->item->category->name ?? 'N/A', 
                            $item->performance_label, 
                            number_format($item->total_quantity, 2, '.', ''), 
                            $item->item->unit_of_measure, 
                            $item->times_sold, 
                            number_format($item->avg_unit_price ?? 0, 2, '.', ''), 
                            number_format($item->total_revenue, 2, '.', ''),
                            number_format($item->total_cogs ?? 0, 2, '.', ''),
                            number_format($item->total_revenue - ($item->total_cogs ?? 0), 2, '.', '')
                        ]);
                    }
                    fputcsv($file, [
                        'TOTAL', 
                        '-', 
                        '-', 
                        '-', 
                        number_format($itemWiseReport->sum('total_quantity'), 2, '.', ''), 
                        '-', 
                        $itemWiseReport->sum('times_sold'), 
                        '-', 
                        number_format($itemWiseReport->sum('total_revenue'), 2, '.', ''),
                        number_format($itemWiseReport->sum('total_cogs'), 2, '.', ''),
                        number_format($itemWiseReport->sum('total_revenue') - $itemWiseReport->sum('total_cogs'), 2, '.', '')
                    ]);
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            } else {
                // Detailed export
                $filename = "sales_report_detailed_{$startDate}_to_{$endDate}.csv";
                $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";
                $callback = function() use ($sales, $categoryId, $itemId, $keywords, $summary) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['Date', 'Invoice', 'Customer', 'Items', 'Revenue', 'COGS', 'Profit', 'Payment Method']);
                    foreach ($sales as $sale) {
                        $saleRevenue = 0; $saleCogs = 0; $itemsStr = "";
                        $filteredItems = $sale->items->filter(function($item) use ($categoryId, $itemId, $keywords) {
                            if ($categoryId && (!$item->item || $item->item->category_id != $categoryId)) return false;
                            if ($itemId && $item->item_id != $itemId) return false;
                            
                            if (!empty($keywords) && $item->item) {
                                $found = false;
                                foreach ($keywords as $keyword) {
                                    if (stripos($item->item->name, $keyword) !== false) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) return false;
                            }

                            return true;
                        });
                        foreach ($filteredItems as $item) {
                            $saleRevenue += $item->total_price;
                            if ($item->item && $item->item->cost_price !== null) $saleCogs += $item->quantity * $item->item->cost_price;
                            $itemsStr .= ($item->item->name ?? 'N/A') . " (" . (float)$item->quantity . " x " . $item->unit_price . "), ";
                        }
                        fputcsv($file, [$sale->created_at->format('Y-m-d H:i'), $sale->invoice_number, $sale->customer->name ?? 'Walk-in Customer', rtrim($itemsStr, ', '), number_format($saleRevenue, 2, '.', ''), number_format($saleCogs, 2, '.', ''), number_format($saleRevenue - $saleCogs, 2, '.', ''), ucfirst($sale->payment_method)]);
                    }
                    fputcsv($file, ['TOTAL', '', '', '', number_format($summary['total_sales'], 2, '.', ''), number_format($summary['total_cogs'], 2, '.', ''), number_format($summary['total_profit'], 2, '.', ''), '']);
                    fclose($file);
                };
                return response()->stream($callback, 200, $headers);
            }
        }

        return back()->with('error', 'Unsupported format');
    }

    private function exportStock(Request $request, $format)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        $items = Item::with(['category', 'supplier'])->get();

        $filename = "stock_report_{$asOfDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($items, $asOfDate) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Item Code', 'Name', 'Category', 'Current Stock', 'Cost Price', 'Valuation']);
            
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->item_code,
                    $item->name,
                    $item->category->name,
                    $item->current_stock,
                    $item->cost_price,
                    $item->current_stock * $item->cost_price,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportProfitLoss(Request $request, $format)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'all'); // all, item
        $categoryId = $request->get('category_id'); // Filter by category
        $itemId = $request->get('item_id'); // Filter by specific item

        $salesQuery = Sale::whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->where('status', 'completed')
            ->with('items.item');
        
        // Filter by category if selected
        if ($categoryId) {
            $salesQuery->whereHas('items.item', function($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }
        
        // Filter by item if selected
        if ($itemId) {
            $salesQuery->whereHas('items', function($query) use ($itemId) {
                $query->where('item_id', $itemId);
            });
        }
        
        $sales = $salesQuery->get();

        $revenue = $sales->sum('total_amount');
        $cogs = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $saleItem) {
                $cogs += $saleItem->quantity * $saleItem->item->cost_price;
            }
        }
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses;

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        if ($reportType === 'item') {
            // Item-wise export
            $itemWiseReportQuery = \App\Models\SaleItem::whereHas('sale', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
                      ->where('status', 'completed');
            })
            ->join('items', 'sale_items.item_id', '=', 'items.id');
            
            // Filter by category if selected
            if ($categoryId) {
                $itemWiseReportQuery->where('items.category_id', $categoryId);
            }
            
            // Filter by item if selected
            if ($itemId) {
                $itemWiseReportQuery->where('sale_items.item_id', $itemId);
            }
            
            $itemWiseReport = $itemWiseReportQuery
            ->select(
                'sale_items.item_id',
                DB::raw('sum(sale_items.quantity) as total_quantity'),
                DB::raw('sum(sale_items.total_price) as total_revenue'),
                DB::raw('sum(sale_items.quantity * items.cost_price) as total_cogs'),
                DB::raw('sum(sale_items.total_price) - sum(sale_items.quantity * items.cost_price) as total_profit'),
                DB::raw('avg(sale_items.unit_price) as avg_selling_price'),
                DB::raw('count(*) as times_sold')
            )
            ->groupBy('sale_items.item_id')
            ->with('item.category')
            ->orderBy('total_profit', 'desc')
            ->get()
            ->map(function($item) {
                $item->profit_margin = $item->total_revenue > 0 
                    ? (($item->total_profit / $item->total_revenue) * 100) 
                    : 0;
                return $item;
            });

            $filename = "profit_loss_item_wise_{$startDate}_to_{$endDate}.csv";
            $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";

            $callback = function() use ($itemWiseReport, $revenue, $cogs, $grossProfit, $expenses, $netProfit) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Item Name', 'Item Code', 'Category', 'Quantity Sold', 'Unit', 'Times Sold', 'Avg Selling Price', 'Total Revenue', 'Total COGS', 'Total Profit', 'Profit Margin %']);
                
                foreach ($itemWiseReport as $item) {
                    fputcsv($file, [
                        $item->item->name,
                        $item->item->item_code ?? 'N/A',
                        $item->item->category->name ?? 'N/A',
                        number_format($item->total_quantity, 2),
                        $item->item->unit_of_measure,
                        $item->times_sold,
                        number_format($item->avg_selling_price ?? 0, 2),
                        number_format($item->total_revenue, 2),
                        number_format($item->total_cogs ?? 0, 2),
                        number_format($item->total_profit ?? 0, 2),
                        number_format($item->profit_margin ?? 0, 2),
                    ]);
                }
                
                // Total row
                fputcsv($file, [
                    'TOTAL',
                    '-',
                    '-',
                    number_format($itemWiseReport->sum('total_quantity'), 2),
                    '-',
                    $itemWiseReport->sum('times_sold'),
                    '-',
                    number_format($itemWiseReport->sum('total_revenue'), 2),
                    number_format($itemWiseReport->sum('total_cogs'), 2),
                    number_format($itemWiseReport->sum('total_profit'), 2),
                    $itemWiseReport->sum('total_revenue') > 0 ? number_format(($itemWiseReport->sum('total_profit') / $itemWiseReport->sum('total_revenue')) * 100, 2) : 0,
                ]);
                
                // Summary section
                fputcsv($file, []);
                fputcsv($file, ['SUMMARY']);
                fputcsv($file, ['Total Revenue', number_format($revenue, 2)]);
                fputcsv($file, ['Total COGS', number_format($cogs, 2)]);
                fputcsv($file, ['Gross Profit', number_format($grossProfit, 2)]);
                fputcsv($file, ['Total Expenses', number_format($expenses, 2)]);
                fputcsv($file, ['Net Profit', number_format($netProfit, 2)]);
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            // Summary export
            $filename = "profit_loss_summary_{$startDate}_to_{$endDate}.csv";
            $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";

            $callback = function() use ($revenue, $cogs, $grossProfit, $expenses, $netProfit) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Item', 'Amount']);
                fputcsv($file, ['Revenue', number_format($revenue, 2)]);
                fputcsv($file, ['Cost of Goods Sold', number_format($cogs, 2)]);
                fputcsv($file, ['Gross Profit', number_format($grossProfit, 2)]);
                fputcsv($file, ['Gross Margin %', $revenue > 0 ? number_format(($grossProfit / $revenue) * 100, 2) : 0]);
                fputcsv($file, ['Expenses', number_format($expenses, 2)]);
                fputcsv($file, ['Net Profit', number_format($netProfit, 2)]);
                fputcsv($file, ['Net Margin %', $revenue > 0 ? number_format(($netProfit / $revenue) * 100, 2) : 0]);
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
    }

    private function exportExpenses(Request $request, $format)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');

        $query = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->with(['category', 'creator']);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses = $query->latest()->get();

        if ($format === 'pdf') {
            $summary = [
                'total' => $expenses->sum('amount'),
                'by_category' => $expenses->groupBy('expense_category_id')->map(function ($group) {
                    return [
                        'category' => $group->first()->category->name ?? 'Uncategorized',
                        'amount' => $group->sum('amount'),
                    ];
                }),
            ];
            $pdf = Pdf::loadView('admin.reports.expenses-pdf', compact('expenses', 'summary', 'startDate', 'endDate'));
            return $pdf->download("expenses_report_{$startDate}_to_{$endDate}.pdf");
        }

        $filename = "expenses_report_{$startDate}_to_{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Created By']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('Y-m-d'),
                    $expense->category->name ?? 'N/A',
                    $expense->description,
                    $expense->amount,
                    $expense->payment_method,
                    $expense->creator->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportSupplierLedger(Request $request, $format)
    {
        $supplierId = $request->get('supplier_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$supplierId) {
            return back()->withErrors(['error' => 'Supplier ID is required']);
        }

        $supplier = \App\Models\Supplier::findOrFail($supplierId);
        $query = SupplierPayment::where('supplier_id', $supplierId)
            ->with('supplier');

        if ($startDate) {
            $query->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $query->latest()->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.supplier-ledger-pdf', compact('supplier', 'payments', 'startDate', 'endDate'));
            return $pdf->download("supplier_ledger_{$supplier->name}.pdf");
        }

        $filename = "supplier_ledger_{$supplier->name}_{$supplierId}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($supplier, $payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Supplier: ' . $supplier->name]);
            fputcsv($file, ['Outstanding Balance: Rs. ' . number_format($supplier->outstanding_balance, 2)]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Invoice Number', 'Invoice Amount', 'Paid Amount', 'Outstanding', 'Payment Method', 'Due Date']);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_date ? $payment->payment_date->format('Y-m-d') : 'N/A',
                    $payment->invoice_number ?? 'N/A',
                    $payment->invoice_amount,
                    $payment->paid_amount,
                    $payment->outstanding_amount,
                    $payment->payment_method,
                    $payment->due_date ? $payment->due_date->format('Y-m-d') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function warrantyJobs(Request $request)
    {
        $query = \App\Models\WarrantyJob::with(['warranty.serialNumber.item', 'branch', 'creator']);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $jobs = $query->latest()->paginate(20);
        $branches = \App\Models\Branch::all();
        $statuses = \App\Models\WarrantyJob::STATUSES;


        return view('admin.reports.warranty-jobs', compact('jobs', 'branches', 'statuses'));
    }

    public function stockByLocation(Request $request)
    {
        $branchId = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $itemId = $request->get('item_id');

        $stockQuery = \App\Models\BranchStock::with(['branch', 'item.category']);

        if ($branchId) {
            $stockQuery->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $stockQuery->whereHas('item', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        if ($itemId) {
            $stockQuery->where('item_id', $itemId);
        }

        $stock = $stockQuery->get();

        $branches = Branch::all();
        $categories = \App\Models\Category::all();
        $items = Item::all();

        return view('admin.reports.stock-by-location', compact('stock', 'branches', 'categories', 'items', 'branchId', 'categoryId', 'itemId'));
    }

public function installmentIncome(Request $request)
{
    $customerId = $request->customer_id;
     $customerSearch = trim($request->customer_search);

    $query = InstallmentAgreement::with([
        'customer',
        'sale.items.item.category',
        'payments'
    ])->where('is_finalized', true)
      ->orderBy('created_at', 'desc');

    $isCustomerSearch = $request->filled('customer_id') || $request->filled('customer_search');

    // Customer filtering
    if ($request->filled('customer_id')) {
        $query->where('customer_id', $request->customer_id);
    } elseif (!empty($customerSearch)) {
              $query->search($customerSearch);
    }

    $startDate = null;
    $endDate = null;

    if ($request->filled('month')) {
        try {
            $month = Carbon::parse($request->input('month'));
            $startDate = $month->startOfMonth();
            $endDate   = $month->endOfMonth();
        } catch (\Exception $e) {}
    } else {
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        }
        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }
    }

    // DB-level filtering for general report
    if (!$isCustomerSearch && $startDate && $endDate) {
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereHas('payments', function ($pq) use ($startDate, $endDate) {
                $pq->whereBetween('payment_date', [$startDate, $endDate]);
            })->orWhereBetween('down_payment_date', [$startDate, $endDate]);
        });
    }

    // ✅ PAGINATION (SAFE)
    $agreements = $query->paginate(50)->withQueryString();

    // ✅ PROCESS PAGINATED DATA
    $processedAgreements = $agreements->through(function ($agreement) use ($isCustomerSearch, $startDate, $endDate) {

        $totalPaid = $agreement->payments->sum('amount');
        $principal = $agreement->total_invoice_value - $agreement->interest_service_charge;
        $interestPaid = max(0, $totalPaid + $agreement->down_payment_amount - $principal);

        $outstandingPrincipal = max(0, $principal - ($totalPaid + $agreement->down_payment_amount));
        $outstandingInterest  = max(0, $agreement->interest_service_charge - $interestPaid);

        // COGS
        $cogs = 0;
        if ($agreement->sale && $agreement->sale->items) {
            $cogs = $agreement->sale->items->sum(
                fn ($item) => ($item->item->cost_price ?? 0) * $item->quantity
            );
        }

        $profit = $agreement->total_invoice_value - $cogs;
        $profitClass = $profit >= 0 ? 'text-success' : 'text-danger';

        // Overdue status
        $overdueStatus = 'On Track';
        $nextDueDate = $agreement->next_due_date;
        $dueDate = ($nextDueDate instanceof Carbon) ? $nextDueDate : (is_string($nextDueDate) && $nextDueDate !== 'Completed' ? Carbon::parse($nextDueDate) : null);

        if ($agreement->status === 'paid_off') {
            $overdueStatus = 'Paid Off';
        } elseif ($dueDate) {
            if (now()->gt($dueDate)) {
                $overdueStatus = 'Overdue';
            } elseif (now()->diffInDays($dueDate, false) <= 7) {
                $overdueStatus = 'Due Soon';
            }
        }

        // Activity in range
        $hasActivityInRange = !$isCustomerSearch || !$startDate || !$endDate;

        if ($isCustomerSearch && $startDate && $endDate) {
            if ($agreement->down_payment_date &&
                Carbon::parse($agreement->down_payment_date)->between($startDate, $endDate)) {
                $hasActivityInRange = true;
            }

            if (!$hasActivityInRange) {
                $hasActivityInRange = $agreement->payments->contains(
                    fn ($p) => Carbon::parse($p->payment_date)->between($startDate, $endDate)
                );
            }
        }

        // ✅ MONTHLY BREAKDOWN
        $monthlyBreakdown = [];
        $totalInstallments = $agreement->total_installments;
        $monthlyInterest = $totalInstallments > 0
            ? $agreement->interest_service_charge / $totalInstallments
            : 0;

        $initialBalance = round($agreement->total_invoice_value - $agreement->down_payment_amount, 2);
        $remainingToAllocateDue = $initialBalance;
        $monthlyAmount = round($agreement->monthly_installment_amount, 2);

        for ($i = 0; $i < $totalInstallments; $i++) {
            $dueDate = Carbon::parse($agreement->first_due_date)->addMonths($i);
            $dueDate = $agreement->applyDueDayOfMonth($dueDate);

            $dueForThisMonth = min($remainingToAllocateDue, $monthlyAmount);
            // Ensure last installment covers any rounding/remaining balance
            if ($i == $totalInstallments - 1) {
                $dueForThisMonth = $remainingToAllocateDue;
            }
            $remainingToAllocateDue = round($remainingToAllocateDue - $dueForThisMonth, 2);

            $monthKey = $dueDate->format('Y-m') . '-' . $i; // Unique key even for same month
            $monthlyBreakdown[$monthKey] = [
                'due_date' => $dueDate->toDateString(),
                'due_amount' => max(0, $dueForThisMonth),
                'paid_amount' => 0,
                'paid_date' => null,
                'status' => 'on_track',
                'monthly_interest' => $monthlyInterest,
                'comment' => '',
            ];
        }

        // Allocate payments to installments in order
        $remainingPayments = $agreement->payments->sortBy('payment_date')->values();
        $paymentIndex = 0;
        $paymentOffset = 0; // amount already used from current payment
        $accumulatedPaid = 0;

        foreach ($monthlyBreakdown as $monthKey => &$data) {
            $dueForThisMonth = round($data['due_amount'], 2);
            $allocatedForThisMonth = 0;

            if ($dueForThisMonth > 0) {
                while ($dueForThisMonth > 0.009 && $paymentIndex < count($remainingPayments)) {
                    $p = $remainingPayments[$paymentIndex];
                    $availableInPayment = round($p->amount - $paymentOffset, 2);
                    
                    $toTake = min($dueForThisMonth, $availableInPayment);
                    
                    $allocatedForThisMonth = round($allocatedForThisMonth + $toTake, 2);
                    $dueForThisMonth = round($dueForThisMonth - $toTake, 2);
                    $paymentOffset = round($paymentOffset + $toTake, 2);
                    
                    $data['paid_date'] = $p->payment_date;
                    $data['comment'] = $p->notes ?? '';
                    
                    if ($paymentOffset >= round($p->amount, 2) - 0.009) {
                        $paymentIndex++;
                        $paymentOffset = 0;
                    }
                }
            }
            
            $data['paid_amount'] = $allocatedForThisMonth;
            $accumulatedPaid = round($accumulatedPaid + $allocatedForThisMonth, 2);

            if ($data['paid_amount'] >= round($data['due_amount'], 2) - 0.009 && $data['due_amount'] > 0) {
                $data['status'] = 'paid_off';
            } elseif ($data['paid_amount'] > 0) {
                $data['status'] = 'partially_paid';
            } elseif ($agreement->balance_amount <= 0.009) {
                $data['status'] = 'paid_off'; // Entire agreement is paid off
            }
        }

        return (object) [
            'agreement' => $agreement,
            'total_paid' => $totalPaid,
            'principal' => $principal,
            'interest_paid' => $interestPaid,
            'outstanding_principal' => $outstandingPrincipal,
            'outstanding_interest' => $outstandingInterest,
            'status' => $agreement->status,
            'profit' => $profit,
            'profit_class' => $profitClass,
            'overdue_status' => $overdueStatus,
            'monthly_breakdown' => $monthlyBreakdown,
            'has_activity_in_range' => $hasActivityInRange,
        ];
    });

    // Group by customer (UI safe)
    $agreementsByCustomer = $processedAgreements->groupBy('agreement.customer_id');

    $data = $agreementsByCustomer->map(function ($items, $customerId) {
        return (object) [
           'customer' => $items->first()->agreement->customer,
            'agreements' => $items,
        ];
    });

    $allCustomers = Customer::has('installmentAgreements')->get();

    $summaryDate = Carbon::parse($request->input('summary_date', 'today'));
    $dailyIncomeSummary = $this->getDailyIncomeSummary($summaryDate);

    // ✅ FIX: PASS EVERYTHING THE BLADE USES
    return view('admin.reports.installment-income', compact(
        'data',
        'processedAgreements',
        'agreements',
        'allCustomers',
        'dailyIncomeSummary',
        'summaryDate'
    ));
}



    private function getDailyIncomeSummary(Carbon $date)
    {
        $downPayments = InstallmentAgreement::whereDate('down_payment_date', $date)
            ->selectRaw('down_payment_method as method, SUM(down_payment_amount) as total')
            ->groupBy('down_payment_method')
            ->get();

        $installmentPayments = InstallmentPayment::whereDate('payment_date', $date)
            ->selectRaw('payment_method as method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        return $downPayments->concat($installmentPayments)
            ->groupBy('method')
            ->map(function ($items) {
                return $items->sum('total');
            });
    }

    public function dailyInstallments(Request $request)
    {
        $startDate = $request->get('start_date', now()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $customerId = $request->get('customer_id');
        $reportType = $request->get('report_type', 'all'); // all, down_payment, installment
        $paymentMethod = $request->get('payment_method');

        $reportData = $this->getDailyInstallmentData($startDate, $endDate, $customerId, $reportType, $paymentMethod);
        $allCustomers = Customer::has('installmentAgreements')->orderBy('name')->get();

        return view('admin.reports.daily-installments', compact('reportData', 'startDate', 'endDate', 'customerId', 'allCustomers', 'reportType', 'paymentMethod'));
    }

    private function getDailyInstallmentData($startDate, $endDate, $customerId = null, $reportType = 'all', $paymentMethod = null)
    {
        $startDateObj = Carbon::parse($startDate)->startOfDay();
        $endDateObj = Carbon::parse($endDate)->endOfDay();

        // Get agreements that have payments in the range or down payment in the range
        $agreementsQuery = InstallmentAgreement::with(['customer', 'sale', 'payments']);
        
        $agreementsQuery->where(function($q) use ($startDate, $endDate, $startDateObj, $endDateObj) {
            $q->whereBetween('down_payment_date', [$startDate, $endDate])
              ->orWhereHas('payments', function($pq) use ($startDateObj, $endDateObj) {
                  $pq->whereBetween('payment_date', [$startDateObj, $endDateObj]);
              });
        });

        if ($customerId) {
            $agreementsQuery->where('customer_id', $customerId);
        }

        $agreements = $agreementsQuery->get();
        $reportData = collect();

        foreach ($agreements as $agreement) {
            // Check Down Payment
            if (($reportType === 'all' || $reportType === 'down_payment') && 
                $agreement->down_payment_date && 
                Carbon::parse($agreement->down_payment_date)->between($startDateObj, $endDateObj)) {
                
                if (!$paymentMethod || $agreement->down_payment_method === $paymentMethod) {
                    $reportData->push([
                        'date' => $agreement->down_payment_date,
                        'type' => 'Down Payment',
                        'customer' => $agreement->customer->name ?? 'N/A',
                        'invoice' => $agreement->sale->invoice_number ?? 'N/A',
                        'due_date' => '-',
                        'method' => $agreement->down_payment_method ?? 'N/A',
                        'interest' => 0,
                        'amount' => $agreement->down_payment_amount,
                        'notes' => '-',
                    ]);
                }
            }

            if ($reportType === 'all' || $reportType === 'installment') {
                // FIFO Allocation for Installment Payments
                $totalInstallments = $agreement->total_installments;
                $monthlyInterest = $totalInstallments > 0
                    ? $agreement->interest_service_charge / $totalInstallments
                    : 0;
                $monthlyAmount = $agreement->monthly_installment_amount;

                $allPayments = $agreement->payments->sortBy('payment_date')->values();
                $paymentIndex = 0;
                $paymentOffset = 0;

                $paymentRows = []; // To store aggregated data per payment ID

                for ($i = 0; $i < $totalInstallments; $i++) {
                    $dueDate = Carbon::parse($agreement->first_due_date)->addMonths($i);
                    $dueDate = $agreement->applyDueDayOfMonth($dueDate);

                    $dueForThisMonth = $monthlyAmount;
                    
                    while ($dueForThisMonth > 0 && $paymentIndex < count($allPayments)) {
                        $p = $allPayments[$paymentIndex];
                        $availableInPayment = $p->amount - $paymentOffset;
                        $toTake = min($dueForThisMonth, $availableInPayment);
                        
                        // If this part of the payment falls in the range, record it
                        if (Carbon::parse($p->payment_date)->between($startDateObj, $endDateObj)) {
                            if (!$paymentMethod || $p->payment_method === $paymentMethod) {
                                $interestPortion = ($monthlyAmount > 0) ? ($toTake / $monthlyAmount) * $monthlyInterest : 0;
                                
                                if (!isset($paymentRows[$p->id])) {
                                    $paymentRows[$p->id] = [
                                        'id' => $p->id,
                                        'date' => Carbon::parse($p->payment_date)->toDateString(),
                                        'type' => 'Installment',
                                        'customer' => $agreement->customer->name ?? 'N/A',
                                        'invoice' => $agreement->sale->invoice_number ?? 'N/A',
                                        'due_date' => $dueDate->toDateString(), // Store the first due date it hits
                                        'method' => $p->payment_method,
                                        'interest' => 0,
                                        'amount' => 0,
                                        'notes' => $p->notes ?? '-',
                                    ];
                                }
                                $paymentRows[$p->id]['interest'] += $interestPortion;
                                $paymentRows[$p->id]['amount'] += $toTake;
                            }
                        }

                        $dueForThisMonth -= $toTake;
                        $paymentOffset += $toTake;

                        if ($paymentOffset >= $p->amount) {
                            $paymentIndex++;
                            $paymentOffset = 0;
                        }
                    }
                }
                
                // Handle overpayments
                while ($paymentIndex < count($allPayments)) {
                    $p = $allPayments[$paymentIndex];
                    if (Carbon::parse($p->payment_date)->between($startDateObj, $endDateObj)) {
                        if (!$paymentMethod || $p->payment_method === $paymentMethod) {
                            if (!isset($paymentRows[$p->id])) {
                                $paymentRows[$p->id] = [
                                    'id' => $p->id,
                                    'date' => Carbon::parse($p->payment_date)->toDateString(),
                                    'type' => 'Installment',
                                    'customer' => $agreement->customer->name ?? 'N/A',
                                    'invoice' => $agreement->sale->invoice_number ?? 'N/A',
                                    'due_date' => 'Additional',
                                    'method' => $p->payment_method,
                                    'interest' => 0,
                                    'amount' => 0,
                                    'notes' => $p->notes ?? 'Additional Payment',
                                ];
                            }
                            $paymentRows[$p->id]['amount'] += ($p->amount - $paymentOffset);
                        }
                    }
                    $paymentIndex++;
                    $paymentOffset = 0;
                }

                foreach ($paymentRows as $row) {
                    $reportData->push($row);
                }
            }
        }

        return $reportData->sortByDesc('date')->values();
    }

    
    public function disconnectReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $search = trim($request->get('search'));
        
        $reportType = $request->get('report_type', 'filtered'); // filtered or all_time

        $disconnectedQuery = InstallmentAgreement::whereNotNull('disconnected_at')
            ->whereNull('unlocked_at') // Currently Locked
            ->with(['customer', 'payments', 'sale.items.item']);

        $unlockedQuery = InstallmentAgreement::whereNotNull('unlocked_at') // Currently Unlocked
            ->with(['customer', 'payments', 'sale.items.item']);

        if ($reportType === 'filtered') {
            $disconnectedQuery->whereBetween('disconnected_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            $unlockedQuery->whereBetween('unlocked_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
        }

        if ($search) {
           $disconnectedQuery->search($search);
            $unlockedQuery->search($search);
        }

        $disconnected = $disconnectedQuery->get();
        $unlocked = $unlockedQuery->get();

        // Calculate counts based on filtered data
        $withinMonthCount = $disconnected->count();

      
        // Calculate balances based on filtered data
        $lockBalance = $disconnected->sum(fn($a) => $a->overdue_installment_amount + $a->remaining_fine);
        $unlockBalance = $unlocked->sum(fn($a) => $a->overdue_installment_amount + $a->remaining_fine);


        return view('admin.reports.disconnect', compact('disconnected', 'unlocked', 'startDate', 'endDate', 'withinMonthCount', 'lockBalance', 'unlockBalance',  'reportType'));
    }
    public function salesGrowth(Request $request)
{
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    $todaySales = Sale::whereDate('created_at', $today)
        ->where('status', 'completed')
        ->sum('total_amount');

    $yesterdaySales = Sale::whereDate('created_at', $yesterday)
        ->where('status', 'completed')
        ->sum('total_amount');

    $thisMonth = now()->month;
    $thisYear = now()->year;

    $lastMonth = now()->subMonth()->month;
    $lastMonthYear = now()->subMonth()->year;

    $thisMonthSales = Sale::whereMonth('created_at', $thisMonth)
        ->whereYear('created_at', $thisYear)
        ->where('status', 'completed')
        ->sum('total_amount');

    $lastMonthSales = Sale::whereMonth('created_at', $lastMonth)
        ->whereYear('created_at', $lastMonthYear)
        ->where('status', 'completed')
        ->sum('total_amount');

    // Weekly sales for the chart
    $weeklySales = Sale::where('created_at', '>=', now()->subWeeks(4))
        ->where('status', 'completed')
        ->get()
        ->groupBy(function ($sale) {
            return $sale->created_at->format('W');
        })
        ->map(function ($group, $week) {
            return [
                'week' => $week,
                'revenue' => $group->sum('total_amount'),
            ];
        })
        ->sortBy('week')
        ->values();

    // Daily sales for the last 30 days
    $dailySales = Sale::where('created_at', '>=', now()->subDays(30))
        ->where('status', 'completed')
        ->get()
        ->groupBy(function ($sale) {
            return $sale->created_at->format('Y-m-d');
        })
        ->map(function ($group, $date) {
            return [
                'date' => $date,
                'revenue' => $group->sum('total_amount'),
            ];
        })
        ->sortBy('date')
        ->values();

    // PDF export
    if ($request->has('format') && $request->get('format') === 'pdf') {

        $pdf = Pdf::loadView(
            'admin.reports.sales-growth-print',
            compact(
                'todaySales',
                'yesterdaySales',
                'thisMonthSales',
                'lastMonthSales',
                'dailySales',
                'weeklySales'
            ),
            ['isPdf' => true]
        );

        return $pdf->download('sales_growth_report.pdf');
    }

    return view(
        'admin.reports.sales-growth',
        compact(
            'todaySales',
            'yesterdaySales',
            'thisMonthSales',
            'lastMonthSales',
            'weeklySales',
            'dailySales'
        )
    );
}
     public function customerBehavior(Request $request)
    {
        $statusFilter = $request->get('status'); // on_time, delayed
              $search = trim($request->get('search'));
        $customers = $this->getCustomerBehaviorData($statusFilter, $search);

        if ($request->has('format') && $request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.customer-behavior-print', compact('customers', 'statusFilter', 'search'), ['isPdf' => true]);
            return $pdf->download("customer_behavior_report.pdf");
        }

        return view('admin.reports.customer-behavior', compact('customers', 'statusFilter', 'search'));
    }

    private function getCustomerBehaviorData($statusFilter = null, $search = null)
    {
        $query = Customer::withCount(['sales', 'installmentAgreements'])
            ->withSum('sales', 'total_amount')
            ->with(['installmentAgreements.payments']);

        if ($search) {
             $search = trim($search);
             $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('nic', 'like', "%{$search}%")
                    ->orWhere('emi_number', 'like', "%{$search}%")
                    ->orWhereHas('installmentAgreements', function ($iq) use ($search) {
                        $iq->search($search);
                    });
            });
        }

        $customers = $query->get()
            ->map(function($customer) {
                $totalPaid = 0;
                $totalDue = 0;
                $onTime = 0;
                $delayed = 0;
                $totalLockDuration = 0;
                $isLockedNow = false;

                foreach ($customer->installmentAgreements as $agreement) {
                    $totalPaid += $agreement->payments->sum('amount') + $agreement->down_payment_amount;
                    $totalDue += $agreement->total_invoice_value;

                    // Calculate on-time and delayed premiums using FIFO logic or simple due date check
                    $monthlyAmount = $agreement->monthly_installment_amount;
                    if ($monthlyAmount > 0) {
                        $payments = $agreement->payments->sortBy('payment_date');
                        
                        for ($i = 0; $i < $agreement->total_installments; $i++) {
                            $dueDate = Carbon::parse($agreement->first_due_date)->addMonths($i);
                            $dueDate = $agreement->applyDueDayOfMonth($dueDate);
                            
                            $targetPaid = ($i + 1) * $monthlyAmount;
                            
                            // Find if we had reached this target by due date
                            $paidByDueDate = $payments->filter(fn($p) => Carbon::parse($p->payment_date)->lte($dueDate->endOfDay()))
                                ->sum('amount');
                            
                            if ($paidByDueDate >= $targetPaid) {
                                $onTime++;
                            } else {
                                // Check if it's actually paid yet
                                $totalPaidForThisAg = $payments->sum('amount');
                                if ($totalPaidForThisAg >= $targetPaid) {
                                    $delayed++;
                                } elseif ($dueDate->lt(now())) {
                                    $delayed++; // It's overdue, so treat as delayed
                                }
                            }
                        }
                    }

                    // Lock status
                    if ($agreement->disconnected_at) {
                        $end = $agreement->unlocked_at ?: now();
                        $totalLockDuration += Carbon::parse($agreement->disconnected_at)->diffInDays(Carbon::parse($end));
                        if (!$agreement->unlocked_at) {
                            $isLockedNow = true;
                        }
                    }
                }

                $customer->payment_ratio = $totalDue > 0 ? ($totalPaid / $totalDue) * 100 : 0;
                $customer->on_time_premiums = $onTime;
                $customer->delayed_premiums = $delayed;
                $customer->total_lock_duration = $totalLockDuration;
                $customer->is_locked_now = $isLockedNow;
                
                // Categorize behavior
                if ($customer->payment_ratio >= 90 && $delayed == 0) {
                    $customer->behavior = 'Excellent';
                    $customer->behavior_class = 'text-green-600';
                } elseif ($customer->payment_ratio >= 70 && $delayed <= 2) {
                    $customer->behavior = 'Good';
                    $customer->behavior_class = 'text-blue-600';
                } elseif ($customer->payment_ratio >= 40) {
                    $customer->behavior = 'Average';
                    $customer->behavior_class = 'text-yellow-600';
                } else {
                    $customer->behavior = 'Risky';
                    $customer->behavior_class = 'text-red-600';
                }
                return $customer;
            });

        if ($statusFilter === 'on_time') {
            $customers = $customers->filter(function($customer) {
                return $customer->delayed_premiums === 0 && $customer->on_time_premiums > 0;
            });
        } elseif ($statusFilter === 'delayed') {
            $customers = $customers->filter(function($customer) {
                return $customer->delayed_premiums > 0;
            });
        }

        return $customers->sortByDesc('sales_sum_total_amount');
    }

    public function cashCollection(Request $request)
    {
        $selectedMonthCount = $request->get('months', 4); // Default 4 months
        $asOfDate = $request->get('as_of_date', now()->toDateString());
        $carbonAsOf = Carbon::parse($asOfDate);

        $agreements = InstallmentAgreement::where('status', 'active')
            ->where('balance_amount', '>', 0)
            ->with(['customer', 'payments'])
            ->get();

        $upcomingCollections = [];
        $totalUpcoming = 0;

        foreach ($agreements as $agreement) {
            $monthlyPremium = $agreement->monthly_installment_amount;
            $remainingBalance = $agreement->balance_amount;
            $nextDueDate = $agreement->next_due_date;

            if (!($nextDueDate instanceof Carbon)) continue;

            $tempNextDue = $nextDueDate->copy();
            
            for ($i = 0; $i < $selectedMonthCount; $i++) {
                if ($remainingBalance <= 0) break;

                $dueAmount = min($monthlyPremium, $remainingBalance);
                $monthKey = $tempNextDue->format('Y-m');

                if (!isset($upcomingCollections[$monthKey])) {
                    $upcomingCollections[$monthKey] = [
                        'month' => $tempNextDue->format('F Y'),
                        'expected' => 0,
                        'received' => 0,
                        'customers' => []
                    ];
                }

                $upcomingCollections[$monthKey]['expected'] += $dueAmount;
                $upcomingCollections[$monthKey]['customers'][] = [
                    'customer' => $agreement->customer->name ?? 'N/A',
                    'phone' => $agreement->customer->phone ?? 'N/A',
                    'due_date' => $tempNextDue->toDateString(),
                    'amount' => $dueAmount
                ];

                $remainingBalance -= $dueAmount;
                $tempNextDue->addMonth();
                $tempNextDue = $agreement->applyDueDayOfMonth($tempNextDue);
            }
        }

        // Calculate actual received for past months - Collection grouping for portability
        $receivedData = InstallmentPayment::where('payment_date', '<=', now()->endOfDay())
            ->where('payment_date', '>=', now()->subMonths(6)->startOfMonth())
            ->get()
            ->groupBy(function($payment) {
                return Carbon::parse($payment->payment_date)->format('Y-m');
            })
            ->map(function($group) {
                return (object)['total' => $group->sum('amount')];
            });

        ksort($upcomingCollections);

        if ($request->has('format') && $request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.cash-collection-print', compact('upcomingCollections', 'selectedMonthCount', 'asOfDate'), ['isPdf' => true]);
            return $pdf->download("cash_collection_report.pdf");
        }

        return view('admin.reports.cash-collection', compact('upcomingCollections', 'receivedData', 'selectedMonthCount', 'asOfDate'));
    } 

    public function salesTracking(Request $request)
    {
        $day1 = $request->get('day1', now()->toDateString());
        $day2 = $request->get('day2', now()->subMonth()->toDateString());

        $day1Data = Sale::whereDate('created_at', $day1)->where('status', 'completed')->sum('total_amount');
        $day2Data = Sale::whereDate('created_at', $day2)->where('status', 'completed')->sum('total_amount');

        if ($request->has('format') && $request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.sales-tracking-print', compact('day1', 'day2', 'day1Data', 'day2Data'), ['isPdf' => true]);
            return $pdf->download("sales_tracking_report.pdf");
        }

        return view('admin.reports.sales-tracking', compact('day1', 'day2', 'day1Data', 'day2Data'));
    }

 public function dailyInstallmentIncome(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Group by date
        $installmentIncome = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(payment_date) as date'), DB::raw('SUM(amount) as total_installments'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $downPaymentIncome = InstallmentAgreement::whereBetween('down_payment_date', [$startDate, $endDate])
            ->select(DB::raw('DATE(down_payment_date) as date'), DB::raw('SUM(down_payment_amount) as total_down_payments'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Combine them
        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $inst = isset($installmentIncome[$dateStr]) ? $installmentIncome[$dateStr]->total_installments : 0;
            $down = isset($downPaymentIncome[$dateStr]) ? $downPaymentIncome[$dateStr]->total_down_payments : 0;
            $dates[$dateStr] = [
                'date' => $dateStr,
                'installments' => $inst,
                'down_payments' => $down,
                'total' => $inst + $down
            ];
            $current->addDay();
        }
        $dailyIncome = collect($dates)->reverse();

        // Customer-wise summary
        $installmentPayments = InstallmentPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->with('agreement.customer')
            ->get();
        
        $downPayments = InstallmentAgreement::whereBetween('down_payment_date', [$startDate, $endDate])
            ->with('customer')
            ->get();

        $customerSummary = [];

        foreach ($installmentPayments as $p) {
            $cId = $p->agreement->customer_id ?? 0;
            if ($cId == 0) continue;

            if (!isset($customerSummary[$cId])) {
                $customerSummary[$cId] = [
                    'name' => $p->agreement->customer->name ?? 'Unknown',
                    'installments' => 0,
                    'down_payments' => 0,
                    'total' => 0
                ];
            }
            $customerSummary[$cId]['installments'] += $p->amount;
            $customerSummary[$cId]['total'] += $p->amount;
        }

        foreach ($downPayments as $dp) {
            $cId = $dp->customer_id ?? 0;
            if ($cId == 0) continue;

            if (!isset($customerSummary[$cId])) {
                $customerSummary[$cId] = [
                    'name' => $dp->customer->name ?? 'Unknown',
                    'installments' => 0,
                    'down_payments' => 0,
                    'total' => 0
                ];
            }
            $customerSummary[$cId]['down_payments'] += $dp->down_payment_amount;
            $customerSummary[$cId]['total'] += $dp->down_payment_amount;
        }

        $customerSummary = collect($customerSummary)->sortByDesc('total');

        return view('admin.reports.daily-installment-income', compact('dailyIncome', 'customerSummary', 'startDate', 'endDate'));
    }

    public function onlineCollection(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $query = InstallmentPayment::where('payment_method', 'Online')
            ->whereBetween('payment_date', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->with(['agreement.customer', 'agreement.sale']);

        $payments = $query->latest('payment_date')->get();

        $totalAmount = $payments->sum('amount');

        if ($request->has('format') && $request->get('format') === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.online-collection-print', compact('payments', 'startDate', 'endDate', 'totalAmount'), ['isPdf' => true]);
            return $pdf->download("online_collection_report_{$startDate}_to_{$endDate}.pdf");
        }

        return view('admin.reports.online-collection', compact('payments', 'startDate', 'endDate', 'totalAmount'));
    }

    public function disconnectReportPrint(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('report_type', 'filtered');

        $disconnectedQuery = InstallmentAgreement::whereNotNull('disconnected_at')
             ->whereNull('unlocked_at') // Currently Locked
             ->with(['customer', 'payments', 'sale.items.item']);

        $unlockedQuery = InstallmentAgreement::whereNotNull('unlocked_at') // Currently Unlocked
          ->with(['customer', 'payments', 'sale.items.item']);

        if ($reportType === 'filtered') {
            $disconnectedQuery->whereBetween('disconnected_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            $unlockedQuery->whereBetween('unlocked_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
        }

        $disconnected = $disconnectedQuery->get();
        $unlocked = $unlockedQuery->get();

        // Calculate counts based on filtered data
        $withinMonthCount = $disconnected->count();

        // Calculate balances based on filtered data
     $lockBalance = $disconnected->sum(fn($a) => $a->overdue_installment_amount + $a->remaining_fine);
        $unlockBalance = $unlocked->sum(fn($a) => $a->overdue_installment_amount + $a->remaining_fine);

        return view('admin.reports.disconnect-print', compact('disconnected', 'unlocked', 'startDate', 'endDate', 'withinMonthCount', 'lockBalance', 'unlockBalance', 'reportType'));
    }

    public function salesGrowthPrint(Request $request)
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $todaySales = Sale::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount');
        $yesterdaySales = Sale::whereDate('created_at', $yesterday)->where('status', 'completed')->sum('total_amount');

        $thisMonth = now()->month;
        $thisYear = now()->year;
        $lastMonth = now()->subMonth()->month;
        $lastMonthYear = now()->subMonth()->year;

        $thisMonthSales = Sale::whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->where('status', 'completed')->sum('total_amount');
        $lastMonthSales = Sale::whereMonth('created_at', $lastMonth)->whereYear('created_at', $lastMonthYear)->where('status', 'completed')->sum('total_amount');

        $dailySales = Sale::where('created_at', '>=', now()->subDays(30))
            ->where('status', 'completed')
            ->get()
            ->groupBy(function($sale) {
                return $sale->created_at->format('Y-m-d');
            })
            ->map(function($group, $date) {
                return [
                    'date' => $date,
                    'revenue' => $group->sum('total_amount'),
                ];
            })
            ->sortBy('date')
            ->values();

        return view('admin.reports.sales-growth-print', compact('todaySales', 'yesterdaySales', 'thisMonthSales', 'lastMonthSales', 'dailySales'));
    }

    public function salesTrackingPrint(Request $request)
    {
        $day1 = $request->get('day1', now()->toDateString());
        $day2 = $request->get('day2', now()->subMonth()->toDateString());

        $day1Data = Sale::whereDate('created_at', $day1)->where('status', 'completed')->sum('total_amount');
        $day2Data = Sale::whereDate('created_at', $day2)->where('status', 'completed')->sum('total_amount');

        return view('admin.reports.sales-tracking-print', compact('day1', 'day2', 'day1Data', 'day2Data'));
    }

    public function customerBehaviorPrint(Request $request)
    {
        $statusFilter = $request->get('status');
        $search = $request->get('search');
        $customers = $this->getCustomerBehaviorData($statusFilter, $search);
        return view('admin.reports.customer-behavior-print', compact('customers', 'statusFilter', 'search'));
    }

    public function cashCollectionPrint(Request $request)
    {
        $selectedMonthCount = $request->get('months', 4);
        $asOfDate = $request->get('as_of_date', now()->toDateString());
          $agreements = InstallmentAgreement::where('status', '!=', 'paid_off')->where('balance_amount', '>', 0)->with(['customer', 'payments'])->get();

        $upcomingCollections = [];
        foreach ($agreements as $agreement) {
            $monthlyPremium = $agreement->monthly_installment_amount;
            $remainingBalance = $agreement->balance_amount;
            $nextDueDate = $agreement->next_due_date;
            if (!($nextDueDate instanceof Carbon)) continue;
            $tempNextDue = $nextDueDate->copy();
            for ($i = 0; $i < $selectedMonthCount; $i++) {
                if ($remainingBalance <= 0) break;
                $dueAmount = min($monthlyPremium, $remainingBalance);
                $monthKey = $tempNextDue->format('Y-m');
                if (!isset($upcomingCollections[$monthKey])) {
                    $upcomingCollections[$monthKey] = ['month' => $tempNextDue->format('F Y'), 'expected' => 0, 'customers' => []];
                }
                $upcomingCollections[$monthKey]['expected'] += $dueAmount;
                $upcomingCollections[$monthKey]['customers'][] = ['customer' => $agreement->customer->name, 'phone' => $agreement->customer->phone, 'due_date' => $tempNextDue->toDateString(), 'amount' => $dueAmount];
                $remainingBalance -= $dueAmount;
                $tempNextDue->addMonth();
                $tempNextDue = $agreement->applyDueDayOfMonth($tempNextDue);
            }
        }
        ksort($upcomingCollections);

        return view('admin.reports.cash-collection-print', compact('upcomingCollections', 'selectedMonthCount', 'asOfDate'));
    }

    public function onlineCollectionPrint(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $query = InstallmentPayment::where('payment_method', 'Online')
            ->whereBetween('payment_date', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->with(['agreement.customer', 'agreement.sale']);

        $payments = $query->latest('payment_date')->get();
        $totalAmount = $payments->sum('amount');

        return view('admin.reports.online-collection-print', compact('payments', 'startDate', 'endDate', 'totalAmount'));
    }

    public function delayPayments(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $query = InstallmentPayment::where('fine_amount', '>', 0)
            ->whereBetween('payment_date', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->with(['agreement.customer', 'agreement.sale']);

        $payments = $query->latest('payment_date')->get();
        $totalFine = $payments->sum('fine_amount');

        return view('admin.reports.delay-payments', compact('payments', 'startDate', 'endDate', 'totalFine'));
    }
 public function onDateInstallments(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $carbonDate = Carbon::parse($date);
        $dayOfMonth = $carbonDate->day;
        $isLastDay = $carbonDate->copy()->endOfMonth()->day === $dayOfMonth;

        $query = InstallmentAgreement::with(['customer', 'payments']);
        
        if ($isLastDay) {
            $query->where('due_day_of_month', '>=', $dayOfMonth);
        } else {
            $query->where('due_day_of_month', $dayOfMonth);
        }
        
        $agreements = $query->get();
        $reportData = collect();

        foreach ($agreements as $agreement) {
            $paymentsBefore = $agreement->payments->filter(function($p) use ($date) {
                return Carbon::parse($p->payment_date)->lt(Carbon::parse($date)->startOfDay());
            });
            $totalPaidBefore = $paymentsBefore->sum('amount');
            
            $paidCountBefore = $agreement->monthly_installment_amount > 0 
                ? floor($totalPaidBefore / $agreement->monthly_installment_amount) 
                : 0;
            
            $expectedDueDate = Carbon::parse($agreement->first_due_date)->addMonths((int)$paidCountBefore);
            $expectedDueDate = $agreement->applyDueDayOfMonth($expectedDueDate);

            if ($expectedDueDate->toDateString() === $date) {
                $paymentsToday = $agreement->payments->filter(function($p) use ($date) {
                    return Carbon::parse($p->payment_date)->toDateString() === $date;
                });

                if ($paymentsToday->count() > 0) {
                    $totalPaidSoFar = $agreement->payments->sum('amount');
                    $paidQuantity = $agreement->monthly_installment_amount > 0 
                        ? floor($totalPaidSoFar / $agreement->monthly_installment_amount) 
                        : 0;

                    $reportData->push([
                        'customer_name' => $agreement->customer->name ?? 'N/A',
                        'phone' => $agreement->customer->phone ?? 'N/A',
                          'guarantor_name' => $agreement->guarantor_name ?? 'N/A',
                        'guarantor_phone' => $agreement->guarantor_mobile_number ?? 'N/A',
                        'emi_number' => $agreement->combined_emi_numbers,
                        'premium' => $agreement->monthly_installment_amount,
                        'balance' => $agreement->balance_amount,
                        'paid_quantity' => (int)$paidQuantity,
                    ]);
                }
            }
        }

        return view('admin.reports.on-date-installments', compact('reportData', 'date'));
    }

      public function outstandingInstallments(Request $request)
    {
        $reportType = $request->get('report_type', 'total');
        $categoryId = $request->get('category_id');
        $search = trim($request->get('search'));
        
        $data = $this->getOutstandingInstallmentsReportData($reportType, $categoryId, $search);
        $reportData = $data['reportData'];
        $summary = $data['summary'];
        $categorySummary = $data['categorySummary'];

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.reports.outstanding-installments', compact('reportData', 'reportType', 'summary', 'categories', 'categoryId', 'categorySummary', 'search'));
    }

    public function outstandingInstallmentsPrint(Request $request)
    {
        $reportType = $request->get('report_type', 'total');
        $categoryId = $request->get('category_id');
        $search = trim($request->get('search'));
        
        $data = $this->getOutstandingInstallmentsReportData($reportType, $categoryId, $search);
        $reportData = $data['reportData'];
        $summary = $data['summary'];
        $categorySummary = $data['categorySummary'];

        return view('admin.reports.outstanding-installments-print', compact('reportData', 'reportType', 'summary', 'categorySummary', 'categoryId', 'search'));
    }


 private function getOutstandingInstallmentsReportData($reportType, $categoryId = null, $search = null)
    {
        // Fetch agreements
        $agreementsQuery = InstallmentAgreement::with(['customer', 'sale.items.item.category', 'payments']);

        if ($categoryId) {
            $agreementsQuery->whereHas('sale.items.item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($search) {
             $agreementsQuery->search($search);
        }

        $allAgreements = $agreementsQuery->get();

        $reportData = collect();
        $categorySummary = [];
        
        $globalTotalLoan = 0;
        $globalTotalPaid = 0;

        foreach ($allAgreements as $agreement) {
            $totalPaid = round($agreement->payments->sum('amount'), 2);
            $initialLoanBalance = round($agreement->total_invoice_value - $agreement->down_payment_amount, 2);
            $outstandingAmount = round($initialLoanBalance - $totalPaid, 2);

            // Accumulate global totals (respecting current filters)
            $globalTotalLoan += $initialLoanBalance;
            $globalTotalPaid += $totalPaid;

            // Category tracking for summary - Include ALL agreements for financial consistency
            $itemCategory = $agreement->sale?->items?->first()?->item?->category?->name ?? 'Uncategorized';
            $itemCategoryId = $agreement->sale?->items?->first()?->item?->category_id ?? 0;

            if (!isset($categorySummary[$itemCategoryId])) {
                $categorySummary[$itemCategoryId] = [
                    'id' => $itemCategoryId,
                    'name' => $itemCategory,
                    'count' => 0,
                    'loan_amount' => 0,
                    'paid_amount' => 0,
                    'outstanding_amount' => 0,
                ];
            }
            
            // Only increment count if it has outstanding balance
            if ($outstandingAmount > 0.009) {
                $categorySummary[$itemCategoryId]['count']++;
            }
            
            $categorySummary[$itemCategoryId]['loan_amount'] += $initialLoanBalance;
            $categorySummary[$itemCategoryId]['paid_amount'] += $totalPaid;
            $categorySummary[$itemCategoryId]['outstanding_amount'] += $outstandingAmount;

            // Skip if already paid off for the detailed report list
            if ($outstandingAmount <= 0.009) {
                continue;
            }

            $paidQuantity = $agreement->monthly_installment_amount > 0 
                ? floor(round($totalPaid / $agreement->monthly_installment_amount, 5)) 
                : 0;

            $dueQuantity = $agreement->monthly_installment_amount > 0 
                ? ceil(round($outstandingAmount / $agreement->monthly_installment_amount, 5)) 
                : 0;

            $reportData->push([
                'customer_id' => $agreement->customer_id,
                'customer_name' => $agreement->customer->name ?? 'N/A',
                'phone' => $agreement->customer->phone ?? 'N/A',
                'guarantor_name' => $agreement->guarantor_name ?? 'N/A',
                'guarantor_phone' => $agreement->guarantor_mobile_number ?? 'N/A',
                'premium' => $agreement->monthly_installment_amount,
                'loan_amount' => $initialLoanBalance,
                'total_paid' => $totalPaid,
                'paid_quantity' => (int)$paidQuantity,
                'due_quantity' => (int)$dueQuantity,
                'outstanding_amount' => $outstandingAmount,
                'emi_number' => $agreement->combined_emi_numbers,
            ]);
        }

        return [
            'reportData' => $reportData->sortBy('customer_name')->values(),
            'summary' => [
                'total_loan_amount' => round($globalTotalLoan, 2),
                'total_paid_amount' => round($globalTotalPaid, 2),
                'total_outstanding_amount' => round($globalTotalLoan - $globalTotalPaid, 2),
            ],
            'categorySummary' => collect($categorySummary)->sortByDesc('outstanding_amount')->values()
        ];
    }


    public function salesCategoryPrint(Request $request)
    {
        return $this->export($request->merge(['format' => 'pdf', 'report_type' => 'category']), 'sales');
    }

    public function salesItemPrint(Request $request)
    {
        return $this->export($request->merge(['format' => 'pdf', 'report_type' => 'item']), 'sales');
    }
    
      private function getEstimateReportData($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
            $agreements = InstallmentAgreement::where('first_due_date', '<=', $end)
       
            ->with(['customer', 'payments', 'sale.items.item'])
            ->get();

        $reportData = collect();

        foreach ($agreements as $agreement) {
           
            $premiumQuantity = 0;
            $emiNumbers = [];
            $latestDueDate = null;

             $totalContractualInstallments = $agreement->total_installments;

            for ($i = 0; $i < $totalContractualInstallments; $i++) {
                $dueDate = Carbon::parse($agreement->first_due_date)->addMonths($i);
                $dueDate = $agreement->applyDueDayOfMonth($dueDate);
                
                $emiNumber = $i + 1;
              
                // Only count installments due within the selected range
                if ($dueDate->between($start, $end)) {
                    $premiumQuantity++;
                    $emiNumbers[] = $emiNumber;
                    $latestDueDate = $dueDate;
                }

                if ($dueDate->isAfter($end)) {
                    break;
                }
            }

            if ($premiumQuantity > 0) {
              
                  $totalValue = $premiumQuantity * $agreement->monthly_installment_amount;
                $reportData->push([
                    'customer_name' => $agreement->customer->name ?? 'N/A',
                    'phone' => $agreement->customer->phone ?? 'N/A',
                    'guarantor_name' => $agreement->guarantor_name ?? 'N/A',
                    'guarantor_phone' => $agreement->guarantor_mobile_number ?? 'N/A',
                    'due_date' => $latestDueDate ? $latestDueDate->toDateString() : 'N/A',
                    'premium' => $totalValue,
                    'premium_quantity' => $premiumQuantity,
                    'emi_numbers' => implode(', ', $emiNumbers),
                    'balance' => $agreement->balance_amount,
                    'combined_emi_numbers' => $agreement->combined_emi_numbers,
                ]);
            }
        }
        return $reportData->sortBy('due_date');
    }

    public function estimateReportPrint(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $selectedMonth = $request->get('month');

        if ($selectedMonth && !$startDate && !$endDate) {
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');
        } elseif (!$startDate && !$endDate) {
            $selectedMonth = now()->format('Y-m');
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }

        $reportData = $this->getEstimateReportData($startDate, $endDate);
        return view('admin.reports.estimate-print', compact('reportData', 'selectedMonth', 'startDate', 'endDate'));
    }

    public function estimateReport(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $selectedMonth = $request->get('month');

        if ($selectedMonth && !$startDate && !$endDate) {
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');
        } elseif (!$startDate && !$endDate) {
            $selectedMonth = now()->format('Y-m');
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }

        $reportData = $this->getEstimateReportData($startDate, $endDate);
        return view('admin.reports.estimate', compact('reportData', 'selectedMonth', 'startDate', 'endDate'));
    }

    public function estimateCollectionReport(Request $request)
    {
        $data = $this->getEstimateCollectionData($request);
        return view('admin.reports.estimate-collection', $data);
    }

    public function estimateCollectionReportPrint(Request $request)
    {
        $data = $this->getEstimateCollectionData($request);
        return view('admin.reports.estimate-collection-print', $data);
    }

    private function getEstimateCollectionData(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $selectedMonth = $request->get('month');
        $statusFilter = $request->get('status'); // paid_off, partial, not_paid

        if ($selectedMonth && !$startDate && !$endDate) {
            $startDate = Carbon::parse($selectedMonth . '-01')->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');
        } elseif (!$startDate && !$endDate) {
            $selectedMonth = now()->format('Y-m');
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $agreements = InstallmentAgreement::where('first_due_date', '<=', $end)
            ->with(['customer', 'payments', 'sale.items.item'])
            ->get();

        $reportData = collect();
        $allStatuses = collect();

        foreach ($agreements as $agreement) {
            $premiumQuantity = 0;
            $emiNumbers = [];
            $latestDueDate = null;
            $estimatedValue = 0;
            $actualReceived = 0;

            $totalContractualInstallments = $agreement->total_installments;
            $monthlyAmount = (float)$agreement->monthly_installment_amount;
            $totalPaidSoFar = (float)$agreement->payments->sum('amount');

            for ($i = 0; $i < $totalContractualInstallments; $i++) {
                $dueDate = Carbon::parse($agreement->first_due_date)->addMonths($i);
                $dueDate = $agreement->applyDueDayOfMonth($dueDate);
                
                // Calculate how much of this specific installment is paid using FIFO
                $installmentPaid = min($monthlyAmount, max(0, $totalPaidSoFar - ($i * $monthlyAmount)));

                // Only count installments due within the selected range
                if ($dueDate->between($start, $end)) {
                    $premiumQuantity++;
                    $emiNumbers[] = $i + 1;
                    $latestDueDate = $dueDate;
                    $estimatedValue += $monthlyAmount;
                    $actualReceived += $installmentPaid;
                }

                if ($dueDate->isAfter($end)) {
                    break;
                }
            }

            if ($premiumQuantity > 0) {
                $rowStatus = 'not_paid';
                if ($actualReceived >= $estimatedValue - 0.01) {
                    $rowStatus = 'paid_off';
                } elseif ($actualReceived > 0.01) {
                    $rowStatus = 'partial';
                }

                $allStatuses->push($rowStatus);

                // Filter by status if requested
                if ($statusFilter && $rowStatus !== $statusFilter) {
                    continue;
                }

                $reportData->push([
                    'agreement_id' => $agreement->id,
                    'customer_name' => $agreement->customer->name ?? 'N/A',
                    'phone' => $agreement->customer->phone ?? 'N/A',
                    'guarantor_name' => $agreement->guarantor_name ?? 'N/A',
                    'guarantor_phone' => $agreement->guarantor_mobile_number ?? 'N/A',
                    'due_date' => $latestDueDate ? $latestDueDate->toDateString() : 'N/A',
                    'estimated_value' => $estimatedValue,
                    'actual_received' => $actualReceived,
                    'premium_quantity' => $premiumQuantity,
                    'emi_numbers' => implode(', ', $emiNumbers),
                    'balance' => $agreement->balance_amount,
                    'combined_emi_numbers' => $agreement->combined_emi_numbers,
                    'status' => $rowStatus,
                ]);
            }
        }

        $reportData = $reportData->sortBy('due_date');
        
        $counts = [
            'total' => $allStatuses->count(),
            'paid_off' => $allStatuses->filter(fn($s) => $s === 'paid_off')->count(),
            'partial' => $allStatuses->filter(fn($s) => $s === 'partial')->count(),
            'not_paid' => $allStatuses->filter(fn($s) => $s === 'not_paid')->count(),
        ];

        return [
            'reportData' => $reportData,
            'selectedMonth' => $selectedMonth,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'counts' => $counts,
            'statusFilter' => $statusFilter
        ];
    }

      public function paidOffAgreements(Request $request)
    {
        $search = trim($request->get('search'));
        $categoryId = $request->get('category_id');

        $query = InstallmentAgreement::where(function($q) {
                $q->where('status', 'paid_off')
                  ->orWhere('balance_amount', '<=', 0.009);
            })
            ->with(['customer', 'sale.items.item.category', 'payments']);

        if ($search) {
            $query->search($search);
        }

        if ($categoryId) {
            $query->whereHas('sale.items.item', function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $agreements = $query->get()->map(function($agreement) {
            $totalPaid = $agreement->payments->sum('amount');
            $totalFinePaid = $agreement->payments->sum('fine_amount');
            $totalInterest = $agreement->interest_service_charge;
            
            return [
                'id' => $agreement->id,
                'customer_id' => $agreement->customer_id,
                'customer_name' => $agreement->customer->name ?? 'N/A',
                'phone' => $agreement->customer->phone ?? 'N/A',
                'nic' => $agreement->customer->nic ?? 'N/A',
                'emi_number' => $agreement->combined_emi_numbers,
                'total_invoice_value' => $agreement->total_invoice_value,
                'down_payment' => $agreement->down_payment_amount,
                'interest_amount' => $totalInterest,
                'fine_paid' => $totalFinePaid,
                'total_collection' => $agreement->down_payment_amount + $totalPaid + $totalFinePaid,
                'paid_off_date' => $agreement->payments->max('payment_date'),
            ];
        })->sortByDesc('paid_off_date');

        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.reports.paid-off-agreements', compact('agreements', 'search', 'categories', 'categoryId'));
    }



}
