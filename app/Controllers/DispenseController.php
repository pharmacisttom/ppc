<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Gateway\JhcisGateway;

class DispenseController
{
    /**
     * Main Primary Care Dispensing Workspace (ระบบจ่ายยา - รพ.สต.)
     */
    public function index(?string $pid = null): void
    {
        View::render('dispense/notice', [
            'pageTitle' => 'ระบบงานบริการคลินิก & ข้อแนะนำการใช้ JHCIS หลัก'
        ]);
    }

    /**
     * JSON API for Reactive Patient Switching
     */
    public function apiDetail(string $pid): void
    {
        $pidInt = (int)$pid;
        $vno = (int)Request::get('vno', 0);
        $detail = JhcisGateway::getDispensingDetail($pidInt, $vno ?: null);

        if (!$detail) {
            Response::json(['error' => 'Patient record not found in JHCIS'], 404);
            return;
        }

        Response::json([
            'status' => 'success',
            'data' => $detail
        ]);
    }

    /**
     * Printable Drug Stickers / Envelope Label
     */
    public function printStickers(string $pid): void
    {
        $pidInt = (int)$pid;
        $vno = (int)Request::get('vno', 0);
        $detail = JhcisGateway::getDispensingDetail($pidInt, $vno ?: null);

        if (!$detail) {
            Session::flash('error', 'ไม่พบข้อมูลสำหรับการพิมพ์สติกเกอร์');
            Response::redirect('/pcc/dispense');
            return;
        }

        $user = Auth::user();

        View::render('dispense/print_stickers', [
            'pageTitle' => 'พิมพ์ฉลากซองยา — ' . ($detail['patient']['full_name'] ?? 'ผู้รับบริการ'),
            'detail' => $detail,
            'user' => $user
        ], 'blank'); // 'blank' layout for direct thermal/A4 label printing
    }
}
