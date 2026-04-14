<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Klasifikasi Penyakit Padi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-cover bg-center relative"
      style="background-image: url('https://images.unsplash.com/photo-1536617621572-1d5f1e6269a0?q=80&w=2070&auto=format&fit=crop');">

    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative z-10 w-full max-w-md px-6">
        
        <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden border border-white/50">
            
            <div class="pt-8 pb-6 text-center">
                <h1 class="text-3xl font-bold text-emerald-800 mb-1">Sistem Pakar Padi</h1>
                <p class="text-sm text-gray-500 font-medium">Silakan login untuk mengelola sistem</p>
            </div>

            <div class="px-8 pb-8">
               <form action="{{ route('login.proses') }}" method="POST">
    @csrf
    
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-5">
        <label class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-2">Username / Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required
            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all shadow-sm"
            placeholder="admin@padi.com">
    </div>

    <div class="mb-6">
        <label class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-2">Password</label>
        <input type="password" name="password" required
            class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all shadow-sm"
            placeholder="••••••••">
    </div>

    <button type="submit" 
        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-600/30 transition-all duration-300 transform hover:-translate-y-1">
        Masuk Dashboard
    </button>
</form>
            </div>

            <div class="bg-gray-50/50 px-8 py-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">
                    &copy; 2025 Ahmad Rizal Habibullah
                </p>
            </div>
        </div>
    </div>

</body>
</html>