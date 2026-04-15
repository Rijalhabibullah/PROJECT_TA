@extends('layouts.admin')
@section('title', 'History Classification')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    <ul class="list-disc pl-5 text-sm">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="w-full">
    <div class="w-full">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b bg-gray-50 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <h3 class="text-lg font-bold text-gray-800">Data History Classification</h3>
                <form method="GET" class="flex gap-2">
                    <input type="text" name="jenis_penyakit" value="{{ request('jenis_penyakit') }}" placeholder="Filter penyakit..." class="border border-gray-300 rounded-lg p-2 text-sm">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Penyakit</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($histories as $history)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $history->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $history->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $history->jenis_penyakit }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $history->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <button type="button" onclick="toggleEdit({{ $history->id }})" class="text-blue-600 hover:text-blue-800 mr-3">Edit</button>
                                <form action="{{ route('history-klasifikasi.destroy', $history->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-row-{{ $history->id }}" class="hidden bg-gray-50">
                            <td colspan="5" class="px-4 py-3">
                                <form action="{{ route('history-klasifikasi.update', $history->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @csrf
                                    @method('PUT')

                                    <select name="user_id" class="border border-gray-300 rounded-lg p-2 text-sm" required>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $history->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>

                                    <input type="text" name="jenis_penyakit" value="{{ $history->jenis_penyakit }}" class="border border-gray-300 rounded-lg p-2 text-sm" required>

                                    <div class="flex gap-2">
                                        <button type="submit" class="bg-emerald-600 text-white px-3 py-2 rounded-lg text-sm">Update</button>
                                        <button type="button" onclick="toggleEdit({{ $history->id }})" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data history classification.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t bg-white">
                {{ $histories->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEdit(id) {
        const row = document.getElementById('edit-row-' + id);
        if (!row) return;
        row.classList.toggle('hidden');
    }
</script>

@endsection
