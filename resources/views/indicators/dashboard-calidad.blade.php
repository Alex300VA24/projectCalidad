@extends('layouts.app')

@section('title', 'Indicadores | SIGI Calidad')
@section('page-label', 'Indicadores')

@section('content')
    <livewire:indicadores.dashboard-calidad />
@endsection

@push('modals')
    <x-pdf-modal />
@endpush
