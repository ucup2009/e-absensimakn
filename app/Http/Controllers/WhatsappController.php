<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WhatsappController extends Controller
{
    public function index(Request $request, WhatsAppGatewayService $service): View
    {
        $filters = [
            'status' => $request->input('status'),
            'q' => $request->input('q'),
            'type' => $request->input('type'),
        ];

        $logs = WhatsappLog::query()
            ->with('student')
            ->when($filters['status'], fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['type'], fn ($q) => $q->where('type', $filters['type']))
            ->when($filters['q'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $sub->where('phone', 'like', '%'.$filters['q'].'%')
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', '%'.$filters['q'].'%'));
            }))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::now()->startOfMonth();

        return view('whatsapp.index', [
            'templates' => WhatsappTemplate::orderBy('id')->get(),
            'logs' => $logs,
            'filters' => $filters,
            'service' => $service,
            'stats' => [
                'sent' => WhatsappLog::where('status', 'sent')->where('created_at', '>=', $monthStart)->count(),
                'failed' => WhatsappLog::where('status', 'failed')->where('created_at', '>=', $monthStart)->count(),
                'pending' => WhatsappLog::where('status', 'pending')->count(),
                'today' => WhatsappLog::whereDate('created_at', $today)->count(),
                'quota' => (int) Setting::get('wa_quota', 2000),
                'used' => (int) Setting::get('wa_used', 1284),
            ],
            'engine' => $service->engine(),
            'simulation' => $service->isSimulation(),
            'enabled' => $service->isEnabled(),
        ]);
    }

    public function updateTemplate(Request $request, WhatsappTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $template->update([
            'body' => $data['body'],
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::record('whatsapp.template', 'Memperbarui template '.$template->name);

        return back()->with('success', 'Template pesan "'.$template->name.'" berhasil disimpan.');
    }

    public function test(Request $request, WhatsAppGatewayService $service): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $sent = $service->send($data['phone'], $data['message'], 'test');

        AuditLog::record('whatsapp.test', 'Uji kirim WhatsApp ke '.$data['phone']);

        return back()->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Pesan uji berhasil diproses. Periksa log pengiriman untuk detail.'
                : 'Pesan uji gagal diproses. Periksa konfigurasi gateway.'
        );
    }

    public function resend(WhatsappLog $log, WhatsAppGatewayService $service): RedirectResponse
    {
        $sent = $service->send(
            $log->phone,
            $log->message,
            $log->type,
            $log->student,
            $log->attendance,
        );

        AuditLog::record('whatsapp.resend', 'Kirim ulang notifikasi ke '.$log->phone);

        return back()->with($sent ? 'success' : 'error', $sent
            ? 'Notifikasi berhasil dikirim ulang ke '.$log->phone.'.'
            : 'Gagal mengirim ulang notifikasi. Periksa gateway.');
    }
}
