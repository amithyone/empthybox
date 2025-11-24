@extends('layouts.app')

@section('title', 'Bulk Log Upload - Admin')

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 pt-16 md:pt-20 pb-20 md:pb-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold gradient-text">Bulk Log Upload</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-yellow-accent hover:text-red-accent transition">← Back</a>
    </div>

    <div class="bg-dark-200 border-2 border-dark-300 rounded-xl shadow-lg p-4 md:p-6">
        <div class="mb-6 p-4 bg-dark-300/30 border border-yellow-accent/30 rounded-xl">
            <h3 class="font-semibold text-gray-200 mb-2">📋 File Format Instructions</h3>
            <p class="text-sm text-gray-400 mb-2">Upload a CSV or TXT file with the following format:</p>
            <div class="bg-dark-300 p-3 rounded-lg font-mono text-xs text-gray-300">
                username,password,email<br>
                user123,pass456,user@email.com<br>
                user456,pass789,
            </div>
            <p class="text-xs text-gray-500 mt-2">• One credential per line<br>• Username and password are required<br>• Email is optional</p>
        </div>

        <form id="bulk-upload-form" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Select Product</label>
                    <select name="product_id" required
                            class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none">
                        <option value="" class="bg-dark-300">Choose a product...</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" class="bg-dark-300">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-300">Upload File</label>
                    <input type="file" name="file" accept=".csv,.txt" required
                           class="w-full bg-dark-300 border-2 border-dark-400 text-gray-200 rounded-xl p-3 focus:border-yellow-accent focus:ring-2 focus:ring-yellow-accent/20 transition outline-none file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-red-accent file:text-white hover:file:bg-red-dark">
                    <p class="text-xs text-gray-500 mt-2">Accepted formats: CSV, TXT (Max 10MB)</p>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-red-accent to-yellow-accent hover:from-red-dark hover:to-yellow-dark text-white py-4 rounded-xl font-semibold transition glow-button relative shadow-lg shadow-red-accent/40">
                    <span class="relative z-10">Upload & Import Logs</span>
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('bulk-upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const btn = this.querySelector('button[type="submit"]');
    const btnText = btn.querySelector('span');
    btn.disabled = true;
    btnText.textContent = 'Uploading...';
    
    try {
        const response = await fetch('{{ route("admin.bulk-upload.process") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            this.reset();
            setTimeout(() => {
                btn.disabled = false;
                btnText.textContent = 'Upload & Import Logs';
            }, 2000);
        } else {
            showAlert(data.message || 'Failed to upload logs', 'error');
            btn.disabled = false;
            btnText.textContent = 'Upload & Import Logs';
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btnText.textContent = 'Upload & Import Logs';
    }
});
</script>
@endsection

