<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

if (! function_exists('user_has_any_role')) {
    function user_has_any_role(?object $user, array $roles): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        if (method_exists($user, 'roles')) {
            return $user->roles()->whereIn('name', $roles)->exists();
        }

        return false;
    }
}

// Public homepage
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::get('/api/discover/open', function () {
    $circles = \App\Models\Circle::with('host:id,name')
        ->withCount(['slots as open_slots_count' => fn ($q) => $q->where('status', 'open')])
        ->where('visibility', 'public')
        ->where('status', 'open')
        ->having('open_slots_count', '>', 0)
        ->latest()
        ->limit(20)
        ->get()
        ->map(fn ($c) => [
            'title' => $c->title,
            'url' => route('circles.show', $c->slug),
            'amount' => $c->contribution_amount,
            'frequency' => $c->frequency,
            'open_slots' => $c->open_slots_count,
            'host' => $c->host->name,
            'line' => '₦'.number_format($c->contribution_amount).'/'.$c->frequency.' pool looking for '.$c->open_slots_count.' member'.($c->open_slots_count === 1 ? '' : 's'),
        ]);

    return response()->json(['data' => $circles]);
})->name('discover.api');



Route::get('/invite/{code}', function ($code) {
    $inviter = \App\Models\User::where('invite_code', $code)->firstOrFail();

    if (Auth::check()) {
        return redirect()->route('dashboard')
            ->with('message', 'You are already on Ajo. Invite friends with your own link.');
    }

    // Store for registration
    session(['invited_by' => $inviter->id]);

    return view('invites.user', compact('inviter', 'code'));
})->name('invite.user');


Route::get('/t/{slug}', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();

    if (Auth::check()) {
        $isMember = \App\Models\CommunityMember::where('community_id', $team->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        if ($isMember) {
            return redirect()->route('teams.feed', $team->slug);
        }

        return redirect()->route('teams.show', $team->slug);
    }

    session(['team_invite_slug' => $team->slug]);

    return view('invites.team', compact('team'));
})->name('invite.team.public');

Route::get('/p/{slug}/{id}', function ($slug, $id) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $project = $team->projects()->findOrFail($id);

    if (Auth::check()) {
        if ($team->isMember(Auth::user())) {
            return redirect()->route('projects.show', [$team->slug, $project->id]);
        }

        return redirect()->route('teams.show', $team->slug)
            ->with('message', 'Join the team to open this project.');
    }

    session([
        'team_invite_slug' => $team->slug,
        'project_invite_id' => $project->id,
    ]);

    return view('invites.project', compact('team', 'project'));
})->name('invite.project.public');

