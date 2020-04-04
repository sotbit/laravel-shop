@extends('layouts.app')
@section('title', ($address->id ? 'modify': 'new') . 'Shipping address')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="text-center">
                        {{ $address->id ? 'modify': 'new' }}收货地址
                    </h2>
                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <h4>An error occurred:</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li><i class="glyphicon glyphicon-remove"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                @endif
                    <user-addresses-create-and-edit inline-template>
                        @if($address->id)
                        <form class="form-horizontal" role="form" action="{{ route('user_addresses.update', ['user_address' => $address->id]) }}" method="post">
                            {{ method_field('PUT') }}
                        @else
                        <form class="form-horizontal" role="form" action="{{ route('user_addresses.store') }}" method="post">
                        @endif
                            {{ csrf_field() }}
                            <select-district :init-value="{{ json_encode([$address->province, $address->city,
                            $address->district]) }}" @change="onDistrictChanged" inline-template>
                                <div class="form-group">
                                    <label class="control-label col-sm-2">Province City District</label>
                                    <div class="col-sm-3">
                                        <select class="form-control" v-model="provinceId">
                                            <option value="">Choose province</option>
                                            <option v-for="(name, id) in provinces" :value="id">@{{ name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" v-model="cityId">
                                            <option value="">Select city</option>
                                            <option v-for="(name, id) in cities" :value="id">@{{ name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" v-model="districtId">
                                            <option value="">Selection area</option>
                                            <option v-for="(name, id) in districts" :value="id">@{{ name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </select-district>
                            <input type="hidden" name="province" v-model="province">
                            <input type="hidden" name="city" v-model="city">
                            <input type="hidden" name="district" v-model="district">
                            <div class="form-group">
                                <label class="control-label col-sm-2">Address</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="address" value="{{ old('address', $address->address) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2">Postcode</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="zip" value="{{ old('zip', $address->zip) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2">Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address->contact_name) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-2">Phone</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $address->contact_phone) }}">
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </user-addresses-create-and-edit>
                </div>
            </div>
        </div>
    </div>
@endsection