{{--
    One box. The Alpha group should be able to say what they find without
    ceremony, and the Institute should be able to read it in one place.
--}}
<x-app-layout>
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-semibold text-stone-800">Tell the Institute what you think</h1>
        <p class="mt-2 text-sm text-stone-600">
            Anything at all. Something that confused you, something missing, something that
            worked well. The Institute reads every one of these, and at this stage they shape
            what gets built next.
        </p>

        <form method="POST" action="{{ route('feedback.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="page" value="{{ $page }}">

            <textarea name="message" rows="7" required maxlength="5000" autofocus
                      class="w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-stone-500 focus:ring-stone-500">{{ old('message') }}</textarea>

            <x-form-errors />

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                    Send
                </button>
                <a href="{{ route('home') }}" class="text-sm text-stone-500 underline underline-offset-2">Not now</a>
            </div>
        </form>
    </div>
</x-app-layout>
