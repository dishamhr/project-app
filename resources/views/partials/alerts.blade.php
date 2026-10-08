@if (session('success'))
    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <span class="mt-0.5 text-emerald-600"><x-icon name="check-circle" :size="18" /></span>
        <p>{{ session('success') }}</p>
        <button type="button" data-dismiss class="ml-auto text-emerald-600 hover:text-emerald-800">
            <x-icon name="x" :size="16" />
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="mb-1 font-semibold">Mohon periksa kembali inputan Anda:</p>
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
