@extends('layouts.app')
@section('title', 'View installments')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading text-center">
                    <h4>Installment details</h4>
                </div>
                <div class="panel-body">
                    <div class="installment-top">
                        <div class="installment-info">
                            <div class="line">
                                <div class="line-label">Commodity orders:</div>
                                <div class="line-value">
                                    <a target="_blank" href="{{ route('orders.show', ['order' => $installment->order_id]) }}">View</a>
                                </div>
                            </div>
                            <div class="line">
                                <div class="line-label">Installment amount:</div>
                                <div class="line-value">${{ $installment->total_amount }}</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Instalment period:</div>
                                <div class="line-value">{{ $installment->count }}期</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Installment rate:</div>
                                <div class="line-value">{{ $installment->fee_rate }}%</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Overdue rate:</div>
                                <div class="line-value">{{ $installment->fine_rate }}%</div>
                            </div>
                            <div class="line">
                                <div class="line-label">Current status:</div>
                                <div class="line-value">{{ \App\Models\Installment::$statusMap[$installment->status] }}</div>
                            </div>
                        </div>
                        <div class="installment-next text-right">
                            
                            @if(is_null($nextItem))
                                <div class="installment-clear text-center">This order has been closed</div>
                            @else
                                <div>
                                    <span>I look forward to:</span>
                                    <div class="value total-amount">${{ $nextItem->total }}</div>
                                </div>
                                <div>
                                    <span>deadline:</span>
                                    <div class="value">{{ $nextItem->due_date->format('Y-m-d') }}</div>
                                </div>
                                <div class="payment-buttons">
                                    <a class="btn btn-primary btn-sm" href="{{ route('installments.alipay', ['installment' => $installment->id]) }}">Pay with Ali-Pay</a>
                                    <button class="btn btn-sm btn-success" id='btn-wechat'>WeChat Pay</button>
                                </div>
                            @endif
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Period</th>
                            <th>Repayment deadline</th>
                            <th>Status</th>
                            <th>Principal</th>
                            <th>Handling fee</th>
                            <th>Overdue fee</th>
                            <th class="text-right">小计</th>
                        </tr>
                        </thead>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    {{ $item->sequence + 1 }}/{{ $installment->count }}期
                                </td>
                                <td>{{ $item->due_date->format('Y-m-d') }}</td>
                                <td>
                                    
                                @if(is_null($item->paid_at))
                                    
                                        @if($item->is_overdue)
                                            <span class="overdue">Past due</span>
                                        @else
                                            <span class="needs-repay">Pending repayment</span>
                                        @endif
                                    @else
                                        <span class="repaid">Repaid</span>
                                    @endif
                                </td>
                                <td>${{ $item->base }}</td>
                                <td>${{ $item->fee }}</td>
                                <td>{{ is_null($item->fine) ? 'no' : ('$'.$item->fine) }}</td>
                                <td class="text-right">${{ $item->total }}</td>
                            </tr>
                        @endforeach
                        <tr><td colspan="7"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
    <script>
        $(document).ready(function() {
            $('#btn-wechat').click(function() {
                swal({
                    content: $('<img src="{{ route('installments.wechat', ['installment' => $installment->id]) }}" />')[0],
                    $buttons: ['shut down', 'Payment completed'],
                })
                    .then(function(result) {
                        $if (result) {
                            location.reload();
                        }
                    })
            });
        });
    </script>
@endsection