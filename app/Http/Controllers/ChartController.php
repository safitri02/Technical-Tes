<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public function summary(Request $request)
    {
        $type = $request->get('type');

        $query = Todo::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assignee')) {
            $query->where('assignee', $request->assignee);
        }

        if ($type === 'assignee') {
            $filteredTodos = $query->get()->groupBy('assignee');
        
            $summary = [];
        
            foreach ($filteredTodos as $assignee => $todos) {
                $totalTodos = $todos->count();
                $totalPending = $todos->where('status', 'in_progress')->count();
                $totalTimeTrackedCompleted = $todos->where('status', 'completed')->sum('time_tracked');
        
                $summary[$assignee] = [
                    'total_todos' => $totalTodos,
                    'total_pending_todos' => $totalPending,
                    'total_timetracked_completed_todos' => $totalTimeTrackedCompleted,
                ];
            }
        
            return response()->json(['assignee_summary' => $summary]);
        }
        
        

        if ($type === 'status') {
            $summary = $query->selectRaw('status, COUNT(*) as total')
                             ->groupBy('status')
                             ->pluck('total', 'status');

            $data = [
                'pending' => $summary['pending'] ?? 0,
                'open' => $summary['open'] ?? 0,
                'in_progress' => $summary['in_progress'] ?? 0,
                'completed' => $summary['completed'] ?? 0,
            ];

            return response()->json(['status_summary' => $data]);
        }

        if ($type === 'priority') {
            $summary = $query->selectRaw('priority, COUNT(*) as total')
                             ->groupBy('priority')
                             ->pluck('total', 'priority');

            $data = [
                'low' => $summary['low'] ?? 0,
                'medium' => $summary['medium'] ?? 0,
                'high' => $summary['high'] ?? 0,
            ];

            return response()->json(['priority_summary' => $data]);
        }

        return response()->json(['error' => 'Invalid type'], 400);
    }

}