// Dashboard (requires login)
Route::get('/dashboard', function () {
    $user = Auth::user();

    $myTeams = \App\Models\CommunityMember::where('user_id', $user->id)
        ->with('community')
        ->where('status', 'active')
        ->latest()
        ->take(5)
        ->get();

    $adminTeamIds = \App\Models\CommunityMember::where('user_id', $user->id)
        ->where('status', 'active')
        ->where('role', 'admin')
        ->pluck('community_id');

    $pendingJoins = \App\Models\CommunityMember::with(['user', 'community'])
        ->whereIn('community_id', $adminTeamIds)
        ->where('status', 'pending')
        ->latest()
        ->take(10)
        ->get();

    $donorProjectIds = \App\Models\ProjectContribution::where('user_id', $user->id)
        ->where('status', 'confirmed')
        ->pluck('project_id')
        ->unique();

    $pendingVotes = \App\Models\VaultAction::with(['project.community'])
        ->where('status', 'pending')
        ->whereIn('project_id', $donorProjectIds)
        ->whereDoesntHave('votes', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->latest()
        ->take(10)
        ->get();

    

    $myTeamIds = \App\Models\CommunityMember::where('user_id', $user->id)
        ->where('status', 'active')
        ->pluck('community_id');

    $friendIds = \App\Models\CommunityMember::whereIn('community_id', $myTeamIds)
        ->where('status', 'active')
        ->where('user_id', '!=', $user->id)
        ->pluck('user_id')
        ->unique();
    $authorIds = $friendIds->push($user->id)->unique();

    $feed = \App\Models\Activity::with(['user', 'community', 'parent'])
        ->withCount(['likes', 'comments', 'reposts', 'quotes'])
        ->where(function ($q) use ($authorIds, $myTeamIds) {
            $q->where(function ($q2) use ($authorIds) {
                $q2->whereNull('community_id')
                ->whereIn('user_id', $authorIds);
            })->orWhereIn('community_id', $myTeamIds);
        })
        ->latest()
        ->limit(40)
        ->get();
    $teamOptions = \App\Models\CommunityMember::where('user_id', $user->id)
        ->where('status', 'active')
        ->with('community')
        ->get();

    $suggestions = \App\Models\User::whereNotIn('id', $friendIds->push($user->id)->unique())
        ->inRandomOrder()
        ->take(5)
        ->get();
    // pass to view
    $joinedIds = \App\Models\CommunityMember::where('user_id', $user->id)
        ->whereIn('status', ['active', 'pending'])
        ->pluck('community_id');

    $suggestedTeams = \App\Models\Community::where('is_discoverable', true)
        ->whereNotIn('id', $joinedIds)
        ->where('visibility', 'public')
        ->latest()
        ->take(3)
        ->get();

    return view('dashboard', compact(
        'myTeams',
        'pendingJoins',
        'pendingVotes',
        'feed',
        'teamOptions',
        'suggestions',
        'suggestedTeams'
    ));
})->middleware(['auth'])->name('dashboard');

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

Route::get('/learn', function () {
    $category = request('category');
    $posts = \App\Models\BlogPost::with('author')->published()
        ->when($category, fn ($q) => $q->where('category', $category))
        ->latest('published_at')->paginate(9);
    return view('blog.index', [
        'posts' => $posts,
        'categories' => \App\Models\BlogPost::categories(),
        'category' => $category,
    ]);
})->name('blog.index');

Route::get('/learn/{slug}', function ($slug) {
    $post = \App\Models\BlogPost::with('author')->published()->where('slug', $slug)->firstOrFail();
    $related = \App\Models\BlogPost::published()
        ->where('category', $post->category)->where('id', '!=', $post->id)
        ->latest('published_at')->take(3)->get();
    return view('blog.show', compact('post', 'related'));
})->name('blog.show');

Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/terms', 'terms')->name('terms');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    

    

    

    // ====================== REPORTS ======================
Route::get('/reports', function () {
    if (!user_has_any_role(request()->user(), ['admin', 'treasurer', 'auditor', 'chairperson'])) {
        abort(403);
    }

    $totalContributions = \App\Models\MonthlyContribution::where('status', 'approved')->sum('total_amount');
    $totalWelfareIn = \App\Models\MonthlyContribution::where('status', 'approved')->sum('welfare_amount');
    $totalInvestment = \App\Models\MonthlyContribution::where('status', 'approved')->sum('investment_amount');
    $totalOperations = \App\Models\MonthlyContribution::where('status', 'approved')->sum('operations_amount');

    // Add penalties + reactivation fees to operations later if needed
    $totalPenalties = \App\Models\MonthlyContribution::where('status', 'approved')->sum('penalty_amount');

    $totalWelfareOut = \App\Models\WelfareRequest::where('status', 'disbursed')->sum('approved_amount');
    $welfareBalance = $totalWelfareIn - $totalWelfareOut;

    $totalUnits = \App\Models\InvestmentUnit::sum('total_units');
    $pendingWelfare = \App\Models\WelfareRequest::where('status', 'pending')->count();
    $awaitingDisbursement = \App\Models\WelfareRequest::where('status', 'approved')->count();
    $totalAssets = \App\Models\InvestmentAsset::sum('current_value');

    return view('reports.index', compact(
        'totalContributions',
        'totalWelfareIn',
        'totalWelfareOut',
        'welfareBalance',
        'totalInvestment',
        'totalOperations',
        'totalPenalties',
        'totalUnits',
        'pendingWelfare',
        'awaitingDisbursement',
        'totalAssets'
    ));
})->name('reports');



    // Investment Cycles
// ====================== CYCLES ======================
Route::get('/cycles', function () {
    $cycles = \App\Models\InvestmentCycle::latest()->get();
    return view('cycles.index', compact('cycles'));
})->name('cycles');

Route::get('/cycles/create', function () {
    if (!Auth::check() || !user_has_any_role(Auth::user(), ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }
    return view('cycles.create');
})->name('cycles.create');

Route::post('/cycles', function (Illuminate\Http\Request $request) {
    if (!Auth::check() || !user_has_any_role(Auth::user(), ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'notes' => 'nullable|string',
    ]);

    $validated['status'] = 'upcoming';

    \App\Models\InvestmentCycle::create($validated);

    return redirect()->route('cycles')->with('message', 'Investment cycle created successfully!');
})->name('cycles.store');



// Settings
Route::get('/settings', function () {
    if (!Auth::check() || !user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    return view('settings.index', [
        'monthly_contribution_amount' => \App\Models\Setting::get('monthly_contribution_amount'),
        'bank_name'                   => \App\Models\Setting::get('bank_name'),
        'account_number'              => \App\Models\Setting::get('account_number'),
        'account_name'                => \App\Models\Setting::get('account_name'),
        'unit_price'                  => \App\Models\Setting::get('unit_price', 100),
        'reactivation_fee'            => \App\Models\Setting::get('reactivation_fee'),
    ]);
})->name('settings');

Route::post('/settings', function (Illuminate\Http\Request $request) {
    if (!Auth::check() || !user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $validated = $request->validate([
        'monthly_contribution_amount' => 'required|numeric|min:1000',
        'bank_name'                   => 'required|string|max:255',
        'account_number'              => 'required|string|max:20',
        'account_name'                => 'required|string|max:255',
        'unit_price'                  => 'required|numeric|min:1',
        'reactivation_fee'            => 'required|numeric|min:0',
    ]);

    foreach ($validated as $key => $value) {
        \App\Models\Setting::set($key, $value);
    }

    return back()->with('message', 'Settings updated successfully.');
})->name('settings.update');

// Reactivation fee
Route::get('/reactivation', function () {
    return view('reactivation.index', [
        'fee' => \App\Models\Setting::get('reactivation_fee', 10000),
        'bank_name' => \App\Models\Setting::get('bank_name'),
        'account_number' => \App\Models\Setting::get('account_number'),
        'account_name' => \App\Models\Setting::get('account_name'),
    ]);
})->name('reactivation');

Route::post('/reactivation', function (Illuminate\Http\Request $request) {
    $request->validate([
        'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    $path = $request->file('proof_of_payment')->store('reactivation_proofs', 'public');

    // For now we just store the proof and notify admin.
    // You can later create a ReactivationRequest model if needed.

    $admins = \App\Models\User::role(['admin', 'treasurer', 'chairperson'])->get();

    foreach ($admins as $admin) {
        $admin->notify(new \App\Notifications\ContributionSubmitted(
            // Temporary reuse - better to create a dedicated notification later
            new \App\Models\MonthlyContribution([
                'user_id' => $request->user()->getAuthIdentifier(),
                'total_amount' => \App\Models\Setting::get('reactivation_fee'),
                'status' => 'pending',
            ])
        ));
    }

    // Simple approach for now: Admin will manually unsuspend after verifying payment
    return back()->with('message', 'Reactivation request submitted. Please wait for Admin/Treasurer approval.');
})->name('reactivation.store');

// Admin manually unsuspends a user
Route::post('/users/{id}/unsuspend', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $user = \App\Models\User::findOrFail($id);
    $user->update([
        'is_suspended' => false,
        'missed_contributions_count' => 0,
    ]);

    return back()->with('message', $user->name . ' has been unsuspended.');
})->name('users.unsuspend');

// Buy units (member)
Route::get('/units/buy', function () {
    return view('units.buy', [
        'unit_price' => (float) \App\Models\Setting::get('unit_price', 100),
        'bank_name' => \App\Models\Setting::get('bank_name'),
        'account_number' => \App\Models\Setting::get('account_number'),
        'account_name' => \App\Models\Setting::get('account_name'),
        'purchases' => request()->user()->unitPurchases()->latest()->get(),
    ]);
})->name('units.buy');

Route::post('/units/buy', function (Illuminate\Http\Request $request) {
    if (Auth::check() && Auth::user()->is_suspended) {
        return back()->with('error', 'Your account is suspended.');
    }

    $unitPrice = (float) \App\Models\Setting::get('unit_price', 100);

    $validated = $request->validate([
        'units' => 'required|numeric|min:1',
        'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ]);

    $units = (float) $validated['units'];
    $amount = $units * $unitPrice;
    $path = $request->file('proof_of_payment')->store('unit_purchase_proofs', 'public');

    $purchase = \App\Models\UnitPurchase::create([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'units' => $units,
        'unit_price' => $unitPrice,
        'amount_paid' => $amount,
        'proof_of_payment' => $path,
        'status' => 'pending',
    ]);

    // Notify officers
    $officers = \App\Models\User::role(['admin', 'investment_officer', 'treasurer', 'chairperson'])->get();
    foreach ($officers as $officer) {
        // Reuse a simple notification or create UnitPurchaseSubmitted later
    }

    return redirect()->route('units.buy')->with('message', 'Unit purchase submitted. Waiting for approval.');
})->name('units.buy.store');

// Manage unit purchases (officers)
Route::get('/units/manage', function () {
    if (!request()->user()->hasAnyRole(['admin', 'investment_officer', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $purchases = \App\Models\UnitPurchase::with('user')->latest()->get();
    return view('units.manage', compact('purchases'));
})->name('units.manage');

Route::post('/units/{id}/approve', function ($id) {
    if (!request()->user()->hasAnyRole(['admin', 'investment_officer', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $purchase = \App\Models\UnitPurchase::with('user')->findOrFail($id);

    if ($purchase->status !== 'pending') {
        return back()->with('error', 'Already processed.');
    }

    $purchase->update([
        'status' => 'approved',
        'approved_by' => request()->user()->id,
        'approved_at' => now(),
    ]);

    // Credit general units
    $unit = \App\Models\InvestmentUnit::firstOrCreate(['user_id' => $purchase->user_id]);
    $unit->total_units += $purchase->units;
    $unit->total_invested += $purchase->amount_paid;
    $unit->average_price = $unit->total_units > 0
        ? $unit->total_invested / $unit->total_units
        : 0;
    $unit->save();

    return back()->with('message', 'Unit purchase approved. Units credited.');
})->name('units.approve');

Route::post('/units/{id}/reject', function ($id) {
    $user = request()->user();

    if (!$user || !$user->hasAnyRole(['admin', 'investment_officer', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $purchase = \App\Models\UnitPurchase::findOrFail($id);
    $purchase->update([
        'status' => 'rejected',
        'approved_by' => $user->id,
        'approved_at' => now(),
    ]);

    return back()->with('message', 'Unit purchase rejected.');
})->name('units.reject');

// List offerings (all members can view open ones)
Route::get('/offerings', function () {
    $user = request()->user();

    $offerings = \App\Models\InvestmentOffering::with('creator')
        ->when(!$user || !$user->hasAnyRole(['admin', 'investment_officer', 'chairperson']), function ($q) {
            $q->whereIn('status', ['open', 'funded', 'closed']);
        })
        ->latest()
        ->get();

    return view('offerings.index', compact('offerings'));
})->name('offerings');

// Create (officers only)
Route::get('/offerings/create', function () {
    $user = request()->user();

    if (!$user || !$user->hasAnyRole(['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    return view('offerings.create', [
        'unit_price' => (float) \App\Models\Setting::get('unit_price', 100),
    ]);
})->name('offerings.create');

Route::post('/offerings', function (Illuminate\Http\Request $request) {
    $user = $request->user();

    if (!$user || !$user->hasAnyRole(['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $unitPrice = (float) \App\Models\Setting::get('unit_price', 100);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string',
        'description' => 'nullable|string',
        'target_capital' => 'required|numeric|min:1000',
        'min_units' => 'required|numeric|min:0.01',
        'start_date' => 'nullable|date',
        'maturity_date' => 'nullable|date|after:start_date',
        'status' => 'required|in:draft,open',
        'images' => 'nullable|array|max:3',
        'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $imagePaths = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imagePaths[] = $image->store('offering_images', 'public');
        }
    }

    \App\Models\InvestmentOffering::create([
        'name' => $validated['name'],
        'type' => $validated['type'],
        'description' => $validated['description'] ?? null,
        'images' => $imagePaths ?: null,
        'target_capital' => $validated['target_capital'],
        'target_units' => $validated['target_capital'] / $unitPrice,
        'min_units' => $validated['min_units'],
        'unit_price_at_open' => $unitPrice,
        'start_date' => $validated['start_date'] ?? null,
        'maturity_date' => $validated['maturity_date'] ?? null,
        'status' => $validated['status'],
        'created_by' => Auth::id(),
    ]);

    return redirect()->route('offerings')->with('message', 'Investment offering created.');
})->name('offerings.store');

// Open a draft offering
Route::post('/offerings/{id}/open', function ($id) {
    $user = Auth::user();

    if (!user_has_any_role($user, ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $offering = \App\Models\InvestmentOffering::findOrFail($id);
    $offering->update(['status' => 'open']);

    return back()->with('message', 'Offering is now open for pledges.');
})->name('offerings.open');

// Close an offering
Route::post('/offerings/{id}/close', function ($id) {
    $user = Auth::user();

    if (!user_has_any_role($user, ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $offering = \App\Models\InvestmentOffering::findOrFail($id);
    $offering->update(['status' => 'closed']);

    return back()->with('message', 'Offering closed.');
})->name('offerings.close');

// Show offering + pledge form
Route::get('/offerings/{id}', function ($id) {
    $offering = \App\Models\InvestmentOffering::with('creator')->findOrFail($id);
    $user = Auth::user();

    if ($offering->status === 'draft' && !user_has_any_role($user, ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $generalUnits = $user && $user->investmentUnit ? $user->investmentUnit->total_units : 0;
    $myCommitments = $user
        ? \App\Models\InvestmentCommitment::where('offering_id', $id)
            ->where('user_id', $user->id)
            ->latest()
            ->get()
        : collect();

    return view('offerings.show', compact('offering', 'generalUnits', 'myCommitments'));
})->name('offerings.show');

// Member pledges units
Route::post('/offerings/{id}/pledge', function (Illuminate\Http\Request $request, $id) {
    $offering = \App\Models\InvestmentOffering::findOrFail($id);

    if ($offering->status !== 'open') {
        return back()->with('error', 'This offering is not open for pledges.');
    }

    if (($request->user()?->is_suspended ?? false)) {
        return back()->with('error', 'Your account is suspended.');
    }

    $validated = $request->validate([
        'units_pledged' => 'required|numeric|min:' . $offering->min_units,
    ]);

    $units = (float) $validated['units_pledged'];
    $generalUnits = optional($request->user()->investmentUnit)->total_units ?? 0;

    // Pending pledges already count against available units
    $pendingUnits = \App\Models\InvestmentCommitment::where('user_id', $request->user()->getAuthIdentifier())
        ->where('status', 'pending')
        ->sum('units_pledged');

    $available = $generalUnits - $pendingUnits;

    if ($units > $available) {
        return back()->with('error', "You only have {$available} general units available.");
    }

    \App\Models\InvestmentCommitment::create([
        'offering_id' => $offering->id,
        'user_id' => $request->user()->getAuthIdentifier(),
        'units_pledged' => $units,
        'status' => 'pending',
    ]);

    return back()->with('message', 'Pledge submitted. Waiting for approval.');
})->name('offerings.pledge');

// Manage pledges (officers)
Route::get('/commitments/manage', function () {
    if (!user_has_any_role(Auth::user(), ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $commitments = \App\Models\InvestmentCommitment::with(['user', 'offering'])
        ->latest()
        ->get();

    return view('commitments.manage', compact('commitments'));
})->name('commitments.manage');

Route::post('/commitments/{id}/approve', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $commitment = \App\Models\InvestmentCommitment::with(['user', 'offering'])->findOrFail($id);

    if ($commitment->status !== 'pending') {
        return back()->with('error', 'Already processed.');
    }

    $userUnit = \App\Models\InvestmentUnit::where('user_id', $commitment->user_id)->first();
    $generalUnits = $userUnit->total_units ?? 0;

    if ($commitment->units_pledged > $generalUnits) {
        return back()->with('error', 'Member no longer has enough general units.');
    }

    // Deduct from general units
    $userUnit->total_units -= $commitment->units_pledged;
    // Keep total_invested; average_price recalculated
    $userUnit->average_price = $userUnit->total_units > 0
        ? $userUnit->total_invested / $userUnit->total_units
        : 0;
    $userUnit->save();

    // Add to holdings
    $holding = \App\Models\InvestmentHolding::firstOrCreate(
        [
            'offering_id' => $commitment->offering_id,
            'user_id' => $commitment->user_id,
        ],
        ['units' => 0, 'average_cost' => $commitment->offering->unit_price_at_open]
    );

    $oldUnits = $holding->units;
    $newUnits = $oldUnits + $commitment->units_pledged;
    $holding->average_cost = $newUnits > 0
        ? (($holding->average_cost * $oldUnits) + ($commitment->offering->unit_price_at_open * $commitment->units_pledged)) / $newUnits
        : $commitment->offering->unit_price_at_open;
    $holding->units = $newUnits;
    $holding->save();

    $commitment->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    // Auto-mark funded if target reached
    $offering = $commitment->offering;
    if ($offering->approvedUnits() >= $offering->target_units) {
        $offering->update(['status' => 'funded']);
    }

    // Notify bookkeepers
    $bookkeepers = \App\Models\User::role(['admin', 'treasurer', 'auditor', 'chairperson'])->get();
    // Optional: notify each

    return back()->with('message', 'Pledge approved. Units allocated to investment.');
})->name('commitments.approve');

Route::post('/commitments/{id}/reject', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    $commitment = \App\Models\InvestmentCommitment::findOrFail($id);
    $commitment->update([
        'status' => 'rejected',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    return back()->with('message', 'Pledge rejected.');
})->name('commitments.reject');

// Discover & list
Route::get('/teams', function () {
    $joinedIds = \App\Models\CommunityMember::where('user_id', Auth::id())
        ->whereIn('status', ['active', 'pending'])
        ->pluck('community_id');

    $teams = \App\Models\Community::with('creator')
        ->withCount(['members' => fn ($q) => $q->where('status', 'active')])
        ->where('is_discoverable', true)
        ->whereNotIn('id', $joinedIds)
        ->latest()
        ->paginate(12);

    $projects = \App\Models\Project::with(['community', 'creator'])
        ->where('visibility', 'public')
        ->whereIn('status', ['active', 'goal_reached'])
        ->whereHas('community', fn ($q) => $q->where('is_discoverable', true))
        ->latest()
        ->take(12)
        ->get();

    $myTeamIds = $joinedIds;

    return view('teams.index', compact('teams', 'projects', 'myTeamIds'));
})->middleware('auth')->name('teams.index');

Route::get('/teams/mine', function () {
    $memberships = \App\Models\CommunityMember::where('user_id', Auth::id())
        ->with([
            'community' => function ($q) {
                $q->withCount(['members' => fn ($m) => $m->where('status', 'active')]);
            }
        ])
        ->where('status', 'active')
        ->latest()
        ->get();

    return view('teams.mine', compact('memberships'));
})->middleware('auth')->name('teams.mine');

// Create
Route::get('/teams/create', fn () => view('teams.create'))->name('teams.create');

Route::post('/teams', function (Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:1000',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'visibility' => 'required|in:public,private',
        'member_limit' => 'nullable|integer|min:2|max:5000',
    ]);

    $cover = null;
    if ($request->hasFile('cover_image')) {
        $cover = $request->file('cover_image')->store('community_covers', 'public');
    }

    $isPublic = $validated['visibility'] === 'public';

    $community = \App\Models\Community::create([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? null,
        'cover_image' => $cover,
        'created_by' => $request->user()->id,
        'visibility' => $validated['visibility'],
        'is_discoverable' => $isPublic,
        'member_limit' => $validated['member_limit'] ?? null,
        'invites_open' => true,
        'members_count' => 1,
    ]);

    \App\Models\CommunityMember::create([
        'community_id' => $community->id,
        'user_id' => $request->user()->id,
        'role' => 'admin',
        'status' => 'active',
        'joined_at' => now(),
    ]);

    return redirect()
        ->route('teams.feed', $community->slug)
        ->with('message', 'Team created. Share the invite link so people can join.');
})->middleware('throttle:create-team')->name('teams.store');

// Show (members only for full content)
Route::get('/teams/{slug}', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $isMember = $team->isMember(\Illuminate\Support\Facades\Auth::user());
    $isAdmin = $team->isAdmin(\Illuminate\Support\Facades\Auth::user());
    $membership = $team->members()->where('user_id', request()->user()?->id)->first();

    // Load featured circle
    $team->load('featuredProject');

    $members = $isMember
        ? $team->activeMembers()->with('user')->get()
        : collect();

    $pending = $isAdmin
        ? $team->pendingMembers()->with('user')->get()
        : collect();

    return view('teams.show', compact('team', 'isMember', 'isAdmin', 'membership', 'members', 'pending'));
})->name('teams.show');

// Request to join
Route::post('/teams/{slug}/join', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();

    if (!$team->invitesOpen()) {
        return back()->with('error', 'This team is not accepting new members.');
    }

    if ($team->isFull()) {
        return back()->with('error', 'This team is full.');
    }

    $existing = $team->members()->where('user_id', request()->user()?->id)->first();

    if ($existing) {
        if ($existing->status === 'active') {
            return redirect()->route('teams.feed', $team->slug)
                ->with('message', 'You are already a member.');
        }
        if ($existing->status === 'pending') {
            return back()->with('error', 'Your request is already pending.');
        }
        $existing->update(['status' => 'pending', 'role' => 'member']);
    } else {
        \App\Models\CommunityMember::create([
            'community_id' => $team->id,
            'user_id' => request()->user()?->id,
            'role' => 'member',
            'status' => 'pending',
        ]);
    }

    $admins = $team->members()->where('status', 'active')->where('role', 'admin')->get();
    foreach ($admins as $admin) {
        $adminUser = \App\Models\User::find($admin->user_id);
        if ($adminUser) {
            $adminUser->notify(new \App\Notifications\JoinRequestReceived($team, request()->user()));
        }
    }

    return back()->with('message', 'Join request sent. Waiting for admin approval.');
})->name('teams.join');

// Approve / reject join
Route::post('/teams/{slug}/members/{memberId}/approve', function ($slug, $memberId) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    if (!$team->isAdmin(request()->user())) abort(403);

    if ($team->isFull()) {
        return back()->with('error', 'Team is full. Raise the limit before approving.');
    }

    $member = $team->members()->where('id', $memberId)->where('status', 'pending')->firstOrFail();
    $memberUser = \App\Models\User::findOrFail($member->user_id);
    $member->update(['status' => 'active', 'joined_at' => now()]);
    $team->increment('members_count');

    $memberUser->notify(new \App\Notifications\JoinRequestResolved($team, 'approved'));
    $memberUser->notify(new \App\Notifications\TeamJoinApproved($team));

    \App\Models\Activity::log([
        'user_id' => $member->user_id,
        'community_id' => $team->id,
        'type' => 'member_joined',
        'title' => $memberUser->name . ' joined ' . $team->name,
        'url' => route('teams.feed', $team->slug),
    ]);

    return redirect()
    ->route('teams.show', $team->slug)
    ->with('message', 'Member approved.');
})->name('teams.members.approve');

Route::post('/teams/{slug}/members/{memberId}/reject', function ($slug, $memberId) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    if (!$team->isAdmin(request()->user())) abort(403);

    $member = $team->members()->where('id', $memberId)->where('status', 'pending')->firstOrFail();
    $memberUser = \App\Models\User::findOrFail($member->user_id);
    $member->update(['status' => 'rejected']);

    $memberUser->notify(new \App\Notifications\JoinRequestResolved($team, 'rejected'));

    return back()->with('message', 'Request rejected.');
})->name('teams.members.reject');

// Promote to admin
Route::post('/teams/{slug}/members/{memberId}/make-admin', function ($slug, $memberId) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();

    if (!$team->isAdmin(request()->user())) {
        abort(403);
    }

    $member = $team->members()->where('id', $memberId)->where('status', 'active')->firstOrFail();
    $memberUser = \App\Models\User::findOrFail($member->user_id);
    $member->update(['role' => 'admin']);

    return back()->with('message', $memberUser->name . ' is now an admin.');
})->name('teams.members.make-admin');

// List projects in a team (members only)
Route::get('/teams/{slug}/projects', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $user = \Illuminate\Support\Facades\Auth::user();

    // Not logged in → login, then return here
    if (!$user) {
        return redirect()->guest(route('login'));
    }

    // Logged in but not a member → join page
    if (!$team->isMember($user)) {
        return redirect()->route('teams.join-link', $slug)
            ->with('message', 'Join the team first to see its projects.');
    }

    $projects = $team->projects()->with('vault', 'creator', 'payoutSlots')->latest()->get();
    $isAdmin = $team->isAdmin($user);

    return view('projects.index', compact('team', 'projects', 'isAdmin'));
})->name('projects.index');

// Create project
Route::get('/teams/{slug}/projects/create', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user || !$team->isMember($user)) abort(403);

    return view('projects.create', compact('team'));
})->name('projects.create');

Route::post('/teams/{slug}/projects', function (Illuminate\Http\Request $request, $slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user || !$team->isMember($user)) abort(403);

    $isRotational = $request->input('project_type') === 'rotational';

    $validated = $request->validate([
        'title' => 'required|string|max:200',
        'description' => 'nullable|string|max:5000',
        'project_type' => 'required|in:goal,rotational',
        'visibility' => 'required|in:public,private',
        'goal_amount' => $isRotational ? 'nullable|numeric|min:0' : 'required|numeric|min:1000',
        'deadline' => 'nullable|date|after:today',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'contribution_amount' => $isRotational ? 'required|numeric|min:100' : 'nullable|numeric',
        'frequency' => $isRotational ? 'required|in:daily,weekly,monthly' : 'nullable',
        'slots_total' => $isRotational ? 'required|integer|min:2|max:31' : 'nullable|integer',
        'start_date' => $isRotational ? 'required|date' : 'nullable|date',
        'grace_days' => 'nullable|integer|min:0|max:14',
        'late_fee' => 'nullable|numeric|min:0',
        'missed_payment_action' => 'nullable|in:mark,fine,remove',
    ]);

    $cover = null;
    if ($request->hasFile('cover_image')) {
        $cover = $request->file('cover_image')->store('project_covers', 'public');
    }

    $project = \App\Models\Project::create([
        'community_id' => $team->id,
        'creator_id' => $user->id,
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'summary' => $validated['description'] ?? null,
        'goal_amount' => $validated['goal_amount'] ?? 0,
        'deadline' => $validated['deadline'] ?? null,
        'cover_image' => $cover,
        'status' => 'active',
        'project_type' => $validated['project_type'],
        'visibility' => $validated['visibility'],
        'contribution_amount' => $validated['contribution_amount'] ?? null,
        'frequency' => $validated['frequency'] ?? null,
        'slots_total' => $validated['slots_total'] ?? null,
        'start_date' => $validated['start_date'] ?? null,
        'grace_days' => $validated['grace_days'] ?? 3,
        'late_fee' => $validated['late_fee'] ?? 0,
        'missed_payment_action' => $validated['missed_payment_action'] ?? 'mark',
    ]);

    // Locked vault (still useful for goal projects; optional for circles)
    \App\Models\ProjectVault::create([
        'project_id' => $project->id,
        'balance' => 0,
        'status' => 'locked',
    ]);

    // Auto-create open slots for rotational / circle projects
    if ($project->project_type === 'rotational' && $project->slots_total) {
        $start = $project->start_date ? \Carbon\Carbon::parse($project->start_date) : now();
        for ($i = 1; $i <= $project->slots_total; $i++) {
            $due = match ($project->frequency) {
                'daily' => $start->copy()->addDays($i - 1),
                'weekly' => $start->copy()->addWeeks($i - 1),
                default => $start->copy()->addMonths($i - 1),
            };
            \App\Models\ProjectPayoutSlot::create([
                'project_id' => $project->id,
                'position' => $i,
                'status' => 'open',
                'eligibility' => 'new',
                'scheduled_payout_date' => $due,
            ]);
        }
    }

    \App\Models\Activity::log([
        'user_id' => $user->id,
        'community_id' => $team->id,
        'project_id' => $project->id,
        'type' => 'project_created',
        'title' => $user->name . ' launched “' . $project->title . '”',
        'body' => $project->project_type === 'rotational'
            ? 'Circle · ₦' . number_format($project->contribution_amount, 0) . ' · ' . $project->frequency
            : 'Goal ₦' . number_format($project->goal_amount, 0),
        'url' => route('projects.show', [$team->slug, $project->id]),
    ]);

    return redirect()->route('projects.show', [$team->slug, $project->id])
        ->with('message', $project->project_type === 'rotational'
            ? 'Circle created. Slots are open.'
            : 'Project created. Vault is locked and ready to receive contributions.');
})->middleware('throttle:create-project')->name('projects.store');

// Show project
Route::get('/teams/{slug}/projects/{id}', function ($slug, $id) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $user = \Illuminate\Support\Facades\Auth::user();

    // Not logged in → login, then return here
    if (!$user) {
        return redirect()->guest(route('login'));
    }

    // Logged in but not a member → join page
    if (!$team->isMember($user)) {
        return redirect()->route('teams.join-link', $slug)
            ->with('message', 'Join the team first to open this project.');
    }

    $project = $team->projects()->with(['vault', 'creator'])->findOrFail($id);
    $contributions = $project->contributions()->with('user')->latest()->get();
    $currentUserId = $user->id;
    $isAdmin = $team->isAdmin($user);
    $isCreator = $project->creator_id === $currentUserId;
    $pendingAction = $project->vaultActions()->with(['votes.user', 'requester'])->where('status', 'pending')->first();
    $isDonor = $project->confirmedContributions()->where('user_id', $currentUserId)->exists();
    $myVote = $pendingAction
        ? $pendingAction->votes->firstWhere('user_id', $currentUserId)
        : null;
    $donorCount = $project->confirmedContributions()->distinct('user_id')->count('user_id');

    $bank = $team->bankDetails();

    return view('projects.show', compact(
        'team', 'project', 'contributions', 'isAdmin', 'isCreator', 'bank',
        'pendingAction', 'isDonor', 'myVote', 'donorCount'
    ));
})->name('projects.show');

    // Contribute
    Route::post('/teams/{slug}/projects/{id}/contribute', function (Illuminate\Http\Request $request, $slug, $id) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        if (!$team->isMember($currentUser)) abort(403);

        $project = $team->projects()->with(['vault', 'community'])->findOrFail($id);

        if ($project->vault->status !== 'locked') {
            return back()->with('error', 'This vault is no longer accepting contributions.');
        }
        if (!in_array($project->status, ['active', 'goal_reached'])) {
            return back()->with('error', 'This project is not accepting contributions.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = $request->file('proof_of_payment')->store('project_contribution_proofs', 'public');

        $contribution = \App\Models\ProjectContribution::create([
            'project_id' => $project->id,
            'user_id' => $currentUser->id,
            'amount' => $validated['amount'],
            'proof_of_payment' => $path,
            'proof_path' => $path,
            'status' => 'pending',
        ]);

        $contribution->load(['project.community', 'user']);

        $notifyUsers = collect([$project->creator]);
        $admins = $team->members()->where('status', 'active')->where('role', 'admin')->with('user')->get();
        foreach ($admins as $a) {
            $adminUser = $a->user()->first();
            if ($adminUser) {
                $notifyUsers->push($adminUser);
            }
        }
        $notifyUsers->unique('id')->each(function ($user) use ($contribution, $currentUser) {
            if ($user->id !== $currentUser->id) {
                $user->notify(new \App\Notifications\ProjectContributionSubmitted($contribution));
            }
        });

        return back()->with('message', 'Contribution submitted. Waiting for confirmation.');
    })->middleware('throttle:money')->name('projects.contribute');

    Route::get('/teams/{slug}/projects/{projectId}/contributions/{contributionId}/confirm', function ($slug, $projectId) {
        return redirect()->route('projects.show', [$slug, $projectId])
            ->with('message', 'Use the Confirm button on the project page.');
    })->middleware('auth');

    // Confirm contribution (community admin or project creator)
    Route::post('/teams/{slug}/projects/{projectId}/contributions/{contributionId}/confirm', function ($slug, $projectId, $contributionId) {
        $team = \App\Models\Community::where('slug', $slug)->first();
        if (!$team) {
            return back()->with('error', 'Team not found: ' . $slug);
        }

        $project = $team->projects()->with('vault')->find($projectId);
        if (!$project) {
            return back()->with('error', 'Project ' . $projectId . ' is not on this team.');
        }

        $currentUser = \Illuminate\Support\Facades\Auth::user();

        if (!$team->isAdmin($currentUser) && $project->creator_id !== ($currentUser?->id ?? null)) {
            abort(403);
        }

        $contribution = $project->contributions()->where('id', $contributionId)->first();
        if (!$contribution) {
            return back()->with('error', 'Contribution ' . $contributionId . ' is not on this project.');
        }
        if ($contribution->status !== 'pending') {
            return back()->with('error', 'Contribution is already ' . $contribution->status . '.');
        }

        if (!$project->vault->isLocked()) {
            return back()->with('error', 'Vault is unlocked; cannot add contributions.');
        }

        $contribution->update([
            'status' => 'confirmed',
            'confirmed_by' => \Illuminate\Support\Facades\Auth::id(),
            'confirmed_at' => now(),
        ]);

        $project->vault->increment('balance', $contribution->amount);

        // Lock reserved slot → active on first confirmed contribution (circles)
        if ($project->project_type === 'rotational') {
            $slot = $project->payoutSlots()
                ->where('user_id', $contribution->user_id)
                ->where('status', 'reserved')
                ->first();

            if ($slot) {
                $slot->update(['status' => 'active']);
            }
        }

        $vaultBalance = $project->vault->fresh()->balance;

        // Project goal reached
        if ($vaultBalance >= $project->goal_amount && $project->status === 'active') {
            $project->update(['status' => 'goal_reached']);

            $title = 'Project funded: ' . $project->title;
            $already = \App\Models\Activity::where('type', 'milestone')
                ->where('community_id', $team->id)
                ->where('title', $title)
                ->exists();

                        if (!$already) {
                \App\Models\Activity::log([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'community_id' => $team->id,
                    'project_id' => $project->id,
                    'type' => 'milestone',
                    'title' => $title,
                    'body' => 'Goal reached · ₦' . number_format($vaultBalance),
                    'url' => route('projects.show', [$team->slug, $project->id]),
                ]);
            }

            $recipients = collect([$project->creator])->filter();
            $admins = $team->members()
                ->where('status', 'active')
                ->where('role', 'admin')
                ->with('user')
                ->get()
                ->pluck('user');

            foreach ($recipients->merge($admins)->unique('id') as $person) {
                if (!$person || !$person->phone || !$person->phone_verified_at || $person->whatsapp_opt_in !== 'on') {
                    continue;
                }

                app(\App\Services\WhatsAppService::class)->sendText(
                    $person->phone,
                    'Ajo: ' . $project->title . ' reached its goal of ₦' . number_format($project->goal_amount, 0) . '.'
                );
            }
        }

        // Team total funding milestones (all confirmed contributions on this team's projects)
        $amount = (float) $contribution->amount;
        $total = \App\Models\ProjectContribution::whereHas('project', function ($q) use ($team) {
                $q->where('community_id', $team->id);
            })
            ->where('status', 'confirmed')
            ->sum('amount');

        foreach ([1_000_000, 5_000_000, 10_000_000] as $mark) {
            if ($total >= $mark && ($total - $amount) < $mark) {
                $title = $team->name . ' reached ₦' . number_format($mark);

                $already = \App\Models\Activity::where('type', 'milestone')
                    ->where('community_id', $team->id)
                    ->where('title', $title)
                    ->exists();

                if (!$already) {
                    \App\Models\Activity::log([
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                        'community_id' => $team->id,
                        'type' => 'milestone',
                        'title' => $title,
                        'body' => 'Team funding milestone',
                        'url' => route('teams.feed', $team->slug),
                    ]);
                }
            }
        }

        $contribution->load('project.community', 'user');
        $contribution->user->notify(new \App\Notifications\ProjectContributionResolved($contribution));

        $member = $contribution->user;
        if ($member->phone && $member->phone_verified_at && $member->whatsapp_opt_in === 'on') {
            $status = $contribution->status === 'confirmed' ? 'confirmed' : 'rejected';
            app(\App\Services\WhatsAppService::class)->sendText(
                $member->phone,
                'Ajo: your ₦' . number_format($contribution->amount, 0) . ' contribution to ' . $contribution->project->title . ' was ' . $status . '.'
            );
        }

        \App\Models\Activity::log([
            'user_id' => $contribution->user_id,
            'community_id' => $team->id,
            'project_id' => $project->id,
            'type' => 'contribution',
            'title' => $contribution->user->name . ' contributed ₦' . number_format($contribution->amount, 0),
            'body' => $project->title . ' · ' . $team->name,
            'url' => route('projects.show', [$team->slug, $project->id]),
        ]);

        return back()->with('message', 'Contribution confirmed. Vault balance updated.');
    })->middleware('throttle:money')->name('projects.contributions.confirm');

    Route::get('/teams/{slug}/projects/{projectId}/contributions/{contributionId}/reject', function ($slug, $projectId) {
        return redirect()->route('projects.show', [$slug, $projectId]);
    })->middleware('auth');

    Route::post('/teams/{slug}/projects/{projectId}/contributions/{contributionId}/reject', function ($slug, $projectId, $contributionId) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        $project = $team->projects()->findOrFail($projectId);

        if (!$team->isAdmin(\Illuminate\Support\Facades\Auth::user()) && $project->creator_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403);
        }

        $contribution = $project->contributions()->where('id', $contributionId)->where('status', 'pending')->firstOrFail();
        $contribution->update(['status' => 'rejected']);

        $contribution->load('project.community', 'user');
        $contribution->user->notify(new \App\Notifications\ProjectContributionResolved($contribution));

        $member = $contribution->user;
        if ($member->phone && $member->phone_verified_at && $member->whatsapp_opt_in === 'on') {
            $status = $contribution->status === 'confirmed' ? 'confirmed' : 'rejected';
            app(\App\Services\WhatsAppService::class)->sendText(
                $member->phone,
                'Ajo: your ₦' . number_format($contribution->amount, 0) . ' contribution to ' . $contribution->project->title . ' was ' . $status . '.'
            );
        }

        return back()->with('message', 'Contribution rejected.');
    })->middleware('throttle:money')->name('projects.contributions.reject');

    // Creator requests claim or terminate
    Route::post('/teams/{slug}/projects/{id}/actions', function (Illuminate\Http\Request $request, $slug, $id) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        if (!$team->isMember(\Illuminate\Support\Facades\Auth::user())) abort(403);

        $project = $team->projects()->with('vault')->findOrFail($id);

        if ($project->creator_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Only the project creator can request this.');
        }

        if (!$project->vault || $project->vault->status !== 'locked') {
            return back()->with('error', 'Vault is not locked.');
        }

        // No overlapping pending action
        if ($project->vaultActions()->where('status', 'pending')->exists()) {
            return back()->with('error', 'There is already a pending request waiting for donor votes.');
        }

        $validated = $request->validate([
            'type' => 'required|in:claim,terminate',
            'amount' => 'nullable|numeric|min:1',
            'note' => 'nullable|string|max:1000',
        ]);

        $donorCount = $project->confirmedContributions()->distinct('user_id')->count('user_id');
        if ($donorCount === 0) {
            return back()->with('error', 'No confirmed donors yet. Cannot open vault.');
        }

        $amount = $validated['type'] === 'claim'
            ? ($validated['amount'] ?? $project->vault->balance)
            : $project->vault->balance;

        if ($validated['type'] === 'claim' && $amount > $project->vault->balance) {
            return back()->with('error', 'Claim amount exceeds vault balance.');
        }

        $action = \App\Models\VaultAction::create([
            'project_id' => $project->id,
            'requested_by' => $request->user()->id,
            'type' => $validated['type'],
            'amount' => $amount,
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        $project->update(['status' => 'claim_pending']);

        // TODO: notify all donors
        $action->load('project.community');
        $donorIds = $project->confirmedContributions()->pluck('user_id')->unique();
        \App\Models\User::whereIn('id', $donorIds)->get()->each(function ($user) use ($action) {
            $user->notify(new \App\Notifications\VaultActionRequested($action));
        });

        return back()->with('message', 'Request submitted. All donors must approve before the vault can open.');
    })->name('projects.actions.store');

    // Donor votes
    Route::post('/teams/{slug}/projects/{projectId}/actions/{actionId}/vote', function (Illuminate\Http\Request $request, $slug, $projectId, $actionId) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        if (!$team->isMember($request->user())) abort(403);

        $project = $team->projects()->with('vault')->findOrFail($projectId);
        $action = $project->vaultActions()->where('id', $actionId)->where('status', 'pending')->firstOrFail();

        $isDonor = $project->confirmedContributions()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->exists();

        if (!$isDonor) {
            abort(403, 'Only confirmed donors can vote.');
        }

        $validated = $request->validate([
            'vote' => 'required|in:approve,reject',
        ]);

        \App\Models\VaultActionVote::updateOrCreate(
            [
                'vault_action_id' => $action->id,
                'user_id' => $request->user()->getAuthIdentifier(),
            ],
            ['vote' => $validated['vote']]
        );

                $action->refresh();

        if ($action->hasRejection()) {
            $action->update([
                'status' => 'rejected',
                'resolved_at' => now(),
            ]);
            $project->update([
                'status' => $project->vault->balance >= $project->goal_amount ? 'goal_reached' : 'active',
            ]);

            $action->load('project.community');
            $notifyIds = $project->confirmedContributions()->pluck('user_id')->push($project->creator_id)->unique();
            \App\Models\User::whereIn('id', $notifyIds)->get()->each(function ($user) use ($action) {
                $user->notify(new \App\Notifications\VaultActionResolved($action));
            });

            return back()->with('error', 'A donor rejected this request. Vault remains locked.');
        }

        if (! $action->allDonorsApproved()) {
            return back()->with('message', 'Vote recorded. Waiting for other donors.');
        }

        // ---- All donors approved ----
        $vault = $project->vault;
        $gross = (float) ($action->amount ?: $vault->balance);
        $gross = min($gross, (float) $vault->balance);

        if ($gross <= 0) {
            return back()->with('error', 'Nothing to release.');
        }

        $rate = (float) \App\Models\Setting::get('platform_fee_rate', 0.05);
        $fee = round($gross * $rate, 2);
        $net = $gross - $fee;

        // ONLY place vault balance decreases
        $vault->decrement('balance', $gross);

        $action->update([
            'status' => 'approved',
            'amount' => $gross,
            'resolved_at' => now(),
            'fee_amount' => $fee,
            'net_amount' => $net,
        ]);

        $vault->update([
            'status' => 'unlocked',
            'opened_at' => now(),
        ]);

        $project->update([
            'status' => $action->type === 'terminate' ? 'terminated' : 'completed',
        ]);

        if ($action->type !== 'terminate') {
            \App\Models\Activity::log([
                'user_id' => request()->user()->getAuthIdentifier(),
                'community_id' => $team->id,
                'type' => 'milestone',
                'title' => 'Project completed: ' . $project->title,
                'body' => 'Vault unlocked · ₦' . number_format($net, 2) . ' net after fee',
                'url' => route('projects.show', [$team->slug, $project->id]),
            ]);
        }

        \App\Models\PlatformFee::create([
            'vault_action_id' => $action->id,
            'project_id' => $project->id,
            'community_id' => $project->community_id,
            'gross_amount' => $gross,
            'fee_rate' => $rate,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'status' => 'recorded',
        ]);

        $action->load('project.community');
        $notifyIds = $project->confirmedContributions()->pluck('user_id')->push($project->creator_id)->unique();
        \App\Models\User::whereIn('id', $notifyIds)->get()->each(function ($user) use ($action) {
            $user->notify(new \App\Notifications\VaultActionResolved($action));
        });

        return back()->with(
            'message',
            'All donors approved. Vault unlocked. Fee ₦' . number_format($fee, 2) . '. Net ₦' . number_format($net, 2) . '.'
        );
    })->name('projects.actions.vote');

    // Creator cancels pending action
    Route::post('/teams/{slug}/projects/{projectId}/actions/{actionId}/cancel', function ($slug, $projectId, $actionId) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        $project = $team->projects()->findOrFail($projectId);
        $action = $project->vaultActions()->where('id', $actionId)->where('status', 'pending')->firstOrFail();

        if ($action->requested_by !== request()->user()->getAuthIdentifier()) {
            abort(403);
        }

        $action->update([
            'status' => 'cancelled',
            'resolved_at' => now(),
        ]);

        $project->update([
            'status' => $project->vault->balance >= $project->goal_amount ? 'goal_reached' : 'active',
        ]);

        return back()->with('message', 'Request cancelled.');
    })->name('projects.actions.cancel');

    // Feed (members only)
    Route::get('/teams/{slug}/feed', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $user = request()->user();

    // Not logged in → login, then return here
    if (!$user) {
        return redirect()->guest(route('login'));
    }

    // Logged in but not a member → join page
    if (!$team->isMember($user)) {
        return redirect()->route('teams.join-link', $slug)
            ->with('message', 'Join the team first to see the feed.');
    }

    $isAdmin = $team->isAdmin($user);

    // Load featured circle
    $team->load('featuredProject');

    $feed = \App\Models\Activity::with([
        'user',
        'community',
        'project',
        'parent.user',
        'comments' => fn ($q) => $q->with('user')->latest()->limit(3),
    ])
    ->withCount(['likes', 'comments'])
    ->where('community_id', $team->id)
    ->latest()
    ->paginate(20);

    $polls = $team->polls()
        ->with(['options.votes', 'votes'])
        ->where('status', 'open')
        ->latest()
        ->take(3)
        ->get();

    return view('teams.feed', compact('team', 'feed', 'isAdmin', 'polls'));
})->name('teams.feed');

   Route::post('/teams/{slug}/feed', function (\Illuminate\Http\Request $request, $slug) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        if (!$team->isMember($user)) abort(403);

        $validated = $request->validate([
            'body' => 'required|string|min:1|max:1000',
            'thread' => 'nullable|array|max:4',
            'thread.*' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,pdf|max:5120',
        ]);

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('community_posts', 'public')
            : null;

        $post = \App\Models\CommunityPost::create([
            'community_id' => $team->id,
            'user_id' => $user->id,
            'body' => trim($validated['body']),
            'attachment' => $path,
        ]);

        $activity = \App\Models\Activity::log([
            'user_id' => $user->id,
            'community_id' => $team->id,
            'type' => 'post',
            'title' => $user->name . ' posted in ' . $team->name,
            'body' => $post->body,
            'url' => route('teams.feed', $team->slug),
            'attachment' => $path,
        ]);

        \App\Support\Mentions::notify($post->body, $activity, $user);

        $parentId = $activity->id;
        foreach (array_filter(array_map('trim', $validated['thread'] ?? [])) as $text) {
            \App\Models\CommunityPost::create([
                'community_id' => $team->id,
                'user_id' => $user->id,
                'body' => $text,
            ]);

            $next = \App\Models\Activity::log([
                'user_id' => $user->id,
                'community_id' => $team->id,
                'type' => 'thread',
                'title' => $user->name . ' continued a thread',
                'body' => $text,
                'parent_id' => $parentId,
                'url' => route('activities.show', $activity),
            ]);

            \App\Support\Mentions::notify($text, $next, $user);
            $parentId = $next->id;
        }

        return back()->with('message', 'Posted.');
    })->name('teams.feed.store');

    Route::delete('/teams/{slug}/feed/{postId}', function ($slug, $postId) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        if (!$team->isMember(\Illuminate\Support\Facades\Auth::user())) abort(403);

        $post = $team->posts()->where('id', $postId)->firstOrFail();

        // Author or admin can delete
        if ($post->user_id !== \Illuminate\Support\Facades\Auth::id() && !$team->isAdmin(\Illuminate\Support\Facades\Auth::user())) {
            abort(403);
        }

        $post->delete();

        return back()->with('message', 'Post deleted.');
    })->name('teams.feed.delete');

    Route::post('/teams/{slug}/invite', function (Illuminate\Http\Request $request, $slug) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        if (!$team->isAdmin(\Illuminate\Support\Facades\Auth::user())) abort(403);

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        if ($team->members()->where('user_id', $user->id)->where('status', 'active')->exists()) {
            return back()->with('error', 'User is already a member.');
        }

        \App\Models\CommunityMember::updateOrCreate(
            ['community_id' => $team->id, 'user_id' => $user->id],
            [
                'role' => 'member',
                'status' => 'pending',
                'invited_by' => \Illuminate\Support\Facades\Auth::id(),
            ]
        );

        // Notify the invited user
        $user->notify(new \App\Notifications\TeamInviteReceived($team, \Illuminate\Support\Facades\Auth::user()));

        $invited = \App\Models\User::where('email', $request->email)->first();
            if ($invited && $invited->phone && $invited->phone_verified_at && $invited->whatsapp_opt_in === 'on') {
                app(\App\Services\WhatsAppService::class)->sendText(
                    $invited->phone,
                    'Ajo: ' . \Illuminate\Support\Facades\Auth::user()->name . ' invited you to ' . $team->name . '. ' . route('invite.team.public', $team->slug)
                );
            }

        return back()->with('message', 'Invite sent to ' . $user->email);
  })->middleware('throttle:invite')->name('teams.invite');

    Route::get('/admin/fees', function () {
        if (!Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $fees = \App\Models\PlatformFee::with(['project.community'])->latest()->get();
        $totalFees = $fees->sum('fee_amount');
        $totalGross = $fees->sum('gross_amount');
        $totalNet = $fees->sum('net_amount');

        return view('admin.fees', compact('fees', 'totalFees', 'totalGross', 'totalNet'));
    })->name('admin.fees');

    Route::get('/admin/settings', function () {
        if (!Auth::user()->hasRole('admin')) abort(403);

        return view('admin.settings', [
            'bank_name' => \App\Models\Setting::get('bank_name', ''),
            'account_number' => \App\Models\Setting::get('account_number', ''),
            'account_name' => \App\Models\Setting::get('account_name', ''),
            'platform_fee_rate' => \App\Models\Setting::get('platform_fee_rate', '0.05'),
        ]);
    })->name('admin.settings');

    Route::post('/admin/settings', function (Illuminate\Http\Request $request) {
        if (!Auth::user()->hasRole('admin')) abort(403);

        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30',
            'account_name' => 'required|string|max:150',
            'platform_fee_rate' => 'required|numeric|min:0|max:0.2',
        ]);

        foreach ($validated as $key => $value) {
            \App\Models\Setting::set($key, $value);
        }

        return back()->with('message', 'Platform settings saved.');
    })->name('admin.settings.update');

    Route::get('/my-contributions', function () {
        $contributions = \App\Models\ProjectContribution::with(['project.community', 'project.vault'])
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->latest()
            ->get();

        $totals = [
            'pending' => $contributions->where('status', 'pending')->sum('amount'),
            'confirmed' => $contributions->where('status', 'confirmed')->sum('amount'),
            'rejected' => $contributions->where('status', 'rejected')->sum('amount'),
        ];

        return view('contributions.mine', compact('contributions', 'totals'));
    })->name('my.contributions');

        Route::get('/teams/{slug}/bank', function ($slug) {
            $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
            if (!$team->isAdmin(\Illuminate\Support\Facades\Auth::user())) abort(403);

            return view('teams.bank', compact('team'));
        })->name('teams.bank');

    Route::post('/teams/{slug}/bank', function (Illuminate\Http\Request $request, $slug) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        if (!$team->isAdmin(\Illuminate\Support\Facades\Auth::user())) abort(403);

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'account_name' => 'nullable|string|max:150',
        ]);

        // Clear all or save all — avoid partial bank data
        if (empty($validated['account_number'])) {
            $team->update([
                'bank_name' => null,
                'account_number' => null,
                'account_name' => null,
            ]);
            return back()->with('message', 'Team bank cleared. Contributions will use Ajo platform escrow.');
        }

        $mode = $team->fresh()->account_number ? 'team' : 'platform';

        $members = $team->activeMembers()->with('user')->get();
        foreach ($members as $m) {
            if ($m->user_id !== \Illuminate\Support\Facades\Auth::id()) {
                $m->user->notify(new \App\Notifications\TeamBankUpdated($team, $mode));
            }
        }

        $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30',
            'account_name' => 'required|string|max:150',
        ]);

        $team->update([
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
        ]);

        return back()->with('message', 'Team bank details saved.');
    })->name('teams.bank.update');

    // Public-ish: must be logged in
    Route::get('/teams/{slug}/join-link', function ($slug) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        $user = \App\Models\User::findOrFail(\Illuminate\Support\Facades\Auth::id());

        if ($team->isMember($user)) {
            return redirect()->route('teams.feed', $team->slug)
                ->with('message', 'You are already a member of this team.');
        }

        $existing = $team->members()->where('user_id', $user->id)->first();

        if ($existing && $existing->status === 'pending') {
            return redirect()->route('teams.show', $slug)
                ->with('message', 'Your join request is already pending.');
        }

        if ($existing) {
            $existing->update(['status' => 'pending', 'role' => 'member']);
        } else {
            \App\Models\CommunityMember::create([
                'community_id' => $team->id,
                'user_id' => $user->id,
                'role' => 'member',
                'status' => 'pending',
            ]);
        }

        // Notify admins (reuse JoinRequestReceived)
        $admins = $team->members()->where('status', 'active')->where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $adminUser = \App\Models\User::find($admin->user_id);

            if ($adminUser) {
                $adminUser->notify(new \App\Notifications\JoinRequestReceived($team, $user));
            }
        }

        return redirect()
        ->route('teams.feed', $team->slug)
        ->with('message', 'You joined '.$team->name.'.');
})->middleware('auth')->name('teams.join-link');

    Route::get('/teams/{slug}/projects/{id}/invite-link', function ($slug, $id) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $project = $team->projects()->findOrFail($id);
    $user = \App\Models\User::findOrFail(request()->user()->getAuthIdentifier());

    if (! $team->isMember($user)) {
        // Remember project so join-link can continue here after they become a member
        session([
            'url.intended' => route('projects.show', [$slug, $id]),
            // or your own key:
            // 'after_join_project' => ['slug' => $slug, 'id' => $id],
        ]);

        return redirect()
            ->route('teams.join-link', $slug)
            ->with('message', 'Join the team first to open this project.');
    }

    return redirect()->route('projects.show', [$slug, $id]);
})->middleware('auth')->name('projects.invite-link');

    // Like toggle
    Route::post('/activities/{id}/like', function ($id) {
        $activity = \App\Models\Activity::with('user')->findOrFail($id);
        $user = Auth::user();

        $myTeamIds = \App\Models\CommunityMember::where('user_id', $user->id)
            ->where('status', 'active')->pluck('community_id');
        if ($activity->community_id && !$myTeamIds->contains($activity->community_id)) {
            abort(403);
        }

        $like = \App\Models\ActivityLike::where('activity_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
            $message = 'Like removed.';
        } else {
            \App\Models\ActivityLike::create([
                'activity_id' => $id,
                'user_id' => $user->id,
            ]);
            $liked = true;
            $message = 'Liked.';

            if ($activity->user_id !== $user->id && $activity->user) {
                $activity->user->notify(new \App\Notifications\ActivityLiked($activity, $user));
            }
        }

        // Always JSON when client asks for it
        if (request()->expectsJson() || request()->ajax() || str_contains(request()->header('Accept', ''), 'application/json')) {
            return response()->json([
                'liked' => $liked,
                'likes_count' => $activity->likes()->count(),
                'message' => $message,
            ]);
        }

        return back()->with('message', $message);
    })->name('activities.like');

    // Comment
    Route::post('/activities/{id}/comment', function (Illuminate\Http\Request $request, $id) {
        $activity = \App\Models\Activity::with('user')->findOrFail($id);
        $user = Auth::user();

        $myTeamIds = \App\Models\CommunityMember::where('user_id', $user->id)
            ->where('status', 'active')->pluck('community_id');
        if ($activity->community_id && !$myTeamIds->contains($activity->community_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:400'],
        ]);

        $comment = \App\Models\ActivityComment::create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);
        $comment->load('user');

        \App\Support\Mentions::notify($validated['body'], $activity, $user);

        if ($activity->user_id !== $user->id && $activity->user) {
            $activity->user->notify(new \App\Notifications\ActivityCommented($activity, $user));
        }

        if (request()->expectsJson() || request()->ajax() || str_contains(request()->header('Accept', ''), 'application/json')) {
            return response()->json([
                'ok' => true,
                'message' => 'Comment added.',
                'comments_count' => $activity->comments()->count(),
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user_name' => $comment->user->name,
                    'time' => $comment->created_at->diffForHumans(),
                ],
            ]);
        }

        return back()->with('message', 'Comment added.');
    })->name('activities.comment');

    Route::post('/activities/{id}/repost', function ($id) {
    $original = \App\Models\Activity::with('user')->findOrFail($id);
    $user = Auth::user();

    $myTeamIds = \App\Models\CommunityMember::where('user_id', $user->id)
        ->where('status', 'active')->pluck('community_id');
    if ($original->community_id && !$myTeamIds->contains($original->community_id)) {
        abort(403);
    }

    $existing = \App\Models\Activity::where('parent_id', $original->id)
        ->where('user_id', $user->id)
        ->where('type', 'repost')
        ->first();

    if ($existing) {
        $existing->delete();
        $reposted = false;
    } else {
        \App\Models\Activity::log([
            'user_id' => $user->id,
            'community_id' => $original->community_id,
            'project_id' => $original->project_id,
            'parent_id' => $original->id,
            'type' => 'repost',
            'title' => $user->name . ' reposted',
            'body' => $original->title ?: $original->body,
            'url' => $original->url,
        ]);
        $reposted = true;

        if ($original->user_id !== $user->id && $original->user) {
            $original->user->notify(new \App\Notifications\ActivityReposted($original, $user));
        }
    }

    $count = \App\Models\Activity::where('parent_id', $original->id)
        ->where('type', 'repost')
        ->count();

    if (request()->expectsJson() || request()->ajax()) {
        return response()->json([
            'ok' => true,
            'reposted' => $reposted,
            'reposts_count' => $count,
            'label' => $reposted ? 'Undo repost' : 'Repost',
        ]);
    }

    return back()->with('message', $reposted ? 'Reposted.' : 'Repost removed.');
})->name('activities.repost');

    // Quote
    Route::post('/activities/{id}/quote', function (Illuminate\Http\Request $request, $id) {
        $original = \App\Models\Activity::with('user')->findOrFail($id);
        $user = Auth::user();

        $myTeamIds = \Illuminate\Support\Facades\DB::table('community_memberships')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('community_id');
        if ($original->community_id && !$myTeamIds->contains($original->community_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'quote_body' => 'required|string|min:1|max:2000',
        ]);

        $activity = \App\Models\Activity::log([
            'user_id' => $user->id,
            'community_id' => $original->community_id,
            'project_id' => $original->project_id,
            'parent_id' => $original->id,
            'type' => 'quote',
            'title' => $user->name . ' quoted',
            'body' => $original->title,
            'quote_body' => $validated['quote_body'],
            'url' => $original->url,
        ]);

        \App\Support\Mentions::notify($validated['quote_body'], $original, $user);

        if ($original->user_id !== $user->id && $original->user) {
            $original->user->notify(new \App\Notifications\ActivityQuoted($original, $user));
        }

        if (request()->expectsJson() || request()->ajax() || str_contains(request()->header('Accept', ''), 'application/json')) {
            return response()->json([
                'ok' => true,
                'message' => 'Quote posted.',
            ]);
        }

        return back()->with('message', 'Quote posted.');
    })->name('activities.quote');

    Route::get('/notifications', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);
        $user->unreadNotifications->markAsRead();
        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');

    Route::get('/notifications/{id}/read', function ($id) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();
        $url = $notification->data['url'] ?? route('dashboard');
        return redirect($url);
    })->name('notifications.read');

    Route::post('/notifications/{id}/read', function ($id) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back();
    })->name('notifications.mark-read');

    Route::get('/search', function (Illuminate\Http\Request $request) {
        $q = trim((string) $request->get('q', ''));

        $teams = collect();
        $projects = collect();
        $people = collect();
        $posts = collect();

        if (strlen($q) >= 2) {
            $like = '%' . $q . '%';

            $teams = \App\Models\Community::query()
                ->where('is_discoverable', true)
                ->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->withCount(['members' => fn ($m) => $m->where('status', 'active')])
                ->latest()
                ->take(10)
                ->get();

            $projects = \App\Models\Project::query()
                ->with('community')
                ->whereHas('community', fn ($q) => $q->where('is_discoverable', true))
                ->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->whereIn('status', ['active', 'goal_reached', 'claim_pending'])
                ->latest()
                ->take(10)
                ->get();

            // Only show people who share a team with you (privacy)
            $myTeamIds = request()->user()->communityMemberships()
                ->where('status', 'active')
                ->pluck('community_id');

            $sharedUserIds = \App\Models\CommunityMember::whereIn('community_id', $myTeamIds)
                ->where('status', 'active')
                ->pluck('user_id')
                ->unique();

            $people = \App\Models\User::query()
                ->whereIn('id', $sharedUserIds)
                ->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->orderBy('name')
                ->take(10)
                ->get();

            $posts = \App\Models\Activity::query()
                ->with(['user', 'community'])
                ->whereIn('community_id', $myTeamIds)
                ->where(function ($query) use ($like) {
                    $query->where('title', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhere('quote_body', 'like', $like);
                })
                ->latest()
                ->take(15)
                ->get();
        }

        return view('search.index', compact('q', 'teams', 'projects', 'people', 'posts'));
    })->middleware('throttle:search')->name('search');

    Route::get('/u/{user}', function (\App\Models\User $user) {
        $teams = $user->communityMemberships()
            ->where('status', 'active')
            ->with('community')
            ->latest()
            ->take(20)
            ->get();

        return view('users.show', compact('user', 'teams'));
    })->name('users.show');

    Route::post('/dashboard/posts', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'thread' => ['nullable', 'array', 'max:4'],
            'thread.*' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,pdf', 'max:5120'],
        ]);

        $path = $request->hasFile('attachment')
            ? $request->file('attachment')->store('community_posts', 'public')
            : null;

        $first = \App\Models\Activity::log([
            'user_id' => $request->user()->getAuthIdentifier(),
            'community_id' => null,
            'type' => 'post',
            'title' => $request->user()->name . ' shared an update',
            'body' => trim($validated['body']),
            'url' => route('users.show', $request->user()->getAuthIdentifier()),
            'attachment' => $path,
        ]);

        $parentId = $first->id;
        foreach (array_filter(array_map('trim', $validated['thread'] ?? [])) as $text) {
            $next = \App\Models\Activity::log([
                'user_id' => $request->user()->getAuthIdentifier(),
                'community_id' => null,
                'type' => 'thread',
                'title' => $request->user()->name . ' continued a thread',
                'body' => $text,
                'parent_id' => $parentId,
                'url' => route('activities.show', $first),
            ]);
            $parentId = $next->id;
        }

        return redirect()->route('dashboard')->with('message', 'Posted to your wall.');
    })->name('dashboard.posts.store');

