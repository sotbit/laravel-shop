@extends('layouts.app')
@section('title', 'Prompt')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Prompt</div>
        <div class="panel-body text-center">
            <h1>Please verify your email first</h1>
            <a class="btn btn-primary" href="{{ route('email_verification.send') }}">Resend verification email</a>
        </div>
    </div>
@endsection