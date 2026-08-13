@extends('layouts.admin')

@section('content')
    @include('events.partials.event-form', ['event' => null])
@endsection

@push('scripts')
    @vite('resources/js/event.js')
@endpush
