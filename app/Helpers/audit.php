<?php

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

if (!function_exists('audit_trail')) {
    function audit_trail($menu, $action, $description = null)
    {
        AuditTrail::create([
            'user_id'    => Auth::id(),
            'menu'       => $menu,
            'action'     => $action,
            'description'=> $description,
        ]);
    }
}
