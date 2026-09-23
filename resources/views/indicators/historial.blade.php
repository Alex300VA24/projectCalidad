@extends('layouts.app')

@section('title', $indicador->nombre.' · Histórico | SIGI Calidad')
@section('page-label', 'Histórico de indicador')

@section('content')
    <livewire:indicadores.indicador-historial :indicador="$indicador" />
@endsection

@push('modals')
    <x-pdf-modal />
@endpush
