@extends('layouts.app')
@section('title', 'Delivery address list')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Delivery address list
                    <a href="{{ route('user_addresses.create') }}" class="pull-right">New shipping address</a>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Receiver</th>
                            <th>Address</th>
                            <th>Postcode</th>
                            <th>Phone</th>
                            <th>Operating</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($addresses as $address)
                            <tr>
                                <td>{{ $address->contact_name }}</td>
                                <td>{{ $address->full_address }}</td>
                                <td>{{ $address->zip }}</td>
                                <td>{{ $address->contact_phone }}</td>
                                <td>
                                    <a href="{{ route('user_addresses.edit', ['user_address' => $address->id]) }}" class="btn btn-primary">modify</a>
                                    <button class="btn btn-danger btn-del-address" type="button" data-id="{{ $address->id }}">delete</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
    <script>
        $(document).ready(function() {
            
            $('.btn-del-address').click(function() {
                
                var id = $(this).data('id');
                
                swal({
                    title: "Are you sure you want to delete this address?",
                    icon: "warning",
                    buttons: ['cancel', 'determine'],
                    dangerMode: true,
                }).then(function(willDelete) { 
                        
                        
                        if (!willDelete) {
                            return;
                        }
                        
                        axios.delete('/user_addresses/' + id).then(function () {
                            
                            location.reload();
                        })
                    });
            });
        });
    </script>
@endsection