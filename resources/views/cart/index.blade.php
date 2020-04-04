@extends('layouts.app')

@section('title', '购物车')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">my shopping cart</div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Product information</th>
                            <th>unit price</th>
                            <th>Quantity</th>
                            <th>operating</th>
                        </tr>
                        </thead>
                        <tbody class="product_list">
                        @foreach($cartItems as $item)
                            <tr data-id="{{ $item->productSku->id }}">
                                <td>
                                    <input type="checkbox" name="select" value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
                                </td>
                                <td class="product_info">
                                    <div class="preview">
                                        <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">
                                            <img src="{{ $item->productSku->product->image_url }}">
                                        </a>
                                    </div>
                                    <div @if(!$item->productSku->product->on_sale) class="not_on_sale" @endif>
                                        <span class="product_title">
                                            <a target="_blank" href="{{ route('products.show', [$item->productSku->product_id]) }}">{{ $item->productSku->product->title }}</a>
                                        </span>
                                        <span class="sku_title">{{ $item->productSku->title }}</span>
                                        @if(!$item->productSku->product->on_sale)
                                        <span class="warning">This product is no longer available</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="price">${{ $item->productSku->price }}</span></td>
                                <td>
                                    <input type="text" class="form-control input-sm amount" @if(!$item->productSku->product->on_sale) disabled @endif name="amount" value="{{ $item->amount }}">
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-danger btn-remove">Remove</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    
                    <div>
                        <form class="form-horizontal" role="form" id="order-form">
                            <div class="form-group">
                                <label class="control-label col-sm-3">Choose delivery address</label>
                                <div class="col-sm-9 col-md-7">
                                    <select class="form-control" name="address">
                                        @foreach($addresses as $address)
                                            <option value="{{ $address->id }}">{{ $address->full_address }} {{ $address->contact_name }} {{ $address->contact_phone }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3">Remarks</label>
                                <div class="col-sm-9 col-md-7">
                                    <textarea name="remark" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-sm-3">Promo Code</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="coupon_code">
                                    <span class="help-block" id="coupon_desc"></span>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button" class="btn btn-success" id="btn-check-coupon">an examination</button>
                                    <button type="button" class="btn btn-danger" style="display: none;" id="btn-cancel-coupon">cancel</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-3">
                                    <button type="button" class="btn btn-primary btn-create-order">Submit orders</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
<script>
    $(document).ready(function () {
        $$('.btn-remove').click(function () {
            $$$var id = $(this).closest('tr').data('id');
            swal({
                title: "Are you sure you want to remove this product?",
                icon: "warning",
                buttons: ['cancel', 'determine'],
                dangerMode: true,
            }).then(function(willDelete) {
                $if (!willDelete) {
                    return;
                }
                axios.delete('/cart/' + id).then(function () {
                    location.reload();
                })
            });
        });

        $$('#select-all').change(function() {
            $$var checked = $(this).prop('checked');
            $$$('input[name=select][type=checkbox]:not([disabled])').each(function() {
                $$(this).prop('checked', checked);
            });
        });

        $$('.btn-create-order').click(function () {
            $var req = {
                address_id: $('#order-form').find('select[name=address]').val(),
                items: [],
                remark: $('#order-form').find('textarea[name=remark]').val(),
                coupon_code: $('input[name=coupon_code]').val(), $};
            $$('table tr[data-id]').each(function () {
                $var $checkbox = $(this).find('input[name=select][type=checkbox]');
                $if ($checkbox.prop('disabled') || !$checkbox.prop('checked')) {
                    return;
                }
                $var $input = $(this).find('input[name=amount]');
                $if ($input.val() == 0 || isNaN($input.val())) {
                    return;
                }
                $req.items.push({
                    sku_id: $(this).data('id'),
                    amount: $input.val(),
                })
            });
            axios.post('{{ route('orders.store') }}', req).then(function (response) {
                swal('Orders submitted successfully', '', 'success').then(() => {
                    location.href = '/orders/' + response.data.id;
                });
            }, function (error) {
                if (error.response.status === 422) {
                    $var html = '<div>';
                    _.each(error.response.data.errors, function (errors) {
                        _.each(errors, function (error) {
                            html += error+'<br>';
                        })
                    });
                    html += '</div>';
                    swal({content: $(html)[0], icon: 'error'})
                } else if (error.response.status === 403) { $swal(error.response.data.msg, '', 'error');
                } else {
                    $swal('system error', '', 'error');
                }
            });
        });

        $$('#btn-check-coupon').click(function () {
            $var code = $('input[name=coupon_code]').val();
            $if(!code) {
                swal('Please enter the promo code', '', 'warning');
                return;
            }
            $axios.get('/coupon_codes/' + encodeURIComponent(code))
                .then(function (response) {  $$('#coupon_desc').text(response.data.description); $$('input[name=coupon_code]').prop('readonly', true); $$('#btn-cancel-coupon').show(); $$('#btn-check-coupon').hide(); $}, function (error) {
                    $if(error.response.status === 404) {
                        swal('Coupon code does not exist', '', 'error');
                    } else if (error.response.status === 403) {
                        $swal(error.response.data.msg, '', 'error');
                    } else {
                        $swal('Internal System Error', '', 'error');
                    }
                })
        });

        $$('#btn-cancel-coupon').click(function () {
            $('#coupon_desc').text(''); $$('input[name=coupon_code]').prop('readonly', false);  $$('#btn-cancel-coupon').hide(); $$('#btn-check-coupon').show(); $});

    });
</script>
@endsection