Route::post('/friends/{user}/request', function ($user) {
    /** @var \App\Models\User $user */
    $user = \App\Models\User::query()->findOrFail($user);

    $authUserId = \Illuminate\Support\Facades\Auth::id();

    abort_if((int) $user->id === (int) $authUserId, 403);

    $exists = \App\Models\Friendship::where(function ($q) use ($authUserId, $user) {
        $q->where('user_id', $authUserId)->where('friend_id', $user->id);
    })->orWhere(function ($q) use ($authUserId, $user) {
        $q->where('user_id', $user->id)->where('friend_id', $authUserId);
    })->first();

    if ($exists) {
        return back()->with('error', 'Request already exists.');
    }

    \App\Models\Friendship::create([
        'user_id' => $authUserId,
        'friend_id' => $user->id,
        'status' => 'pending',
    ]);

    $user->notify(new \App\Notifications\FriendRequestReceived(\Illuminate\Support\Facades\Auth::user()));

    if ($user->phone && $user->phone_verified_at && $user->whatsapp_opt_in === 'on') {
        app(\App\Services\WhatsAppService::class)->sendText(
            $user->phone,
            'Ajo: ' . \Illuminate\Support\Facades\Auth::user()->name . ' sent you a friend request.'
        );
    }

    return back()->with('message', 'Friend request sent.');
})->middleware('throttle:friend-request')->name('friends.request');

