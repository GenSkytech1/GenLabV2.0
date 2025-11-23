<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class VoucherController extends Controller
{
    public function index()
    {
        $users = User::select('id','name')->get();
        $vouchers = Voucher::with(['user','creator','approver'])->orderBy('created_at', 'desc')->get();
        return view('superadmin.accounts.vouchers.create', compact('users','vouchers'));
    }

    public function create()
    {
        $users = User::select('id','name')->get();
        $vouchers = Voucher::with(['user','creator','approver'])->orderBy('created_at', 'desc')->get();
        return view('superadmin.accounts.vouchers.create', compact('users','vouchers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric',
            'purpose' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('vouchers', 'public');
            $data['attachment'] = $path;
        }

        $data['status'] = 'pending';
        $data['created_by'] = Auth::id();

        Voucher::create($data);

        return redirect()->route('superadmin.vouchers.create')->with('success', 'Voucher created and sent for approval.');
    }

    public function edit(Voucher $voucher)
    {
        $users = User::select('id','name')->get();
        $vouchers = Voucher::with(['user','creator','approver'])->orderBy('created_at', 'desc')->get();
        return view('superadmin.accounts.vouchers.create', compact('voucher','users','vouchers'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric',
            'purpose' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($voucher->status !== 'pending') {
            return back()->with('error', 'Only pending vouchers can be edited.');
        }

        if ($request->hasFile('attachment')) {
            // delete old file if exists
            if ($voucher->attachment) {
                Storage::disk('public')->delete($voucher->attachment);
            }
            $path = $request->file('attachment')->store('vouchers', 'public');
            $data['attachment'] = $path;
        }

        $voucher->update($data);

        return redirect()->route('superadmin.vouchers.create')->with('success', 'Voucher updated.');
    }

    public function destroy(Voucher $voucher)
    {
        if ($voucher->status !== 'pending') {
            return back()->with('error', 'Only pending vouchers can be deleted.');
        }

        // remove uploaded file if present
        if ($voucher->attachment) {
            Storage::disk('public')->delete($voucher->attachment);
        }

        $voucher->delete();
        return back()->with('success', 'Voucher deleted.');
    }

    public function approveIndex()
    {
        $pending = Voucher::with('user')->where('status', 'pending')->orderBy('created_at','desc')->get();
        $processed = Voucher::with(['user','approver'])->whereIn('status', ['approved','rejected'])->orderBy('created_at','desc')->get();
        return view('superadmin.accounts.vouchers.approve', compact('pending','processed'));
    }

    public function exportPdf(Request $request)
    {
        $query = Voucher::with(['user','approver']);
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($year = $request->input('year')) { $query->whereYear('created_at', $year); }
        if ($month = $request->input('month')) { $query->whereMonth('created_at', $month); }
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search){
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search){
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $vouchers = $query->orderByDesc('created_at')->get();

        $pdf = Pdf::loadView('superadmin.accounts.vouchers.export_pdf', ['vouchers' => $vouchers])->setPaper('a4', 'portrait');
        $filename = 'vouchers-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $query = Voucher::with(['user','approver']);
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($year = $request->input('year')) { $query->whereYear('created_at', $year); }
        if ($month = $request->input('month')) { $query->whereMonth('created_at', $month); }
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search){
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search){
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $vouchers = $query->orderByDesc('created_at')->get();

        $columns = ['ID','User','Amount','Purpose','Attachment','Status','Created At','Approved By'];
        $rows = [];
        foreach($vouchers as $v){
            $rows[] = [
                $v->id,
                optional($v->user)->name,
                number_format((float)$v->amount, 2),
                $v->purpose,
                $v->attachment ? asset('storage/' . $v->attachment) : '-',
                $v->status,
                $v->created_at->format('Y-m-d H:i'),
                optional($v->approver)->name ?? '-',
            ];
        }

        $filename = 'vouchers-' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($columns, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach($rows as $row) fputcsv($file, $row);
            fclose($file);
        };

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Generate a professional voucher PDF for an approved voucher.
     */
    public function generate(Voucher $voucher)
    {
        if ($voucher->status !== 'approved') {
            return back()->with('error', 'Only approved vouchers can be printed.');
        }

        $voucher->load(['user','approver','creator']);

        $pdf = Pdf::loadView('superadmin.accounts.vouchers.voucher_template', [
            'voucher' => $voucher,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('voucher_%s_%s.pdf', $voucher->id, now()->format('Ymd_His'));
        // Stream the PDF so it opens in the browser (preview tab) instead of forcing download
        return $pdf->stream($filename);
    }

    public function approve(Voucher $voucher)
    {
        if ($voucher->status !== 'pending') {
            return back()->with('error', 'Voucher already processed.');
        }
        $voucher->update(['status' => 'approved', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Voucher approved.');
    }

    public function reject(Voucher $voucher)
    {
        if ($voucher->status !== 'pending') {
            return back()->with('error', 'Voucher already processed.');
        }
        $voucher->update(['status' => 'rejected', 'approved_by' => Auth::id()]);
        return back()->with('success', 'Voucher rejected.');
    }

    /**
     * Update payment status for a processed (approved) voucher.
     */
    public function payment(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'payment_status' => 'required|in:paid,unpaid'
        ]);

        if ($voucher->status !== 'approved') {
            return back()->with('error', 'Only approved vouchers can have payment status updated.');
        }

        $voucher->update(['payment_status' => $data['payment_status']]);

        return back()->with('success', 'Payment status updated.');
    }
}
