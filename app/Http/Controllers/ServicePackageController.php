<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServicePackageController extends Controller
{
    public function index()
    {
        try {
            $servicePackages = ServicePackage::all();
            foreach ($servicePackages as $servicePackage) {
                if (!empty($servicePackage->getRawOriginal('included_services'))) {
                    $servicePackage->services = Service::whereIn('services_id', json_decode($servicePackage->getRawOriginal('included_services'), true) ?? [])->get();
                } else {
                    $servicePackage->services = collect();
                }
            }
            return view('service_packages.index', compact('servicePackages'));
        } catch (\Exception $e) {
            Log::error('Error al listar paquetes de servicios: ' . $e->getMessage());
            $error = 'Error al cargar la lista de paquetes de servicios: ' . $e->getMessage();
            return view('service_packages.index', compact('error'));
        }
    }

    public function create()
    {
        $services = Service::all();
        return view('service_packages.create', compact('services'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|unique:service_packages,nombre',
                'precio' => 'required|numeric|min:0',
                'acreditado' => 'boolean',
                'services' => 'nullable|array',
                'services.*' => 'exists:services,services_id',
            ]);

            $servicePackage = new ServicePackage();
            $servicePackage->nombre = $validated['nombre'];
            $servicePackage->precio = $validated['precio'];
            $servicePackage->acreditado = $request->has('acreditado');
            $servicePackage->included_services = $validated['services'] ?? [];
            $servicePackage->save();

            $success = 'Paquete de servicios creado exitosamente.';
            return redirect()->route('service_packages.index')->with('success', $success);
        } catch (\Exception $e) {
            Log::error('Error al crear paquete de servicios: ' . $e->getMessage());
            $error = 'Error al crear el paquete de servicios: ' . $e->getMessage();
            return view('service_packages.create', compact('error'));
        }
    }

    public function edit($id)
    {
        try {
            $servicePackage = ServicePackage::findOrFail($id);
            $services = Service::all();
            return view('service_packages.edit', compact('servicePackage', 'services'));
        } catch (\Exception $e) {
            Log::error('Error al cargar paquete de servicios para edición: ' . $e->getMessage());
            $error = 'Error al cargar el paquete de servicios para edición: ' . $e->getMessage();
            return view('service_packages.index', compact('error'));
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $servicePackage = ServicePackage::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|unique:service_packages,nombre,' . $id . ',service_packages_id',
                'precio' => 'required|numeric|min:0',
                'acreditado' => 'boolean',
                'included_services' => 'nullable|array',
                'included_services.*' => 'exists:services,services_id',
            ]);

            $servicePackage->nombre = $validated['nombre'];
            $servicePackage->precio = $validated['precio'];
            $servicePackage->acreditado = $request->has('acreditado');
            $servicePackage->included_services = $validated['included_services'] ?? [];
            $servicePackage->save();

            $success = 'Paquete de servicios actualizado exitosamente.';
            return redirect()->route('service_packages.index')->with('success', $success);
        } catch (\Exception $e) {
            Log::error('Error al actualizar paquete de servicios: ' . $e->getMessage());
            $error = 'Error al actualizar el paquete de servicios: ' . $e->getMessage();
            return view('service_packages.edit', compact('error', 'servicePackage'));
        }
    }

    public function destroy($id)
    {
        try {
            $servicePackage = ServicePackage::findOrFail($id);
            $servicePackage->delete();
            $success = 'Paquete de servicios eliminado exitosamente.';
            return redirect()->route('service_packages.index')->with('success', $success);
        } catch (\Exception $e) {
            Log::error('Error al eliminar paquete de servicios: ' . $e->getMessage());
            $error = 'Error al eliminar el paquete de servicios: ' . $e->getMessage();
            return view('service_packages.index', compact('error'));
        }
    }
}