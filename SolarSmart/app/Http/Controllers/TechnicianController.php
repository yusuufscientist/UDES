<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Panel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    /**
     * Display technician dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        if (! $user->isTechnician() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $todayInterventions = Intervention::where('technician_id', $user->id)
            ->whereDate('scheduled_date', today())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['solarSystem', 'panel'])
            ->get();

        $upcomingInterventions = Intervention::where('technician_id', $user->id)
            ->upcoming()
            ->with(['solarSystem', 'panel'])
            ->take(10)
            ->get();

        $completedToday = Intervention::where('technician_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('completed_date', today())
            ->count();

        $stats = [
            'today_count' => $todayInterventions->count(),
            'upcoming_count' => Intervention::where('technician_id', $user->id)->upcoming()->count(),
            'completed_total' => Intervention::where('technician_id', $user->id)->completed()->count(),
            'completed_today' => $completedToday,
        ];

        return view('technician.dashboard', compact(
            'todayInterventions',
            'upcomingInterventions',
            'stats'
        ));
    }

    /**
     * Display all interventions for the technician
     */
    public function interventions()
    {
        $user = Auth::user();

        if (! $user->isTechnician() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $interventions = Intervention::where('technician_id', $user->id)
            ->with(['solarSystem', 'panel'])
            ->orderBy('scheduled_date', 'desc')
            ->paginate(20);

        return view('technician.interventions', compact('interventions'));
    }

    /**
     * Display panels needing maintenance
     */
    public function maintenanceNeeded()
    {
        $user = Auth::user();

        if (! $user->isTechnician() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Get panels with low efficiency or faulty status
        $panels = Panel::where(function ($query) {
            $query->where('status', 'faulty')
                ->orWhere('status', 'maintenance');
        })
            ->orWhereHas('alerts', function ($query) {
                $query->where('status', 'active')
                    ->where('type', 'low_production');
            })
            ->with(['solarSystem', 'alerts'])
            ->paginate(20);

        return view('technician.maintenance', compact('panels'));
    }

    /**
     * Update panel status (for technicians)
     */
    public function updatePanelStatus(Request $request, Panel $panel)
    {
        $user = Auth::user();

        if (! $user->isTechnician() && ! $user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,faulty,maintenance'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $panel->update($validated);

        return redirect()->back()
            ->with('success', 'Panel status updated successfully.');
    }

    /**
     * Complete an intervention
     */
    public function completeIntervention(Request $request, Intervention $intervention)
    {
        $user = Auth::user();

        if (($user->id !== $intervention->technician_id) && ! $user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'parts_replaced' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $intervention->complete($validated['notes'] ?? null, $validated['duration_minutes']);

        return redirect()->route('technician.dashboard')
            ->with('success', 'Intervention completed successfully.');
    }
}
