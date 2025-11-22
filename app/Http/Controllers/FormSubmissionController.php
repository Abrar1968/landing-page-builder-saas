<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\Page;
use App\Notifications\FormSubmissionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormSubmissionController extends Controller
{
    public function index(Page $page): View
    {
        $this->authorize('view', $page);
        
        $submissions = $page->formSubmissions()->latest()->paginate(20);
        
        return view('dashboard.submissions.index', compact('page', 'submissions'));
    }

    public function store(Request $request, Page $page): JsonResponse
    {
        $submission = FormSubmission::create([
            'page_id' => $page->id,
            'form_id' => $request->input('form_id'),
            'data' => $request->except(['_token', 'form_id']),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Send notification to page owner
        $page->user->notify(new FormSubmissionNotification($submission));

        return response()->json([
            'success' => true,
            'message' => 'Form submitted successfully',
        ]);
    }

    public function show(FormSubmission $submission): JsonResponse
    {
        $this->authorize('view', $submission->page);
        
        $submission->markAsRead();
        
        return response()->json([
            'success' => true,
            'submission' => $submission,
        ]);
    }

    public function destroy(FormSubmission $submission): JsonResponse
    {
        $this->authorize('delete', $submission->page);
        
        $submission->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Submission deleted',
        ]);
    }

    public function export(Page $page): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $page);
        
        $submissions = $page->formSubmissions()->latest()->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="submissions-' . $page->slug . '.csv"',
        ];
        
        return response()->stream(function () use ($submissions) {
            $handle = fopen('php://output', 'w');
            
            // Get all unique keys from submissions
            $keys = $submissions->flatMap(fn($s) => array_keys($s->data))->unique()->values()->toArray();
            
            // Header row
            fputcsv($handle, array_merge(['Submitted At', 'IP Address'], $keys));
            
            // Data rows
            foreach ($submissions as $submission) {
                $row = [
                    $submission->created_at->toDateTimeString(),
                    $submission->ip_address,
                ];
                
                foreach ($keys as $key) {
                    $row[] = $submission->data[$key] ?? '';
                }
                
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, 200, $headers);
    }
}
