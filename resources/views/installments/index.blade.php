@extends('layouts.app')
@section('title', 'Installment list')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading text-center"><h2>Installment list</h2></div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Numbering</th>
                            <th>Amount</th>
                            <th>Period</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Operating</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($installments as $installment)
                            <tr>
                                <td>{{ $installment->no }}</td>
                                <td>${{ $installment->total_amount }}</td>
                                <td>{{ $installment->count }}</td>
                                <td>{{ $installment->fee_rate }}%</td>
                                <td>{{ \App\Models\Installment::$statusMap[$installment->status] }}</td>
                                <td><a class="btn btn-primary btn-xs" href="{{ route('installments.show', ['installment' => $installment->id]) }}">View</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="pull-right">{{ $installments->render() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection