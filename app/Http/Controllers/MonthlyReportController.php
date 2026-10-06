<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthlyReportRequest;
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
        $rooms = $report->rooms($request->user())->orderBy('name')->get();
        abort_if($roomId && ! $rooms->contains('id', $roomId), 404);
        $query = $report->bookings($request->user(), $month, $roomId);
        $details = (clone $query)->with(['room', 'applicant'])->orderBy('date')->orderBy('start_time')->orderBy('id');
        $generatedAt = CarbonImmutable::now(config('booking.timezone'));
        $scope = $request->user()->can('access-admin') ? 'Seluruh ruangan' : 'Ruangan penugasan PIC saat ini';
        $selectedRoom = $roomId ? $rooms->firstWhere('id', $roomId) : null;

        if ($request->routeIs('*.reports.export')) {
            return response()->streamDownload(function () use ($details) {
                $stream = fopen('php://output', 'w');
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['ID Booking', 'Tanggal Rapat (WIB)', 'Ruangan', 'Pemohon', 'Unit saat Pengajuan', 'Agenda', 'Mulai (WIB)', 'Selesai (WIB)', 'Status'], ',', '"', '');
                foreach ($details->lazy(500) as $booking) {
                    fputcsv($stream, array_map([MonthlyBookingReport::class, 'csvCell'], [
                        $booking->id, $booking->date, $booking->room->name, $booking->applicant->name,
                        $booking->unit_name, $booking->agenda, substr($booking->start_time, 0, 5), substr($booking->end_time, 0, 5),
                        MonthlyBookingReport::STATUSES[$booking->status],
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

        return view('reports.monthly', compact('month', 'roomId', 'rooms', 'selectedRoom', 'scope', 'generatedAt', 'summary', 'printing', 'bookings', 'routePrefix') + ['statuses' => MonthlyBookingReport::STATUSES]);
    }
}
