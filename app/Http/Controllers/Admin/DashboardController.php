<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Fellowship;
use App\Models\Page;
use App\Models\Petition;
use App\Models\PetitionSignature;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

        $totalArticles = Page::count();
        $activeArticles = Page::where('status', 'active')->count();
        $draftArticles = Page::where('status', 'draft')->count();

        $totalFellowships = Fellowship::count();
        $activeFellowships = Fellowship::where('status', 'active')->count();
        $upcomingFellowships = Fellowship::where('start_date', '>', now())->count();

        $totalPetitions = Petition::count();
        $activePetitions = Petition::where('status', 'active')->count();
        $totalSignatures = PetitionSignature::where('is_verified', true)->count();
        $pendingSignatures = PetitionSignature::where('is_verified', false)->count();

        $totalComments = Comment::count();
        $pendingComments = Comment::where('is_approved', false)->count();

        $totalUsers = User::count();

        $recentArticles = Page::with('translations')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->translation($locale)?->title ?? $p->slug,
                'status' => $p->status,
                'created_at' => $p->created_at,
            ]);

        $recentPetitions = Petition::with('translations')
            ->withCount('verifiedSignatures')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->translation($locale)?->title ?? $p->slug,
                'status' => $p->status,
                'signatures' => $p->verified_signatures_count,
                'created_at' => $p->created_at,
            ]);

        $recentComments = Comment::with('page.translations')
            ->where('is_approved', false)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'body' => $c->body,
                'page_title' => $c->page?->translation($locale)?->title ?? '-',
                'created_at' => $c->created_at,
            ]);

        $topPetitions = Petition::with('translations')
            ->withCount('verifiedSignatures')
            ->having('verified_signatures_count', '>', 0)
            ->orderBy('verified_signatures_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'title' => $p->translation($locale)?->title ?? $p->slug,
                'signatures' => $p->verified_signatures_count,
                'goal' => $p->goal_count,
            ]);

        $avgProgress = $activePetitions > 0
            ? round(Petition::where('status', 'active')->get()->avg(fn ($p) => $p->progressPercent()))
            : 0;

        return view('pages.admin.dashboard-admin', compact(
            'totalArticles',
            'activeArticles',
            'draftArticles',
            'totalFellowships',
            'activeFellowships',
            'upcomingFellowships',
            'totalPetitions',
            'activePetitions',
            'totalSignatures',
            'pendingSignatures',
            'totalComments',
            'pendingComments',
            'totalUsers',
            'recentArticles',
            'recentPetitions',
            'recentComments',
            'topPetitions',
            'avgProgress',
        ));
    }
}
