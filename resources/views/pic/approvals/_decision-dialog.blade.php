@php($hasDecisionError = $errors->any() && (string) old('approval_id') === (string) $booking->id)
<dialog id="approval-{{ $booking->id }}" data-approval-dialog @if($hasDecisionError) data-reopen="{{ old('decision', 'rejected') }}" @endif aria-labelledby="approval-title-{{ $booking->id }}" aria-describedby="approval-summary-{{ $booking->id }}" class="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-xl overflow-y-auto rounded-2xl border border-secondaryLight bg-surface p-6 text-text shadow-xl backdrop:bg-primaryDark/50 sm:p-8">
    <h2 id="approval-title-{{ $booking->id }}"  class="text-2xl font-bold text-primaryDark">Review Pengajuan</h2>
    <div id="approval-summary-{{ $booking->id }}" class="mt-5 space-y-2 break-words rounded-xl bg-background p-4 text-base leading-7">
        <p class="font-semibold text-primaryDark">{{ $booking->room->name }}</p>
        <p>{{ $booking->agenda }}</p>
        <p>Pemohon: {{ $booking->applicant->name }}</p><p>Unit: {{ $booking->unit_name ?? 'Belum ditentukan' }}</p><p>Tanggal: {{ $booking->date }}</p>
        <p>{{ substr($booking->start_time, 0, 5) }}â€“{{ substr($booking->end_time, 0, 5) }} WIB</p>
        <p class="whitespace-pre-line break-words">Catatan pemohon: {{ $booking->notes ?: 'Tidak ada catatan.' }}</p>
    </div>
    @if($hasDecisionError)
        <ul role="alert" class="mt-4 list-disc space-y-1 rounded-xl bg-danger/10 p-4 pl-8 text-base text-danger">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif
    <form method="POST" action="{{ route('pic.approvals.decide', $booking->id) }}" class="mt-5" data-decision-form>
        @csrf
        <input type="hidden" name="approval_id" value="{{ $booking->id }}">
        <input type="hidden" name="return_to" value="list">
        <input type="hidden" name="page" value="{{ $approvals->currentPage() }}">
        <label for="decision-notes-{{ $booking->id }}" class="block text-base font-semibold text-primaryDark">Catatan</label>
        <p id="decision-help-{{ $booking->id }}" class="mt-2 text-sm leading-6 text-slate-600">Opsional untuk persetujuan, wajib untuk penolakan. Terlihat oleh pemohon. Maksimal 2.000 karakter.</p>
        <textarea id="decision-notes-{{ $booking->id }}" name="decision_notes" rows="4" maxlength="2000" aria-describedby="decision-help-{{ $booking->id }}" class="mt-3 w-full rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-primary">{{ $hasDecisionError ? old('decision_notes', old('rejection_reason')) : '' }}</textarea>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="button" data-close-decision class="workspace-button-secondary" autofocus>Batal</button>
            <button type="submit" name="decision" value="rejected" class="rounded-xl border border-danger px-5 py-3 text-base font-semibold text-danger hover:bg-danger/10">Tolak</button>
            <button type="submit" name="decision" value="approved" class="workspace-button">Setujui</button>
        </div>
    </form>
</dialog>
