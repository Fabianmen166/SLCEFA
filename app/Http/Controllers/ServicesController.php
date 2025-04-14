<?php

namespace App\Http\Controllers;

use App\Models\Service; // Updated to use Services model
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize the query
        $query = Service::query();

        // Filter by description
        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        // Get the filtered services
        $services = $query->get();

        // Debug: Log the services to verify data
 

        return view('listas', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('formus');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'acreditado' => 'boolean',
        ]);

        $service = new Service();
        $service->descripcion = $request->input('descripcion');
        $service->precio = $request->input('precio');
        $service->acreditado = $request->has('acreditado') ? 1 : 0;
        $service->save();

        return redirect()->route('listas')->with('success', 'Service registered successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'acreditado' => 'boolean',
        ]);

        $service->descripcion = $request->input('descripcion');
        $service->precio = $request->input('precio');
        $service->acreditado = $request->has('acreditado') ? 1 : 0;
        $service->save();

        return redirect()->route('listas')->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return redirect()->route('listas')->with('success', 'Service deleted successfully.');
    }
}