Route::post('/friends/{friendship}/accept', function (\App\Models\Friendship $friendship) {
    abort_unless($friendship->friend_id === \Illuminate\Support\Facades\Auth::id() && $friendship->status === 'pending', 403);

    $friendship->update(['status' => 'accepted', 'accepted_at' => now()]);

    $sender = \App\Models\User::find($friendship->user_id);
    $sender?->notify(new \App\Notifications\FriendRequestAccepted(\Illuminate\Support\Facades\Auth::user()));

    return back()->with('message', 'Friend request accepted.');
})->name('friends.accept');

Route::post('/friends/{friendship}/decline', function (\App\Models\Friendship $friendship) {
    abort_unless($friendship->friend_id === \Illuminate\Support\Facades\Auth::id() && $friendship->status === 'pending', 403);

    $friendship->delete();

    return back()->with('message', 'Request declined.');
})->name('friends.decline');

Route::delete('/friends/{friendship}/cancel', function (\App\Models\Friendship $friendship) {
    abort_unless(
        $friendship->user_id === \Illuminate\Support\Facades\Auth::id() && $friendship->status === 'pending',
        403
    );

    $friendship->delete();

    return back()->with('message', 'Friend request cancelled.');
})->name('friends.cancel');

Route::delete('/friends/{user}', function (\App\Models\User $user) {
    $authId = \Illuminate\Support\Facades\Auth::id();

    $friendship = \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($q) use ($authId, $user) {
            $q->where('user_id', $authId)->where('friend_id', $user->id)
              ->orWhere(fn ($q2) => $q2->where('user_id', $user->id)->where('friend_id', $authId));
        })
        ->first();

    if ($friendship) {
        $friendship->delete();
    }

    return back()->with('message', 'Unfriended.');
})->name('friends.destroy');

