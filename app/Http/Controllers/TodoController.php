<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use App\Models\Todo;

class TodoController extends Controller
{

    public function store(Request $request){

        //Untuk validasi
        $validator = Validator($request->all(), [
            'title'     => 'required|string|max:225',
            'assignee'     => 'required|string|max:255',
            'due_date'     => 'required|date',
            'time_tracked' => 'required|numeric|min:0',
            'status'       => 'required|in:pending,open,in_progress,completed',
            'priority'     => 'required|in:low,medium,high',
        ]);

        //jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        //Tambahkan ke database
        $todo = Todo::create($request->only([
              'title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority'
         ]));


         //Response berhasil
         return response()->json([
            'status' => 'success',
            'data'   => $todo
        ], 201);

    }

    public function exportExcel(Request $request)
    {
        $query = Todo::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $todos = $query->get([
            'title', 'assignee', 'due_date', 'time_tracked', 'status', 'priority', 'created_at', 'updated_at'
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Title', 'Assignee', 'Due Date', 'Time Tracked', 'Status', 'Priority', 'Created At', 'Updated At'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($todos as $todo) {
            $sheet->fromArray([
                $todo->title,
                $todo->assignee,
                $todo->due_date,
                $todo->time_tracked,
                $todo->status,
                $todo->priority,
                $todo->created_at,
                $todo->updated_at,
            ], null, "A{$row}");
            $row++;
        }

        $fileName = 'todos_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }


}
