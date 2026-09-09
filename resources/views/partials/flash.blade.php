@if(session('success'))
    <div class="mx-4 mt-4 md:mx-6 p-3.5 rounded-xl border border-brand-200 dark:border-brand-800/60 bg-brand-50 dark:bg-brand-900/20 text-brand-800 dark:text-brand-200 text-sm shadow-soft" role="status">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mx-4 mt-4 md:mx-6 p-3.5 rounded-xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm shadow-soft" role="alert">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mx-4 mt-4 md:mx-6 p-3.5 rounded-xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm shadow-soft" role="alert">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
