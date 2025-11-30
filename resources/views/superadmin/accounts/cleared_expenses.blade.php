@extends('superadmin.layouts.app')

@section('title', 'Cleared Expenses')

@section('content')
<div class="card mt-3">
    <div class="page-header">
        <div class="add-item d-flex ms-4 mt-4">
            <div class="page-title">
                <h4>Cleared Expenses</h4>
                <h6 class="text-muted">Generated PDFs when Approver clicked "In Account"</h6>
            </div>
        </div>
    </div>

    <div class="card-header">
        <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="marketing_person_code" class="form-select form-select-sm">
                    <option value="">All persons</option>
                    @foreach(($persons ?? collect()) as $p)
                        <option value="{{ $p->user_code }}" {{ (isset($selected_person) && $selected_person == $p->user_code) ? 'selected' : '' }}>{{ $p->name }}{{ $p->user_code ? ' ('.$p->user_code.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="month" class="form-select form-select-sm">
                    <option value="">All months</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ (isset($selected_month) && (int)$selected_month === $m) ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm">
                    <option value="">All years</option>
                    @foreach(range(date('Y'), date('Y') - 5) as $y)
                        <option value="{{ $y }}" {{ (isset($selected_year) && (int)$selected_year === (int)$y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
            <div class="col-auto">
                <select name="cleared_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([10,15,25,50,100] as $pp)
                        <option value="{{ $pp }}" {{ (isset($selected_cleared_per_page) && (int)$selected_cleared_per_page === $pp) ? 'selected' : '' }}>{{ $pp }} rows</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="card-body">
        @if($items->isEmpty())
            <div class="alert alert-info">No cleared expenses found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name / File</th>
                            <th class="text-end">Total Approved Amount</th>
                            <th>Approver</th>
                            <th>Generated</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $it)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if(!empty($it['display_name']))
                                        {{ $it['display_name'] }}
                                        @if(empty($it['meta']['hide_from_personal']) && !empty($it['meta']['person_code']))
                                            ({{ $it['meta']['person_code'] }})
                                        @endif
                                        <div class="muted small">{{ $it['filename'] }}</div>
                                    @else
                                        {{ $it['approved_section'] ? ucfirst($it['approved_section']) : '' }} {{ $it['filename'] }}
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float) ($it['approved_total'] ?? 0), 2) }}</td>
                                <td>{{ $it['approver_name'] ?? '-' }}</td>
                                <td>{{ $it['created_at'] ?? '-' }}</td>
                                <td>
                                    @php $pdfUrl = asset('storage/' . $it['path']); @endphp
                                    <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">Open PDF</a>
                                    <a href="{{ $pdfUrl }}" download class="btn btn-sm btn-primary">Download</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-end">
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
