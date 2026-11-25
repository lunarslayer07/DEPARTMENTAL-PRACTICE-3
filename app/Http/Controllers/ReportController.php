<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\BookReport;
use App\Services\InventoryExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, BookReport $report): View
    {
        $filters = $report->validateFilters($request->only(['category', 'availability_status']));
        $books = $report->query($request->user(), $filters)->get();

        return view('reports.index', [
            'books' => $books,
            'filters' => $filters,
            'categories' => Book::CATEGORIES,
            'availabilityStatuses' => Book::AVAILABILITY_STATUSES,
        ]);
    }

    public function pdf(Request $request, BookReport $report, InventoryExport $export): Response
    {
        $books = $report->query($request->user(), $report->validateFilters($request->only(['category', 'availability_status'])))->get();

        return response($export->pdf($books), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="book-inventory-report.pdf"',
        ]);
    }

    public function excel(Request $request, BookReport $report, InventoryExport $export): Response
    {
        $books = $report->query($request->user(), $report->validateFilters($request->only(['category', 'availability_status'])))->get();

        return response($export->xlsx($books), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="book-inventory-report.xlsx"',
        ]);
    }
}
