@extends('layouts.admin')
@section('title', 'Manajemen Dataset (Training Data)')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach($stats as $stat)
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <p class="text-xs text-gray-400 uppercase">{{ $stat->label }}</p>
        <p class="text-xl font-bold text-emerald-600">{{ $stat->total }} <span class="text-xs text-gray-400 font-normal">Citra</span></p>
    </div>
    @endforeach
</div>

<div class="flex flex-col lg:flex-row gap-8">
    <div class="w-full lg:w-1/3">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-4">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Upload Data Latih</h3>
            <form action="{{ route('dataset.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label Penyakit</label>
                    <select name="label" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border">
                        <option value="Brown Spot">Brown Spot</option>
                        <option value="Leaf Smut">Leaf Smut</option>
                        <option value="Bacterial Leaf Blight">Bacterial Leaf Blight</option>
                        <option value="Healthy">Healthy (Sehat)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">File Citra (Gambar)</label>
                    <input type="file" name="image" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG. Max: 2MB</p>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-lg transition">Upload Dataset</button>
            </form>
        </div>
    </div>

    <div class="w-full lg:w-2/3">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Galeri Data</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($datasets as $data)
                <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ asset('storage/' . $data->image_path) }}" class="w-full h-32 object-cover" alt="{{ $data->label }}">
                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 p-2">
                        <p class="text-white text-xs font-bold">{{ $data->label }}</p>
                    </div>
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                        <form action="{{ route('dataset.destroy', $data->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white p-1 rounded-full hover:bg-red-700" title="Hapus Gambar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection