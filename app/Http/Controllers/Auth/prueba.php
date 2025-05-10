<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class prueba extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()->cannot('create.post')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Lógica para crear un post
        return response()->json(['message' => 'Post created successfully.']);
    }

    public function edit(Request $request, $id)
    {
        if ($request->user()->cannot('edit.post')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Lógica para editar un post
        return response()->json(['message' => 'Post updated successfully.']);
    }

    public function delete(Request $request, $id)
    {
        if ($request->user()->cannot('delete.post')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Lógica para eliminar un post
        return response()->json(['message' => 'Post deleted successfully.']);
    }
}
