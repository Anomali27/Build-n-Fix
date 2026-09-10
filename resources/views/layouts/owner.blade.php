@extends('layouts.app')

@section('content')
    @yield('owner_content', $__env->yieldContent('content'))
@endsection
