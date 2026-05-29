<x-filament-panels::page>
@php
    $d       = $this->getDashboardData();
    $user    = auth()->user();
    $tenant  = Filament\Facades\Filament::getTenant();
    $hour    = now()->hour;
    $greet   = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $first   = explode(' ', $user->name)[0];
    $role    = $user->roles->pluck('name')->map(fn($r) => ucfirst($r))->join(', ');

    $trend   = $d['trend'];
    $maxVal  = max($trend->max(fn($t) => $t['approved'] + $t['pending']), 1);

    $appTotal = max($d['totalApps'], 1);
    $r = 38; $cx = 50; $cy = 50; $circ = 2 * M_PI * $r;
    $slices = [
        ['v' => $d['approvedApps'], 'c' => '#10b981', 'l' => 'Approved'],
        ['v' => $d['pendingApps'],  'c' => '#f59e0b', 'l' => 'Pending'],
        ['v' => $d['rejectedApps'], 'c' => '#f43f5e', 'l' => 'Rejected'],
    ];
    $off = 0; $segs = [];
    foreach ($slices as $s) {
        $fr = $s['v'] / $appTotal;
        $segs[] = ['d' => $fr * $circ, 'g' => $circ - $fr * $circ, 'o' => $off, 'c' => $s['c']];
        $off += $fr * $circ;
    }
@endphp

