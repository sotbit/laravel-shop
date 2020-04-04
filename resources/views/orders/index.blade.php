@extends('layouts.app')
@section('title', 'Order List')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Order List</div>
                <div class="panel-body">
                    <ul class="list-group">
                        @foreach($orders as $order)
                            <li class="list-group-item">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        order number: {{ $order->no }}
                                        <span class="pull-right">{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>Product information</th>
                                                <th class="text-center">Unit price</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-center">Total order price</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Operating</th>
                                            </tr>
                                            </thead>
                                            @foreach($order->items as $index => $item)
                                                <tr>
                                                    <td class="product-info">
                                                        <div class="preview">
                                                            <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                                                <img src="{{ $item->product->image_url }}">
                                                            </a>
                                                        </div>
                                                        <div>
                                                            <span class="product-title">
                                                               <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
                                                            </span>
                                                            <span class="sku-title">{{ $item->productSku->title }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="sku-price text-center">${{ $item->price }}</td>
                                                    <td class="sku-amount text-center">{{ $item->amount }}</td>
                                                    @if($index === 0)
                                                        <td rowspan="{{ count($order->items) }}" class="text-center total-amount">${{ $order->total_amount }}</td>
                                                        <td rowspan="{{ count($order->items) }}" class="text-center">
                                                            @if($order->paid_at)
                                                                @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                                                    Paid
                                                                @else
                                                                    {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                                                @endif
                                                            @elseif($order->closed)
                                                                Closed
                                                            @else
                                                                unpaid<br>
                                                                Please {{ $order->created_at->addSeconds(config('app.order_ttl'))->format('H:i') }} Complete payment before<br>
                                                                Otherwise the order will be closed automatically
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ count($order->items) }}" class="text-center">
                                                            <a class="btn btn-primary btn-xs" href="{{ route('orders.show', ['order' => $order->id]) }}">check order</a>
                                                            
                                                            @if($order->paid_at)
                                                            <a class="btn btn-success btn-xs" href="{{ route('orders.review.show', ['order' => $order->id]) }}">
                                                                {{ $order->reviewed ? 'View reviews' : 'Evaluation' }}
                                                            </a>
                                                            @endif
                                                            
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="pull-right">{{ $orders->render() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection