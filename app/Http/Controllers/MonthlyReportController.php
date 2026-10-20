<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthlyReportRequest;
use App\Models\Branch;
use App\Services\MonthlyBookingReport;
use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyReportController extends Controller
{
    public function index(MonthlyReportRequest $request, MonthlyBookingReport $report): View|StreamedResponse
    {
        $data = $request->validated();
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month'] ?? now(config('booking.timezone'))->format('Y-m'), config('booking.timezone'));
        $roomId = isset($data['room_id']) ? (int) $data['room_id'] : null;
        $branchId = isset($data['branch_id']) ? (int) $data['branch_id'] : null;
        $branches = Branch::query()->when(! $request->user()->can('access-admin'), fn ($branches) => $branches->whereHas('rooms', fn ($query) => $query->whereIn('id', $report->rooms($request->user())->select('rooms.id'))))->orderBy('name')->get();
        abort_if($branchId && ! $branches->contains('id', $branchId), 404);
        $rooms = $report->rooms($request->user())->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->with('branch')->orderBy('name')->get();
        abort_if($roomId && ! $rooms->contains('id', $roomId), 404);
        $query = $report->bookings($request->user(), $month, $roomId, $branchId);
        $details = (clone $query)->with(['room.branch', 'applicant'])->orderBy('date')->orderBy('start_time')->orderBy('id');
        $generatedAt = CarbonImmutable::now(config('booking.timezone'));
        $scope = $request->user()->can('access-admin') ? 'Seluruh ruangan' : 'Ruangan penugasan PIC saat ini';
        $selectedRoom = $roomId ? $rooms->firstWhere('id', $roomId) : null;

        if ($request->routeIs('*.reports.export')) {
            return response()->streamDownload(function () use ($details) {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['ID Booking', 'Tanggal Rapat (WIB)', 'Ruangan', 'Pemohon', 'Unit saat Pengajuan', 'Agenda', 'Mulai (WIB)', 'Selesai (WIB)', 'Status', 'Kantor / Cabang', 'Jumlah Peserta (perkiraan)', 'Snack Diminta', 'Makanan Diminta', 'Snack Disetujui', 'Makanan Disetujui', 'Teknisi IT Diminta', 'Teknisi IT Disetujui', 'Catatan Pemohon', 'Catatan Keputusan', 'Peminta Tamu', 'Instansi Tamu', 'Kode Registrasi Tamu'], ',', '"', '');
                foreach ($details->lazy(500) as $booking) {
                    fputcsv($stream, array_map([MonthlyBookingReport::class, 'csvCell'], [
                        $booking->id, $booking->date, $booking->room->name, $booking->applicant->name,
                        $booking->unit_name, $booking->agenda, substr($booking->start_time, 0, 5), substr($booking->end_time, 0, 5),
                        MonthlyBookingReport::STATUSES[$booking->status], $booking->room->branch->name,
                        $booking->participant_count, $booking->consumption_snack ? 'Ya' : 'Tidak', $booking->consumption_meal ? 'Ya' : 'Tidak',
                        $booking->status === 'pending' ? 'Menunggu review' : ($booking->approved_consumption_snack ? 'Ya' : 'Tidak'),
                        $booking->status === 'pending' ? 'Menunggu review' : ($booking->approved_consumption_meal ? 'Ya' : 'Tidak'),
                        $booking->it_support ? 'Ya' : 'Tidak', $booking->itSupportDecisionLabel(), $booking->notes, $booking->decision_notes, $booking->guest_name, $booking->guest_organization, $booking->guest_reference,
                    ]), ',', '"', '');
                }
                fclose($stream);
            }, 'laporan-booking-'.$month->format('Y-m').($roomId ? '-ruangan-'.$roomId : '').'.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store',
            ]);
        }

        $summary = $report->summary($query, $selectedRoom ? collect([$selectedRoom]) : $rooms);
        $printing = $request->routeIs('*.reports.print');
        $bookings = $printing ? $details->get() : $details->paginate(20)->withQueryString();
        $routePrefix = $request->user()->can('access-admin') ? 'admin' : 'pic';

        return view('reports.monthly', compact('branchId', 'branches', 'month', 'roomId', 'rooms', 'selectedRoom', 'scope', 'generatedAt', 'summary', 'printing', 'bookings', 'routePrefix') + ['statuses' => MonthlyBookingReport::STATUSES]);
    }
}
