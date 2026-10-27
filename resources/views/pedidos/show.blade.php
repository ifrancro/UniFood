@extends('layouts.app')
@section('title', 'Detalle del pedido · UniFood')
@section('content')
<x-order-detail :pedido="$pedido"/>
@endsection
