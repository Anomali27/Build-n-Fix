@extends('layouts.app')

@section('content')
    @yield('admin_content', $__env->yieldContent('content'))
@endsection
