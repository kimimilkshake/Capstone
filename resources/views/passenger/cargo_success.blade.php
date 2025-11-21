@extends('layouts.app')

@section('content')
<div class="text-center mt-5">
    <h1 class="text-success">✔ Successful!</h1>
    <p>Your cargo booking has been submitted.</p>
    <p>Please wait for approval.</p>

    <a href="{{ url('/') }}" class="btn btn-primary mt-3">Back to Home</a>
</div>
@endsection
