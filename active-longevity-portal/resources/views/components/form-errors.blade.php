@props(['bag' => 'default'])

@if ($errors->{$bag}->any())
    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-medium">Please check the following:</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->{$bag}->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
