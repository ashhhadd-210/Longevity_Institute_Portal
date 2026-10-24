<x-guest-layout>
    <h1 class="mb-2 text-lg font-semibold">Confirm your password</h1>
    <p class="mb-6 text-sm text-stone-600">
        For your security, please confirm your password to continue.
    </p>

    @if ($errors->any())
        <p class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ $errors->first() }}
        </p>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" name="password" type="password" required autofocus autocomplete="current-password"
                   class="w-full rounded border border-stone-300 px-3 py-2 text-sm focus:border-stone-500 focus:outline-none">
        </div>
        <button type="submit"
                class="w-full rounded bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
            Confirm
        </button>
    </form>
</x-guest-layout>
