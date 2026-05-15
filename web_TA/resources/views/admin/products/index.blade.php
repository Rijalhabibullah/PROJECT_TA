@extends('layouts.admin')
@section('title', 'Manajemen Produk')

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

<div class="flex flex-col lg:flex-row gap-8">
    
    <div class="w-full lg:w-1/3">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Tambah Produk Baru</h3>
            <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2 border" placeholder="Contoh: Urea" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" name="price" value="{{ old('price') }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link Marketplace</label>
                    <input type="url" name="marketplace_link" value="{{ old('marketplace_link') }}" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border" placeholder="https://shopee.co.id/..." required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Penyakit</label>
                    <select name="disease_tag" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border">
                        <option value="">Tidak terkait</option>
                        <option value="Bacterial Blight" {{ old('disease_tag') == 'Bacterial Blight' ? 'selected' : '' }}>Bacterial Blight</option>
                        <option value="Brown Spot" {{ old('disease_tag') == 'Brown Spot' ? 'selected' : '' }}>Brown Spot</option>
                        <option value="Leaf Smut" {{ old('disease_tag') == 'Leaf Smut' ? 'selected' : '' }}>Leaf Smut</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika produk untuk umum.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                    <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-xs text-gray-500 mt-1">Maksimal 2 MB (jpg/png).</p>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-lg transition">Simpan Produk</button>
            </form>
        </div>
    </div>

    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full table-fixed divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/3">Info Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Harga</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    @if($product->image)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $product->image) }}" alt="">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">📦</div>
                                    @endif
                                </div>
                                <div class="ml-4 min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">
                                        {{ $product->disease_tag ?: 'Tidak terkait' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($product->price) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="inline-flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="text-emerald-600 hover:text-emerald-900"
                                    data-edit-product
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->price }}"
                                    data-description="{{ $product->description }}"
                                    data-marketplace="{{ $product->marketplace_link }}"
                                    data-disease="{{ $product->disease_tag }}"
                                    data-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}"
                                >
                                    Edit
                                </button>
                                <form action="{{ route('produk.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editProductModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-edit-close></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-lg border border-gray-100">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">Edit Produk</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-edit-close>✕</button>
            </div>
            <form id="editProductForm" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                    <input type="text" name="name" id="editName" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 p-2 border" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                    <input type="number" name="price" id="editPrice" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="editDescription" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Link Marketplace</label>
                    <input type="url" name="marketplace_link" id="editMarketplace" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Penyakit</label>
                    <select name="disease_tag" id="editDisease" class="w-full border-gray-300 rounded-lg shadow-sm p-2 border">
                        <option value="">Tidak terkait</option>
                        <option value="Bacterial Blight">Bacterial Blight</option>
                        <option value="Brown Spot">Brown Spot</option>
                        <option value="Leaf Smut">Leaf Smut</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika produk untuk umum.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Produk</label>
                    <input type="file" name="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti.</p>
                    <div id="editImageWrap" class="mt-3 hidden items-center gap-3">
                        <img id="editImagePreview" class="h-12 w-12 rounded-lg object-cover" src="" alt="">
                        <span class="text-xs text-gray-500">Foto saat ini</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition">Simpan Perubahan</button>
                    <button type="button" class="text-gray-600 hover:text-gray-900" data-edit-close>Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const editModal = document.getElementById('editProductModal');
    const editForm = document.getElementById('editProductForm');
    const editName = document.getElementById('editName');
    const editPrice = document.getElementById('editPrice');
    const editDescription = document.getElementById('editDescription');
    const editMarketplace = document.getElementById('editMarketplace');
    const editDisease = document.getElementById('editDisease');
    const editImageWrap = document.getElementById('editImageWrap');
    const editImagePreview = document.getElementById('editImagePreview');

    const openEditModal = (button) => {
        const id = button.dataset.id;
        editForm.action = `{{ url('produk') }}/${id}`;
        editName.value = button.dataset.name || '';
        editPrice.value = button.dataset.price || '';
        editDescription.value = button.dataset.description || '';
        editMarketplace.value = button.dataset.marketplace || '';
        editDisease.value = button.dataset.disease || '';

        if (button.dataset.image) {
            editImagePreview.src = button.dataset.image;
            editImageWrap.classList.remove('hidden');
            editImageWrap.classList.add('flex');
        } else {
            editImagePreview.src = '';
            editImageWrap.classList.add('hidden');
            editImageWrap.classList.remove('flex');
        }

        editModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeEditModal = () => {
        editModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-edit-product]').forEach((button) => {
        button.addEventListener('click', () => openEditModal(button));
    });

    document.querySelectorAll('[data-edit-close]').forEach((button) => {
        button.addEventListener('click', closeEditModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !editModal.classList.contains('hidden')) {
            closeEditModal();
        }
    });
</script>
@endsection