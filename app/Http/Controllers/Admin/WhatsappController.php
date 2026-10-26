<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveWhatsappSettingsRequest;
use App\Http\Requests\WhatsappHistoryRequest;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappRecipient;
use App\Models\WhatsappTemplate;
use App\Services\BookingWhatsapp;
use App\Services\WhatsappSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WhatsappController extends Controller
{
    public function index(WhatsappHistoryRequest $request): View
    {
        $filters = $request->validated();

        return view('admin.whatsapp.index', [
            'templates' => WhatsappTemplate::orderBy('id')->get(),
            'selected' => WhatsappRecipient::where('is_active', true)->whereNotNull('user_id')->get()->groupBy('purpose'),
            'users' => User::orderBy('name')->get(),
            'pics' => Room::with(['pics' => fn ($query) => $query->where('role', User::ROLE_ROOM_PIC)->where('is_active', true)])
                ->where('is_active', true)->where('requires_approval', true)->orderBy('name')->get(),
            'legacyRecipients' => WhatsappRecipient::whereNull('user_id')->whereNotNull('number')->get(),
            'messages' => WhatsappMessage::with('recipient')->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))->latest('id')->paginate(20)->withQueryString(),
            'filters' => $filters,
            'preview' => $request->session()->get('whatsapp_preview'),
            'variables' => BookingWhatsapp::VARIABLES,
            'messageFields' => BookingWhatsapp::FIELDS,
            'enabled' => (bool) config('whatsapp.enabled'), 'hasToken' => (bool) config('whatsapp.token'),
        ]);
    }

    public function update(SaveWhatsappSettingsRequest $request, WhatsappTemplate $template, WhatsappSettings $settings): RedirectResponse
    {
        $settings->save($template, $request->validated());

        return redirect()->route('admin.whatsapp.index')->with('status', 'Pengaturan '.$template->label().' tersimpan. Template baru berlaku untuk booking berikutnya; pesan antrean mempertahankan isi saat dibuat.');
    }

    public function preview(SaveWhatsappSettingsRequest $request, WhatsappTemplate $template, BookingWhatsapp $whatsapp): RedirectResponse
    {
        $booking = Booking::latest('id')->first();
        $preview = ['event' => $template->event, 'message' => $booking ? $whatsapp->render($booking, $request->validated('body'), $request->user()) : null];

        return redirect()->route('admin.whatsapp.index')->withInput()->with('whatsapp_preview', $preview)->with('status', 'Pratinjau tidak menyimpan pengaturan atau mengirim WhatsApp.');
    }

    public function retry(WhatsappMessage $message, WhatsappSettings $settings): RedirectResponse
    {
        $settings->retry($message->id);

        return redirect()->route('admin.whatsapp.index')->with('status', 'Pesan masuk antrean untuk dicoba ulang oleh scheduler.');
    }
}
