@extends('layouts.app')
@section('title', 'Error')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Error</div>
        <div class="panel-body text-center">
            <h1>{{ $msg }}</h1>
            <a class="btn btn-primary" href="{{ route('root') }}">Back to top</a>
        </div>
    </div>
@endsection