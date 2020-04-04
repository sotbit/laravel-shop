@extends('layouts.app')
@section('title', $product->title)

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-body product-info">
                    <div class="row">
                        <div class="col-sm-5">
                            <img class="cover" src="{{ $product->image_url }}" alt="">
                        </div>
                        <div class="col-sm-7">
                            <div class="title">{{ $product->long_title ?: $product->title }}</div>


                            @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)

                                <div class="crowdfunding-info">
                                    <div class="have-text">Raised</div>
                                    <div class="total-amount"><span class="symbol">￥</span>{{ $product->crowdfunding->total_amount }}</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-success progress-bar-striped"
                                             role="progressbar"
                                             aria-valuenow="{{ $product->crowdfunding->percent }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="min-width: 1em; width: {{ min($product->crowdfunding->percent, 100) }}%">
                                        </div>
                                    </div>
                                    <div class="progress-info">
                                        <span class="current-progress">Current progress:{{ $product->crowdfunding->percent }}%</span>
                                        <span class="pull-right user-count">{{ $product->crowdfunding->user_count }}Supporters</span>
                                    </div>

                                    @if ($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                        <div>This item must be
                                            <span class="text-red">{{ $product->crowdfunding->end_at->format('Y-m-d H:i:s') }}</span>
                                            Get before
                                            <span class="text-red">￥{{ $product->crowdfunding->target_amount }}</span>
                                            Support can be successful,
                                            Fundraising will be at<span class="text-red">{{ $product->crowdfunding->end_at->diffForHumans(now()) }}</span>End!
                                        </div>
                                    @endif
                                </div>

                            @else

                                <div class="price"><label>price</label><em>￥</em><span>{{ $product->price }}</span></div>
                                <div class="sales_and_reviews">
                                    <div class="sold_count">Cumulative sales <span class="count">{{ $product->sold_count }}</span></div>
                                    <div class="review_count">Cumulative evaluation <span class="count">{{ $product->review_count }}</span></div>
                                    <div class="rating" title="score {{ $product->rating }}">score <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
                                </div>

                            @endif

                            <div class="skus">
                                <label>select</label>
                                <div class="btn-group" data-toggle="buttons">
                                    @foreach($product->skus as $sku)
                                        <label class="btn btn-default sku-btn"
                                                data-price="{{ $sku->price }}"
                                                data-stock="{{ $sku->stock }}"
                                                data-toggle="tooltip"
                                                title="{{ $sku->description }}"
                                                data-placement="bottom">
                                            <input type="radio" name="skus" autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="cart_amount"><label>Quantity</label><input type="text" class="form-control input-sm" value="1"><span>件</span><span class="stock"></span></div>
                            <div class="buttons">
                                @if($favored)
                                    <button class="btn btn-danger btn-disfavor">Uncollect</button>
                                @else
                                    <button class="btn btn-success btn-favor">❤ Collect</button>
                                @endif

                                @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                                    @if(Auth::check())
                                        @if($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                                            <button class="btn btn-primary btn-crowdfunding">Participate in crowdfunding</button>
                                        @else
                                            <button class="btn btn-primary disabled">
                                                {{ \App\Models\CrowdfundingProduct::$statusMap[$product->crowdfunding->status] }}
                                            </button>
                                        @endif
                                    @else
                                        <a class="btn btn-primary" href="{{ route('login') }}">please log in first</a>
                                    @endif
                                @elseif($product->type === \App\Models\Product::TYPE_SECKILL)
                                    @if(Auth::check())
                                        @if($product->seckill->is_before_start)
                                            <button class="btn btn-primary btn-seckill disabled countdown">Countdown to snapping up</button>
                                        @elseif($product->seckill->is_after_end)
                                            <button class="btn btn-primary btn-seckill disabled">Panic buying has ended</button>
                                        @else
                                            <button class="btn btn-primary btn-seckill">Snap up now</button>
                                        @endif
                                    @else
                                        <a class="btn btn-primary" href="{{ route('login') }}">please log in first</a>
                                    @endif
                                @else
                                    <button class="btn btn-primary btn-add-to-cart">add to Shopping Cart</button>
                                @endif

                            </div>
                        </div>
                    </div>
                    <div class="product-detail">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab">product details</a></li>
                            <li role="presentation"><a href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab">用户评价</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
                                
                                <div class="properties-list">
                                    <div class="properties-list-title">Product parameters:</div>
                                    <ul class="properties-list-body">
                                        @foreach($product->grouped_properties as $name => $values)
                                            <li>{{ $name }}：{{ join(' ', $values) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="product-description">
                                    {!! $product->description !!}
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <td>user</td>
                                        <td>Product</td>
                                        <td>score</td>
                                        <td>Evaluation</td>
                                        <td>time</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($reviews as $review)
                                        <tr>
                                            <td>{{ $review->order->user->name }}</td>
                                            <td>{{ $review->productSku->title }}</td>
                                            <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                            <td>{{ $review->review }}</td>
                                            <td>{{ $review->reviewed_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    
                    @if(count($similar) > 0)
                    <div class="similar-products">
                        <div class="title">you may also like</div>
                        <div class="row products-list">
                            @foreach($similar as $product)
                                <div class="col-xs-3 product-item">
                                    <div class="product-content">
                                        <div class="top">
                                            <div class="img">
                                                <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                                    <img src="{{ $product->image_url }}" alt="">
                                                </a>
                                            </div>
                                            <div class="price"><b>￥</b>{{ $product->price }}</div>
                                            <div class="title">
                                                <a href="{{ route('products.show', ['product' => $product->id]) }}">{{ $product->title }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')

    @if($product->type == \App\Models\Product::TYPE_SECKILL && $product->seckill->is_before_start)
        <script src="https:
    @endif

    <script>
        $(document).ready(function () {

            
            $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
            $('.sku-btn').click(function () {
                $('.product-info .price span').text($(this).data('price'));
                $('.product-info .stock').text('库存：' + $(this).data('stock') + '件');
            });

            
            @if($product->type == \App\Models\Product::TYPE_SECKILL && $product->seckill->is_before_start)
            
            var startTime = moment.unix({{ $product->seckill->start_at->getTimestamp() }});
            
            var hdl = setInterval(function () {
                
                var now = moment();
                
                if (now.isAfter(startTime)) {
                    
                    $('.btn-seckill').removeClass('disabled').removeClass('countdown').text('立即抢购');
                    
                    clearInterval(hdl);
                    return;
                }

                
                var hourDiff = startTime.diff(now, 'hours');
                var minDiff = startTime.diff(now, 'minutes') % 60;
                var secDiff = startTime.diff(now, 'seconds') % 60;
                
                $('.btn-seckill').text('Countdown to snapping up '+hourDiff+':'+minDiff+':'+secDiff);
            }, 500);
            @endif

            
            $('.btn-seckill').click(function () {
                
                if($(this).hasClass('disabled')) {
                    return;
                }
                if (!$('label.active input[name=skus]').val()) {
                    swal('Please select the product first');
                    return;
                }
                
                var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};
                
                var addressSelector = $('<select class="form-control"></select>');
                
                addresses.forEach(function (address) {
                    
                    addressSelector.append("<option value='" + address.id + "'>" + address.full_address + ' ' + address.contact_name + ' ' + address.contact_phone + '</option>');
                });
                
                swal({
                    text: 'Choose delivery address',
                    content: addressSelector[0],
                    buttons: ['cancel', 'determine']
                }).then(function (ret) {
                    
                    if (!ret) {
                        return;
                    }
                    
                    var req = {
                        address_id: addressSelector.val(),
                        sku_id: $('label.active input[name=skus]').val()
                    };
                    
                    axios.post('{{ route('seckill_orders.store') }}', req)
                        .then(function (response) {
                            swal('Orders submitted successfully', '', 'success')
                                .then(() => {
                                    location.href = '/orders/' + response.data.id;
                                });
                        }, function (error) {
                            
                            if (error.response.status === 422) {
                                var html = '<div>';
                                _.each(error.response.data.errors, function (errors) {
                                    _.each(errors, function (error) {
                                        html += error+'<br>';
                                    })
                                });
                                html += '</div>';
                                swal({content: $(html)[0], icon: 'error'})
                            } else if (error.response.status === 403) {
                                swal(error.response.data.msg, '', 'error');
                            } else {
                                swal('system error', '', 'error');
                            }
                        });
                });
            });

            
            $('.btn-favor').click(function () {
                axios.post('{{ route('products.favor', ['product' => $product->id]) }}').then(function () {
                    swal('Successful operation', '', 'success').then(function () {  
                        location.reload();
                    });
                }, function(error) {
                    if (error.response && error.response.status === 401) {
                        swal('please log in first', '', 'error');
                    }  else if (error.response && error.response.data.msg) {
                        swal(error.response.data.msg, '', 'error');
                    }  else {
                        swal('system error', '', 'error');
                    }
                });
            });
            $('.btn-disfavor').click(function () {
                axios.delete('{{ route('products.disfavor', ['product' => $product->id]) }}').then(function () {
                    swal('Successful operation', '', 'success').then(function () {
                        location.reload();
                    });
                });
            });

            
            $('.btn-add-to-cart').click(function () {
                axios.post('{{ route('cart.add') }}', {
                    sku_id: $('label.active input[name=skus]').val(),
                    amount: $('.cart_amount input').val(),
                }).then(function () { 
                    swal('Add to Cart successful', '', 'success').then(function() {
                        location.href = '{{ route('cart.index') }}';
                    });
                }, function (error) { 
                    if (error.response.status === 401) {

                        
                        swal('please log in first', '', 'error');

                    } else if (error.response.status === 422) {

                        
                        var html = '<div>';
                        _.each(error.response.data.errors, function (errors) {
                            _.each(errors, function (error) {
                                html += error+'<br>';
                            })
                        });
                        html += '</div>';
                        swal({content: $(html)[0], icon: 'error'})
                    } else {

                        
                        swal('system error', '', 'error');
                    }
                })
            });

            
            $('.btn-crowdfunding').click(function () {
                
                if (!$('label.active input[name=skus]').val()) {
                    swal('Please select the product first');
                    return;
                }
                
                var addresses = {!! json_encode(Auth::check() ? Auth::user()->addresses : []) !!};
                
                var $form = $('<form class="form-horizontal" role="form"></form>');
                
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">Choose address</label>' +
                    '<div class="col-sm-9">' +
                    '<select class="form-control" name="address_id"></select>' +
                    '</div></div>');
                
                addresses.forEach(function (address) {
                    
                    $form.find('select[name=address_id]')
                        .append("<option value='" + address.id + "'>" +
                            address.full_address + ' ' + address.contact_name + ' ' + address.contact_phone +
                            '</option>');
                });
                
                $form.append('<div class="form-group">' +
                    '<label class="control-label col-sm-3">Purchase quantity</label>' +
                    '<div class="col-sm-9"><input class="form-control" name="amount">' +
                    '</div></div>');
                
                swal({
                    text: 'Participate in crowdfunding',
                    content: $form[0], 
                    buttons: ['cancel', 'determine']
                }).then(function (ret) {
                    
                    if (!ret) {
                        return;
                    }
                    
                    var req = {
                        address_id: $form.find('select[name=address_id]').val(),
                        amount: $form.find('input[name=amount]').val(),
                        sku_id: $('label.active input[name=skus]').val()
                    };
                    
                    axios.post('{{ route('crowdfunding_orders.store') }}', req)
                        .then(function (response) {
                            
                            swal('Orders submitted successfully', '', 'success')
                                .then(() => {
                                    location.href = '/orders/' + response.data.id;
                                });
                        }, function (error) {
                            
                            if (error.response.status === 422) {
                                var html = '<div>';
                                _.each(error.response.data.errors, function (errors) {
                                    _.each(errors, function (error) {
                                        html += error+'<br>';
                                    })
                                });
                                html += '</div>';
                                swal({content: $(html)[0], icon: 'error'})
                            } else if (error.response.status === 403) {
                                swal(error.response.data.msg, '', 'error');
                            } else {
                                swal('system error', '', 'error');
                            }
                        });
                });
            });

        });
    </script>
@endsection