@extends('layouts.app')

@section('content')
    @yield('customer_content', $__env->yieldContent('content'))
@endsection
