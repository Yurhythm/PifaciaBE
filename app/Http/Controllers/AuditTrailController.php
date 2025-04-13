<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditTrail::with('user');

        if ($request->has('menu')) {
            $query->where('menu', $request->menu)->orderBy('created_at','DESC');
        }

        return response()->json($query->latest()->paginate(10));
    }
}