Route::get('/friends', function () {
    $userId = \Illuminate\Support\Facades\Auth::id();

    // Accepted friends only
    $friendRows = \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('friend_id', $userId);
        })
        ->get(['user_id', 'friend_id']);

    $friendIds = $friendRows->map(fn ($f) => $f->user_id === $userId ? $f->friend_id : $f->user_id);
    $friends = \App\Models\User::whereIn('id', $friendIds)->orderBy('name')->get();

    // Pending incoming
    $pending = \App\Models\Friendship::with('requester')
        ->where('friend_id', $userId)
        ->where('status', 'pending')
        ->latest()
        ->get();

    // Exclude self + accepted friends + pending (both directions)
    $pendingIds = \App\Models\Friendship::where('status', 'pending')
        ->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('friend_id', $userId);
        })
        ->get(['user_id', 'friend_id'])
        ->flatMap(fn ($f) => [$f->user_id, $f->friend_id]);

    $excludedIds = $friendRows
        ->flatMap(fn ($f) => [$f->user_id, $f->friend_id])
        ->merge($pendingIds)
        ->push($userId)
        ->unique();

    // Teams the current user belongs to
    $myTeamIds = \App\Models\CommunityMember::where('user_id', $userId)
        ->where('status', 'active')
        ->pluck('community_id');

    // Teammates
    $teammateIds = \App\Models\CommunityMember::whereIn('community_id', $myTeamIds)
        ->where('user_id', '!=', $userId)
        ->where('status', 'active')
        ->whereNotIn('user_id', $excludedIds)
        ->pluck('user_id')
        ->unique()
        ->take(12);

    $suggestions = \App\Models\User::whereIn('id', $teammateIds)
        ->get()
        ->map(fn ($user) => (object) [
            'user' => $user,
            'from_team' => true,
            'mutual' => 0,
        ]);

    // Fallback
    if ($suggestions->count() < 8) {
        $extraIds = \App\Models\User::whereNotIn('id', $excludedIds->merge($teammateIds))
            ->orderBy('name')
            ->limit(8 - $suggestions->count())
            ->pluck('id');

        $extra = \App\Models\User::whereIn('id', $extraIds)
            ->get()
            ->map(fn ($user) => (object) [
                'user' => $user,
                'from_team' => false,
                'mutual' => 0,
            ]);

        $suggestions = $suggestions->concat($extra);
    }

    return view('friends.index', compact('friends', 'pending', 'suggestions'));
})->name('friends.index');

Route::get('/u/{user}/friends', function (\App\Models\User $user) {
    $ids = $user->friendIds();
    $friends = \App\Models\User::whereIn('id', $ids)->orderBy('name')->get();

    return view('users.friends', compact('user', 'friends'));
})->name('users.friends');

Route::post('/activities/{activity}/share', function (\App\Models\Activity $activity) {
    if ($activity->community_id) {
        $ok = \App\Models\CommunityMember::where('community_id', $activity->community_id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('status', 'active')
            ->exists();
        abort_unless($ok || $activity->user_id === \Illuminate\Support\Facades\Auth::id(), 403);
    }

    \App\Models\Activity::log([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'community_id' => null,
        'type' => 'share',
        'parent_id' => $activity->id,
        'title' => \Illuminate\Support\Facades\Auth::user()->name . ' shared a post',
        'body' => \Illuminate\Support\Str::limit($activity->body ?? $activity->title, 200),
        'url' => $activity->url ?? route('dashboard'),
        'attachment' => $activity->attachment,
    ]);

    if (request()->expectsJson() || request()->wantsJson()) {
        return response()->json(['message' => 'Shared to your wall.']);
    }

    return back()->with('message', 'Shared to your wall.');
})->name('activities.share');

Route::post('/teams/{slug}/projects/{project}/share', function ($slug, $project) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $project = $team->projects()->findOrFail($project);

    \App\Models\Activity::log([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'community_id' => null,
        'type' => 'share',
        'title' => \Illuminate\Support\Facades\Auth::user()->name . ' shared a project win',
        'body' => $project->title,
        'url' => route('projects.show', [$team->slug, $project->id]),
    ]);

    return back()->with('message', 'Shared to your wall.');
})->name('projects.share');

// Public-ish post page (auth)
Route::get('/a/{activity}', function (\App\Models\Activity $activity) {
    $user = \Illuminate\Support\Facades\Auth::user();

    // Wall post: author, friends, or self
    if ($activity->community_id === null) {
        $ok = $activity->user_id === $user->id
                || \App\Models\Friendship::where('status', 'accepted')
                    ->where(function ($query) use ($user, $activity) {
                        $query->where(function ($query) use ($user, $activity) {
                            $query->where('user_id', $user->id)
                                ->where('friend_id', $activity->user_id);
                        })->orWhere(function ($query) use ($user, $activity) {
                            $query->where('user_id', $activity->user_id)
                                ->where('friend_id', $user->id);
                        });
                    })
                    ->exists();
        abort_unless($ok, 403);
    } else {
        $ok = \App\Models\CommunityMember::where('community_id', $activity->community_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
        abort_unless($ok, 403);
    }

    $activity->load(['user', 'community', 'parent.user', 'comments.user'])
    ->loadCount(['likes', 'comments', 'reposts', 'quotes']);

    return view('activities.show', compact('activity'));
})->name('activities.show');

Route::post('/activities/{activity}/bookmark', function (\App\Models\Activity $activity) {
    $userId = Auth::id();

    $row = \App\Models\ActivityBookmark::where('activity_id', $activity->id)
        ->where('user_id', $userId)
        ->first();

    if ($row) {
        $row->delete();
        $bookmarked = false;
    } else {
        \App\Models\ActivityBookmark::create([
            'activity_id' => $activity->id,
            'user_id' => $userId,
        ]);
        $bookmarked = true;
    }

    if (request()->expectsJson()) {
        return response()->json(['bookmarked' => $bookmarked]);
    }
    return back();
})->name('activities.bookmark');

Route::delete('/activities/{activity}', function (\App\Models\Activity $activity) {
    $user = Auth::user();
    $allowed = $activity->user_id === $user->id;

    if (!$allowed && $activity->community_id) {
        $allowed = \App\Models\CommunityMember::where('community_id', $activity->community_id)
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->exists();
    }
    abort_unless($allowed, 403);

    $activity->delete();

    if (request()->expectsJson() || request()->ajax()) {
        return response()->json(['ok' => true]);
    }

    return redirect()->route('dashboard')->with('message', 'Post deleted.');
})->middleware('auth')->name('activities.destroy');

Route::post('/activities/{activity}/thread', function (\Illuminate\Http\Request $request, \App\Models\Activity $activity) {
    $validated = $request->validate([
        'body' => ['required', 'string', 'max:400'],
    ]);
    $user = $request->user();

    // same visibility checks as comment/show...

    $root = $activity->parent_id ? \App\Models\Activity::find($activity->parent_id) ?? $activity : $activity;

    \App\Models\Activity::log([
        'user_id' => $user->getAuthIdentifier(),
        'community_id' => $activity->community_id, // same wall or team
        'parent_id' => $root->id,
        'type' => 'thread',
        'title' => $user->name . ' continued a thread',
        'body' => $validated['body'],
        'url' => route('activities.show', $root),
    ]);

    return back()->with('message', 'Added to thread.');
})->name('activities.thread');

Route::get('/saved', function () {
    $bookmarks = \App\Models\ActivityBookmark::where('user_id', Auth::id())
        ->latest()
        ->get();

    $ids = $bookmarks->pluck('activity_id');

    $items = \App\Models\Activity::with(['user', 'community', 'parent'])
        ->withCount(['likes', 'comments', 'reposts', 'quotes'])
        ->whereIn('id', $ids)
        ->get()
        ->keyBy('id');

    $feed = $ids->map(fn ($id) => $items->get($id))->filter()->values();

    return view('activities.saved', compact('feed'));
})->middleware('auth')->name('activities.saved');

Route::get('/admin/money-check', function () {
    $user = Auth::user();
    $isAdmin = $user && (
        (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
        (method_exists($user, 'roles') && $user->roles()->where('name', 'admin')->exists()) ||
        (property_exists($user, 'role') && $user->role === 'admin') ||
        (method_exists($user, 'isAdmin') && $user->isAdmin())
    );
    abort_unless($isAdmin, 403);

    $projects = \App\Models\Project::with(['vault', 'community'])->get()->map(function ($p) {
        $confirmed = (float) $p->contributions()->where('status', 'confirmed')->sum('amount');
        $released = (float) \App\Models\PlatformFee::where('project_id', $p->id)->sum('gross_amount');
        $vault = (float) ($p->vault->balance ?? 0);
        $expectedVault = $confirmed - $released;
        $diff = round($vault - $expectedVault, 2);

        return [
            'team' => $p->community->name ?? '',
            'project' => $p->title,
            'status' => $p->status,
            'vault_status' => $p->vault->status ?? '',
            'confirmed' => $confirmed,
            'vault' => $vault,
            'released' => $released,
            'diff' => $diff,
            'ok' => abs($diff) < 0.01,
        ];
    });

    return view('admin.money-check', compact('projects'));
})->middleware('auth')->name('admin.money-check');

Route::get('/messages', function () {
    $me = \Illuminate\Support\Facades\Auth::id();

    $conversations = \App\Models\Conversation::with(['userOne', 'userTwo', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
        ->where('user_one_id', $me)
        ->orWhere('user_two_id', $me)
        ->orderByDesc('last_message_at')
        ->get();

    return view('messages.index', compact('conversations'));
})->name('messages.index');

Route::get('/messages/{user}', function (\App\Models\User $user) {
    $authUser = \Illuminate\Support\Facades\Auth::user();

    if (! $authUser instanceof \App\Models\User) {
        abort(403);
    }

    abort_if($user->id === $authUser->id, 403);

    $friends = \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($query) use ($authUser, $user) {
            $query->where(function ($query) use ($authUser, $user) {
                $query->where('user_id', $authUser->id)
                    ->where('friend_id', $user->id);
            })->orWhere(function ($query) use ($authUser, $user) {
                $query->where('user_id', $user->id)
                    ->where('friend_id', $authUser->id);
            });
        })
        ->exists();
    abort_unless($friends, 403, 'You can only message friends.');

    $conversation = \App\Models\Conversation::between($authUser->id, $user->id);
    $conversation->messages()
        ->where('sender_id', '!=', $authUser->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    $messages = $conversation->messages()->with('sender')->orderBy('created_at')->get();

    return view('messages.show', compact('conversation', 'messages', 'user'));
})->name('messages.show');

Route::post('/messages/{user}', function (\Illuminate\Http\Request $request, \App\Models\User $user) {
    abort_if($user->id === \Illuminate\Support\Facades\Auth::id(), 403);
    $authUser = \Illuminate\Support\Facades\Auth::user();
    abort_unless($authUser instanceof \App\Models\User, 403);

    $friends = \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($query) use ($authUser, $user) {
            $query->where(function ($query) use ($authUser, $user) {
                $query->where('user_id', $authUser->id)
                    ->where('friend_id', $user->id);
            })->orWhere(function ($query) use ($authUser, $user) {
                $query->where('user_id', $user->id)
                    ->where('friend_id', $authUser->id);
            });
        })
        ->exists();
    abort_unless($friends, 403);

    $data = $request->validate([
        'body' => 'required|string|max:2000',
    ]);

    $conversation = \App\Models\Conversation::between($authUser->id, $user->id);

    $conversation->messages()->create([
        'sender_id' => $authUser->id,
        'body' => $data['body'],
    ]);

    $conversation->update(['last_message_at' => now()]);

    $user->notify(new \App\Notifications\DirectMessageReceived($authUser, $conversation));

    return redirect()->route('messages.show', $user);
})->name('messages.store');

Route::get('/teams/{slug}/meetings', function (\Illuminate\Http\Request $request, $slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($team->isMember($request->user()), 403);

    $meetings = $team->meetings()
        ->with('creator')
        ->where('starts_at', '>=', now()->subDay())
        ->orderBy('starts_at')
        ->get();

    $isAdmin = $team->isAdmin($request->user()) || $team->creator_id === $request->user()->id;

    return view('teams.meetings.index', compact('team', 'meetings', 'isAdmin'));
})->name('teams.meetings.index');

Route::get('/teams/{slug}/meetings/create', function (\Illuminate\Http\Request $request, $slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($team->isAdmin($request->user()) || $team->creator_id === $request->user()->id, 403);

    return view('teams.meetings.create', compact('team'));
})->name('teams.meetings.create');

    Route::post('/teams/{slug}/meetings', function (\Illuminate\Http\Request $request, $slug) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        abort_unless($team->isAdmin($request->user()) || $team->creator_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => 'required|string|max:120',
            'agenda' => 'nullable|string|max:2000',
            'meet_link' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $meeting = $team->meetings()->create([
            ...$data,
            'created_by' => $request->user()->id,
            'status' => 'scheduled',
        ]);
        $meeting->load('community');

        $members = $team->members()->where('status', 'active')->with('user')->get();
        foreach ($members as $member) {
            $member->user?->notify(new \App\Notifications\TeamMeetingScheduled($meeting));
        }

        \App\Models\Activity::log([
            'user_id' => $request->user()->id,
            'community_id' => $team->id,
            'type' => 'milestone',
            'title' => 'Meeting scheduled: ' . $meeting->title,
            'body' => $meeting->starts_at->format('D d M, g:i A'),
            'url' => route('teams.meetings.index', $team->slug),
        ]);

        return redirect()->route('teams.meetings.index', $team->slug)
            ->with('message', 'Meeting scheduled. Members have been notified.');
    })->name('teams.meetings.store');

    Route::post('/teams/{slug}/meetings/{meeting}/cancel', function ($slug, \App\Models\TeamMeeting $meeting) {
        $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
        abort_unless($meeting->community_id === $team->id, 404);

        $user = \Illuminate\Support\Facades\Auth::user();
        abort_unless($user && ($team->isAdmin($user) || $team->creator_id === $user->id), 403);

        $meeting->update(['status' => 'cancelled']);

        
        $meeting->load('community');

        $members = $meeting->community->members()
            ->where('status', 'active')
            ->with('user')
            ->get();

        foreach ($members as $member) {
            if ($member->user_id === $user->id) {
                continue;
            }
            $recipient = $member->getRelationValue('user');
            if ($recipient instanceof \App\Models\User) {
                $recipient->notify(new \App\Notifications\TeamMeetingCancelled($meeting, $user));
            }
        }

        return back()->with('message', 'Meeting cancelled. Members have been notified.');

        
    })->name('teams.meetings.cancel');

    Route::post('/teams/{slug}/polls', function (\Illuminate\Http\Request $request, $slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($team->isAdmin($request->user()) || $team->creator_id === $request->user()->getAuthIdentifier(), 403);

    $data = $request->validate([
        'question' => 'required|string|max:180',
        'options' => 'required|array|min:2',
        'options.*' => 'nullable|string|max:80',
        'closes_at' => 'nullable|date|after:now',
    ]);

    $labels = collect($data['options'])->map(fn ($label) => trim((string) $label))->filter()->values();
    if ($labels->count() < 2) {
        return back()->with('error', 'Add at least two options.')->withInput();
    }

    $poll = $team->polls()->create([
        'created_by' => $request->user()->getAuthIdentifier(),
        'question' => $data['question'],
        'status' => 'open',
        'closes_at' => $request->filled('closes_at') ? $data['closes_at'] : null,
    ]);

    foreach ($labels as $label) {
        $poll->options()->create(['label' => $label]);
    }

    $poll->load('community');
    $members = $team->members()->where('status', 'active')->with('user')->get();
    foreach ($members as $member) {
        $member->user?->notify(new \App\Notifications\TeamPollUpdated($poll, 'opened'));
    }

    return redirect()->route('teams.feed', $team->slug)->with('message', 'Poll opened. Members have been notified.');
})->name('teams.polls.store');

Route::post('/teams/{slug}/polls/{poll}/vote', function (\Illuminate\Http\Request $request, $slug, \App\Models\TeamPoll $poll) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($poll->community_id === $team->id, 404);
    abort_unless($team->isMember($request->user()), 403);
    abort_unless($poll->isOpen(), 403);

    $data = $request->validate([
        'option_id' => 'required|exists:team_poll_options,id',
    ]);

    $option = $poll->options()->where('id', $data['option_id'])->firstOrFail();

    \App\Models\TeamPollVote::updateOrCreate(
        ['team_poll_id' => $poll->id, 'user_id' => $request->user()->id],
        ['team_poll_option_id' => $option->id]
    );

    $poll->load('options.votes', 'votes');
    $total = max(1, $poll->votes->count());

    return response()->json([
        'votes_count' => $poll->votes->count(),
        'selected' => (int) $option->id,
        'options' => $poll->options->map(fn ($opt) => [
            'id' => $opt->id,
            'count' => $opt->votes->count(),
            'pct' => (int) round(($opt->votes->count() / $total) * 100),
        ]),
    ]);
})->name('teams.polls.vote');

Route::post('/teams/{slug}/polls/{poll}/close', function ($slug, \App\Models\TeamPoll $poll) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($poll->community_id === $team->id, 404);
    abort_unless($team->isAdmin(\Illuminate\Support\Facades\Auth::user()) || $team->creator_id === \Illuminate\Support\Facades\Auth::id(), 403);
    $poll->update(['status' => 'closed']);
    $poll->load('community');
    $members = $team->members()->where('status', 'active')->with('user')->get();
    foreach ($members as $member) {
        $member->user?->notify(new \App\Notifications\TeamPollUpdated($poll, 'closed'));
    }
    return back()->with('message', 'Poll closed.');
})->name('teams.polls.close');

$adminCheck = function () {
    $user = \Illuminate\Support\Facades\Auth::user();

    if (!$user) {
        return false;
    }

    if (method_exists($user, 'hasRole')) {
        return (bool) $user->hasRole('admin');
    }

    return (bool) (($user->is_admin ?? false) || (($user->role ?? null) === 'admin'));
};

Route::get('/admin/newsletter', function () use ($adminCheck) {
    abort_unless($adminCheck(), 403);

    $campaigns = \App\Models\EmailCampaign::latest()->take(20)->get();
    $userCount = \App\Models\User::whereNotNull('email')->count();

    return view('admin.newsletter', compact('campaigns', 'userCount'));
})->name('admin.newsletter');

Route::post('/admin/newsletter', function (\Illuminate\Http\Request $request) use ($adminCheck) {
    abort_unless($adminCheck(), 403);

    $data = $request->validate([
        'subject' => 'required|string|max:140',
        'body' => 'required|string|max:5000',
        'image' => 'nullable|image|max:2048',
    ]);

    $imageUrl = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('newsletters', 'public');
        $imageUrl = asset('storage/' . $path);
    }

    $campaign = \App\Models\EmailCampaign::create([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'subject' => $data['subject'],
        'body' => $data['body'],
        'audience' => 'all',
        'status' => 'sending',
    ]);

    $sent = 0;
    \App\Models\User::whereNotNull('email')->orderBy('id')->chunk(50, function ($users) use ($data, $imageUrl, &$sent) {
        foreach ($users as $user) {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\NewsletterMail($data['subject'], $data['body'], $imageUrl));
            $sent++;
        }
    });

    $campaign->update([
        'status' => 'sent',
        'sent_count' => $sent,
        'sent_at' => now(),
    ]);

    return back()->with('message', 'Newsletter sent to ' . $sent . ' members.');
})->name('admin.newsletter.send');

