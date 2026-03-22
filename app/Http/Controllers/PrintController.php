<?php

namespace App\Http\Controllers;

use App\Models\ClaimRequest;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function claimReceipt(Request $request, ClaimRequest $claimRequest)
    {
        $user = $request->user();

        if ($user->role === 'user' && $claimRequest->claimant_id !== $user->id) {
            abort(403, 'You are not allowed to print this claim receipt.');
        }

        if (
            $user->role === 'staff' &&
            $claimRequest->foundItem->staff_id !== $user->id
        ) {
            abort(403, 'You are not allowed to print this claim receipt.');
        }

        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        return view('print.claim-receipt', compact('claimRequest'));
    }

    public function foundItemSlip(Request $request, FoundItem $foundItem)
    {
        $user = $request->user();

        if ($user->role === 'staff' && $foundItem->staff_id !== $user->id) {
            abort(403, 'You are not allowed to print this found item slip.');
        }

        if ($user->role === 'user') {
            abort(403, 'You are not allowed to print this found item slip.');
        }

        $foundItem->load(['staff', 'category']);

        return view('print.found-item-slip', compact('foundItem'));
    }

    public function lostItemReport(Request $request, LostItem $lostItem)
    {
        $user = $request->user();

        if ($user->role === 'user' && $lostItem->user_id !== $user->id) {
            abort(403, 'You are not allowed to print this lost item report.');
        }

        if ($user->role === 'staff') {
            abort(403, 'You are not allowed to print this lost item report.');
        }

        $lostItem->load(['user', 'category']);

        return view('print.lost-item-report', compact('lostItem'));
    }

    public function claimsSummary(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'You are not allowed to print this report.');
        }

        $claimRequests = ClaimRequest::with([
            'claimant',
            'foundItem.category',
            'approver',
        ])->latest()->get();

        return view('print.claims-summary', compact('claimRequests'));
    }
}
