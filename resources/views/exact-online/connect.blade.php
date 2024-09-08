@extends('layouts.app')

@section('content')
    <form method="post" action="{{ route('exact.authorize') }}">
        {{ csrf_field() }}
        <button class="btn btn-primary" type="submit">Verbinden met Nationale Interim Bank Exact App</button>
    </form>
@endsection