Route::post('/profile/phone/send-code', function (\Illuminate\Http\Request $request) {
    $user = $request->user();

    if (!$user->phone) {
        return back()->with('error', 'Add a phone number first.');
    }

    $code = (string) random_int(100000, 999999);

    $user->forceFill([
        'phone_otp' => $code,
        'phone_otp_expires_at' => now()->addMinutes(10),
    ])->save();

    $sent = app(\App\Services\WhatsAppService::class)->sendText(
        $user->phone,
        "Your Ajo code is {$code}. It expires in 10 minutes."
    );

    if (!$sent) {
        return back()->with('message', 'Code created: ' . $code . ' (WhatsApp is not configured yet, so it is shown here).');
    }

    return back()->with('message', 'Code sent to your WhatsApp number.');
})->name('profile.phone.send');

Route::post('/profile/phone/verify', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'code' => ['required', 'digits:6'],
    ]);

    $user = \Illuminate\Support\Facades\Auth::user();

    if (!$user || !$user instanceof \App\Models\User) {
        abort(401);
    }

    $expires = $user->phone_otp_expires_at
    ? \Illuminate\Support\Carbon::parse($user->phone_otp_expires_at)
    : null;

    if (!$user->phone_otp || !$expires || $expires->isPast()) {
        return back()->with('error', 'Code expired. Send a new one.');
    }

    if ($user->phone_otp !== $data['code']) {
        return back()->with('error', 'Wrong code.');
    }

    $user->phone_verified_at = now();
    $user->whatsapp_opt_in = 'on';
    $user->phone_otp = null;
    $user->phone_otp_expires_at = null;
    $user->save();

    return back()->with('message', 'Phone verified. WhatsApp alerts are on.');
})->name('profile.phone.verify');

Route::get('/admin/guides', function () use ($adminCheck) {
    abort_unless($adminCheck(), 403);
    $posts = \App\Models\BlogPost::latest()->paginate(20);
    return view('blog.admin', compact('posts'));
})->name('blog.admin');

Route::get('/admin/guides/create', function () use ($adminCheck) {
    abort_unless($adminCheck(), 403);
    return view('blog.edit', ['post' => new \App\Models\BlogPost()]);
})->name('blog.create');

Route::post('/admin/guides', function (\Illuminate\Http\Request $request) use ($adminCheck) {
    abort_unless($adminCheck(), 403);
    $data = $request->validate([
        'title' => 'required|string|max:160',
        'category' => 'required|in:'.implode(',', array_keys(\App\Models\BlogPost::categories())),
        'excerpt' => 'required|string|max:300',
        'body' => 'required|string|min:40',
        'cover' => 'nullable|image|max:4096',
        'publish' => 'nullable|boolean',
    ]);
    $post = new \App\Models\BlogPost($data);
    $post->user_id = $request->user()->getAuthIdentifier();
    $post->slug = \Illuminate\Support\Str::slug($data['title']).'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));
    if ($request->hasFile('cover')) {
        $post->cover = $request->file('cover')->store('blog', 'public');
    }
    $post->published_at = $request->boolean('publish') ? now() : null;
    $post->save();
    return redirect()->route('blog.admin')->with('message', 'Guide saved.');
})->name('blog.store');

Route::get('/admin/guides/{post}/edit', function (\App\Models\BlogPost $post) use ($adminCheck) {
    abort_unless($adminCheck(), 403);
    return view('blog.edit', compact('post'));
})->name('blog.edit');

Route::put('/admin/guides/{post}', function (\Illuminate\Http\Request $request, \App\Models\BlogPost $post) use ($adminCheck) {
    abort_unless($adminCheck(), 403);
    $data = $request->validate([
        'title' => 'required|string|max:160',
        'category' => 'required|in:'.implode(',', array_keys(\App\Models\BlogPost::categories())),
        'excerpt' => 'required|string|max:300',
        'body' => 'required|string|min:40',
        'cover' => 'nullable|image|max:4096',
        'publish' => 'nullable|boolean',
    ]);
    $post->fill($data);
    if ($request->hasFile('cover')) {
        $post->cover = $request->file('cover')->store('blog', 'public');
    }
    $post->published_at = $request->boolean('publish') ? ($post->published_at ?? now()) : null;
    $post->save();
    return redirect()->route('blog.admin')->with('message', 'Guide updated.');
})->name('blog.update');

Route::delete('/admin/guides/{post}', function (\App\Models\BlogPost $post) use ($adminCheck) {
    abort_unless($adminCheck(), 403);
    $post->delete();
    return redirect()->route('blog.admin')->with('message', 'Guide deleted.');
})->name('blog.destroy');

Route::get('/admin', function () use ($adminCheck) {
    abort_unless($adminCheck(), 403);

    $since = now()->subDays(7);

    $confirmed = \App\Models\ProjectContribution::where('status', 'confirmed');

    return view('admin.overview', [
        'stats' => [
            'users' => \App\Models\User::count(),
            'users_week' => \App\Models\User::where('created_at', '>=', $since)->count(),
            'teams' => \App\Models\Community::count(),
            'teams_week' => \App\Models\Community::where('created_at', '>=', $since)->count(),
            'members' => \App\Models\CommunityMember::where('status', 'active')->count(),
            'projects' => \App\Models\Project::count(),
            'funded' => \App\Models\Project::where('status', 'goal_reached')->count(),
            'raised' => (clone $confirmed)->sum('amount'),
            'raised_week' => (clone $confirmed)->where('confirmed_at', '>=', $since)->sum('amount'),
            'pending' => \App\Models\ProjectContribution::where('status', 'pending')->count(),
            'posts' => \App\Models\Activity::where('created_at', '>=', $since)->count(),
            'friends' => \App\Models\Friendship::where('status', 'accepted')->count(),
            'guides' => \App\Models\BlogPost::published()->count(),
        ],
        'recentUsers' => \App\Models\User::latest()->take(8)->get(),
        'recentTeams' => \App\Models\Community::withCount(['members' => fn ($q) => $q->where('status', 'active')])->latest()->take(8)->get(),
        'recentMoney' => \App\Models\ProjectContribution::with(['user', 'project.community'])
            ->where('status', 'confirmed')->latest('confirmed_at')->take(8)->get(),
    ]);
})->name('admin.overview');

Route::post('/learn/{slug}/like', function ($slug) {
    $post = \App\Models\BlogPost::published()->where('slug', $slug)->firstOrFail();
    $like = $post->likes()->where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
    if ($like) {
        $like->delete();
    } else {
        $post->likes()->create(['user_id' => \Illuminate\Support\Facades\Auth::id()]);
    }
    return back();
})->name('blog.like');

Route::post('/teams/{slug}/projects/{project}/ballot', function ($slug, \App\Models\Project $project) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($project->community_id === $team->id, 404);
    abort_unless($team->isAdmin(\Illuminate\Support\Facades\Auth::user()) || $project->creator_id === \Illuminate\Support\Facades\Auth::id(), 403);

    if ($project->ballot) {
        return back()->with('error', 'This circle already has a draw.');
    }

    $members = $team->members()->where('status', 'active')->pluck('user_id')->unique()->values()->all();

    if (count($members) < 2) {
        return back()->with('error', 'Need at least 2 active members before drawing the payout order.');
    }

    // shuffle
    for ($i = count($members) - 1; $i > 0; $i--) {
        $j = random_int(0, $i);
        [$members[$i], $members[$j]] = [$members[$j], $members[$i]];
    }

    $step = ($project->cadence === 'weekly') ? 7 : 30;
    $start = now()->startOfDay()->addDays($step);

    $ballot = \App\Models\ProjectBallot::create([
        'project_id' => $project->id,
        'executed_by' => \Illuminate\Support\Facades\Auth::id(),
        'draw_hash' => hash('sha256', $project->id.'|'.now()->toIso8601String().'|'.implode(',', $members)),
        'executed_at' => now(),
        'swap_until' => now()->addHours(24),
        'status' => 'pending_swap',
    ]);

    $made = [];
    foreach ($members as $index => $userId) {
        $made[] = \App\Models\ProjectPayoutSlot::create([
            'project_id' => $project->id,
            'ballot_id' => $ballot->id,
            'user_id' => $userId,
            'position' => $index + 1,
            'original_position' => $index + 1,
            'scheduled_payout_date' => $start->copy()->addDays($step * $index),
        ]);
    }

    foreach ($made as $slot) {
        $user = $slot->user_id ? \App\Models\User::find($slot->user_id) : null;
        $user?->notify(new \App\Notifications\BallotDrawn($project, $slot->position, $team));
    }

    return back()->with('message', 'Draw complete. Members can swap slots for 24 hours.');
})->name('projects.ballot.draw');

Route::post('/teams/{slug}/projects/{project}/slots/{slot}/swap', function ($slug, \App\Models\Project $project, \App\Models\ProjectPayoutSlot $slot) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($project->community_id === $team->id && $slot->project_id === $project->id, 404);
    abort_unless($project->ballot && $project->ballot->swapsOpen(), 422);
    abort_if($slot->user_id === \Illuminate\Support\Facades\Auth::id(), 422);

    $mine = $project->payoutSlots()->where('user_id', \Illuminate\Support\Facades\Auth::id())->firstOrFail();
    $recipient = \App\Models\User::findOrFail($slot->user_id);

    \Illuminate\Support\Facades\DB::table('payout_swap_requests')->updateOrInsert(
        ['project_id' => $project->id, 'from_user_id' => \Illuminate\Support\Facades\Auth::id(), 'to_user_id' => $slot->user_id, 'status' => 'pending'],
        ['updated_at' => now(), 'created_at' => now()]
    );

    $project->load('community');
    $recipient->notify(new \App\Notifications\PayoutSwapRequested($project, $mine, \Illuminate\Support\Facades\Auth::user()));

    return back()->with('message', 'Swap request sent to '.$recipient->name.'.');
})->name('projects.slots.swap');

Route::post('/teams/{slug}/projects/{project}/swaps/{swap}/accept', function ($slug, \App\Models\Project $project, $swap) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($project->community_id === $team->id, 404);
    abort_unless($project->ballot && $project->ballot->swapsOpen(), 422);

    $row = \Illuminate\Support\Facades\DB::table('payout_swap_requests')->where('id', $swap)->first();
    abort_unless($row && $row->to_user_id === \Illuminate\Support\Facades\Auth::id() && $row->status === 'pending', 403);

    $a = $project->payoutSlots()->where('user_id', $row->from_user_id)->firstOrFail();
    $b = $project->payoutSlots()->where('user_id', $row->to_user_id)->firstOrFail();

    $aPos = $a->position; $aDate = $a->scheduled_payout_date;
    $a->update(['position' => $b->position, 'scheduled_payout_date' => $b->scheduled_payout_date]);
    $b->update(['position' => $aPos, 'scheduled_payout_date' => $aDate]);

    \Illuminate\Support\Facades\DB::table('payout_swap_requests')->where('id', $swap)->update(['status' => 'accepted', 'updated_at' => now()]);

    return back()->with('message', 'Slots swapped.');
})->name('projects.swaps.accept');

Route::post('/teams/{slug}/settings', function (\Illuminate\Http\Request $request, $slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($team->isAdmin(\Illuminate\Support\Facades\Auth::user()), 403);

    $data = $request->validate([
        'visibility' => 'required|in:public,private',
        'member_limit' => 'nullable|integer|min:2|max:5000',
        'invites_open' => 'nullable|boolean',
    ]);

    $active = $team->members()->where('status', 'active')->count();
    if (!empty($data['member_limit']) && (int) $data['member_limit'] < $active) {
        return back()->with('error', 'Limit cannot be below the current member count ('.$active.').');
    }

    $isPublic = $data['visibility'] === 'public';

    $team->update([
        'visibility' => $data['visibility'],
        'is_discoverable' => $isPublic,
        'member_limit' => $data['member_limit'] ?: null,
        'invites_open' => $request->boolean('invites_open'),
    ]);

    return back()->with('message', 'Team settings saved.');
})->middleware('auth')->name('teams.settings');

