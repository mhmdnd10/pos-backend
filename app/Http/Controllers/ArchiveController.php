<?php

namespace App\Http\Controllers;

use App\Services\ArchiveService;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    protected $archiveService;
    public function __construct(ArchiveService $archiveService)
    {
        $this->archiveService = $archiveService;
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $archives = $this->archiveService->archiveClosedMonth($data['month'] ?? null);
        return response()->json($archives, 200);
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $archives = $this->archiveService->getArchivesByMonth($data['month']);

        return response()->json($archives, 200);
    }
}
