?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Member;

class ApplyController extends Controller
{
    public function store(Request $request)
    {
        // VALIDASI INPUT
        $validated = $request->validate([
            'type' => 'required|in:individual,group',
            'department_id' => 'nullable|exists:departments,id',

            'leader_name' => 'required|string|max:255',
            'leader_email' => 'required|email',
            'leader_phone' => 'required|string|max:20',

            'university' => 'required|string|max:255',
            'major' => 'required|string|max:255',

            'duration' => 'required|numeric|max:5',
            'period' => 'required|date',

            'file' => 'required|mimes:pdf|max:5120', // 5MB
        ]);
    }
}