Route::post('/teams/{slug}/leave', function ($slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $membership = $team->members()->where('user_id', Auth::id())->where('status', 'active')->firstOrFail();

    if ($membership->user_id === $team->created_by) {
        return back()->with('error', 'The creator cannot leave. Remove the team instead.');
    }

    $adminCount = $team->members()->where('status', 'active')->where('role', 'admin')->count();
    if ($membership->role === 'admin' && $adminCount <= 1) {
        return back()->with('error', 'Make another member an admin before you leave.');
    }

    $membership->update(['status' => 'removed']);
    $team->update(['members_count' => $team->activeMemberCount()]);

    return redirect()->route('teams.mine')->with('message', 'You left '.$team->name.'.');
})->name('teams.leave');

Route::post('/teams/{slug}/members/{userId}/remove', function ($slug, $userId) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($team->isAdmin(Auth::user()), 403);
    abort_if((int) $userId === Auth::id(), 403);

    $membership = $team->members()->where('user_id', $userId)->where('status', 'active')->firstOrFail();
    if ((int) $userId === (int) $team->created_by) {
        return back()->with('error', 'The team creator cannot be removed.');
    }

    $membership->update(['status' => 'removed']);
    $team->update(['members_count' => $team->activeMemberCount()]);

    return back()->with('message', 'Member removed.');
})->name('teams.members.remove');

Route::get('/circles/create', function () {
    return view('circles.create');
})->name('circles.create');

Route::post('/circles', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'title' => ['required', 'string', 'max:80'],
        'summary' => ['nullable', 'string', 'max:500'],
        'contribution_amount' => ['required', 'integer', 'min:100'],
        'frequency' => ['required', 'in:daily,weekly,monthly'],
        'slots_total' => ['required', 'integer', 'min:2', 'max:31'],
        'start_date' => ['required', 'date', 'after_or_equal:today'],
    ]);

    $circle = \App\Models\Circle::create([
        ...$data,
        'host_id' => $request->user()->getAuthIdentifier(),
        'slug' => \Illuminate\Support\Str::slug($data['title']).'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4)),
        'status' => 'open',
        'visibility' => 'public',
    ]);

    $start = \Carbon\Carbon::parse($data['start_date']);

    for ($i = 1; $i <= $data['slots_total']; $i++) {
        $due = match ($data['frequency']) {
            'daily' => $start->copy()->addDays($i - 1),
            'weekly' => $start->copy()->addWeeks($i - 1),
            'monthly' => $start->copy()->addMonths($i - 1),
        };

        $circle->slots()->create([
            'position' => $i,
            'status' => 'open',
            'eligibility' => 'open',
            'payout_due_on' => $due->toDateString(),
        ]);
    }

    $knownUntil = (int) ceil($data['slots_total'] / 2);
    $circle->slots()->where('position', '<=', $knownUntil)->update(['eligibility' => 'known']);

    return redirect()->route('discover')->with('message', 'Circle listed. Members can now see the open slots.');
})->middleware('throttle:create-team')->name('circles.store');

Route::get('/discover', function () {
    $joinedIds = \App\Models\CommunityMember::where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->whereIn('status', ['active', 'pending'])
        ->pluck('community_id');

    $teams = \App\Models\Community::with('creator')
        ->withCount(['members' => fn ($q) => $q->where('status', 'active')])
        ->where('is_discoverable', true)
        ->whereNotIn('id', $joinedIds)
        ->latest()
        ->paginate(12);

    $projects = \App\Models\Project::with(['community', 'creator', 'payoutSlots'])
        ->where('visibility', 'public')
        ->whereIn('status', ['active', 'goal_reached'])
        ->where('project_type', 'rotational')
        ->when(request('frequency'), fn ($q, $f) => $q->where('frequency', $f))
        ->when(request('max_amount'), fn ($q, $n) => $q->where('contribution_amount', '<=', (int) $n))
        ->latest()
        ->paginate(12);

    $myTeamIds = $joinedIds;

    return view('teams.index', compact('teams', 'projects', 'myTeamIds'));
})->middleware('auth')->name('discover');

Route::get('/circles/mine', function () {
    $hosting = \App\Models\Circle::withCount(['slots as open_slots_count' => fn ($q) => $q->where('status', 'open')])
        ->where('host_id', Auth::id())
        ->latest()
        ->get();

    $joined = \App\Models\CircleSlot::with('circle')
        ->where('user_id', Auth::id())
        ->whereIn('status', ['reserved', 'active', 'paid'])
        ->latest()
        ->get();

    return view('circles.mine', compact('hosting', 'joined'));
})->name('circles.mine');

Route::get('/circles/{slug}', function ($slug) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();

    $circle->slots()
        ->where('status', 'reserved')
        ->where('reserved_until', '<', now())
        ->update([
            'status' => 'open',
            'user_id' => null,
            'reserved_until' => null,
        ]);

    $circle->load(['host', 'slots.user', 'slots.payments']);

    return view('circles.show', compact('circle'));
})->name('circles.show');

Route::post('/circles/{slug}/slots/{slot}/reserve', function ($slug, $slot) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    $slot = $circle->slots()->where('id', $slot)->firstOrFail();

    $isKnownToHost = Auth::id() === $circle->host_id || \App\Models\Friendship::where('status', 'accepted')
        ->where(function ($query) use ($circle) {
            $query->where(function ($query) use ($circle) {
                $query->where('user_id', Auth::id())->where('friend_id', $circle->host_id);
            })->orWhere(function ($query) use ($circle) {
                $query->where('user_id', $circle->host_id)->where('friend_id', Auth::id());
            });
        })
        ->exists();

    if ($slot->eligibility === 'known' && !$isKnownToHost) {
        return back()->with('error', 'This slot is for people the host already knows. Take a later open slot.');
    }

    if ($slot->status !== 'open') {
        return back()->with('error', 'That slot is no longer open.');
    }

    $already = $circle->slots()
        ->where('user_id', Auth::id())
        ->whereIn('status', ['reserved', 'active'])
        ->exists();

    if ($already) {
        return back()->with('error', 'You already have a slot in this circle.');
    }

    $slot->update([
        'user_id' => Auth::id(),
        'status' => 'reserved',
        'reserved_until' => now()->addHours(24),
    ]);

    $circle->host->notify(new \App\Notifications\CircleSlotUpdated(
        Auth::user()->name . ' reserved slot ' . $slot->position . ' in ' . $circle->title,
        route('circles.show', $circle->slug)
    ));

    return back()->with('message', 'Slot held for 24 hours. Confirm your place before it opens again.');
})->name('circles.slots.reserve');

Route::post('/circles/{slug}/slots/{slot}/confirm', function ($slug, $slot) {
    $circle = \App\Models\Circle::where('slug', $slug)->where('status', 'open')->firstOrFail();
    $slot = $circle->slots()->where('id', $slot)->firstOrFail();

    if ($slot->user_id !== Auth::id() || $slot->status !== 'reserved') {
        return back()->with('error', 'This hold is not yours.');
    }

    if ($slot->reserved_until && $slot->reserved_until->isPast()) {
        $slot->update(['status' => 'open', 'user_id' => null, 'reserved_until' => null]);
        return back()->with('error', 'That hold expired. Reserve the slot again.');
    }

    $slot->update([
        'status' => 'active',
        'reserved_until' => null,
    ]);

    if ($circle->slots()->where('status', 'open')->doesntExist()) {
        $circle->update(['status' => 'full']);
    }

    return back()->with('message', 'You are in slot '.$slot->position.'.');
})->name('circles.slots.confirm');

Route::post('/circles/{slug}/slots/{slot}/pay', function (\Illuminate\Http\Request $request, $slug, $slot) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    $slot = $circle->slots()->where('id', $slot)->firstOrFail();

    if ($slot->user_id !== Auth::id() || $slot->status !== 'active') {
        return back()->with('error', 'Confirm your slot before you pay.');
    }

    $pending = $slot->payments()->whereIn('status', ['pending', 'approved'])->exists();
    if ($pending) {
        return back()->with('error', 'This slot already has a payment.');
    }

    $data = $request->validate([
        'amount' => ['required', 'integer', 'min:1'],
        'paid_on' => ['required', 'date', 'before_or_equal:today'],
        'proof' => ['required', 'image', 'max:4096'],
    ]);

    $path = $request->file('proof')->store('circle-payments', 'public');

    $slot->payments()->create([
        'user_id' => Auth::id(),
        'amount' => $data['amount'],
        'paid_on' => $data['paid_on'],
        'proof_path' => $path,
        'status' => 'pending',
    ]);

    $circle->host->notify(new \App\Notifications\CircleSlotUpdated(
    Auth::user()->name . ' uploaded a receipt for slot ' . $slot->position,
    route('circles.show', $circle->slug)
));

    return back()->with('message', 'Proof sent. The host will approve it.');
})->name('circles.slots.pay');

Route::post('/circles/{slug}/payments/{payment}/approve', function ($slug, $payment) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    abort_unless($circle->host_id === Auth::id(), 403);

    $payment = \App\Models\CirclePayment::where('id', $payment)
        ->whereHas('slot', fn ($q) => $q->where('circle_id', $circle->id))
        ->firstOrFail();

    $payment->update([
        'status' => 'approved',
        'reviewed_by' => Auth::id(),
        'reviewed_at' => now(),
    ]);

    $payment->slot->update(['status' => 'paid']);

    $payment->slot->user->notify(new \App\Notifications\CircleSlotUpdated(
        'Your payment for slot ' . $payment->slot->position . ' was ' . $payment->status,
        route('circles.show', $circle->slug)
    ));

    return back()->with('message', 'Payment approved.');
})->name('circles.payments.approve');

Route::post('/circles/{slug}/payments/{payment}/reject', function ($slug, $payment) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    abort_unless($circle->host_id === Auth::id(), 403);

    $payment = \App\Models\CirclePayment::where('id', $payment)
        ->whereHas('slot', fn ($q) => $q->where('circle_id', $circle->id))
        ->firstOrFail();

    $payment->update([
        'status' => 'rejected',
        'reviewed_by' => Auth::id(),
        'reviewed_at' => now(),
    ]);

    $payment->slot->user->notify(new \App\Notifications\CircleSlotUpdated(
        'Your payment for slot ' . $payment->slot->position . ' was ' . $payment->status,
        route('circles.show', $circle->slug)
    ));

    return back()->with('message', 'Payment rejected. The member can send a new proof.');
})->name('circles.payments.reject');

Route::post('/circles/{slug}/bank', function (\Illuminate\Http\Request $request, $slug) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    abort_unless($circle->host_id === Auth::id(), 403);

    $data = $request->validate([
        'bank_name' => ['required', 'string', 'max:80'],
        'account_name' => ['required', 'string', 'max:120'],
        'account_number' => ['required', 'string', 'max:20'],
    ]);

    $circle->update($data);

    return back()->with('message', 'Payout account saved.');
})->name('circles.bank');

Route::post('/circles/{slug}/slots/{slot}/payout', function ($slug, $slot) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    abort_unless((int) $circle->host_id === (int) Auth::id(), 403);

    $slot = $circle->slots()->where('id', $slot)->firstOrFail();

    if (!in_array($slot->status, ['active', 'paid'], true)) {
        return back()->with('error', 'Only a confirmed slot can be paid out.');
    }

    if ($slot->paid_out_at) {
        return back()->with('error', 'This slot was already paid out.');
    }

    $earlier = $circle->slots()
        ->where('position', '<', $slot->position)
        ->whereNull('paid_out_at')
        ->whereIn('status', ['active', 'paid'])
        ->exists();

    if ($earlier) {
        return back()->with('error', 'Pay the earlier slot first.');
    }

    $slot->update([
        'paid_out_at' => now(),
        'paid_out_by' => Auth::id(),
    ]);

    $left = $circle->slots()->whereNull('paid_out_at')->exists();
    if (!$left) {
        $circle->update(['status' => 'completed']);
    }

    $slot->user->notify(new \App\Notifications\CircleSlotUpdated(
        'You were paid out for slot ' . $slot->position . ' in ' . $circle->title,
        route('circles.show', $circle->slug)
    ));

    return back()->with('message', 'Slot ' . $slot->position . ' marked as paid out.');
})->name('circles.slots.payout');

Route::get('/circles/{slug}/messages', function ($slug) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    $holdsSlot = $circle->slots()->where('user_id', Auth::id())->whereIn('status', ['active', 'paid'])->exists();
    abort_unless($holdsSlot || (int) $circle->host_id === (int) Auth::id(), 403);

    $messages = $circle->messages()->with('from')->latest()->take(50)->get()->reverse();

    return view('circles.messages', compact('circle', 'messages'));
})->name('circles.messages');

Route::post('/circles/{slug}/messages', function ($slug, \Illuminate\Http\Request $request) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    $holdsSlot = $circle->slots()->where('user_id', Auth::id())->whereIn('status', ['active', 'paid'])->exists();
    abort_unless($holdsSlot || (int) $circle->host_id === (int) Auth::id(), 403);

    $data = $request->validate(['body' => ['required', 'string', 'max:1000']]);

    $to = (int) $circle->host_id === (int) Auth::id()
        ? $circle->slots()->whereNotNull('user_id')->value('user_id')
        : $circle->host_id;

    $message = $circle->messages()->create([
        'from_user_id' => Auth::id(),
        'to_user_id' => $to,
        'body' => $data['body'],
    ]);

    \App\Models\User::find($to)?->notify(new \App\Notifications\CircleSlotUpdated(
        $request->user()->name . ' sent a message in ' . $circle->title,
        route('circles.messages', $circle->slug)
    ));

    return back();
})->name('circles.messages.store');

Route::post('/circles/{slug}/rate', function ($slug, \Illuminate\Http\Request $request) {
    $circle = \App\Models\Circle::where('slug', $slug)->firstOrFail();
    $finished = $circle->slots()
        ->where('user_id', \Illuminate\Support\Facades\Auth::id())
        ->whereNotNull('paid_out_at')
        ->exists();
    abort_unless($finished, 403);

    $data = $request->validate([
        'score' => ['required', 'integer', 'min:1', 'max:5'],
        'comment' => ['nullable', 'string', 'max:300'],
    ]);

    \App\Models\CircleRating::updateOrCreate(
        ['circle_id' => $circle->id, 'user_id' => \Illuminate\Support\Facades\Auth::id()],
        [
            'host_id' => $circle->host_id,
            'score' => $data['score'],
            'comment' => $data['comment'] ?? null,
        ]
    );

    return back()->with('message', 'Rating saved.');
})->name('circles.rate');

Route::post('/teams/{slug}/feature-circle', function (\Illuminate\Http\Request $request, $slug) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    abort_unless($team->isAdmin(\Illuminate\Support\Facades\Auth::user()), 403);

    $validated = $request->validate([
        'project_id' => 'nullable|exists:projects,id',
    ]);

    $projectId = $validated['project_id'] ?? null;

    if ($projectId) {
        $project = $team->projects()->where('id', $projectId)->where('project_type', 'rotational')->first();
        if (!$project) {
            return back()->with('error', 'You can only feature a rotational circle that belongs to this team.');
        }
    }

    $team->update(['featured_project_id' => $projectId]);

    return back()->with('message', $projectId ? 'Circle featured on team.' : 'Featured circle removed.');
})->name('teams.feature-circle');

Route::post('/teams/{slug}/projects/{projectId}/slots/{slotId}/reserve', function ($slug, $projectId, $slotId) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $project = $team->projects()->findOrFail($projectId);

    if ($project->project_type !== 'rotational') {
        return back()->with('error', 'This is not a circle.');
    }

    $slot = $project->payoutSlots()->where('id', $slotId)->where('status', 'open')->firstOrFail();

    // Optional: require team membership first
    if (!$team->isMember(\Illuminate\Support\Facades\Auth::user())) {
        return redirect()->route('teams.join-link', $slug)
            ->with('message', 'Join the team first, then reserve a slot.');
    }

    $slot->update([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'status' => 'reserved',
        'reserved_until' => now()->addHours(24), // optional hold window
    ]);

    return back()->with('message', 'Slot reserved. Complete your first contribution to lock it in.');
})->middleware('auth')->name('projects.slots.reserve');

Route::post('/teams/{slug}/projects/{projectId}/slots/{slotId}/payout', function ($slug, $projectId, $slotId) {
    $team = \App\Models\Community::where('slug', $slug)->firstOrFail();
    $project = $team->projects()->findOrFail($projectId);
    $currentUser = \Illuminate\Support\Facades\Auth::user();

    if (!$team->isAdmin($currentUser) && $project->creator_id !== $currentUser?->id) {
        abort(403);
    }

    $slot = $project->payoutSlots()->where('id', $slotId)->where('status', 'active')->firstOrFail();
    $slotUser = $slot->user;

    $slot->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    \App\Models\Activity::log([
        'user_id' => $currentUser?->id,
        'community_id' => $team->id,
        'project_id' => $project->id,
        'type' => 'milestone',
        'title' => ($slotUser?->name ?? 'Member') . ' collected ₦' . number_format($project->contribution_amount, 0),
        'body' => 'Circle payout · position ' . $slot->position,
        'url' => route('projects.show', [$team->slug, $project->id]),
    ]);

    return back()->with('message', 'Payout marked. Slot closed.');
})->middleware('auth')->name('projects.slots.payout');

});


 // ====================== WELFARE ======================


