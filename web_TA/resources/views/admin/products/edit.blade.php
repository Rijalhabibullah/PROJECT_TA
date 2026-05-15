@extends('layouts.admin')
@section('title', 'Edit Produk')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-2xl">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Edit Produk</h3>
        <form action="{{ route('produk.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2 border" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                <input type="number" name="price" value="{{ old('price', $product->price) }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Link Marketplace</label>
                <input type="url" name="marketplace_link" value="{{ old('marketplace_link', $product->marketplace_link) }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti.</p>
                @if($product->image)
                    <div class="mt-3 flex items-center gap-3">
                        <img class="h-12 w-12 rounded-lg object-cover" src="{{ asset('storage/' . $product->image) }}" alt="">
                        <span class="text-xs text-gray-500">Foto saat ini</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition">Simpan Perubahan</button>
                <a href="{{ route('produk.index') }}" class="text-gray-600 hover:text-gray-900">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
