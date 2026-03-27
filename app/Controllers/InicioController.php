<?php

require_once __DIR__ . '/../Models/LibrosModel.php';
require_once __DIR__ . '/../Models/TesisModel.php';
require_once __DIR__ . '/../Models/PublicacionModel.php';

class InicioController
{
    public function index()
    {
        // Cargar modelos
        $librosModel = new LibrosModel();
        $tesisModel = new TesisModel();
        $publicacionesModel = new PublicacionModel();

        // Obtener datos desde la BD
        $libros = $librosModel->getAll();
        $tesis = $tesisModel->getAll();
        $publicaciones = $publicacionesModel->getAll();

        // Pasar los datos a la vista
        require_once '../app/Views/Inicio/index.php';
    }
}