Route::middleware(['auth', 'ajo.only'])->group(function () {
    // paste welfare.*, contributions.monthly*, portfolio, assets, polls, etc.

     // Welfare
        Route::get('/welfare/create', function () {
            return view('welfare.create');
        })->name('welfare.create');

        Route::post('/welfare', function (Illuminate\Http\Request $request) {

        if ($request->user()->is_suspended) {
        return back()->with('error', 'Your account is suspended. Please pay the reactivation fee to continue.');
        }

        $validated = $request->validate([
            'request_type' => 'required|in:medical,bereavement,emergency,education,others',
            'amount_requested' => 'required|numeric|min:1000',
            'description' => 'required|min:20',
            'supporting_docs' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = [
            'user_id' => $request->user()->id,
            'request_type' => $validated['request_type'],
            'amount_requested' => $validated['amount_requested'],
            'description' => $validated['description'],
            'status' => 'pending',
        ];

        if ($request->hasFile('supporting_docs')) {
            $data['supporting_docs'] = $request->file('supporting_docs')->store('welfare_docs', 'public');
        }

        // Create the request and store it in a variable
        $welfareRequest = \App\Models\WelfareRequest::create($data);

        // Notify all officers and admins
        $officers = \App\Models\User::role(['admin', 'officer', 'welfare_officer', 'chairperson'])->get();

        foreach ($officers as $officer) {
            $officer->notify(new \App\Notifications\WelfareRequestSubmitted($welfareRequest));
        }

        return redirect()->route('welfare.create')
                        ->with('message', 'Welfare request submitted successfully!');
    })->name('welfare.store');

        // ====================== WELFARE ======================
    Route::get('/welfare/manage', function () {
        if (!\App\Models\User::role(['admin', 'welfare_officer', 'chairperson'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403, 'Only Admin, Welfare Officer or Chairperson can access this page.');
        }

        $requests = \App\Models\WelfareRequest::with(['user', 'reviewer'])->latest()->get();
        return view('welfare.manage', compact('requests'));
    })->name('welfare.manage');

        Route::post('/welfare/{id}/approve', function (Illuminate\Http\Request $request, $id) {
        if (!\App\Models\User::role(['admin', 'welfare_officer', 'chairperson'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'approved_amount' => 'required|numeric|min:1000',
        ]);

        $welfare = \App\Models\WelfareRequest::with('user')->findOrFail($id);

        if ($welfare->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $welfare->update([
            'status' => 'approved',
            'approved_amount' => $validated['approved_amount'],
            'reviewed_by' => \Illuminate\Support\Facades\Auth::id(),
            'decision_date' => now(),
        ]);

        // Notify member
        $welfare->user->notify(new \App\Notifications\WelfareRequestStatusUpdated($welfare));

        // Notify Treasurer / Admin for disbursement
        $treasurers = \App\Models\User::role(['admin', 'treasurer', 'chairperson'])->get();
        foreach ($treasurers as $person) {
            $person->notify(new \App\Notifications\WelfareRequestStatusUpdated($welfare));
        }

        return back()->with('message', 'Request approved. Sent to Treasurer for disbursement.');
    })->name('welfare.approve');

    Route::post('/welfare/{id}/reject', function (Illuminate\Http\Request $request, $id) {
        if (!\App\Models\User::role(['admin', 'welfare_officer', 'chairperson'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403);
        }

        $welfare = \App\Models\WelfareRequest::with('user')->findOrFail($id);

        if ($welfare->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $welfare->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'decision_date' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        // Notify member
        $welfare->user->notify(new \App\Notifications\WelfareRequestStatusUpdated($welfare));

        return back()->with('message', 'Request rejected.');
    })->name('welfare.reject');

    Route::get('/welfare/disburse', function () {
        if (!\App\Models\User::role(['admin', 'treasurer', 'chairperson'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403);
        }

        $requests = \App\Models\WelfareRequest::with(['user', 'reviewer'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('welfare.disburse', compact('requests'));
    })->name('welfare.disburse');

    Route::post('/welfare/{id}/disburse', function ($id) {
        if (!\App\Models\User::role(['admin', 'treasurer', 'chairperson'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403);
        }

        $welfare = \App\Models\WelfareRequest::with('user')->findOrFail($id);

        if ($welfare->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be disbursed.');
        }

        // Check member has bank details
        if (!$welfare->user->account_number) {
            return back()->with('error', 'Member has not provided bank details. Cannot disburse.');
        }

        $welfare->update([
            'status' => 'disbursed',
            'disbursed_by' => \Illuminate\Support\Facades\Auth::id(),
            'disbursed_at' => now(),
        ]);

        // Notify member
        $welfare->user->notify(new \App\Notifications\WelfareRequestStatusUpdated($welfare));

        // Notify Admin, Treasurer, Auditor for bookkeeping
        $bookkeepers = \App\Models\User::role(['admin', 'treasurer', 'auditor', 'chairperson'])->get();
        foreach ($bookkeepers as $person) {
            if ($person->id !== \Illuminate\Support\Facades\Auth::id()) {
                $person->notify(new \App\Notifications\WelfareRequestStatusUpdated($welfare));
            }
        }

        return back()->with('message', 'Funds marked as disbursed. Bookkeeping notifications sent.');
    })->name('welfare.disburse.store');

    Route::get('/welfare/monthly-list', function () {
        if (!\App\Models\User::role(['admin', 'welfare_officer', 'chairperson', 'treasurer'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403);
        }

        $month = request('month', now()->format('Y-m'));

        $requests = \App\Models\WelfareRequest::with(['user', 'reviewer', 'disbursedBy'])
            ->whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))
            ->latest()
            ->get();

        return view('welfare.monthly-list', compact('requests', 'month'));
    })->name('welfare.monthly-list');

    
    Route::get('/welfare/monthly-list/download', function () {
        if (!\App\Models\User::role(['admin', 'welfare_officer', 'chairperson', 'treasurer'])
            ->whereKey(\Illuminate\Support\Facades\Auth::id())
            ->exists()) {
            abort(403);
        }

        $month = request('month', now()->format('Y-m'));

        $requests = \App\Models\WelfareRequest::with('user')
            ->whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))
            ->latest()
            ->get();

        $filename = 'welfare-requests-' . $month . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S/N', 'Name', 'Type', 'Amount Requested', 'Amount Approved', 'Status', 'Date']);

            foreach ($requests as $index => $r) {
                fputcsv($file, [
                    $index + 1,
                    $r->user->name,
                    $r->request_type,
                    $r->amount_requested,
                    $r->approved_amount ?? 0,
                    $r->status,
                    $r->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('welfare.monthly-list.download');

    // Monthly Contributions
        Route::get('/contributions/monthly', function () {
            return view('contributions.monthly');
        })->name('contributions.monthly');

        Route::post('/contributions/monthly', function (Illuminate\Http\Request $request) {
        $user = $request->user();

        // Check if suspended
        if ($user->is_suspended ?? false) {
            return back()->with('error', 'Your account is suspended. Please pay the reactivation fee to continue.');
        }

        $validated = $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'amount' => 'required|numeric',
            'is_late' => 'required|boolean',
            'penalty_amount' => 'required|numeric|min:0',
        ]);

        $amount = (float) \App\Models\Setting::get('monthly_contribution_amount', 50000);
        $isLate = (bool) $validated['is_late'];
        $penalty = $isLate ? $amount * 0.10 : 0;

        // Prevent duplicate contribution for the same month
        $currentMonth = now()->format('Y-m') . '-01';
        $exists = \App\Models\MonthlyContribution::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'You have already submitted a contribution for this month.');
        }

        $path = $request->file('proof_of_payment')->store('contribution_proofs', 'public');

        $welfare = $amount * 0.30;
        $investment = $amount * 0.60;
        $operations = $amount * 0.10;

        $contribution = \App\Models\MonthlyContribution::create([
            'user_id' => $user->id,
            'total_amount' => $amount,
            'welfare_amount' => $welfare,
            'investment_amount' => $investment,
            'operations_amount' => $operations,
            'penalty_amount' => $penalty,
            'is_late' => $isLate,
            'month' => $currentMonth,
            'status' => 'pending',
            'proof_of_payment' => $path,
        ]);

        // Notify Admins and Treasurer
        $officers = \App\Models\User::role(['admin', 'treasurer', 'chairperson'])->get();
        foreach ($officers as $officer) {
            $officer->notify(new \App\Notifications\ContributionSubmitted($contribution));
        }

        return redirect()->route('contributions.monthly')
                        ->with('message', 'Contribution submitted successfully! Waiting for approval.');
    })->name('contributions.monthly.store');

    // generate monthly contributions list
Route::get('/reports/monthly-contributions', function () {
    if (!user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $month = request('month', now()->format('Y-m'));

    $contributions = \App\Models\MonthlyContribution::with('user')
        ->where('month', $month . '-01')
        ->where('status', 'approved')
        ->orderBy('user_id')
        ->get();

    return view('reports.monthly-contributions', compact('contributions', 'month'));
})->name('reports.monthly-contributions');

//Download Monthly Contributions List
Route::get('/reports/monthly-contributions/download', function () {
    if (!user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $month = request('month', now()->format('Y-m'));

    $contributions = \App\Models\MonthlyContribution::with('user')
        ->where('month', $month . '-01')
        ->where('status', 'approved')
        ->orderBy('user_id')
        ->get();

    $filename = 'contributions-' . $month . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function () use ($contributions) {
        $file = fopen('php://output', 'w');

        // Header row
        fputcsv($file, ['S/N', 'Name', 'Email', 'Amount', 'Penalty', 'Date']);

        // Data rows
        foreach ($contributions as $index => $c) {
            fputcsv($file, [
                $index + 1,
                $c->user->name,
                $c->user->email,
                $c->total_amount,
                $c->penalty_amount ?? 0,
                $c->created_at->format('Y-m-d'),
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
})->name('reports.monthly-contributions.download');

// Contribution Management (Admin / Treasurer)
// ====================== CONTRIBUTIONS ======================
Route::get('/contributions/manage', function () {
    if (!user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403, 'Only Admin, Treasurer or Chairperson can access this page.');
    }

    $contributions = \App\Models\MonthlyContribution::with('user')->latest()->get();
    return view('contributions.manage', compact('contributions'));
})->name('contributions.manage');

Route::post('/contributions/{id}/approve', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $contribution = \App\Models\MonthlyContribution::with('user')->findOrFail($id);

    if ($contribution->status !== 'pending') {
        return back()->with('error', 'This contribution has already been processed.');
    }

    // Approve the contribution
    $contribution->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    // Credit investment units (only from the investment portion)
    $unit = \App\Models\InvestmentUnit::firstOrCreate(['user_id' => $contribution->user_id]);
    $unitPrice = (float) \App\Models\Setting::get('unit_price', 100);
    $newUnits = $contribution->investment_amount / $unitPrice;

    $unit->total_units += $newUnits;
    $unit->total_invested += $contribution->investment_amount;
    $unit->average_price = $unit->total_units > 0 
        ? $unit->total_invested / $unit->total_units 
        : 0;
    $unit->save();

    // Notify the member
    $contribution->user->notify(new \App\Notifications\ContributionStatusUpdated($contribution));

    // Notify Admin, Treasurer and Internal Auditor for bookkeeping
    $bookkeepers = \App\Models\User::role(['admin', 'treasurer', 'auditor', 'chairperson'])->get();

    foreach ($bookkeepers as $person) {
        // Avoid notifying the same person twice if they already received status update
        if ($person->id !== $contribution->user_id) {
            $person->notify(new \App\Notifications\ContributionStatusUpdated($contribution));
        }
    }

    return back()->with('message', 'Contribution approved successfully. Units credited and notifications sent.');
})->name('contributions.approve');

Route::post('/contributions/{id}/reject', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'treasurer', 'chairperson'])) {
        abort(403);
    }

    $contribution = \App\Models\MonthlyContribution::findOrFail($id);

    $contribution->update([
        'status' => 'rejected',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    // Notify the member
    $contribution->user->notify(new \App\Notifications\ContributionStatusUpdated($contribution));

    return back()->with('message', 'Contribution rejected.');
})->name('contributions.reject');

// Portfolio
Route::get('/portfolio', function () {
    $user = Auth::user();

    if (! $user) {
        abort(403);
    }

    $unit = $user->investmentUnit;
    $contributions = \App\Models\MonthlyContribution::where('user_id', $user->id)->latest()->get();
    $holdings = \App\Models\InvestmentHolding::with('offering')
        ->where('user_id', $user->id)
        ->where('units', '>', 0)
        ->get();

    return view('portfolio.index', compact('unit', 'contributions', 'holdings'));
})->name('portfolio');

// Assets
// ====================== ASSETS ======================
Route::get('/assets', function () {
    $assets = \App\Models\InvestmentAsset::latest()->get();
    return view('assets.index', compact('assets'));
})->name('assets');

Route::get('/assets/create', function () {
    $user = Auth::user();

    if (! user_has_any_role($user, ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }

    return view('assets.create');
})->name('assets.create');

Route::post('/assets', function (Illuminate\Http\Request $request) {
    if (! user_has_any_role(Auth::user(), ['admin', 'investment_officer', 'chairperson'])) {
        abort(403);
    }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
            'purchase_value' => 'required|numeric|min:1',
            'current_value' => 'nullable|numeric',
            'purchase_date' => 'required|date',
            'maturity_date' => 'nullable|date',
            'asset_manager' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $validated['added_by'] = Auth::id();
        $validated['status'] = 'active';
        $validated['current_value'] = $validated['current_value'] ?? $validated['purchase_value'];

        \App\Models\InvestmentAsset::create($validated);

        return redirect()->route('assets')->with('message', 'Investment asset added successfully!');
    })->name('assets.store');

    // Polls
    Route::get('/polls', function () {
    $polls = \App\Models\Poll::with(['options', 'votes'])
        ->where('status', 'active')
        ->latest()
        ->get();

    return view('polls.index', compact('polls'));
})->name('polls');

    Route::get('/polls/create', function () {
    if (!user_has_any_role(Auth::user(), ['admin', 'chairperson'])) {
        abort(403);
    }
    return view('polls.create');
})->name('polls.create');

    Route::post('/polls', function (Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'options' => 'required|array|min:2',
        'options.*' => 'required|string|max:255',
    ]);

    $poll = \App\Models\Poll::create([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'created_by' => Auth::id(),
        'status' => 'pending',   // ← changed from 'active'
    ]);

    foreach ($validated['options'] as $option) {
        $poll->options()->create(['option_text' => $option]);
    }

    return redirect()->route('polls.manage')->with('message', 'Poll created and waiting for approval.');
})->name('polls.store');



    Route::post('/polls/{poll}/vote', function (Illuminate\Http\Request $request, \App\Models\Poll $poll) {
        if ($poll->votes()->where('user_id', Auth::id())->exists()) {
            return back()->with('error', 'You have already voted.');
        }

        $request->validate(['option_id' => 'required|exists:poll_options,id']);

        $poll->votes()->create([
            'poll_option_id' => $request->option_id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('message', 'Your vote has been recorded.');
    })->name('polls.vote');

    // Polls Management
// ====================== POLLS MANAGEMENT ======================
Route::get('/polls/manage', function () {
    if (!user_has_any_role(Auth::user(), ['admin', 'chairperson'])) {
        abort(403);
    }

    $polls = \App\Models\Poll::with(['options.votes', 'votes', 'creator'])->latest()->get();
    return view('polls.manage', compact('polls'));
})->name('polls.manage');

Route::post('/polls/{id}/close', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'officer', 'chairperson'])) {
        abort(403);
    }

    $poll = \App\Models\Poll::findOrFail($id);
    $poll->update(['status' => 'closed']);

    return back()->with('message', 'Poll closed successfully.');
})->name('polls.close');

Route::delete('/polls/{id}', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'officer', 'chairperson'])) {
        abort(403);
    }

    $poll = \App\Models\Poll::findOrFail($id);
    $poll->delete();

    return back()->with('message', 'Poll deleted successfully.');
})->name('polls.destroy');

Route::post('/polls/{id}/approve', function ($id) {
    if (!user_has_any_role(Auth::user(), ['admin', 'chairperson'])) {
        abort(403);
    }

    $poll = \App\Models\Poll::findOrFail($id);
    $poll->update(['status' => 'active']);

    return back()->with('message', 'Poll approved and is now active.');
})->name('polls.approve');

    // Members
    Route::get('/members', function () {
    $members = \App\Models\User::with('roles')->orderBy('name')->get();
    return view('members.index', compact('members'));
})->name('members');

});

require __DIR__.'/auth.php';