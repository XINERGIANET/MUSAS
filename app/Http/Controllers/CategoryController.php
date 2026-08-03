<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $category = ProductCategory::with('category')->where('estado', 0)->paginate(10);
        $type = Category::where('estado', 0)->get();
        return view('category.index', compact('category', 'type'));
    }

    public function create()
    {
        $type = Category::where('estado', 0)->get();
        return view('category.create', compact('type'));
    }

    // Guardar
    public function store(Request $request)
    {
        $validatedData = $this->validateCategory($request);

        $validatedData['nombre'] = strtoupper($validatedData['nombre']);

        ProductCategory::create($validatedData);

        return redirect()->route('category.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function show($id)
    {
        // Obtener el registro por ID
        $category = ProductCategory::with('category')->findOrFail($id);
        return response()->json($category);
    }

    public function edit($id)
    {
        // Obtener el registro por ID para editarlo
        $category = ProductCategory::with('category')->findOrFail($id);
        return view('category.edit', compact('category'));
    }


    // Actualizar
    public function update(Request $request, $id)
    {
        $validatedData = $this->validateCategory($request);

        $validatedData['nombre'] = strtoupper($validatedData['nombre']);

        $category = ProductCategory::findOrFail($id);
        $category->update($validatedData);

        return redirect()->route('category.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    // Eliminar
    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->update(['estado' => 1]);

        return redirect()->route('category.index')
            ->with('success', 'Categoria eliminada exitosamente.');
    }

    protected function validateCategory(Request $request)
    {
        // Validar los campos del formulario
        $request->merge([
            'nombre' => trim($request->input('nombre')),
        ]);
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:category,id',
        ]);
    }
}