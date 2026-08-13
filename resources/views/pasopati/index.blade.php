@extends('pasopati.layout')

{{-- Permalink satu event (/fire/<slug>): judul + deskripsi + OG/Twitter meta
     diisi dari event itu supaya preview link (WhatsApp/Twitter) memuat kartu
     event, bukan meta generik halaman /fire. Halaman base /fire tidak mengisi
     $metaEvent, jadi judul/deskripsi default layout tetap dipakai. --}}
@if (! empty($metaEvent))
  @section('title', $metaEvent['title'])
  @section('description', $metaEvent['description'])
  @push('meta')
    <meta property="og:title" content="{{ $metaEvent['title'] }}" />
    <meta property="og:description" content="{{ $metaEvent['description'] }}" />
    <meta property="og:image" content="{{ $metaEvent['image'] }}" />
    <meta property="og:url" content="{{ $metaEvent['url'] }}" />
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $metaEvent['title'] }}" />
    <meta name="twitter:image" content="{{ $metaEvent['image'] }}" />
  @endpush
@endif

@section('konten')
  @include('pasopati.beranda')
  @include('pasopati.peta')
@endsection
