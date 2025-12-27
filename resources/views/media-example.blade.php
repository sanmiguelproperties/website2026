@extends('layouts.app')

@section('title', 'Ejemplo Media Manager')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-soft">
        <h1 class="text-2xl font-bold mb-6">Ejemplo de Media Manager</h1>

        <div class="space-y-6">
            <!-- Input simple (un archivo) -->
            <div>
                <label class="block text-sm font-medium mb-2">Imagen de perfil (única)</label>
                <x-media-input
                    name="profile_image_id"
                    mode="single"
                    placeholder="Selecciona una imagen de perfil"
                    button="Elegir imagen"
                />
            </div>

            <!-- Input múltiple (varios archivos) -->
            <div>
                <label class="block text-sm font-medium mb-2">Galería de imágenes (máximo 6)</label>
                <x-media-input
                    name="gallery_ids"
                    mode="multiple"
                    :max="6"
                    placeholder="Selecciona hasta 6 imágenes"
                    button="Seleccionar imágenes"
                />
            </div>

            <!-- Input con preview personalizado -->
            <div>
                <label class="block text-sm font-medium mb-2">Archivos adjuntos (máximo 10, 4 columnas)</label>
                <x-media-input
                    name="attachments[]"
                    mode="multiple"
                    :max="10"
                    :columns="4"
                    preview="true"
                    button="Seleccionar archivos"
                />
            </div>

            <!-- Formulario de ejemplo -->
            <form method="POST" action="#" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-2">Título</label>
                    <input type="text" name="title" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-slate-800" placeholder="Ingresa un título">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Descripción</label>
                    <textarea name="description" rows="4" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-slate-800" placeholder="Ingresa una descripción"></textarea>
                </div>

                <!-- Media inputs dentro del formulario -->
                <div>
                    <label class="block text-sm font-medium mb-2">Imagen destacada</label>
                    <x-media-input
                        name="featured_image_id"
                        mode="single"
                        placeholder="Selecciona imagen destacada"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Archivos adjuntos</label>
                    <x-media-input
                        name="file_ids[]"
                        mode="multiple"
                        :max="5"
                        placeholder="Selecciona archivos adjuntos"
                    />
                </div>

                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Guardar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection