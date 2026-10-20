@if($booking->consumption_snack || $booking->consumption_meal)
    <fieldset class="mb-5 space-y-3 rounded-xl border border-slate-200 p-4">
        <legend class="px-1 text-base font-semibold text-primaryDark">Konsumsi yang disetujui</legend>
        <p class="text-sm leading-6 text-slate-600">Centang yang disetujui. Boleh memilih salah satu, keduanya, atau tanpa konsumsi.</p>
        @foreach(['snack' => 'Snack', 'meal' => 'Makanan'] as $type => $label)
            @if($booking->{'consumption_'.$type})
                <label class="flex min-h-[44px] items-center gap-3 text-base">
                    <input type="checkbox" name="approved_consumption_{{ $type }}" value="1" @checked((string) old('approval_id', $booking->id) === (string) $booking->id && old('approved_consumption_'.$type, false)) class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
                    {{ $label }}
                </label>
                @error('approved_consumption_'.$type)<p role="alert" class="text-danger">{{ $message }}</p>@enderror
            @endif
        @endforeach
    </fieldset>
@endif

@if($booking->it_support)
    <div class="mb-5 rounded-xl border border-slate-200 p-4">
        <label class="flex min-h-[44px] items-center gap-3 text-base">
            <input type="checkbox" name="approved_it_support" value="1" @checked((string) old('approval_id', $booking->id) === (string) $booking->id && old('approved_it_support', false)) class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
            Setujui kebutuhan teknisi IT
        </label>
        <p class="mt-2 text-sm text-slate-600">Jika tidak dicentang, booking dapat disetujui tanpa teknisi IT.</p>
        @error('approved_it_support')<p role="alert" class="text-danger">{{ $message }}</p>@enderror
    </div>
@endif
