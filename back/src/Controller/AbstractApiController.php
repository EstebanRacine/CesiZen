<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;


abstract class AbstractApiController extends AbstractController
{
    /**
     * Extrait les données d'une requête en gérant automatiquement JSON et multipart/form-data
     * 
     * @param Request $request
     * @param array $fields Les champs à extraire
     * @return array
     */
    protected function extractRequestData(Request $request, array $fields): array
    {
        $data = [];
        $contentType = $request->headers->get('Content-Type', '');
        
        if (str_contains($contentType, 'multipart/form-data')) {
            // Pour les requêtes multipart/form-data, utiliser $request->request
            foreach ($fields as $field) {
                $data[$field] = $request->request->get($field);
            }
        } else {
            // Pour les requêtes JSON classiques
            $jsonData = json_decode($request->getContent(), true) ?? [];
            foreach ($fields as $field) {
                $data[$field] = $jsonData[$field] ?? null;
            }
        }
        
        return $data;
    }
    
    /**
     * Vérifie si une requête est de type multipart/form-data
     * 
     * @param Request $request
     * @return bool
     */
    protected function isMultipartRequest(Request $request): bool
    {
        $contentType = $request->headers->get('Content-Type', '');
        return str_contains($contentType, 'multipart/form-data');
    }

    /**
     * Extrait toutes les données d'une requête
     * 
     * @param Request $request
     * @return array
     */
    protected function extractAllRequestData(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type', '');
        
        if (str_contains($contentType, 'multipart/form-data')) {
            // Pour les requêtes multipart/form-data
            return $request->request->all();
        } else {
            // Pour les requêtes JSON classiques
            return json_decode($request->getContent(), true) ?? [];
        }
    }
}