<style>
:root{--cs:rgba(255,255,255,.035);--cb:rgba(255,255,255,.08);--ct:#f1f5f9;--cm:#64748b;--ce:#94a3b8;--rad:16px;--rads:10px;}
.dash{display:grid;grid-template-columns:repeat(12,1fr);gap:18px;}
@media(max-width:1100px){.dash{grid-template-columns:repeat(6,1fr);}}
@media(max-width:640px){.dash{grid-template-columns:1fr;}}
.card{background:var(--cs);border:1px solid var(--cb);border-radius:var(--rad);padding:22px 24px;position:relative;overflow:hidden;backdrop-filter:blur(10px);transition:transform .2s,box-shadow .2s;}
.card:hover{transform:translateY(-2px);box-shadow:0 16px 40px rgba(0,0,0,.28);}
.sp-12{grid-column:span 12;} .sp-8{grid-column:span 8;} .sp-7{grid-column:span 7;} .sp-6{grid-column:span 6;} .sp-5{grid-column:span 5;} .sp-4{grid-column:span 4;} .sp-3{grid-column:span 3;}
@media(max-width:1100px){.sp-12,.sp-8,.sp-7,.sp-6,.sp-5,.sp-4,.sp-3{grid-column:span 6;}}
@media(max-width:640px){.sp-12,.sp-8,.sp-7,.sp-6,.sp-5,.sp-4,.sp-3{grid-column:span 1;}}
/* Hero */
.hero{background:linear-gradient(120deg,#0f172a 0%,#1e1b4b 45%,#172554 100%);border-color:rgba(99,102,241,.25);display:flex;align-items:center;justify-content:space-between;gap:20px;padding:30px 32px;}
.hero::after{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 55% 100% at 95% 50%,rgba(249,115,22,.22) 0%,transparent 65%);pointer-events:none;}
.hero-name{font-size:clamp(1.25rem,2.5vw,1.8rem);font-weight:800;color:#fff;letter-spacing:-.03em;line-height:1.1;}
.hero-sub{font-size:.875rem;color:#a5b4fc;margin-top:5px;}
.hero-pill{display:inline-flex;align-items:center;gap:6px;margin-top:12px;background:rgba(249,115,22,.18);border:1px solid rgba(249,115,22,.4);color:#fb923c;font-size:.7rem;font-weight:700;padding:4px 13px;border-radius:999px;text-transform:uppercase;letter-spacing:.07em;}
.hero-pill::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}
.hero-right{text-align:right;flex-shrink:0;line-height:1.4;}
.hero-day{font-size:3rem;font-weight:900;color:#fff;letter-spacing:-.05em;line-height:1;}
.hero-month{font-size:.85rem;color:#a5b4fc;font-weight:600;}
.hero-dow{font-size:.75rem;color:#6366f1;font-weight:700;text-transform:uppercase;letter-spacing:.1em;margin-top:3px;}
/* Alert */
.alert{display:flex;align-items:center;gap:12px;background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.28);padding:13px 18px;border-radius:var(--rad);}
.alert-txt{font-size:.83rem;color:#fbbf24;flex:1;}
.alert-lnk{font-size:.8rem;font-weight:700;color:#fbbf24;text-decoration:underline;white-space:nowrap;}
/* KPI */
.kpi-ic{width:42px;height:42px;border-radius:var(--rads);display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
.kpi-val{font-size:2.2rem;font-weight:900;color:var(--ct);letter-spacing:-.04em;line-height:1;}
.kpi-lbl{font-size:.72rem;color:var(--cm);font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-top:5px;}
.kpi-sub{font-size:.75rem;font-weight:600;margin-top:8px;display:flex;align-items:center;gap:4px;}
.kpi-bar{position:absolute;bottom:0;left:0;right:0;height:3px;border-radius:0 0 var(--rad) var(--rad);}
/* Sec title */
.sec-ttl{font-size:.9rem;font-weight:700;color:var(--ct);margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;}
.sec-lnk{font-size:.75rem;color:#f97316;font-weight:600;text-decoration:none;}
.sec-lnk:hover{text-decoration:underline;}
.sec-sub{font-size:.72rem;color:var(--cm);font-weight:400;}
/* Bar chart */
.barchart{display:flex;align-items:flex-end;gap:8px;height:110px;}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;height:100%;justify-content:flex-end;}
.bar-stack{width:100%;display:flex;flex-direction:column-reverse;gap:2px;border-radius:5px 5px 0 0;overflow:hidden;}
.bar-seg{width:100%;min-height:3px;transition:height .7s cubic-bezier(.22,1,.36,1);}
.bar-seg:hover{filter:brightness(1.2);}
.bar-lbl{font-size:.67rem;color:var(--cm);font-weight:600;}
.chart-leg{display:flex;gap:16px;margin-top:14px;}
.leg-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0;}
.leg-txt{font-size:.73rem;color:var(--ce);display:flex;align-items:center;gap:6px;}
/* Donut */
.donut-wrap{display:flex;align-items:center;gap:22px;margin-top:6px;}
.donut-leg{flex:1;display:flex;flex-direction:column;gap:12px;}
.dleg-row{display:flex;align-items:center;justify-content:space-between;font-size:.8rem;}
.dleg-left{display:flex;align-items:center;gap:8px;color:var(--ce);}
.dleg-dot{width:9px;height:9px;border-radius:3px;}
.dleg-val{font-weight:800;color:var(--ct);}
.dleg-pct{font-size:.68rem;color:var(--cm);}
/* Section divider */
.sec-div{grid-column:span 12;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:var(--cm);padding-bottom:4px;border-bottom:1px solid var(--cb);}
@media(max-width:1100px){.sec-div{grid-column:span 6;}}
@media(max-width:640px){.sec-div{grid-column:span 1;}}
/* Widgets */
.wg-section{grid-column:span 12;display:flex;flex-direction:column;gap:18px;}
@media(max-width:1100px){.wg-section{grid-column:span 6;}}
@media(max-width:640px){.wg-section{grid-column:span 1;}}
/* Actions */
.act-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-top:16px;}
.act-btn{display:flex;align-items:center;gap:12px;padding:13px 16px;border-radius:var(--rads);background:rgba(255,255,255,.04);border:1px solid var(--cb);color:var(--ct);font-size:.83rem;font-weight:600;text-decoration:none;cursor:pointer;transition:background .15s,transform .15s,border-color .15s;}
.act-btn:hover{background:rgba(255,255,255,.09);transform:translateY(-1px);border-color:rgba(255,255,255,.16);}
.act-ic{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.badge-cnt{margin-left:auto;background:#f59e0b;color:#000;border-radius:999px;padding:1px 8px;font-size:.68rem;font-weight:800;}
</style>

<div class="dash">

    {{-- Alert --}}
    @if($d['pendingApps'] > 0)
    <div class="sp-12 card alert">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        <span class="alert-txt"><strong>{{ $d['pendingApps'] }} application{{ $d['pendingApps'] > 1 ? 's' : '' }}</strong> awaiting your review.</span>
        <a href="{{ route('filament.admin.resources.applications.index', ['tenant' => $tenant]) }}" class="alert-lnk">Review now →</a>
    </div>
    @endif

    {{-- Hero --}}
    <div class="sp-12 card hero">
        <div>
            <div class="hero-name">{{ $greet }}, {{ $first }} 👋</div>
            <div class="hero-sub">Here's your institution overview for today.</div>
            <div class="hero-pill">{{ $role }}</div>
        </div>
        <div class="hero-right">
            <div class="hero-day">{{ now()->format('d') }}</div>
            <div class="hero-month">{{ now()->format('F Y') }}</div>
            <div class="hero-dow">{{ now()->format('l') }}</div>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="sec-div">Overview</div>

    <div class="sp-3 card">
        <div class="kpi-ic" style="background:rgba(16,185,129,.14);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
        </div>
        <div class="kpi-val">{{ number_format($d['totalMentors']) }}</div>
        <div class="kpi-lbl">Total Mentors</div>
        <div class="kpi-sub" style="color:#10b981;">↑ {{ $d['activeMentors'] }} active · +{{ $d['newMentorsMonth'] }} this month</div>
        <div class="kpi-bar" style="background:#10b981;"></div>
    </div>

    <div class="sp-3 card">
        <div class="kpi-ic" style="background:rgba(56,189,248,.14);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#38bdf8" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        </div>
        <div class="kpi-val">{{ number_format($d['totalStudents']) }}</div>
        <div class="kpi-lbl">Total Students</div>
        <div class="kpi-sub" style="color:#38bdf8;">↑ {{ $d['activeStudents'] }} active · +{{ $d['newStudentsMonth'] }} this month</div>
        <div class="kpi-bar" style="background:#38bdf8;"></div>
    </div>

    <div class="sp-3 card">
        <div class="kpi-ic" style="background:rgba(249,115,22,.14);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#f97316" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
        </div>
        <div class="kpi-val">{{ number_format($d['activePrograms']) }}</div>
        <div class="kpi-lbl">Active Programs</div>
        <div class="kpi-sub" style="color:#f97316;">{{ $d['totalPrograms'] }} total · {{ $d['totalPrograms'] - $d['activePrograms'] }} inactive</div>
        <div class="kpi-bar" style="background:#f97316;"></div>
    </div>

    <div class="sp-3 card">
        <div class="kpi-ic" style="background:rgba(244,63,94,.14);">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#f43f5e" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="kpi-val">{{ number_format($d['pendingApps']) }}</div>
        <div class="kpi-lbl">Pending Applications</div>
        <div class="kpi-sub"style="color:{{ $d['pendingApps'] > 0 ? '#f43f5e' : '#10b981' }};">
            @if($d['pendingApps'] > 0) ⚠ Needs attention @else ✓ All reviewed @endif
        </div>
        <div class="kpi-bar" style="background:#f43f5e;"></div>
    </div>

    {{-- Analytics --}}
    <div class="sec-div">Analytics</div>

    {{-- Bar chart --}}
    <div class="sp-7 card">
        <div class="sec-ttl">Application Trend <span class="sec-sub">Last 6 months</span></div>
        <div class="barchart">
            @foreach($trend as $bar)
            @php
                $tot = $bar['approved'] + $bar['pending'];
                $h   = $maxVal > 0 ? ($tot / $maxVal * 100) : 0;
                $aH  = $tot > 0 ? ($bar['approved'] / $tot * 100) : 0;
                $pH  = $tot > 0 ? ($bar['pending']  / $tot * 100) : 0;
            @endphp
            <div class="bar-col">
                <div class="bar-stack"style="height:{{ max($h,3) }}%;width:100%;">
                    <div class="bar-seg"style="height:{{ $aH }}%;background:#10b981;" title="Approved: {{ $bar['approved'] }}"></div>
                    <div class="bar-seg"style="height:{{ $pH }}%;background:#f59e0b;" title="Pending: {{ $bar['pending'] }}"></div>
                </div>
                <div class="bar-lbl">{{ $bar['label'] }}</div>
            </div>
            @endforeach
        </div>
        <div class="chart-leg">
            <div class="leg-txt"><div class="leg-dot" style="background:#10b981;"></div>Approved</div>
            <div class="leg-txt"><div class="leg-dot" style="background:#f59e0b;"></div>Pending</div>
        </div>
    </div>

    {{-- Donut --}}
    <div class="sp-5 card">
        <div class="sec-ttl">Application Breakdown</div>
        <div class="donut-wrap">
            <svg viewBox="0 0 100 100" width="120" height="120" style="flex-shrink:0;transform:rotate(-90deg)">
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="16"/>
                @foreach($segs as $seg)
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="none"
                    stroke="{{ $seg['c'] }}" stroke-width="16"
                    stroke-dasharray="{{ $seg['d'] }} {{ $seg['g'] }}"
                    stroke-dashoffset="{{ -$seg['o'] }}"
                    stroke-linecap="butt"/>
                @endforeach
            </svg>
            <div class="donut-leg">
                @foreach($slices as $s)
                <div class="dleg-row">
                    <div class="dleg-left">
                        <div class="dleg-dot"style="background:{{ $s['c'] }};"></div>
                        {{ $s['l'] }}
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="dleg-val">{{ $s['v'] }}</div>
                        <div class="dleg-pct">{{ $d['totalApps'] > 0 ? round($s['v']/$d['totalApps']*100) : 0 }}%</div>
                    </div>
                </div>
                @endforeach
                <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:10px;display:flex;justify-content:space-between;">
                    <span style="font-size:.72rem;color:var(--cm);">Total</span>
                    <span style="font-size:.85rem;font-weight:800;color:var(--ct);">{{ $d['totalApps'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Existing Filament widgets (StatsOverview + Pending table) --}}
    <div class="sec-div">Live Data</div>
    <div class="wg-section">
        @foreach($this->getWidgets() as $widget)
            @livewire($widget, key($widget))
        @endforeach
    </div>

    {{-- Quick actions --}}
    <div class="sec-div">Quick Actions</div>
    <div class="sp-12 card" style="padding:20px 24px;">
        <div class="act-grid">
            <a href="{{ route('filament.admin.resources.students.create', ['tenant' => $tenant]) }}" class="act-btn">
                <div class="act-ic" style="background:rgba(56,189,248,.13);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#38bdf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>Enroll Student
            </a>
            <a href="{{ route('filament.admin.resources.mentors.create', ['tenant' => $tenant]) }}" class="act-btn">
                <div class="act-ic" style="background:rgba(16,185,129,.13);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#10b981" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>Add Mentor
            </a>
            <a href="{{ route('filament.admin.resources.applications.index', ['tenant' => $tenant]) }}" class="act-btn">
                <div class="act-ic" style="background:rgba(245,158,11,.13);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>Applications
                @if($d['pendingApps'] > 0)<span class="badge-cnt">{{ $d['pendingApps'] }}</span>@endif
            </a>
            <a href="{{ route('filament.admin.resources.programs.index', ['tenant' => $tenant]) }}" class="act-btn">
                <div class="act-ic" style="background:rgba(249,115,22,.13);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f97316" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>Programs
            </a>
            <a href="{{ route('filament.admin.resources.mentors.index', ['tenant' => $tenant]) }}" class="act-btn">
                <div class="act-ic" style="background:rgba(99,102,241,.13);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#818cf8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493"/></svg>
                </div>All Mentors
            </a>
            <a href="{{ route('filament.admin.resources.students.index', ['tenant' => $tenant]) }}" class="act-btn">
                <div class="act-ic" style="background:rgba(244,63,94,.13);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#f43f5e" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
                </div>All Students
            </a>
        </div>
    </div>

</div>
</x-filament-panels::page>