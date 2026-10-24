<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold">Two-factor confirmation</h1>
    <p class="mb-6 text-sm text-stone-600">
        Enter the six-digit code from your authenticator application, or use one of your recovery codes.
    </p>

    @if ($errors->any())
        <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ $errors->first() }}
        </p>
    @endif

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="code" class="mb-1 block text-sm font-medium">Authentication code</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" autofocus
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <div>
            <label for="recovery_code" class="mb-1 block text-sm font-medium">Or recovery code</label>
            <input id="recovery_code" name="recovery_code" type="text" autocomplete="off"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <button type="submit"
                class="w-full rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Confirm
        </button>
    </form>
</x-guest-layout>
