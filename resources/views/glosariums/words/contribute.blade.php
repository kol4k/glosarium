@extends('layouts.app')

@section('header')
<header class="page-header bg-img" style="background-image: url({{ asset('assets/img/bg-banner1.jpg') }});">
    <div class="container page-name">
        <h1 class="text-center">Kontribusi Kata</h1>
        <p class="lead text-center">Di halaman ini, kamu dapat melihat kata yang telah dikirim ke {{ config('app.name') }}. Kamu juga dapat menyunting kata, menghapus, atau menambah kata baru.</p>
    </div>
    <div class="container">
        <form action="{{ url()->current() }}" method="get">

            <div class="row">
                <div class="form-group col-xs-12 col-sm-8">
                    <input type="text" name="katakunci" class="form-control" value="{{ request('katakunci') }}" placeholder="Katakunci dalam bahasa asing atau dalam bahasa lokal">
                </div>
                <div class="form-group col-xs-12 col-sm-4">
                    <select name="kategori[]" class="form-control selectpickers" multiple>
                        @foreach($categories as $slug => $name)
                            <option value="{{ $slug }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="button-group">
                <div class="action-buttons">
                    <button class="btn btn-primary" type="submit">Saring</button>
                </div>
            </div>
        </form>
    </div>
</header>
@endsection

@section('content')
<section class="bg-alt">
    <div class="container">
        <div class="row">

            @include('partials.message')

            @if($words->total() >= 1)
            <div class="col-xs-12 text-right">
                <br>
                <a class="btn btn-primary btn-sm" href="{{ route('glosarium.word.create') }}">Ajukan Kata Baru</a>
            </div>
            @endif

            @if($words->total() <= 0)
            <div class="alert alert-info">
                <strong>Info.</strong><br>
                Belum ada kata yang kamu tambahkan. <a href="{{ route('glosarium.word.create') }}" class="alert-link">Tambah sekarang</a>.
            </div>
            @endif
            
            @foreach ($words as $word)
            <div class="col-xs-12">
                <div class="item-block">
                    <header>
                        <div class="hgroup">
                            @php
                                $url = route('glosarium.word.show', [
                                    'category' => $word->category->slug,
                                    'slug' => $word->slug
                                ])
                            @endphp
                            <h4><a href="{{ $url }}">{{ $word->origin }}</a></h4>
                            <h5><a href="{{ $url }}">{{ $word->locale }}</a></h5>
                        </div>
                        <div class="header-meta">
                            <span class="category">
                                <i class="{{ $word->category->metadata['icon'] }} fa-fw"></i>
                                <a href="{{ route('glosarium.category.show', $word->category->slug) }}" title="Lihat kategori {{ $word->category->name }}">{{ $word->category->name }}</a>
                            </span>
                            <time datetime="{{ $word->created_at }}">{{ $word->created_at->diffForHumans() }}</time>
                        </div>
                    </header>
                    <footer>
                        <p class="status"><strong>Status:</strong> {{ $word->is_published ? 'Dipublikasikan' : 'Belum dipublikasikan' }}</p>
                        <div class="action-btn">
                            <a class="btn btn-xs btn-gray" href="{{ route('glosarium.word.edit', $word->slug) }}">Sunting</a>
                            <a class="btn btn-xs btn-danger" href="{{ route('glosarium.word.destroy', $word->slug) }}">Hapus</a>
                        </div>
                    </footer>
                </div>
            </div>
            @endforeach

        </div>
        
        <nav>{{ $words->links() }}</nav>
    </div>
</section>
@endsection

@push('js')
<script>
    $(function(){
        $('.selectpickers').selectpicker({
            noneSelectedText: 'Tidak ada kategori dipilih'
        })
    })
</script>
@endpush